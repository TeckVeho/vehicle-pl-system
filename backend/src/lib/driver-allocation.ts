import { prisma } from "./prisma.js";
import { SALARY_DAILY_PRORATION_CODES } from "./salary-daily-proration.js";

/**
 * ドライバー配賦を実行し、MonthlyRecord を更新する。
 * 乗務日数按分（1日複数車両の場合はその日のウェイトを 1/車両数 で按分）
 *
 * ※乗務員給料・通勤手当は salary-run-count-allocation.ts で乗車回数ベース配賦のため除外
 *
 * @param yearMonth "YYYY-MM"
 * @param locationId 指定時は該当拠点の車両のみ対象
 */
export async function runDriverAllocation(
  yearMonth: string,
  locationId?: string | null
): Promise<{ vehiclesUpdated: number; recordsUpdated: number }> {
  const driverRelatedItems = await prisma.accountItem.findMany({
    where: {
      isDriverRelated: true,
      isSubtotal: false,
      code: { notIn: Array.from(SALARY_DAILY_PRORATION_CODES) },
    },
    select: { id: true },
  });
  const driverRelatedIds = new Set(driverRelatedItems.map((a) => a.id));
  if (driverRelatedIds.size === 0) {
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

  const assignments = await prisma.dailyDriverAssignment.findMany({
    where: {
      yearMonth,
      vehicleId: { in: Array.from(vehicleIds) },
    },
    select: { driverId: true, vehicleId: true, date: true },
  });

  const driverIds = new Set(assignments.map((a) => a.driverId));
  const driverAmounts = await prisma.driverMonthlyAmount.findMany({
    where: {
      yearMonth,
      driverId: { in: Array.from(driverIds) },
      accountItemId: { in: Array.from(driverRelatedIds) },
    },
    select: { driverId: true, accountItemId: true, amount: true },
  });

  // 日ごとの乗務車両数: driverId -> date -> count
  const vehiclesPerDriverPerDay = new Map<string, Map<string, number>>();
  for (const a of assignments) {
    let dayMap = vehiclesPerDriverPerDay.get(a.driverId);
    if (!dayMap) {
      dayMap = new Map();
      vehiclesPerDriverPerDay.set(a.driverId, dayMap);
    }
    const prev = dayMap.get(a.date) ?? 0;
    dayMap.set(a.date, prev + 1);
  }

  // 車両ごとのウェイト合計: driverId -> vehicleId -> weight
  const weightByDriverVehicle = new Map<string, Map<string, number>>();
  for (const a of assignments) {
    const dayCount = vehiclesPerDriverPerDay.get(a.driverId)?.get(a.date) ?? 1;
    const weight = 1 / dayCount;

    let vMap = weightByDriverVehicle.get(a.driverId);
    if (!vMap) {
      vMap = new Map();
      weightByDriverVehicle.set(a.driverId, vMap);
    }
    const prev = vMap.get(a.vehicleId) ?? 0;
    vMap.set(a.vehicleId, prev + weight);
  }

  // 車両別・勘定科目別の配賦結果: vehicleId -> accountItemId -> amount
  const allocatedByVehicle = new Map<string, Map<string, number>>();
  for (const vId of Array.from(vehicleIds)) {
    allocatedByVehicle.set(vId, new Map());
  }

  for (const da of driverAmounts) {
    const amount = Number(da.amount);
    if (amount === 0) continue;

    const vMap = weightByDriverVehicle.get(da.driverId);
    if (!vMap || vMap.size === 0) continue;

    const totalWeight = Array.from(vMap.values()).reduce((s, w) => s + w, 0);
    if (totalWeight === 0) continue;

    for (const [vehicleId, weight] of Array.from(vMap.entries())) {
      if (!vehicleIds.has(vehicleId)) continue;
      const allocated = amount * (weight / totalWeight);
      const itemMap = allocatedByVehicle.get(vehicleId)!;
      const prev = itemMap.get(da.accountItemId) ?? 0;
      itemMap.set(da.accountItemId, prev + allocated);
    }
  }

  let recordsUpdated = 0;
  for (const [vehicleId, itemMap] of Array.from(allocatedByVehicle.entries())) {
    for (const [accountItemId, amount] of Array.from(itemMap.entries())) {
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
    vehiclesUpdated: vehicleIds.size,
    recordsUpdated,
  };
}
