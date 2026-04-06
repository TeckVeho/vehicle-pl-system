import { beforeEach, describe, expect, it, vi } from "vitest";

const prismaMock = vi.hoisted(() => ({
  accountItem: { findMany: vi.fn() },
  locationMonthlyExpense: { findMany: vi.fn() },
  vehicle: { findMany: vi.fn() },
  monthlyRecord: { findUnique: vi.fn(), upsert: vi.fn() },
  $executeRawUnsafe: vi.fn(),
}));

vi.mock("./prisma.js", () => ({
  prisma: prismaMock,
}));

import { runLocationExpenseAllocation } from "./location-expense-allocation.js";

describe("runLocationExpenseAllocation", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.$executeRawUnsafe.mockResolvedValue(0);
  });

  it("returns zeros when no location expense account items exist", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([]);
    const result = await runLocationExpenseAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
    expect(prismaMock.locationMonthlyExpense.findMany).not.toHaveBeenCalled();
  });

  it("returns zeros when account items exist but no location expenses for yearMonth", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([]);
    const result = await runLocationExpenseAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
    expect(prismaMock.vehicle.findMany).not.toHaveBeenCalled();
  });

  it("returns zeros when expenses exist but no vehicles at the locations", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([
      { locationId: "loc1", accountItemId: "ai1", amount: 10000 },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([]);
    const result = await runLocationExpenseAllocation("2026-03");
    // No vehicles → allocatedByVehicle is empty → 0 rows → recordsUpdated = 0
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
  });

  it("splits expense equally across 2 vehicles at the same location", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([
      { locationId: "loc1", accountItemId: "ai1", amount: 10000 },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", locationId: "loc1" },
      { id: "v2", locationId: "loc1" },
    ]);

    const result = await runLocationExpenseAllocation("2026-03");

    expect(result.vehiclesUpdated).toBe(2);
    expect(result.recordsUpdated).toBe(2);

    // Should have called $executeRawUnsafe with INSERT containing 2 value rows
    expect(prismaMock.$executeRawUnsafe).toHaveBeenCalledTimes(1);
    const sql = prismaMock.$executeRawUnsafe.mock.calls[0][0] as string;
    expect(sql).toContain("INSERT INTO MonthlyRecord");
    expect(sql).toContain("5000"); // 10000 / 2 vehicles
  });

  it("handles multiple locations and account items independently", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([
      { id: "ai1" },
      { id: "ai2" },
    ]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([
      { locationId: "locA", accountItemId: "ai1", amount: 9000 },
      { locationId: "locB", accountItemId: "ai2", amount: 6000 },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "vA1", locationId: "locA" },
      { id: "vA2", locationId: "locA" },
      { id: "vA3", locationId: "locA" },
      { id: "vB1", locationId: "locB" },
      { id: "vB2", locationId: "locB" },
    ]);

    const result = await runLocationExpenseAllocation("2026-03");

    // locA: 3 vehicles * 1 item = 3 rows; locB: 2 vehicles * 1 item = 2 rows
    expect(result.vehiclesUpdated).toBe(5);
    expect(result.recordsUpdated).toBe(5);

    const sql = prismaMock.$executeRawUnsafe.mock.calls[0][0] as string;
    expect(sql).toContain("3000"); // 9000 / 3
    expect(sql).toContain("3000"); // 6000 / 2
  });

  it("skips location expenses with zero amount", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.locationMonthlyExpense.findMany.mockResolvedValue([
      { locationId: "loc1", accountItemId: "ai1", amount: 0 },
    ]);
    prismaMock.vehicle.findMany.mockResolvedValue([
      { id: "v1", locationId: "loc1" },
    ]);

    const result = await runLocationExpenseAllocation("2026-03");
    // amount is 0 → skipped in the loop → no rows
    expect(result.vehiclesUpdated).toBe(0);
    expect(result.recordsUpdated).toBe(0);
    expect(prismaMock.$executeRawUnsafe).not.toHaveBeenCalled();
  });
});
