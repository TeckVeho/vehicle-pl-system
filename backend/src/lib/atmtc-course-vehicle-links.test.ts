import { describe, it, expect, vi, beforeEach } from "vitest";

vi.mock("./prisma.js", () => ({
  prisma: {
    course: { findMany: vi.fn() },
  },
}));

const { buildCourseShareWeightsMock } = vi.hoisted(() => ({
  buildCourseShareWeightsMock: vi.fn(),
}));

vi.mock("./course-allocation.js", async (importOriginal) => {
  const actual = await importOriginal<typeof import("./course-allocation.js")>();
  return {
    ...actual,
    buildCourseShareWeights: buildCourseShareWeightsMock,
  };
});

import { prisma } from "./prisma.js";
import { buildAtmtcCourseVehicleLinks } from "./atmtc-course-vehicle-links.js";
import { NO_COURSE_SLOT_KEY } from "./course-allocation.js";

describe("buildAtmtcCourseVehicleLinks", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("maps course slots to vehicles with share percents", async () => {
    buildCourseShareWeightsMock.mockResolvedValue({
      byVehicle: new Map([
        [
          "v1",
          new Map([
            ["c1", 6],
            ["c2", 4],
          ]),
        ],
        ["v2", new Map([["c1", 1]])],
      ]),
      vehicleTotals: new Map([
        ["v1", 10],
        ["v2", 1],
      ]),
    });
    vi.mocked(prisma.course.findMany).mockResolvedValue([
      { id: "c1", name: "A便", code: "001-001" },
      { id: "c2", name: "B便", code: "001-002" },
    ]);

    const links = await buildAtmtcCourseVehicleLinks("2026-07", "loc1");

    expect(links.vehicleSharePercentByCourseSlot.get("c1")?.get("v1")).toBe(60);
    expect(links.vehicleSharePercentByCourseSlot.get("c2")?.get("v1")).toBe(40);
    expect(links.vehicleSharePercentByCourseSlot.get("c1")?.get("v2")).toBe(100);
    expect(links.courseSharesByVehicleId.get("v1")?.map((l) => l.sharePercent)).toEqual([
      60, 40,
    ]);
  });

  it("tracks uncourse runs with share percent", async () => {
    buildCourseShareWeightsMock.mockResolvedValue({
      byVehicle: new Map([["v3", new Map([[NO_COURSE_SLOT_KEY, 1]])]]),
      vehicleTotals: new Map([["v3", 1]]),
    });
    vi.mocked(prisma.course.findMany).mockResolvedValue([]);

    const links = await buildAtmtcCourseVehicleLinks("2026-07", "loc1");

    expect(links.vehicleIdsByCourseSlot.get(NO_COURSE_SLOT_KEY)).toEqual(["v3"]);
    expect(links.courseSharesByVehicleId.get("v3")?.[0]).toMatchObject({
      name: "コースなし",
      sharePercent: 100,
    });
    expect(links.vehicleIdsWithUncourseRuns.has("v3")).toBe(true);
  });
});
