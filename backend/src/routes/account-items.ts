import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";
import { requireRole, ROLES } from "../lib/auth.js";

export const accountItemsRouter = Router();

accountItemsRouter.get("/", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string | undefined;

  const accountItems = await prisma.accountItem.findMany({
    orderBy: { sortOrder: "asc" },
    where: yearMonth ? accountItemEffectiveWhere(yearMonth) : undefined,
  });
  res.json(accountItems);
});

accountItemsRouter.post("/", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const {
      code,
      name,
      category,
      sortOrder,
      isSubtotal,
      isVehicleRelated,
      isDriverRelated,
      revenuePricingType,
      linkageMethod,
      effectiveFrom,
      effectiveTo,
    } = req.body;

    if (!code || !name || !category) {
      res.status(400).json({ error: "code, name, category are required" });
      return;
    }

    const maxSort = await prisma.accountItem.aggregate({
      _max: { sortOrder: true },
    });
    const nextSort = (maxSort._max.sortOrder ?? 0) + 1;

    const accountItem = await prisma.accountItem.create({
      data: {
        code: String(code),
        name: String(name),
        category: String(category),
        sortOrder: sortOrder ?? nextSort,
        isSubtotal: Boolean(isSubtotal ?? false),
        isVehicleRelated: Boolean(isVehicleRelated ?? false),
        isDriverRelated: Boolean(isDriverRelated ?? false),
        revenuePricingType:
          revenuePricingType && ["per_run", "monthly"].includes(String(revenuePricingType))
            ? String(revenuePricingType)
            : null,
        linkageMethod:
          linkageMethod && String(linkageMethod).trim()
            ? String(linkageMethod).trim()
            : null,
        effectiveFrom:
          effectiveFrom && String(effectiveFrom).trim()
            ? String(effectiveFrom).trim()
            : null,
        effectiveTo:
          effectiveTo && String(effectiveTo).trim()
            ? String(effectiveTo).trim()
            : null,
      },
    });
    res.json(accountItem);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to create account item" });
  }
});

accountItemsRouter.put("/:id", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const {
      code,
      name,
      category,
      sortOrder,
      isSubtotal,
      isVehicleRelated,
      isDriverRelated,
      revenuePricingType,
      linkageMethod,
      effectiveFrom,
      effectiveTo,
    } = req.body;

    const accountItem = await prisma.accountItem.update({
      where: { id },
      data: {
        ...(code !== undefined && { code: String(code) }),
        ...(name !== undefined && { name: String(name) }),
        ...(category !== undefined && { category: String(category) }),
        ...(sortOrder !== undefined && { sortOrder: Number(sortOrder) }),
        ...(isSubtotal !== undefined && { isSubtotal: Boolean(isSubtotal) }),
        ...(isVehicleRelated !== undefined && { isVehicleRelated: Boolean(isVehicleRelated) }),
        ...(isDriverRelated !== undefined && { isDriverRelated: Boolean(isDriverRelated) }),
        ...(revenuePricingType !== undefined && {
          revenuePricingType:
            revenuePricingType && ["per_run", "monthly"].includes(String(revenuePricingType))
              ? String(revenuePricingType)
              : null,
        }),
        ...(linkageMethod !== undefined && {
          linkageMethod:
            linkageMethod && String(linkageMethod).trim()
              ? String(linkageMethod).trim()
              : null,
        }),
        ...(effectiveFrom !== undefined && {
          effectiveFrom:
            effectiveFrom && String(effectiveFrom).trim()
              ? String(effectiveFrom).trim()
              : null,
        }),
        ...(effectiveTo !== undefined && {
          effectiveTo:
            effectiveTo && String(effectiveTo).trim()
              ? String(effectiveTo).trim()
              : null,
        }),
      },
    });
    res.json(accountItem);
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to update account item" });
  }
});

accountItemsRouter.delete("/:id", requireRole(ROLES.MASTER), async (req: Request, res: Response) => {
  try {
    const { id } = req.params;

    const [monthlyCount, driverAmountCount] = await Promise.all([
      prisma.monthlyRecord.count({ where: { accountItemId: id } }),
      prisma.driverMonthlyAmount.count({ where: { accountItemId: id } }),
    ]);
    if (monthlyCount > 0 || driverAmountCount > 0) {
      res.status(400).json({
        error: `この勘定科目は月次データ${monthlyCount}件、ドライバー配賦データ${driverAmountCount}件で使用されています。削除するには先に該当データを削除してください。`,
      });
      return;
    }

    await prisma.accountItem.delete({
      where: { id },
    });
    res.json({ success: true });
  } catch (e) {
    console.error(e);
    res.status(500).json({ error: "Failed to delete account item" });
  }
});
