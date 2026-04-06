import { beforeEach, describe, expect, it, vi } from "vitest";

const prismaMock = vi.hoisted(() => ({
  accountItem: { findMany: vi.fn() },
  vehicle: { findMany: vi.fn() },
  dailyDriverAssignment: { findMany: vi.fn() },
  driverMonthlyAmount: { findMany: vi.fn() },
  dailyOperatingRecord: { findMany: vi.fn() },
  monthlyRecord: { findUnique: vi.fn(), upsert: vi.fn() },
}));

vi.mock("./prisma.js", () => ({
  prisma: prismaMock,
}));

import { runSalaryRunCountAllocation } from "./salary-run-count-allocation.js";

describe("runSalaryRunCountAllocation", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.monthlyRecord.findUnique.mockResolvedValue(null);
    prismaMock.monthlyRecord.upsert.mockResolvedValue({} as never);
  });

  it("returns zeros when no salary account items (6138/6147) exist", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([]);
    const result = await runSalaryRunCountAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
    expect(prismaMock.vehicle.findMany).not.toHaveBeenCalled();
  });

  it("returns zeros when salary items exist but no vehicles", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "sal1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([]);
    const result = await runSalaryRunCountAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
    expect(prismaMock.dailyDriverAssignment.findMany).not.toHaveBeenCalled();
  });

  it("allocates salary based on run count: driver with 2 runs on v1 and 3 runs on v2", async () => {
    // Setup: 1 salary item, 2 vehicles
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "sal1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1" },
      { id: "v2" },
    ]);

    // Driver d1 assigned to v1 on March 1, and to v2 on March 1
    prismaMock.dailyDriverAssignment.findMany.mockResolvedValue([
      { driverId: "d1", vehicleId: "v1", date: "2026-03-01" },
      { driverId: "d1", vehicleId: "v2", date: "2026-03-01" },
    ]);

    // d1 has 31000 salary per month for sal1
    prismaMock.driverMonthlyAmount.findMany.mockResolvedValue([
      { driverId: "d1", accountItemId: "sal1", amount: 31000 },
    ]);

    // v1 has 2 runs on March 1, v2 has 3 runs
    prismaMock.dailyOperatingRecord.findMany.mockResolvedValue([
      { vehicleId: "v1", date: "2026-03-01", runCount: 2 },
      { vehicleId: "v2", date: "2026-03-01", runCount: 3 },
    ]);

    const result = await runSalaryRunCountAllocation("2026-03");

    // March 2026 has 31 days
    // daily unit price = 31000 / 31 = 1000
    // v1: 1000 * 2 = 2000
    // v2: 1000 * 3 = 3000
    expect(result.vehiclesUpdated).toBe(2);
    expect(result.recordsUpdated).toBe(2);

    // Check upsert calls
    expect(prismaMock.monthlyRecord.upsert).toHaveBeenCalledTimes(2);
    const amounts = prismaMock.monthlyRecord.upsert.mock.calls.map(
      (c) => (c[0] as { create: { amount: number } }).create.amount
    );
    expect(amounts).toContain(2000);
    expect(amounts).toContain(3000);
  });

  it("skips vehicles with 0 run count (no allocation)", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "sal1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([{ id: "v1" }]);

    prismaMock.dailyDriverAssignment.findMany.mockResolvedValue([
      { driverId: "d1", vehicleId: "v1", date: "2026-03-01" },
    ]);

    prismaMock.driverMonthlyAmount.findMany.mockResolvedValue([
      { driverId: "d1", accountItemId: "sal1", amount: 31000 },
    ]);

    // v1 has 0 runs on that day
    prismaMock.dailyOperatingRecord.findMany.mockResolvedValue([
      { vehicleId: "v1", date: "2026-03-01", runCount: 0 },
    ]);

    const result = await runSalaryRunCountAllocation("2026-03");

    // runCount is 0 → amount stays at 0 → no change from old (null → 0) → no upsert
    expect(result.vehiclesUpdated).toBe(1);
    expect(result.recordsUpdated).toBe(0);
  });

  it("skips driver amounts that are zero", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "sal1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([{ id: "v1" }]);

    prismaMock.dailyDriverAssignment.findMany.mockResolvedValue([
      { driverId: "d1", vehicleId: "v1", date: "2026-03-01" },
    ]);

    prismaMock.driverMonthlyAmount.findMany.mockResolvedValue([
      { driverId: "d1", accountItemId: "sal1", amount: 0 },
    ]);

    prismaMock.dailyOperatingRecord.findMany.mockResolvedValue([
      { vehicleId: "v1", date: "2026-03-01", runCount: 5 },
    ]);

    const result = await runSalaryRunCountAllocation("2026-03");

    // amount 0 → daily unit price 0/31 = 0 → skipped in dailyUnitPriceByDriver
    // allocatedByVehicle totals remain 0 → no upsert
    expect(result.vehiclesUpdated).toBe(1);
    expect(result.recordsUpdated).toBe(0);
  });
});
