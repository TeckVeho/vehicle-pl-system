import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { requireRole, ROLES } from "../lib/auth.js";
import { runLocationExpenseAllocation } from "../lib/location-expense-allocation.js";
import { LOCATION_EXPENSE_PRORATION_CODES } from "../lib/location-expense-proration.js";
import { esc, generateCuid } from "../lib/sql-utils.js";

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

      // --- Batch fetch: account items (1 query) ---
      const validCodes = new Set(LOCATION_EXPENSE_PRORATION_CODES);
      const accountItems = await prisma.accountItem.findMany({
        where: { code: { in: Array.from(validCodes) } },
        select: { id: true, code: true },
      });
      const accountByCode = new Map(accountItems.map((a) => [a.code, a]));

      // --- Batch fetch: all locations by code (1 query instead of N) ---
      const lookupCodes = new Set<string>();
      for (const e of expenses) {
        if (!e.locationId && (e.departmentId || e.locationCode)) {
          lookupCodes.add(String(e.departmentId ?? e.locationCode));
        }
      }
      const locationsByCode = new Map<string, string>();
      if (lookupCodes.size > 0) {
        const locations = await prisma.location.findMany({
          where: { code: { in: Array.from(lookupCodes) } },
          select: { id: true, code: true },
        });
        for (const loc of locations) {
          locationsByCode.set(loc.code, loc.id);
        }
      }

      // --- Resolve all rows to (locationId, accountItemId, amount) ---
      const errors: string[] = [];
      const rows: Array<{ locationId: string; accountItemId: string; amount: number }> = [];

      for (const e of expenses) {
        const { locationId, departmentId, locationCode, accountItemCode, amount } = e;

        let locId: string | null = locationId ? String(locationId) : null;
        if (!locId && (departmentId || locationCode)) {
          const code = String(departmentId ?? locationCode);
          locId = locationsByCode.get(code) ?? null;
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

        rows.push({ locationId: locId, accountItemId: accountItem.id, amount: amountVal });
      }

      // --- Bulk upsert via raw SQL (1 query per chunk instead of N upserts) ---
      let upserted = 0;
      const CHUNK_SIZE = 500;
      const now = new Date().toISOString().slice(0, 19).replace("T", " ");

      for (let i = 0; i < rows.length; i += CHUNK_SIZE) {
        const chunk = rows.slice(i, i + CHUNK_SIZE);
        const values = chunk
          .map(
            (r) =>
              `(${esc(generateCuid())}, ${esc(r.locationId)}, ${esc(r.accountItemId)}, ${esc(yearMonth)}, ${r.amount}, '${now}', '${now}')`
          )
          .join(",\n");

        await prisma.$executeRawUnsafe(`
          INSERT INTO LocationMonthlyExpense (id, locationId, accountItemId, yearMonth, amount, createdAt, updatedAt)
          VALUES ${values}
          ON DUPLICATE KEY UPDATE amount = VALUES(amount), updatedAt = VALUES(updatedAt)
        `);
        upserted += chunk.length;
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

