import { VEHICLE_COST_ACCOUNT_CODES } from "../routes/vehicle-monthly-costs.js";

/** 勘定科目コード → VehicleMonthlyCost フィールド名 */
const CODE_TO_FIELD: Record<string, keyof VehicleCostFields> = {
  "6191": "leaseDepreciation", // リース車償却
  "6192": "vehicleDepreciation", // 車両償却費
  "6193": "vehicleLease", // 車両リース
  "6194": "insuranceCost", // 損害保険料(自賠責)
  "6195": "taxCost", // 賦課税(自動車税)
};

export interface VehicleCostFields {
  leaseDepreciation: number;
  vehicleDepreciation: number;
  vehicleLease: number;
  insuranceCost: number;
  taxCost: number;
  fuelEfficiency: number;  // 燃費（L、ITP連携）
  roadUsageFee: number;   // 道路使用料（ITP連携の生データ）
}

/** 拠点別計算パラメータ（燃料費・道路使用料の算出に使用） */
export interface LocationCalculationParam {
  fuelUnitPrice: number;         // 燃料単価（円/L）
  roadUsageDiscountRate: number;  // 使用料割引率（0〜1）
}

/** 燃料費・道路使用料の勘定科目コード（ITP連携＋計算） */
export const FUEL_ACCOUNT_CODE = "6175";
export const ROAD_USAGE_ACCOUNT_CODE = "6176";

/** 勘定科目コードが車両月次費用（イズミクラウド連携）対象か */
export function isVehicleCostAccount(code: string): boolean {
  return VEHICLE_COST_ACCOUNT_CODES.includes(code as (typeof VEHICLE_COST_ACCOUNT_CODES)[number]);
}

/** 勘定科目コードが燃料費・道路使用料（ITP連携＋計算）対象か */
export function isFuelOrRoadAccount(code: string): boolean {
  return code === FUEL_ACCOUNT_CODE || code === ROAD_USAGE_ACCOUNT_CODE;
}

/** 勘定科目コードから VehicleMonthlyCost の金額を取得（直接マッピング科目） */
export function getVehicleCostAmount(
  cost: VehicleCostFields | null | undefined,
  accountCode: string
): number {
  if (!cost) return 0;
  const field = CODE_TO_FIELD[accountCode];
  if (!field) return 0;
  const val = cost[field];
  return val != null ? Number(val) : 0;
}

/** 燃料費・道路使用料計算用の最小フィールド */
export interface FuelRoadCostFields {
  fuelEfficiency: number;
  roadUsageFee: number;
}

/** 燃料費を計算: 燃費（L）× 燃料単価（円/L） */
export function getFuelCostAmount(
  cost: FuelRoadCostFields | null | undefined,
  param: LocationCalculationParam | null | undefined
): number {
  if (!cost || !param) return 0;
  const fuelL = Number(cost.fuelEfficiency ?? 0);
  const unitPrice = Number(param.fuelUnitPrice ?? 0);
  return Math.round(fuelL * unitPrice * 100) / 100;
}

/** 道路使用料を計算: 道路使用料（ITP）× 使用料割引率 */
export function getRoadUsageCostAmount(
  cost: FuelRoadCostFields | null | undefined,
  param: LocationCalculationParam | null | undefined
): number {
  if (!cost || !param) return 0;
  const fee = Number(cost.roadUsageFee ?? 0);
  const rate = Number(param.roadUsageDiscountRate ?? 1);
  return Math.round(fee * rate * 100) / 100;
}
