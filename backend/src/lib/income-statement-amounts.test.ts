import "../__tests__/helpers/setup-prisma-mock.js";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { prismaMock } from "../__tests__/helpers/setup-prisma-mock.js";

import {
  buildVehicleRecordMapForLocation,
  MANUAL_INPUT_ONLY_NAMES,
} from "./income-statement-amounts.js";

describe("buildVehicleRecordMapForLocation", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.vehicle.findMany.mockResolvedValue([]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      {
        id: "ai-rev",
        code: "5010",
        name: "山崎製パン",
        category: "revenue",
        isDriverRelated: false,
        isSubtotal: false,
      },
      {
        id: "ai-manual",
        code: "5010",
        name: "その他",
        category: "revenue",
        isDriverRelated: false,
        isSubtotal: false,
      },
      {
        id: "ai-lease",
        code: "6191",
        name: "リース車償却",
        category: "expense",
        isDriverRelated: false,
        isSubtotal: false,
      },
    ]);
  });

  it("returns empty recordMap when location has no vehicles", async () => {
    const result = await buildVehicleRecordMapForLocation("2026-03", "loc-1");

    expect(result.vehicles).toEqual([]);
    expect(result.vehicleIds).toEqual([]);
    expect(result.recordMap.size).toBe(0);
    expect(result.revenueFromSpreadsheetIds).toEqual(new Set(["ai-rev"]));
    expect(MANUAL_INPUT_ONLY_NAMES).toContain("その他");
    expect(result.revenueFromSpreadsheetIds.has("ai-manual")).toBe(false);
  });

  it("applies vehicle cost and drive revenue overrides", async () => {
    prismaMock.vehicle.findMany.mockResolvedValue([
      {
        id: "v1",
        locationId: "loc-1",
        vehicleNo: "001",
        serviceType: null,
      },
    ]);
    prismaMock.monthlyRecord.findMany.mockResolvedValue([]);
    prismaMock.vehicleMonthlyCost.findMany.mockResolvedValue([
      {
        vehicleId: "v1",
        leaseDepreciation: 1000,
        vehicleDepreciation: 0,
        vehicleLease: 0,
        insuranceCost: 0,
        taxCost: 0,
        fuelEfficiency: 0,
        roadUsageFee: 0,
      },
    ]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([]);
    prismaMock.locationCalculationParameter.findMany.mockResolvedValue([]);
    prismaMock.driveSpreadsheetRevenueLine.findMany.mockResolvedValue([
      {
        vehicleId: "v1",
        accountItemId: "ai-rev",
        amount: 50000,
      },
    ]);

    const result = await buildVehicleRecordMapForLocation("2026-03", "loc-1");

    expect(result.recordMap.get("v1-ai-lease")).toBe(1000);
    expect(result.recordMap.get("v1-ai-rev")).toBe(50000);
  });

  it("prorates location expenses across vehicles", async () => {
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", locationId: "loc-1", vehicleNo: "001", serviceType: null },
      { id: "v2", locationId: "loc-1", vehicleNo: "002", serviceType: null },
    ]);
    prismaMock.accountItem.findMany.mockResolvedValue([
      {
        id: "ai-exp",
        code: "6150",
        name: "旅費交通地",
        category: "expense",
        isDriverRelated: false,
        isSubtotal: false,
      },
    ]);
    prismaMock.monthlyRecord.findMany.mockResolvedValue([]);
    prismaMock.vehicleMonthlyCost.findMany.mockResolvedValue([]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([
      { accountItemId: "ai-exp", amount: 1000 },
    ]);
    prismaMock.locationCalculationParameter.findMany.mockResolvedValue([]);
    prismaMock.driveSpreadsheetRevenueLine.findMany.mockResolvedValue([]);

    const result = await buildVehicleRecordMapForLocation("2026-03", "loc-1");

    expect(result.recordMap.get("v1-ai-exp")).toBe(500);
    expect(result.recordMap.get("v2-ai-exp")).toBe(500);
  });
});
