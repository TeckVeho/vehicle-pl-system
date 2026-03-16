import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import {
  REVENUE_CATEGORY,
  EXPENSE_CATEGORY,
  calcNetRevenue,
  calcTotalExpense,
} from "../lib/calc.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";

export const dashboardRouter = Router();

dashboardRouter.get("/summary", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const [locations, vehicles, accountItems, records] = await Promise.all([
    prisma.location.findMany({ orderBy: { code: "asc" } }),
    prisma.vehicle.findMany({
      select: {
        id: true,
        locationId: true,
        vehicleNo: true,
        serviceType: true,
        createdAt: true,
        updatedAt: true,
        location: true,
        course: { select: { id: true, name: true, code: true } },
      },
      orderBy: [{ locationId: "asc" }, { vehicleNo: "asc" }],
    }),
    prisma.accountItem.findMany({
      where: accountItemEffectiveWhere(yearMonth),
      orderBy: { sortOrder: "asc" },
    }),
    prisma.monthlyRecord.findMany({
      where: { yearMonth },
      include: { vehicle: true, accountItem: true },
    }),
  ]);

  const revenueItemIds = new Set(
    accountItems.filter((a) => a.category === REVENUE_CATEGORY).map((a) => a.id)
  );
  const expenseItemIds = new Set(
    accountItems.filter((a) => a.category === EXPENSE_CATEGORY).map((a) => a.id)
  );

  const recordMap = new Map<string, number>();
  let lastUpdatedAt: Date | null = null;
  for (const r of records) {
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    if (!lastUpdatedAt || r.updatedAt > lastUpdatedAt) {
      lastUpdatedAt = r.updatedAt;
    }
  }

  const getVehicleAmounts = (vehicleId: string) => {
    const amountByItem = new Map<string, number>();
    for (const item of accountItems) {
      const amt = recordMap.get(`${vehicleId}-${item.id}`) ?? 0;
      amountByItem.set(item.id, amt);
    }
    const netRevenue = calcNetRevenue(amountByItem, revenueItemIds);
    const totalExpense = calcTotalExpense(amountByItem, expenseItemIds);
    return { netRevenue, totalExpense };
  };

  const locationSummaries = locations.map((loc) => {
    const locVehicles = vehicles.filter((v) => v.locationId === loc.id);
    let netRevenue = 0;
    let totalExpense = 0;
    for (const v of locVehicles) {
      const { netRevenue: nr, totalExpense: te } = getVehicleAmounts(v.id);
      netRevenue += nr;
      totalExpense += te;
    }
    const grossProfit = netRevenue - totalExpense;
    return {
      locationId: loc.id,
      locationCode: loc.code,
      locationName: loc.name,
      vehicleCount: locVehicles.length,
      netRevenue,
      totalExpense,
      grossProfit,
    };
  });

  const totalNetRevenue = locationSummaries.reduce((s, l) => s + l.netRevenue, 0);
  const totalExpense = locationSummaries.reduce((s, l) => s + l.totalExpense, 0);
  const totalGrossProfit = totalNetRevenue - totalExpense;

  res.json({
    yearMonth,
    lastUpdatedAt: lastUpdatedAt ? lastUpdatedAt.toISOString() : null,
    summary: {
      totalNetRevenue,
      totalExpense,
      totalGrossProfit,
      totalVehicleCount: vehicles.length,
      locationCount: locations.length,
    },
    locationSummaries,
  });
});
