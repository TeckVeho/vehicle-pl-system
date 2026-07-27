/**
 * ATMTC run シェアに基づくコース別月次金額の算出・永続化
 */
import { prisma } from "./prisma.js";
import {
  buildVehicleRecordMapForLocation,
  MANUAL_INPUT_ONLY_NAMES,
} from "./income-statement-amounts.js";
import { isLocationExpenseProrationAccount } from "./location-expense-proration.js";
import { isVehicleCostAccount } from "./vehicle-costs.js";

export const NO_COURSE_SLOT_KEY = "__no_course__";

export type CourseShareWeights = {
  byVehicle: Map<string, Map<string, number>>;
  vehicleTotals: Map<string, number>;
};

export function courseSlotKeyFromCourseId(courseId: string | null | undefined): string {
  return courseId ?? NO_COURSE_SLOT_KEY;
}

export async function buildCourseShareWeights(
  yearMonth: string,
  locationId: string
): Promise<CourseShareWeights> {
  const runs = await prisma.dailyAtmtcRun.findMany({
    where: { yearMonth, locationId },
    select: { vehicleId: true, courseId: true, weight: true },
  });

  const byVehicle = new Map<string, Map<string, number>>();
  const vehicleTotals = new Map<string, number>();

  for (const r of runs) {
    const slot = courseSlotKeyFromCourseId(r.courseId);
    const w = Number(r.weight);
    const weight = Number.isFinite(w) && w >= 0 ? w : 1;

    let slotMap = byVehicle.get(r.vehicleId);
    if (!slotMap) {
      slotMap = new Map();
      byVehicle.set(r.vehicleId, slotMap);
    }
    slotMap.set(slot, (slotMap.get(slot) ?? 0) + weight);
    vehicleTotals.set(r.vehicleId, (vehicleTotals.get(r.vehicleId) ?? 0) + weight);
  }

  return { byVehicle, vehicleTotals };
}

/** run weight から合計 100 の整数％（最大余り法） */
export function allocateIntegerPercentsFromWeights(
  slotWeights: Map<string, number>
): Map<string, number> {
  let total = 0;
  for (const w of slotWeights.values()) total += w;
  if (total <= 0) return new Map();

  const raw = Array.from(slotWeights.entries()).map(([slotKey, w]) => ({
    slotKey,
    amount: (100 * w) / total,
  }));
  let sumFloored = 0;
  const floored = raw.map((r) => {
    const f = Math.floor(r.amount);
    sumFloored += f;
    return { slotKey: r.slotKey, floored: f, frac: r.amount - f };
  });
  let remainder = 100 - sumFloored;
  const sorted = [...floored].sort((a, b) => b.frac - a.frac);
  let i = 0;
  while (remainder > 0 && sorted.length > 0) {
    sorted[i % sorted.length].floored += 1;
    remainder -= 1;
    i++;
    if (i > sorted.length * 1000) break;
  }
  const result = new Map<string, number>();
  for (const row of sorted) {
    result.set(row.slotKey, row.floored);
  }
  return result;
}

function distributeWithRemainder(
  total: number,
  slots: Array<{ slotKey: string; share: number }>
): Map<string, number> {
  const result = new Map<string, number>();
  if (slots.length === 0 || total === 0) return result;

  const raw = slots.map((s) => ({
    slotKey: s.slotKey,
    amount: total * s.share,
  }));
  let sumFloored = 0;
  const floored = raw.map((r) => {
    const f = Math.floor(r.amount * 100) / 100;
    sumFloored += f;
    return { slotKey: r.slotKey, floored: f, frac: r.amount - f };
  });
  let remainder = Math.round((total - sumFloored) * 100) / 100;
  const sorted = [...floored].sort((a, b) => b.frac - a.frac);
  let i = 0;
  while (remainder > 0 && sorted.length > 0) {
    sorted[i % sorted.length].floored =
      Math.round((sorted[i % sorted.length].floored + 0.01) * 100) / 100;
    remainder = Math.round((remainder - 0.01) * 100) / 100;
    i++;
    if (i > sorted.length * 1000) break;
  }
  for (const row of sorted) {
    result.set(row.slotKey, row.floored);
  }
  return result;
}

function isAllocatableLeafAccount(item: {
  category: string;
  isSubtotal: boolean;
}): boolean {
  if (item.isSubtotal) return false;
  if (
    item.category === "subtotal_revenue" ||
    item.category === "subtotal_expense" ||
    item.category === "subtotal_gross" ||
    item.category === "summary"
  ) {
    return false;
  }
  return item.category === "revenue" || item.category === "expense";
}

export function isCourseReadOnlyAccount(item: {
  category: string;
  name: string;
  code: string;
  isDriverRelated: boolean;
}): boolean {
  if (item.isDriverRelated) return true;
  if (isVehicleCostAccount(item.code)) return true;
  if (item.code === "6175" || item.code === "6176") return true;
  if (isLocationExpenseProrationAccount(item.code)) return true;
  if (item.category === "revenue" && !MANUAL_INPUT_ONLY_NAMES.includes(item.name)) {
    return true;
  }
  return false;
}

export async function runCourseAllocation(
  yearMonth: string,
  locationId: string
): Promise<{ courseSlotsUpdated: number; recordsWritten: number }> {
  const [{ recordMap, accountItems, revenueFromSpreadsheetIds }, weights, courseRevenueLines] =
    await Promise.all([
      buildVehicleRecordMapForLocation(yearMonth, locationId),
      buildCourseShareWeights(yearMonth, locationId),
      prisma.driveSpreadsheetRevenueCourseLine.findMany({
        where: { yearMonth, locationId },
      }),
    ]);

  const aggregated = new Map<string, number>();
  const leafItems = accountItems.filter(isAllocatableLeafAccount);

  for (const item of leafItems) {
    if (revenueFromSpreadsheetIds.has(item.id)) {
      continue;
    }

    for (const [key, amount] of recordMap) {
      const suffix = `-${item.id}`;
      if (!key.endsWith(suffix)) continue;
      const vehicleId = key.slice(0, key.length - suffix.length);
      const total = Number(amount);
      if (total === 0) continue;

      const vehicleTotal = weights.vehicleTotals.get(vehicleId) ?? 0;
      const slotMap = weights.byVehicle.get(vehicleId);

      if (!slotMap || vehicleTotal <= 0) {
        const aggKey = `${NO_COURSE_SLOT_KEY}|${item.id}`;
        aggregated.set(aggKey, (aggregated.get(aggKey) ?? 0) + total);
        continue;
      }

      const slots = Array.from(slotMap.entries()).map(([slotKey, w]) => ({
        slotKey,
        share: w / vehicleTotal,
      }));
      const distributed = distributeWithRemainder(total, slots);
      for (const [slotKey, part] of distributed) {
        const aggKey = `${slotKey}|${item.id}`;
        aggregated.set(aggKey, (aggregated.get(aggKey) ?? 0) + part);
      }
    }
  }

  for (const line of courseRevenueLines) {
    if (!revenueFromSpreadsheetIds.has(line.accountItemId)) continue;
    const aggKey = `${line.courseId}|${line.accountItemId}`;
    aggregated.set(aggKey, (aggregated.get(aggKey) ?? 0) + Number(line.amount));
  }

  await prisma.courseMonthlyRecord.deleteMany({
    where: { locationId, yearMonth },
  });

  let recordsWritten = 0;
  const slotKeys = new Set<string>();
  for (const aggKey of aggregated.keys()) {
    const sep = aggKey.indexOf("|");
    if (sep < 1) continue;
    slotKeys.add(aggKey.slice(0, sep));
  }

  for (const [aggKey, amount] of aggregated) {
    if (amount === 0) continue;
    const sep = aggKey.indexOf("|");
    const courseSlotKey = aggKey.slice(0, sep);
    const accountItemId = aggKey.slice(sep + 1);
    const courseId =
      courseSlotKey === NO_COURSE_SLOT_KEY ? null : courseSlotKey;

    await prisma.courseMonthlyRecord.create({
      data: {
        locationId,
        yearMonth,
        courseSlotKey,
        courseId,
        accountItemId,
        amount: Math.round(amount * 100) / 100,
      },
    });
    recordsWritten++;
  }

  return { courseSlotsUpdated: slotKeys.size, recordsWritten };
}
