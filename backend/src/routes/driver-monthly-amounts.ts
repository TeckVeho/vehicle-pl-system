import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";
import { runDriverAllocation } from "../lib/driver-allocation.js";
import { runSalaryRunCountAllocation } from "../lib/salary-run-count-allocation.js";

export const driverMonthlyAmountsRouter = Router();

/**
 * 外部システム連携用: ドライバー別・勘定科目別・月次金額の一括同期（upsert）
 * 連携後、ドライバー配賦を再計算して MonthlyRecord を更新する。
 *
 * POST /api/driver-monthly-amounts/sync
 * Body: {
 *   yearMonth: "2026-03",
 *   locationId?: string,
 *   records: [
 *     {
 *       driverId?: string,
 *       driverExternalId?: string,
 *       accountItemId?: string,
 *       accountItemCode?: string,
 *       amount: 150000
 *     },
 *     ...
 *   ]
 * }
 */
driverMonthlyAmountsRouter.post("/sync", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { yearMonth, locationId, departmentId, records } = req.body;

    let locId: string | null = locationId ? String(locationId) : null;
    if (!locId && departmentId) {
      const loc = await prisma.location.findUnique({
        where: { code: String(departmentId) },
        select: { id: true },
      });
      locId = loc?.id ?? null;
    }

    if (!yearMonth || !Array.isArray(records)) {
      res.status(400).json({
        error: "yearMonth and records array are required",
      });
      return;
    }

    const yearMonthStr = String(yearMonth).trim();
    if (!/^\d{4}-\d{2}$/.test(yearMonthStr)) {
      res.status(400).json({ error: "yearMonth must be YYYY-MM format" });
      return;
    }

    const driverRelatedItems = await prisma.accountItem.findMany({
      where: { isDriverRelated: true },
      select: { id: true, code: true },
    });
    const accountById = new Map(driverRelatedItems.map((a) => [a.id, a]));
    const accountByCode = new Map(driverRelatedItems.map((a) => [a.code, a]));

    let upserted = 0;
    const errors: string[] = [];

    for (const r of records) {
      const { driverId, driverExternalId, accountItemId, accountItemCode, amount } = r;

      let did: string | null = null;
      if (driverId) {
        const d = await prisma.driver.findUnique({
          where: { id: String(driverId) },
          select: { id: true, locationId: true },
        });
        if (d && (!locId || d.locationId === locId)) {
          did = d.id;
        }
      }
      if (!did && driverExternalId) {
        const d = await prisma.driver.findFirst({
          where: {
            externalId: String(driverExternalId),
            ...(locId ? { locationId: locId } : {}),
          },
          select: { id: true },
        });
        if (d) did = d.id;
      }

      let aid: string | null = null;
      if (accountItemId) {
        const item = accountById.get(String(accountItemId));
        if (item) aid = item.id;
      }
      if (!aid && accountItemCode) {
        const item = accountByCode.get(String(accountItemCode));
        if (item) aid = item.id;
      }

      if (!did) {
        errors.push(`Driver not found for amount record`);
        continue;
      }
      if (!aid) {
        errors.push(`Account item not found or not driver-related: ${accountItemCode ?? accountItemId}`);
        continue;
      }

      const amountVal = typeof amount === "number" ? amount : parseFloat(String(amount)) || 0;

      await prisma.driverMonthlyAmount.upsert({
        where: {
          driverId_accountItemId_yearMonth: {
            driverId: did,
            accountItemId: aid,
            yearMonth: yearMonthStr,
          },
        },
        create: {
          driverId: did,
          accountItemId: aid,
          yearMonth: yearMonthStr,
          amount: amountVal,
        },
        update: { amount: amountVal },
      });
      upserted++;
    }

    const [allocationResult, salaryAllocationResult] = await Promise.all([
      runDriverAllocation(yearMonthStr, locId),
      runSalaryRunCountAllocation(yearMonthStr, locId),
    ]);

    await prisma.dataSyncLog.create({
      data: {
        source: "タイムシート連携",
        syncType: "driver_monthly_amounts",
        recordCount: upserted,
        yearMonth: yearMonthStr,
        locationId: locId ? String(locId) : null,
      },
    });

    res.status(200).json({
      success: true,
      upserted,
      allocation: allocationResult,
      salaryAllocation: salaryAllocationResult,
      ...(errors.length > 0 && { errors }),
    });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to sync driver monthly amounts" });
  }
});
