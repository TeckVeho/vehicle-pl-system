/**
 * 乗務員給料・通勤手当の乗車回数ベース配賦
 *
 * - 各ドライバーの1日単価 = 月次金額 ÷ 月の日数
 * - 各 (driver, vehicle, date) について、その車両のその日の runCount × 1日単価 を累計
 * - 車両別の合計を MonthlyRecord に保存
 *
 * ※法定福利費は driver-allocation.ts の乗務日数按分で別途処理（福利厚生費は手動インポート）
 */
import { prisma } from "./prisma.js";
import { SALARY_DAILY_PRORATION_CODES } from "./salary-daily-proration.js";

function getDaysInMonth(yearMonth: string): number {
  const [year, month] = yearMonth.split("-").map(Number);
  return new Date(year, month, 0).getDate();
}

export async function runSalaryRunCountAllocation(
  yearMonth: string,
  locationId?: string | null
): Promise<{ vehiclesUpdated: number; recordsUpdated: number }> {
  const salaryItems = await prisma.accountItem.findMany({
    where: { code: { in: Array.from(SALARY_DAILY_PRORATION_CODES) } },
    select: { id: true },
  });
  const salaryItemIds = new Set(salaryItems.map((a) => a.id));
  if (salaryItemIds.size === 0) {
    return { vehiclesUpdated: 0, recordsUpdated: 0 };
  }

  const vehicles = await prisma.vehicle.findMany({
    where: locationId ? { locationId } : undefined,
    select: { id: true },
  });
  const vehicleIds = new Set(vehicles.map((v) => v.id));
  if (vehicleIds.size === 0) {
    return { vehiclesUpdated: 0, recordsUpdated: 0 };
  }

  const [assignments, driverAmounts, dailyOperating] = await Promise.all([
    prisma.dailyDriverAssignment.findMany({
      where: {
        yearMonth,
        vehicleId: { in: Array.from(vehicleIds) },
      },
      select: { driverId: true, vehicleId: true, date: true },
    }),
    prisma.driverMonthlyAmount.findMany({
      where: {
        yearMonth,
        accountItemId: { in: Array.from(salaryItemIds) },
      },
      select: { driverId: true, accountItemId: true, amount: true },
    }),
    prisma.dailyOperatingRecord.findMany({
      where: {
        yearMonth,
        vehicleId: { in: Array.from(vehicleIds) },
      },
      select: { vehicleId: true, date: true, runCount: true },
    }),
  ]);

  const daysInMonth = getDaysInMonth(yearMonth);

  // vehicleId -> date -> runCount
  const runCountByVehicleDate = new Map<string, Map<string, number>>();
  for (const d of dailyOperating) {
    let dateMap = runCountByVehicleDate.get(d.vehicleId);
    if (!dateMap) {
      dateMap = new Map();
      runCountByVehicleDate.set(d.vehicleId, dateMap);
    }
    dateMap.set(d.date, d.runCount);
  }

  // driverId -> accountItemId -> 1日単価
  const dailyUnitPriceByDriver = new Map<string, Map<string, number>>();
  for (const da of driverAmounts) {
    const amount = Number(da.amount);
    if (amount === 0) continue;
    const dailyUnitPrice = amount / daysInMonth;
    let itemMap = dailyUnitPriceByDriver.get(da.driverId);
    if (!itemMap) {
      itemMap = new Map();
      dailyUnitPriceByDriver.set(da.driverId, itemMap);
    }
    itemMap.set(da.accountItemId, dailyUnitPrice);
  }

  // vehicleId -> accountItemId -> 累計金額
  const allocatedByVehicle = new Map<string, Map<string, number>>();
  for (const vId of Array.from(vehicleIds)) {
    allocatedByVehicle.set(vId, new Map());
  }

  for (const a of assignments) {
    const runCount = runCountByVehicleDate.get(a.vehicleId)?.get(a.date) ?? 0;
    if (runCount === 0) continue;

    const driverPrices = dailyUnitPriceByDriver.get(a.driverId);
    if (!driverPrices) continue;

    const itemMap = allocatedByVehicle.get(a.vehicleId)!;
    for (const [accountItemId, dailyUnitPrice] of Array.from(driverPrices.entries())) {
      const addAmount = dailyUnitPrice * runCount;
      const prev = itemMap.get(accountItemId) ?? 0;
      itemMap.set(accountItemId, prev + addAmount);
    }
  }

  let recordsUpdated = 0;
  for (const vehicleId of Array.from(vehicleIds)) {
    const itemMap = allocatedByVehicle.get(vehicleId)!;
    for (const accountItemId of Array.from(salaryItemIds)) {
      const amount = itemMap.get(accountItemId) ?? 0;
      const rounded = Math.round(amount * 100) / 100;
      const existing = await prisma.monthlyRecord.findUnique({
        where: {
          vehicleId_accountItemId_yearMonth: {
            vehicleId,
            accountItemId,
            yearMonth,
          },
        },
      });
      const oldAmount = existing ? Number(existing.amount) : 0;
      if (oldAmount === rounded) continue;

      await prisma.monthlyRecord.upsert({
        where: {
          vehicleId_accountItemId_yearMonth: {
            vehicleId,
            accountItemId,
            yearMonth,
          },
        },
        update: { amount: rounded },
        create: {
          vehicleId,
          accountItemId,
          yearMonth,
          amount: rounded,
        },
      });
      recordsUpdated++;
    }
  }

  return {
    vehiclesUpdated: vehicles.length,
    recordsUpdated,
  };
}
