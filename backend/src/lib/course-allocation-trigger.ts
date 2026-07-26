import { prisma } from "./prisma.js";
import { runCourseAllocation } from "./course-allocation.js";

/** 対象拠点（未指定時は全拠点）のコース別集計を再計算 */
export async function runCourseAllocationScope(
  yearMonth: string,
  locationId?: string | null
): Promise<Array<{ locationId: string; recordsWritten: number }>> {
  if (locationId) {
    const r = await runCourseAllocation(yearMonth, locationId);
    return [{ locationId, recordsWritten: r.recordsWritten }];
  }
  const locations = await prisma.location.findMany({ select: { id: true } });
  const results: Array<{ locationId: string; recordsWritten: number }> = [];
  for (const loc of locations) {
    const r = await runCourseAllocation(yearMonth, loc.id);
    results.push({ locationId: loc.id, recordsWritten: r.recordsWritten });
  }
  return results;
}
