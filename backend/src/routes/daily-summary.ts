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

  const [records, accountItems, dailyOperating] = await Promise.all([
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
    prisma.dailyOperatingRecord.findMany({
      where: {
        yearMonth,
        vehicleId: { in: vehicleIds },
      },
    }),
  ]);

  const validAccountItemIds = new Set(accountItems.map((a) => a.id));
  const revenueItems = accountItems.filter((a) => a.category === "revenue");
  // 月次合計（全勘定科目）
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

  // 車両×勘定科目ごとの月次金額
  const recordMap = new Map<string, number>();
  for (const r of records) {
    if (!validAccountItemIds.has(r.accountItemId)) continue;
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
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

    // 経費科目: 均等割り
    for (const item of accountItems) {
      if (item.category !== "expense") continue;
      const monthlyAmount =
        recordMap.get(`${v.id}-${item.id}`) ?? 0;
      if (monthlyAmount === 0) continue;
      const perDay = monthlyAmount / daysInMonth;
      for (let day = 1; day <= daysInMonth; day++) {
        vehicleDaily[day] = (vehicleDaily[day] ?? 0) + perDay;
      }
    }
  }

  res.json({
    vehicles,
    dailyAmountByVehicleByDay,
    monthlyTotalByVehicle,
    daysInMonth,
    yearMonth,
  });
});
