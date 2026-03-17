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
}

/** 勘定科目コードが車両月次費用（イズミクラウド連携）対象か */
export function isVehicleCostAccount(code: string): boolean {
  return VEHICLE_COST_ACCOUNT_CODES.includes(code as (typeof VEHICLE_COST_ACCOUNT_CODES)[number]);
}

/** 勘定科目コードから VehicleMonthlyCost の金額を取得 */
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
