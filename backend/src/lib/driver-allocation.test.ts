import { beforeEach, describe, expect, it, vi } from "vitest";

const prismaMock = vi.hoisted(() => ({
  accountItem: { findMany: vi.fn() },
  vehicle: { findMany: vi.fn() },
  dailyDriverAssignment: { findMany: vi.fn() },
  driverMonthlyAmount: { findMany: vi.fn() },
  monthlyRecord: { findUnique: vi.fn(), upsert: vi.fn() },
}));

vi.mock("./prisma.js", () => ({
  prisma: prismaMock,
}));

import { runDriverAllocation } from "./driver-allocation.js";

describe("runDriverAllocation", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.monthlyRecord.findUnique.mockResolvedValue(null);
    prismaMock.monthlyRecord.upsert.mockResolvedValue({} as never);
  });

  it("returns zeros when no driver-related account items exist", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([]);
    const result = await runDriverAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
    expect(prismaMock.vehicle.findMany).not.toHaveBeenCalled();
  });

  it("returns zeros when account items exist but no vehicles", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([]);
    const result = await runDriverAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 0, recordsUpdated: 0 });
    expect(prismaMock.dailyDriverAssignment.findMany).not.toHaveBeenCalled();
  });

  it("skips upserts when driver amounts are all zero", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([{ id: "v1" }]);
    prismaMock.dailyDriverAssignment.findMany.mockResolvedValue([
      { driverId: "d1", vehicleId: "v1", date: "2026-03-01" },
    ]);
    prismaMock.driverMonthlyAmount.findMany.mockResolvedValue([
      { driverId: "d1", accountItemId: "ai1", amount: 0 },
    ]);
    const result = await runDriverAllocation("2026-03");
    expect(result).toEqual({ vehiclesUpdated: 1, recordsUpdated: 0 });
    expect(prismaMock.monthlyRecord.upsert).not.toHaveBeenCalled();
  });

  it("splits one driver amount across two vehicles same day (weight 1/2 each) and upserts monthly records", async () => {
    prismaMock.accountItem.findMany.mockResolvedValue([{ id: "ai1" }]);
    prismaMock.vehicle.findMany.mockResolvedValue([{ id: "v1" }, { id: "v2" }]);
    prismaMock.dailyDriverAssignment.findMany.mockResolvedValue([
      { driverId: "d1", vehicleId: "v1", date: "2026-03-01" },
      { driverId: "d1", vehicleId: "v2", date: "2026-03-01" },
    ]);
    prismaMock.driverMonthlyAmount.findMany.mockResolvedValue([
      { driverId: "d1", accountItemId: "ai1", amount: 100 },
    ]);

    const result = await runDriverAllocation("2026-03");

    expect(result).toEqual({ vehiclesUpdated: 2, recordsUpdated: 2 });
    expect(prismaMock.monthlyRecord.upsert).toHaveBeenCalledTimes(2);

    const amounts = prismaMock.monthlyRecord.upsert.mock.calls.map(
      (c) => (c[0] as { create: { amount: number }; update: { amount: number } }).create.amount
    );
    expect(amounts).toEqual(expect.arrayContaining([50, 50]));
  });
});
