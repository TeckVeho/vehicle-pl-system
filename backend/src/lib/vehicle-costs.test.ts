import { describe, expect, it } from "vitest";
import {
  getFuelCostAmount,
  getRoadUsageCostAmount,
  getVehicleCostAmount,
  isFuelOrRoadAccount,
  isVehicleCostAccount,
} from "./vehicle-costs.js";

describe("isVehicleCostAccount", () => {
  it("returns true for Izumi cloud vehicle cost codes", () => {
    expect(isVehicleCostAccount("6191")).toBe(true);
    expect(isVehicleCostAccount("6195")).toBe(true);
  });

  it("returns false for other codes", () => {
    expect(isVehicleCostAccount("6175")).toBe(false);
    expect(isVehicleCostAccount("5010")).toBe(false);
  });
});

describe("isFuelOrRoadAccount", () => {
  it("identifies fuel and road usage codes", () => {
    expect(isFuelOrRoadAccount("6175")).toBe(true);
    expect(isFuelOrRoadAccount("6176")).toBe(true);
    expect(isFuelOrRoadAccount("6191")).toBe(false);
  });
});

describe("getVehicleCostAmount", () => {
  const cost = {
    leaseDepreciation: 100,
    vehicleDepreciation: 200,
    vehicleLease: 300,
    insuranceCost: 400,
    taxCost: 500,
    fuelEfficiency: 10,
    roadUsageFee: 20,
  };

  it("returns 0 when cost is null", () => {
    expect(getVehicleCostAmount(null, "6191")).toBe(0);
  });

  it("maps account codes to fields", () => {
    expect(getVehicleCostAmount(cost, "6191")).toBe(100);
    expect(getVehicleCostAmount(cost, "6192")).toBe(200);
    expect(getVehicleCostAmount(cost, "6193")).toBe(300);
    expect(getVehicleCostAmount(cost, "6194")).toBe(400);
    expect(getVehicleCostAmount(cost, "6195")).toBe(500);
  });

  it("returns 0 for unknown code", () => {
    expect(getVehicleCostAmount(cost, "9999")).toBe(0);
  });
});

describe("getFuelCostAmount", () => {
  it("returns 0 when cost or param is missing", () => {
    expect(getFuelCostAmount(null, { fuelUnitPrice: 100, roadUsageDiscountRate: 1 })).toBe(0);
    expect(getFuelCostAmount({ fuelEfficiency: 10, roadUsageFee: 5 }, null)).toBe(0);
  });

  it("calculates fuel cost with rounding", () => {
    const amount = getFuelCostAmount(
      { fuelEfficiency: 12.345, roadUsageFee: 0 },
      { fuelUnitPrice: 150.5, roadUsageDiscountRate: 1 }
    );
    expect(amount).toBe(1857.92);
  });
});

describe("getRoadUsageCostAmount", () => {
  it("returns 0 when cost or param is missing", () => {
    expect(getRoadUsageCostAmount(null, { fuelUnitPrice: 1, roadUsageDiscountRate: 0.5 })).toBe(0);
  });

  it("applies discount rate with rounding", () => {
    const amount = getRoadUsageCostAmount(
      { fuelEfficiency: 0, roadUsageFee: 1000 },
      { fuelUnitPrice: 0, roadUsageDiscountRate: 0.85 }
    );
    expect(amount).toBe(850);
  });
});
