import { Router, Request, Response } from "express";
import { prisma } from "../lib/prisma.js";
import { accountItemEffectiveWhere } from "../lib/account-item-filter.js";
import {
  isSalaryDailyProrationAccount,
  getSalaryDailyAmount,
  getPreviousYearMonth,
} from "../lib/salary-daily-proration.js";
import { isLocationExpenseProrationAccount } from "../lib/location-expense-proration.js";
import {
  getFuelCostAmount,
  getRoadUsageCostAmount,
} from "../lib/vehicle-costs.js";
import { getRevenueFromSpreadsheets } from "../lib/spreadsheet-revenue.js";

/** 按分ロジック対象外（関東運輸より後の手入力専用科目） */
const ALLOCATION_EXCLUDED_NAMES = ["その他", "不動産収入", "人材派遣収入"];

/** 手入力専用（スプレッドシート対象外・MonthlyRecord から取得） */
const MANUAL_INPUT_ONLY_NAMES = ["その他", "不動産収入", "人材派遣収入"];

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
  const daysInMonth = getDaysInMonth(yearMonth);

  if (vehicleIds.length === 0) {
    res.json({
      vehicles: [],
      dailyAmountByVehicleByDay: {},
      monthlyTotalByVehicle: {},
      daysInMonth,
      yearMonth,
    });
    return;
  }

  const prevYearMonth = getPreviousYearMonth(yearMonth);

  const locationIdsForSummary = Array.from(new Set(vehicles.map((v) => v.locationId)));
  const [records, prevMonthRecords, accountItems, dailyOperating, locationExpenses, prevMonthVehicleCosts, locationParams] =
    await Promise.all([
      prisma.monthlyRecord.findMany({
        where: {
          yearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.monthlyRecord.findMany({
        where: {
          yearMonth: prevYearMonth,
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
      prisma.dailyOperatingRecord.findMany({
        where: {
          yearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.locationMonthlyExpense.findMany({
        where: {
          yearMonth,
          locationId: { in: locationIdsForSummary },
        },
        select: { locationId: true, accountItemId: true, amount: true },
      }),
      prisma.vehicleMonthlyCost.findMany({
        where: {
          yearMonth: prevYearMonth,
          vehicleId: { in: vehicleIds },
        },
      }),
      prisma.locationCalculationParameter.findMany({
        where: {
          yearMonth: prevYearMonth,
          locationId: { in: locationIdsForSummary },
        },
      }),
    ]);

  const validAccountItemIds = new Set(accountItems.map((a) => a.id));
  // 按分対象: 関東運輸まで（その他・不動産収入・人材派遣収入は対象外）
  const revenueItems = accountItems.filter(
    (a) => a.category === "revenue" && !ALLOCATION_EXCLUDED_NAMES.includes(a.name)
  );
  // 売上科目（山崎製パン〜関東運輸）は MonthlyRecord を参照せずスプレッドシートのみ。その他・不動産・人材派遣は手入力で MonthlyRecord
  const revenueFromSpreadsheetIds = new Set(revenueItems.map((a) => a.id));

  // 月次合計（全勘定科目）は recordMap 構築後に再計算
  const monthlyTotalByVehicle: Record<string, number> = {};
  for (const v of vehicles) {
    monthlyTotalByVehicle[v.id] = 0;
  }

  // 日次稼働データを Map 化: vehicleId -> date -> { runCount, isOperating }
  const dailyByVehicle = new Map<
    string,
    Map<string, { runCount: number; isOperating: boolean }>
  >();
  for (const d of dailyOperating) {
    let m = dailyByVehicle.get(d.vehicleId);
    if (!m) {
      m = new Map();
      dailyByVehicle.set(d.vehicleId, m);
    }
    m.set(d.date, {
      runCount: d.runCount,
      isOperating: d.isOperating,
    });
  }

  // 車両×勘定科目ごとの月次金額（売上・手入力専用以外は MonthlyRecord から取得）
  const recordMap = new Map<string, number>();
  for (const r of records) {
    if (!validAccountItemIds.has(r.accountItemId)) continue;
    if (revenueFromSpreadsheetIds.has(r.accountItemId)) continue;
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
  }

  // 給与系科目（乗務員給料・通勤手当）は前月分のデータを使用
  const salaryItems = accountItems.filter((a) =>
    isSalaryDailyProrationAccount(a.code)
  );
  for (const r of prevMonthRecords) {
    const item = salaryItems.find((a) => a.id === r.accountItemId);
    if (item) {
      recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
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
    const fuelItem = accountItems.find((a) => a.code === "6175");
    const roadItem = accountItems.find((a) => a.code === "6176");
    if (fuelItem) {
      recordMap.set(`${v.id}-${fuelItem.id}`, getFuelCostAmount(costForCalc, locParam));
    }
    if (roadItem) {
      recordMap.set(`${v.id}-${roadItem.id}`, getRoadUsageCostAmount(costForCalc, locParam));
    }
  }

  // 拠点別経費（別システム連携）対象科目は LocationMonthlyExpense を車両数で按分
  for (const locId of locationIdsForSummary) {
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

  // スプレッドシート反映後の月次合計を再計算
  for (const v of vehicles) {
    let sum = 0;
    for (const item of accountItems) {
      if (!validAccountItemIds.has(item.id)) continue;
      sum += recordMap.get(`${v.id}-${item.id}`) ?? 0;
    }
    monthlyTotalByVehicle[v.id] = sum;
  }

  // 日付別・車両別の日次売上を計算
  // dailyAmountByVehicleByDay[vehicleId][day] = その日の売上
  const dailyAmountByVehicleByDay: Record<string, Record<number, number>> = {};
  for (const v of vehicles) {
    dailyAmountByVehicleByDay[v.id] = {};
    for (let day = 1; day <= daysInMonth; day++) {
      dailyAmountByVehicleByDay[v.id][day] = 0;
    }
  }

  for (const v of vehicles) {
    const dailyMap = dailyByVehicle.get(v.id);
    const vehicleDaily = dailyAmountByVehicleByDay[v.id];

    for (const item of revenueItems) {
      const monthlyAmount =
        recordMap.get(`${v.id}-${item.id}`) ?? 0;
      if (monthlyAmount === 0) continue;

      if (item.revenuePricingType === "per_run") {
        // 回数単価: その日の runCount × 単価
        // 単価 = 月次合計 / 月間走行回数（月間走行回数が0なら均等割り）
        const monthRuns =
          dailyMap &&
          Array.from(dailyMap.values()).reduce(
            (s: number, x: { runCount: number }) => s + x.runCount,
            0
          );
        const unitPrice =
          monthRuns && monthRuns > 0 ? monthlyAmount / monthRuns : 0;

        if (dailyMap) {
          for (const [dateStr, { runCount }] of Array.from(dailyMap.entries())) {
            const day = parseInt(dateStr.slice(8, 10), 10);
            if (day >= 1 && day <= daysInMonth) {
              vehicleDaily[day] =
                (vehicleDaily[day] ?? 0) + runCount * unitPrice;
            }
          }
        } else {
          // 稼働データなし: 均等割り
          const perDay = monthlyAmount / daysInMonth;
          for (let day = 1; day <= daysInMonth; day++) {
            vehicleDaily[day] = (vehicleDaily[day] ?? 0) + perDay;
          }
        }
      } else if (item.revenuePricingType === "monthly") {
        // 月額単価: 稼働日のみ日割り（月次合計 / 稼働日数）
        const operatingDays =
          dailyMap &&
          Array.from(dailyMap.values()).filter((x) => x.isOperating).length;
        const dailyAmount =
          operatingDays && operatingDays > 0
            ? monthlyAmount / operatingDays
            : monthlyAmount / daysInMonth;

        if (dailyMap && operatingDays && operatingDays > 0) {
          for (const [dateStr, { isOperating }] of Array.from(dailyMap.entries())) {
            if (!isOperating) continue;
            const day = parseInt(dateStr.slice(8, 10), 10);
            if (day >= 1 && day <= daysInMonth) {
              vehicleDaily[day] = (vehicleDaily[day] ?? 0) + dailyAmount;
            }
          }
        } else {
          // 稼働データなし: 均等割り
          const perDay = monthlyAmount / daysInMonth;
          for (let day = 1; day <= daysInMonth; day++) {
            vehicleDaily[day] = (vehicleDaily[day] ?? 0) + perDay;
          }
        }
      } else {
        // null: 均等割り（従来どおり）
        const perDay = monthlyAmount / daysInMonth;
        for (let day = 1; day <= daysInMonth; day++) {
          vehicleDaily[day] = (vehicleDaily[day] ?? 0) + perDay;
        }
      }
    }

    // 経費科目: 均等割り（給与系は乗車回数ベースで単価×乗車回数）
    for (const item of accountItems) {
      if (item.category !== "expense") continue;
      const monthlyAmount = recordMap.get(`${v.id}-${item.id}`) ?? 0;
      if (monthlyAmount === 0) continue;
      if (isSalaryDailyProrationAccount(item.code)) {
        const totalRunCount =
          dailyMap &&
          Array.from(dailyMap.values()).reduce((s, x) => s + x.runCount, 0);
        for (let day = 1; day <= daysInMonth; day++) {
          const dateStr = `${yearMonth}-${String(day).padStart(2, "0")}`;
          const runCount = dailyMap?.get(dateStr)?.runCount ?? 0;
          const dayAmount = getSalaryDailyAmount(
            monthlyAmount,
            totalRunCount ?? 0,
            runCount,
            daysInMonth
          );
          vehicleDaily[day] = (vehicleDaily[day] ?? 0) + dayAmount;
        }
      } else {
        const perDay = monthlyAmount / daysInMonth;
        for (let day = 1; day <= daysInMonth; day++) {
          vehicleDaily[day] = (vehicleDaily[day] ?? 0) + perDay;
        }
      }
    }
  }

  // 月計は日次合計と一致させる（給与系は前月日数で按分するため）
  for (const v of vehicles) {
    let sum = 0;
    for (let day = 1; day <= daysInMonth; day++) {
      sum += dailyAmountByVehicleByDay[v.id]?.[day] ?? 0;
    }
    monthlyTotalByVehicle[v.id] = sum;
  }

  res.json({
    vehicles,
    dailyAmountByVehicleByDay,
    monthlyTotalByVehicle,
    daysInMonth,
    yearMonth,
  });
});
