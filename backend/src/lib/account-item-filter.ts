import type { Prisma } from "@prisma/client";

/**
 * 指定年月に有効な勘定科目のPrisma where条件を返す
 */
export function accountItemEffectiveWhere(yearMonth: string): Prisma.AccountItemWhereInput {
  return {
    OR: [
      { effectiveFrom: null, effectiveTo: null },
      { effectiveFrom: null, effectiveTo: { gte: yearMonth } },
      { effectiveFrom: { lte: yearMonth }, effectiveTo: null },
      {
        effectiveFrom: { lte: yearMonth },
        effectiveTo: { gte: yearMonth },
      },
    ],
  };
}
