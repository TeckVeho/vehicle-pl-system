import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import {
  REVENUE_CATEGORY,
  EXPENSE_CATEGORY,
  calcNetRevenue,
  calcTotalExpense,
} from "../lib/calc.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";
import {
  isVehicleCostAccount,
  getVehicleCostAmount,
  getFuelCostAmount,
  getRoadUsageCostAmount,
} from "../lib/vehicle-costs.js";
import { getPreviousYearMonth } from "../lib/salary-daily-proration.js";
import { isLocationExpenseProrationAccount } from "../lib/location-expense-proration.js";
import { getRevenueFromSpreadsheets } from "../lib/spreadsheet-revenue.js";

/** 手入力専用（スプレッドシート対象外・MonthlyRecord から取得） */
const MANUAL_INPUT_ONLY_NAMES = ["その他", "不動産収入", "人材派遣収入"];

export const dashboardRouter = Router();

dashboardRouter.get("/summary", async (req: Request, res: Response) => {
  const yearMonth = req.query.yearMonth as string;

  if (!yearMonth) {
    res.status(400).json({ error: "yearMonth is required" });
    return;
  }

  const prevYearMonth = getPreviousYearMonth(yearMonth);

  const [locations, vehicles, accountItems, records, prevMonthRecords, vehicleCosts, prevMonthVehicleCosts, locationExpenses, locationParams] =
    await Promise.all([
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
      prisma.monthlyRecord.findMany({
        where: { yearMonth: prevYearMonth },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: { yearMonth },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: { yearMonth: prevYearMonth },
      }),
      prisma.locationMonthlyExpense.findMany({
        where: { yearMonth },
        select: { locationId: true, accountItemId: true, amount: true },
      }),
      prisma.locationCalculationParameter.findMany({
        where: { yearMonth: prevYearMonth },
      }),
    ]);

  const revenueItemIds = new Set(
    accountItems.filter((a) => a.category === REVENUE_CATEGORY).map((a) => a.id)
  );
  const expenseItemIds = new Set(
    accountItems.filter((a) => a.category === EXPENSE_CATEGORY).map((a) => a.id)
  );

  // 売上科目（手入力専用以外）は MonthlyRecord を参照せずスプレッドシートのみ
  const revenueFromSpreadsheetIds = new Set(
    accountItems
      .filter(
        (a) =>
          a.category === REVENUE_CATEGORY &&
          !MANUAL_INPUT_ONLY_NAMES.includes(a.name)
      )
      .map((a) => a.id)
  );

  const recordMap = new Map<string, number>();
  let lastUpdatedAt: Date | null = null;
  for (const r of records) {
    if (revenueFromSpreadsheetIds.has(r.accountItemId)) continue;
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    if (!lastUpdatedAt || r.updatedAt > lastUpdatedAt) {
      lastUpdatedAt = r.updatedAt;
    }
  }

  // 車両月次費用（イズミクラウド連携）対象科目は VehicleMonthlyCost の値を優先
  const vehicleCostMap = new Map<
    string,
    {
      leaseDepreciation: number;
      vehicleDepreciation: number;
      vehicleLease: number;
      insuranceCost: number;
      taxCost: number;
      fuelEfficiency: number;
      roadUsageFee: number;
    }
  >();
  for (const vc of vehicleCosts) {
    vehicleCostMap.set(vc.vehicleId, {
      leaseDepreciation: Number(vc.leaseDepreciation),
      vehicleDepreciation: Number(vc.vehicleDepreciation),
      vehicleLease: Number(vc.vehicleLease),
      insuranceCost: Number(vc.insuranceCost),
      taxCost: Number(vc.taxCost),
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }
  for (const v of vehicles) {
    const cost = vehicleCostMap.get(v.id);
    for (const a of accountItems) {
      if (isVehicleCostAccount(a.code)) {
        recordMap.set(
          `${v.id}-${a.id}`,
          getVehicleCostAmount(cost ?? null, a.code)
        );
      }
    }
  }

  const prevMonthVehicleCostMap = new Map<string, { fuelEfficiency: number; roadUsageFee: number }>();
  for (const vc of prevMonthVehicleCosts) {
    prevMonthVehicleCostMap.set(vc.vehicleId, {
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }
  const locationParamMap = new Map<string, { fuelUnitPrice: number; roadUsageDiscountRate: number }>();
  for (const p of locationParams) {
    locationParamMap.set(p.locationId, {
      fuelUnitPrice: Number(p.fuelUnitPrice ?? 0),
      roadUsageDiscountRate: Number(p.roadUsageDiscountRate ?? 1),
    });
  }

  // 燃料費・道路使用料（ITP連携＋計算）は前月の VehicleMonthlyCost と拠点パラメータで算出
  for (const v of vehicles) {
    const prevCost = prevMonthVehicleCostMap.get(v.id);
    const costForCalc = prevCost
      ? {
          fuelEfficiency: prevCost.fuelEfficiency,
          roadUsageFee: prevCost.roadUsageFee,
        }
      : null;
    const locParam = locationParamMap.get(v.locationId) ?? null;
    for (const a of accountItems) {
      if (a.code === "6175") {
        recordMap.set(`${v.id}-${a.id}`, getFuelCostAmount(costForCalc, locParam));
      } else if (a.code === "6176") {
        recordMap.set(`${v.id}-${a.id}`, getRoadUsageCostAmount(costForCalc, locParam));
      }
    }
  }

  // 拠点別経費（別システム連携）対象科目は LocationMonthlyExpense を車両数で按分
  const locationIdsForDashboard = Array.from(new Set(vehicles.map((v) => v.locationId)));
  for (const locId of locationIdsForDashboard) {
    const locVehicles = vehicles.filter((v) => v.locationId === locId);
    const locVehicleCount = locVehicles.length;
    if (locVehicleCount === 0) continue;
    const locExpenses = locationExpenses.filter((e) => e.locationId === locId);
    for (const exp of locExpenses) {
      const item = accountItems.find((a) => a.id === exp.accountItemId);
      if (item && isLocationExpenseProrationAccount(item.code)) {
        const amountPerVehicle =
          Math.round((Number(exp.amount) / locVehicleCount) * 100) / 100;
        for (const v of locVehicles) {
          recordMap.set(`${v.id}-${exp.accountItemId}`, amountPerVehicle);
        }
      }
    }
  }

  // 給与系科目（乗務員給料・通勤手当）は前月分の MonthlyRecord をそのまま表示（乗車回数ベースで配賦済み）
  for (const r of prevMonthRecords) {
    const a = accountItems.find((x) => x.id === r.accountItemId);
    if (a && (a.code === "6138" || a.code === "6147")) {
      recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    }
  }

  // 売上科目（手入力専用以外）は各拠点スプレッドシート参照のみ
  const revenueAccountItemIds = Array.from(revenueFromSpreadsheetIds);
  if (revenueAccountItemIds.length > 0) {
    const locationIds = Array.from(new Set(vehicles.map((v) => v.locationId)));
    for (const locId of locationIds) {
      const locVehicleIds = vehicles
        .filter((v) => v.locationId === locId)
        .map((v) => v.id);
      const spreadsheetRevenue = await getRevenueFromSpreadsheets({
        locationId: locId,
        yearMonth,
        vehicleIds: locVehicleIds,
        revenueAccountItemIds,
      });
      spreadsheetRevenue.forEach((amount, key) => {
        recordMap.set(key, amount);
      });
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
