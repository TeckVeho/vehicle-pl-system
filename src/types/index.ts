export interface VehicleWithLocation {
  id: string;
  vehicleNo: string;
  serviceType: string | null;
  location: {
    id: string;
    code: string;
    name: string;
  };
  course?: { id: string; name: string; code: string } | null;
}

export interface AccountItemWithAmount {
  id: string;
  code: string;
  name: string;
  category: string;
  sortOrder: number;
  isSubtotal: boolean;
  amount?: number;
}

export interface MonthlyRecordRow {
  vehicleId: string;
  accountItemId: string;
  amount: number;
}

export interface PLTableData {
  accountItems: AccountItemWithAmount[];
  vehicles: VehicleWithLocation[];
  records: Map<string, number>; // key: `${vehicleId}-${accountItemId}`
  yearMonth: string;
}
