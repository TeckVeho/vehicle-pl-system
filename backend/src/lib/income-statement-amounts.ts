/**
 * 損益計算書の車両別表示金額を組み立てる（MonthlyRecord + 連携・計算の上書き）
 */
import { prisma } from "./prisma.js";
import { accountItemEffectiveWhere } from "./account-item-filter.js";
import {
  getFuelCostAmount,
  getRoadUsageCostAmount,
  getVehicleCostAmount,
  isVehicleCostAccount,
} from "./vehicle-costs.js";
import { getPreviousYearMonth } from "./salary-daily-proration.js";
import { isLocationExpenseProrationAccount } from "./location-expense-proration.js";

export const MANUAL_INPUT_ONLY_NAMES = ["その他", "不動産収入", "人材派遣収入"];

export type VehicleRecordMap = Map<string, number>;

export async function buildVehicleRecordMapForLocation(
  yearMonth: string,
  locationId: string
): Promise<{
  vehicles: Array<{
    id: string;
    locationId: string;
    vehicleNo: string;
    serviceType: string | null;
  }>;
  vehicleIds: string[];
  recordMap: VehicleRecordMap;
  accountItems: Array<{
    id: string;
    code: string;
    name: string;
    category: string;
    isDriverRelated: boolean;
    isSubtotal: boolean;
  }>;
  revenueFromSpreadsheetIds: Set<string>;
}> {
  const vehicles = await prisma.vehicle.findMany({
    where: { locationId },
    select: {
      id: true,
      locationId: true,
      vehicleNo: true,
      serviceType: true,
    },
    orderBy: [{ vehicleNo: "asc" }],
  });
  const vehicleIds = vehicles.map((v) => v.id);
  const prevYearMonth = getPreviousYearMonth(yearMonth);

  const accountItemsForCost = await prisma.accountItem.findMany({
    where: accountItemEffectiveWhere(yearMonth),
    select: {
      id: true,
      code: true,
      name: true,
      category: true,
      isDriverRelated: true,
      isSubtotal: true,
    },
  });

  const revenueFromSpreadsheetIds = new Set(
    accountItemsForCost
      .filter(
        (a) =>
          a.category === "revenue" && !MANUAL_INPUT_ONLY_NAMES.includes(a.name)
      )
      .map((a) => a.id)
  );

  if (vehicleIds.length === 0) {
    return {
      vehicles,
      vehicleIds,
      recordMap: new Map(),
      accountItems: accountItemsForCost,
      revenueFromSpreadsheetIds,
    };
  }

  const [
    records,
    prevMonthRecords,
    vehicleCosts,
    prevMonthVehicleCosts,
    locationExpenses,
    locationParams,
    driveRevenueLines,
  ] = await Promise.all([
    prisma.monthlyRecord.findMany({
      where: { yearMonth, vehicleId: { in: vehicleIds } },
    }),
    prisma.monthlyRecord.findMany({
      where: { yearMonth: prevYearMonth, vehicleId: { in: vehicleIds } },
    }),
    prisma.vehicleMonthlyCost.findMany({
      where: { yearMonth, vehicleId: { in: vehicleIds } },
    }),
    prisma.vehicleMonthlyCost.findMany({
      where: { yearMonth: prevYearMonth, vehicleId: { in: vehicleIds } },
    }),
    prisma.locationMonthlyExpense.findMany({
      where: { yearMonth, locationId },
      select: { accountItemId: true, amount: true },
    }),
    prisma.locationCalculationParameter.findMany({
      where: { yearMonth: prevYearMonth, locationId },
    }),
    prisma.driveSpreadsheetRevenueLine.findMany({
      where: { yearMonth, locationId },
    }),
  ]);

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

  const prevMonthVehicleCostMap = new Map<
    string,
    { fuelEfficiency: number; roadUsageFee: number }
  >();
  for (const vc of prevMonthVehicleCosts) {
    prevMonthVehicleCostMap.set(vc.vehicleId, {
      fuelEfficiency: Number(vc.fuelEfficiency ?? 0),
      roadUsageFee: Number(vc.roadUsageFee ?? 0),
    });
  }

  const locationParam = locationParams[0] ?? null;
  const locationParamForCalc = locationParam
    ? {
        fuelUnitPrice: Number(locationParam.fuelUnitPrice ?? 0),
        roadUsageDiscountRate: Number(locationParam.roadUsageDiscountRate ?? 1),
      }
    : null;

  const recordMap: VehicleRecordMap = new Map();
  for (const r of records) {
    if (revenueFromSpreadsheetIds.has(r.accountItemId)) continue;
    recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
  }

  for (const v of vehicles) {
    const cost = vehicleCostMap.get(v.id);
    for (const a of accountItemsForCost) {
      if (isVehicleCostAccount(a.code)) {
        recordMap.set(`${v.id}-${a.id}`, getVehicleCostAmount(cost ?? null, a.code));
      }
    }
  }

  for (const v of vehicles) {
    const prevCost = prevMonthVehicleCostMap.get(v.id);
    const costForCalc = prevCost
      ? {
          fuelEfficiency: prevCost.fuelEfficiency,
          roadUsageFee: prevCost.roadUsageFee,
        }
      : null;
    for (const a of accountItemsForCost) {
      if (a.code === "6175") {
        recordMap.set(
          `${v.id}-${a.id}`,
          getFuelCostAmount(costForCalc, locationParamForCalc)
        );
      } else if (a.code === "6176") {
        recordMap.set(
          `${v.id}-${a.id}`,
          getRoadUsageCostAmount(costForCalc, locationParamForCalc)
        );
      }
    }
  }

  const vehicleCount = vehicles.length;
  if (vehicleCount > 0) {
    for (const exp of locationExpenses) {
      const a = accountItemsForCost.find((x) => x.id === exp.accountItemId);
      if (a && isLocationExpenseProrationAccount(a.code)) {
        const amountPerVehicle =
          Math.round((Number(exp.amount) / vehicleCount) * 100) / 100;
        for (const v of vehicles) {
          recordMap.set(`${v.id}-${exp.accountItemId}`, amountPerVehicle);
        }
      }
    }
  }

  for (const r of prevMonthRecords) {
    const a = accountItemsForCost.find((x) => x.id === r.accountItemId);
    if (a && (a.code === "6138" || a.code === "6147")) {
      recordMap.set(`${r.vehicleId}-${r.accountItemId}`, Number(r.amount));
    }
  }

  for (const line of driveRevenueLines) {
    recordMap.set(`${line.vehicleId}-${line.accountItemId}`, Number(line.amount));
  }

  return {
    vehicles,
    vehicleIds,
    recordMap,
    accountItems: accountItemsForCost,
    revenueFromSpreadsheetIds,
  };
}
