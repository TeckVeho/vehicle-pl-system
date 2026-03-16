import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";

function getDaysInMonth(yearMonth: string): number {
  const [year, month] = yearMonth.split("-").map(Number);
  return new Date(year, month, 0).getDate();
}

export const dailySummaryRouter = Router();

dailySummaryRouter.get("/", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;
  const locationId = req.query.locationId as string | undefined;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const vehicles = await prisma.vehicle.findMany({
    where: locationId && locationId !== "all" ? { locationId } : undefined,
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
  });

  const vehicleIds = vehicles.map((v) => v.id);
  if (vehicleIds.length === 0) {
    const daysInMonth = getDaysInMonth(yearMonth);
    res.json({
      vehicles: [],
      dailyAmountByVehicle: {},
      monthlyTotalByVehicle: {},
      daysInMonth,
      yearMonth,
    });
    return;
  }

  const [records, accountItems] = await Promise.all([
    prisma.monthlyRecord.findMany({
      where: {
        yearMonth,
        vehicleId: { in: vehicleIds },
      },
    }),
    prisma.accountItem.findMany({
      where: {
        AND: [
          { isSubtotal: false, category: { not: "summary" } },
          accountItemEffectiveWhere(yearMonth),
        ],
      },
    }),
  ]);

  const validAccountItemIds = new Set(accountItems.map((a) => a.id));

  const monthlyTotalByVehicle: Record<string, number> = {};
  for (const v of vehicles) {
    monthlyTotalByVehicle[v.id] = 0;
  }

  for (const r of records) {
    if (!validAccountItemIds.has(r.accountItemId)) continue;
    const amount = Number(r.amount);
    monthlyTotalByVehicle[r.vehicleId] =
      (monthlyTotalByVehicle[r.vehicleId] ?? 0) + amount;
  }

  const daysInMonth = getDaysInMonth(yearMonth);
  const dailyAmountByVehicle: Record<string, number> = {};
  for (const [vehicleId, total] of Object.entries(monthlyTotalByVehicle)) {
    dailyAmountByVehicle[vehicleId] = total / daysInMonth;
  }

  res.json({
    vehicles,
    dailyAmountByVehicle,
    monthlyTotalByVehicle,
    daysInMonth,
    yearMonth,
  });
});
