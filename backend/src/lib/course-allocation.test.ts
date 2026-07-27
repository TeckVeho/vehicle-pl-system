import { describe, it, expect, vi, beforeEach } from "vitest";

const { prismaMock } = vi.hoisted(() => ({
  prismaMock: {
    dailyAtmtcRun: { findMany: vi.fn() },
    courseMonthlyRecord: { deleteMany: vi.fn(), create: vi.fn() },
    driveSpreadsheetRevenueCourseLine: { findMany: vi.fn() },
  },
}));

vi.mock("./prisma.js", () => ({ prisma: prismaMock }));

vi.mock("./income-statement-amounts.js", () => ({
  buildVehicleRecordMapForLocation: vi.fn(),
  MANUAL_INPUT_ONLY_NAMES: ["その他", "不動産収入", "人材派遣収入"],
}));

import { buildVehicleRecordMapForLocation } from "./income-statement-amounts.js";
import {
  buildCourseShareWeights,
  runCourseAllocation,
  NO_COURSE_SLOT_KEY,
  allocateIntegerPercentsFromWeights,
} from "./course-allocation.js";

describe("allocateIntegerPercentsFromWeights", () => {
  it("returns integer percents summing to 100", () => {
    const m = allocateIntegerPercentsFromWeights(
      new Map([
        ["c1", 6],
        ["c2", 4],
      ])
    );
    expect(m.get("c1")).toBe(60);
    expect(m.get("c2")).toBe(40);
    expect([...m.values()].reduce((a, b) => a + b, 0)).toBe(100);
  });
});

describe("buildCourseShareWeights", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("aggregates weight per vehicle and course slot", async () => {
    prismaMock.dailyAtmtcRun.findMany.mockResolvedValue([
      { vehicleId: "v1", courseId: "c1", weight: 2 },
      { vehicleId: "v1", courseId: "c2", weight: 1 },
    ]);
    const w = await buildCourseShareWeights("2026-07", "loc1");
    expect(w.vehicleTotals.get("v1")).toBe(3);
    expect(w.byVehicle.get("v1")?.get("c1")).toBe(2);
    expect(w.byVehicle.get("v1")?.get("c2")).toBe(1);
  });
});

describe("runCourseAllocation", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    prismaMock.courseMonthlyRecord.deleteMany.mockResolvedValue({ count: 0 });
    prismaMock.courseMonthlyRecord.create.mockResolvedValue({});
    prismaMock.driveSpreadsheetRevenueCourseLine.findMany.mockResolvedValue([]);
  });

  it("allocates vehicle expense to courses by run share", async () => {
    prismaMock.dailyAtmtcRun.findMany.mockResolvedValue([
      { vehicleId: "v1", courseId: "c1", weight: 1 },
      { vehicleId: "v1", courseId: "c2", weight: 1 },
    ]);
    vi.mocked(buildVehicleRecordMapForLocation).mockResolvedValue({
      vehicles: [{ id: "v1", locationId: "loc1", vehicleNo: "1", serviceType: null }],
      vehicleIds: ["v1"],
      recordMap: new Map([["v1-ai1", 1000]]),
      accountItems: [
        {
          id: "ai1",
          code: "6191",
          name: "リース車償却",
          category: "expense",
          isDriverRelated: false,
          isSubtotal: false,
        },
      ],
      revenueFromSpreadsheetIds: new Set(),
    });

    const result = await runCourseAllocation("2026-07", "loc1");
    expect(result.recordsWritten).toBe(2);
    expect(prismaMock.courseMonthlyRecord.create).toHaveBeenCalledTimes(2);
    const amounts = prismaMock.courseMonthlyRecord.create.mock.calls.map(
      (c: [{ data: { amount: number; courseSlotKey: string } }]) => c[0].data
    );
    const c1 = amounts.find((a: { courseSlotKey: string }) => a.courseSlotKey === "c1");
    const c2 = amounts.find((a: { courseSlotKey: string }) => a.courseSlotKey === "c2");
    expect(c1?.amount).toBe(500);
    expect(c2?.amount).toBe(500);
  });

  it("puts full amount in no-course bucket when no runs", async () => {
    prismaMock.dailyAtmtcRun.findMany.mockResolvedValue([]);
    vi.mocked(buildVehicleRecordMapForLocation).mockResolvedValue({
      vehicles: [{ id: "v1", locationId: "loc1", vehicleNo: "1", serviceType: null }],
      vehicleIds: ["v1"],
      recordMap: new Map([["v1-ai1", 300]]),
      accountItems: [
        {
          id: "ai1",
          code: "9999",
          name: "その他",
          category: "expense",
          isDriverRelated: false,
          isSubtotal: false,
        },
      ],
      revenueFromSpreadsheetIds: new Set(),
    });

    await runCourseAllocation("2026-07", "loc1");
    expect(prismaMock.courseMonthlyRecord.create).toHaveBeenCalledWith(
      expect.objectContaining({
        data: expect.objectContaining({
          courseSlotKey: NO_COURSE_SLOT_KEY,
          amount: 300,
        }),
      })
    );
  });
});
