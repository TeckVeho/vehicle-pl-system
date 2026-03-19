/**
 * 乗務員給料・通勤手当の按分ロジック
 *
 * 配賦は salary-run-count-allocation.ts で乗車回数ベースで実行し MonthlyRecord に保存。
 * 日次サマリー用の getSalaryDailyAmount は乗車回数ベースで日別金額を計算。
 *
 * ※業務給料（6139）は手動インポートのため、本按分対象外
 */

/** 日次単価按分対象の勘定科目コード（乗務員給料・通勤手当） */
export const SALARY_DAILY_PRORATION_CODES = ["6138", "6147"] as const;

/** 勘定科目が給与日次按分対象か */
export function isSalaryDailyProrationAccount(code: string): boolean {
  return (SALARY_DAILY_PRORATION_CODES as readonly string[]).includes(code);
}

/**
 * 前月の yearMonth を返す
 */
export function getPreviousYearMonth(yearMonth: string): string {
  const [y, m] = yearMonth.split("-").map(Number);
  if (m === 1) {
    return `${y - 1}-12`;
  }
  return `${y}-${String(m - 1).padStart(2, "0")}`;
}

/**
 * 日次サマリー用: その日1日分の金額を計算（1回あたり単価 × 乗車回数）
 * 乗車回数が0の場合は均等割りでフォールバック
 *
 * @param monthlyAmount 前月の車両別累計（乗車回数ベース配賦済み）
 * @param totalRunCountInMonth 表示月の乗車回数合計
 * @param runCount その日の乗車回数
 * @param daysInMonth 表示月の日数（乗車回数0時のフォールバック用）
 * @returns 1日分の金額
 */
export function getSalaryDailyAmount(
  monthlyAmount: number,
  totalRunCountInMonth: number,
  runCount: number,
  daysInMonth: number
): number {
  if (monthlyAmount === 0) return 0;
  if (totalRunCountInMonth > 0 && runCount > 0) {
    const unitPricePerRun = monthlyAmount / totalRunCountInMonth;
    return Math.round(unitPricePerRun * runCount * 100) / 100;
  }
  // 乗車回数が0の場合は均等割り
  return Math.round((monthlyAmount / daysInMonth) * 100) / 100;
}
