/**
 * 当月 ATMTC 運行実績に基づくコース↔車両の紐づけ（損益表ヘッダ表示用）
 */
import { prisma } from "./prisma.js";
import {
  NO_COURSE_SLOT_KEY,
  buildCourseShareWeights,
  allocateIntegerPercentsFromWeights,
} from "./course-allocation.js";

export type AtmtcCourseRef = {
  id: string;
  name: string;
  code: string;
};

/** 車両ヘッダ用：当月のコース別按分比率 */
export type AtmtcCourseShareLine = {
  id: string | null;
  name: string;
  code: string | null;
  sharePercent: number;
};

export type AtmtcVehicleShareLine = {
  vehicleId: string;
  sharePercent: number;
};

export type AtmtcCourseVehicleLinks = {
  vehicleIdsByCourseSlot: Map<string, string[]>;
  /** courseSlotKey → vehicleId → その車両の当月 run に占める当コースの比率（%） */
  vehicleSharePercentByCourseSlot: Map<string, Map<string, number>>;
  courseSharesByVehicleId: Map<string, AtmtcCourseShareLine[]>;
  vehicleIdsWithUncourseRuns: Set<string>;
};

export async function buildAtmtcCourseVehicleLinks(
  yearMonth: string,
  locationId: string
): Promise<AtmtcCourseVehicleLinks> {
  const weights = await buildCourseShareWeights(yearMonth, locationId);

  const courseIds = new Set<string>();
  for (const slotMap of weights.byVehicle.values()) {
    for (const slot of slotMap.keys()) {
      if (slot !== NO_COURSE_SLOT_KEY) courseIds.add(slot);
    }
  }

  const courseRows =
    courseIds.size > 0
      ? await prisma.course.findMany({
          where: { id: { in: Array.from(courseIds) } },
          select: { id: true, name: true, code: true },
        })
      : [];
  const courseById = new Map(courseRows.map((c) => [c.id, c]));

  const vehicleSharePercentByCourseSlot = new Map<string, Map<string, number>>();
  const slotToVehicles = new Map<string, Set<string>>();
  const courseSharesByVehicleId = new Map<string, AtmtcCourseShareLine[]>();
  const vehicleIdsWithUncourseRuns = new Set<string>();

  for (const [vehicleId, slotMap] of weights.byVehicle) {
    const percents = allocateIntegerPercentsFromWeights(slotMap);
    const lines: AtmtcCourseShareLine[] = [];

    for (const [slot, pct] of percents) {
      if (pct <= 0) continue;

      let slotVehiclePercents = vehicleSharePercentByCourseSlot.get(slot);
      if (!slotVehiclePercents) {
        slotVehiclePercents = new Map();
        vehicleSharePercentByCourseSlot.set(slot, slotVehiclePercents);
      }
      slotVehiclePercents.set(vehicleId, pct);

      let vset = slotToVehicles.get(slot);
      if (!vset) {
        vset = new Set();
        slotToVehicles.set(slot, vset);
      }
      vset.add(vehicleId);

      if (slot === NO_COURSE_SLOT_KEY) {
        vehicleIdsWithUncourseRuns.add(vehicleId);
        lines.push({
          id: null,
          name: "コースなし",
          code: null,
          sharePercent: pct,
        });
      } else {
        const c = courseById.get(slot);
        if (c) {
          lines.push({
            id: c.id,
            name: c.name,
            code: c.code,
            sharePercent: pct,
          });
        }
      }
    }

    lines.sort((a, b) =>
      (a.code ?? a.name).localeCompare(b.code ?? b.name, "ja")
    );
    if (lines.length > 0) {
      courseSharesByVehicleId.set(vehicleId, lines);
    }
  }

  const vehicleIdsByCourseSlot = new Map<string, string[]>();
  for (const [slot, vset] of slotToVehicles) {
    vehicleIdsByCourseSlot.set(slot, Array.from(vset).sort());
  }

  return {
    vehicleIdsByCourseSlot,
    vehicleSharePercentByCourseSlot,
    courseSharesByVehicleId,
    vehicleIdsWithUncourseRuns,
  };
}
