/**
 * 拠点別月額経費の車両数按分を実行し、MonthlyRecord を更新する。
 *
 * 各拠点の月額合計を、その拠点の車両数で均等に按分して車両別に配賦する。
 *
 * @param yearMonth "YYYY-MM"
 * @param locationId 指定時は該当拠点のみ対象
 */
import { prisma } from "./prisma.js";
import { LOCATION_EXPENSE_PRORATION_CODES } from "./location-expense-proration.js";

export async function runLocationExpenseAllocation(
  yearMonth: string,
  locationId?: string | null
): Promise<{ vehiclesUpdated: number; recordsUpdated: number }> {
  const locationExpenseItems = await prisma.accountItem.findMany({
    where: {
      code: { in: [...LOCATION_EXPENSE_PRORATION_CODES] },
      isSubtotal: false,
    },
    select: { id: true },
  });
  const accountItemIds = new Set(locationExpenseItems.map((a) => a.id));
  if (accountItemIds.size === 0) {
    return { vehiclesUpdated: 0, recordsUpdated: 0 };
  }

  const locationExpenses = await prisma.locationMonthlyExpense.findMany({
    where: {
      yearMonth,
      ...(locationId ? { locationId } : {}),
      accountItemId: { in: Array.from(accountItemIds) },
    },
    select: {
      locationId: true,
      accountItemId: true,
      amount: true,
    },
  });

  if (locationExpenses.length === 0) {
    return { vehiclesUpdated: 0, recordsUpdated: 0 };
  }

  const locationIds = Array.from(new Set(locationExpenses.map((e) => e.locationId)));
  const vehiclesByLocation = await prisma.vehicle.findMany({
    where: { locationId: { in: locationIds } },
    select: { id: true, locationId: true },
  });

  const vehicleCountByLocation = new Map<string, number>();
  const vehiclesByLocationMap = new Map<string, string[]>();
  for (const v of vehiclesByLocation) {
    const count = vehicleCountByLocation.get(v.locationId) ?? 0;
    vehicleCountByLocation.set(v.locationId, count + 1);

    let list = vehiclesByLocationMap.get(v.locationId);
    if (!list) {
      list = [];
      vehiclesByLocationMap.set(v.locationId, list);
    }
    list.push(v.id);
  }

  const allocatedByVehicle = new Map<string, Map<string, number>>();

  for (const exp of locationExpenses) {
    const vehicleIds = vehiclesByLocationMap.get(exp.locationId) ?? [];
    const vehicleCount = vehicleCountByLocation.get(exp.locationId) ?? 0;
    const amount = Number(exp.amount);

    if (vehicleCount === 0 || amount === 0) continue;

    const amountPerVehicle = Math.round((amount / vehicleCount) * 100) / 100;

    for (const vId of vehicleIds) {
      let itemMap = allocatedByVehicle.get(vId);
      if (!itemMap) {
        itemMap = new Map();
        allocatedByVehicle.set(vId, itemMap);
      }
      itemMap.set(exp.accountItemId, amountPerVehicle);
    }
  }

  let recordsUpdated = 0;
  for (const [vehicleId, itemMap] of Array.from(allocatedByVehicle.entries())) {
    for (const [accountItemId, amount] of Array.from(itemMap.entries())) {
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
      if (oldAmount === amount) continue;

      await prisma.monthlyRecord.upsert({
        where: {
          vehicleId_accountItemId_yearMonth: {
            vehicleId,
            accountItemId,
            yearMonth,
          },
        },
        update: { amount },
        create: {
          vehicleId,
          accountItemId,
          yearMonth,
          amount,
        },
      });
      recordsUpdated++;
    }
  }

  return {
    vehiclesUpdated: allocatedByVehicle.size,
    recordsUpdated,
  };
}
