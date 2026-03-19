import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";
import { runLocationExpenseAllocation } from "../lib/location-expense-allocation.js";
import { LOCATION_EXPENSE_PRORATION_CODES } from "../lib/location-expense-proration.js";

export const locationMonthlyExpensesRouter = Router();

/**
 * 外部システム連携用: 拠点別月額経費の一括同期（upsert）
 * 連携後、車両数で按分して MonthlyRecord を更新する。
 *
 * POST /api/location-monthly-expenses/sync
 * Body: {
 *   yearMonth: "2026-03",
 *   expenses: [
 *     {
 *       locationId?: string,
 *       departmentId?: string,  // Location.code（イズミクラウドの部門ID）
 *       accountItemCode: "6150",
 *       amount: 100000
 *     },
 *     ...
 *   ]
 * }
 */
locationMonthlyExpensesRouter.post(
  "/sync",
  requireRole(ROLES.MASTER),
  async (req: Request, res: Response) => {
    try {
      const { yearMonth, expenses } = req.body as {
        yearMonth: string;
        expenses: Array<{
          locationId?: string;
          departmentId?: string;
          locationCode?: string;
          accountItemCode: string;
          amount: number;
        }>;
      };

      if (!yearMonth || !/^\d{4}-\d{2}$/.test(yearMonth)) {
        res.status(400).json({ error: "yearMonth (YYYY-MM) is required" });
        return;
      }

      if (!Array.isArray(expenses)) {
        res.status(400).json({ error: "expenses array is required" });
        return;
      }

      const validCodes = new Set(LOCATION_EXPENSE_PRORATION_CODES);
      const accountItems = await prisma.accountItem.findMany({
        where: { code: { in: Array.from(validCodes) } },
        select: { id: true, code: true },
      });
      const accountByCode = new Map(accountItems.map((a) => [a.code, a]));

      let upserted = 0;
      const errors: string[] = [];

      for (const e of expenses) {
        const { locationId, departmentId, locationCode, accountItemCode, amount } = e;

        let locId: string | null = locationId ? String(locationId) : null;
        if (!locId && (departmentId || locationCode)) {
          const code = departmentId ?? locationCode;
          const loc = await prisma.location.findUnique({
            where: { code: String(code) },
            select: { id: true },
          });
          locId = loc?.id ?? null;
        }

        const accountItem = accountByCode.get(String(accountItemCode));
        if (!accountItem) {
          errors.push(
            `Account item not found or not proration target: ${accountItemCode}`
          );
          continue;
        }

        if (!locId) {
          errors.push(`Location not found for departmentId/locationCode: ${departmentId ?? locationCode}`);
          continue;
        }

        const amountVal =
          typeof amount === "number" ? amount : parseFloat(String(amount)) || 0;

        await prisma.locationMonthlyExpense.upsert({
          where: {
            locationId_accountItemId_yearMonth: {
              locationId: locId,
              accountItemId: accountItem.id,
              yearMonth,
            },
          },
          create: {
            locationId: locId,
            accountItemId: accountItem.id,
            yearMonth,
            amount: amountVal,
          },
          update: { amount: amountVal },
        });
        upserted++;
      }

      const allocationResult = await runLocationExpenseAllocation(yearMonth);

      await prisma.dataSyncLog.create({
        data: {
          source: "拠点別経費連携",
          syncType: "location_monthly_expenses",
          recordCount: upserted,
          yearMonth,
        },
      });

      res.status(200).json({
        success: true,
        upserted,
        allocation: allocationResult,
        ...(errors.length > 0 && { errors }),
      });
    } catch (err) {
      console.error(err);
      res.status(500).json({
        error: "Failed to sync location monthly expenses",
      });
    }
  }
);
