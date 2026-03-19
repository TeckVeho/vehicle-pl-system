/**
 * 拠点別月額経費の車両数按分ロジック
 *
 * 別システムから拠点ごとに月額データを連携し、各拠点の車両数で按分して
 * 車両別の MonthlyRecord に反映する。
 */

/** 拠点別経費按分対象の勘定科目コード */
export const LOCATION_EXPENSE_PRORATION_CODES = [
  "6150", // 旅費交通費（旅費交通地）
  "6151", // 消耗品
  "6154", // 修繕費
  "6156", // 通信費
  "6159", // 水道光熱費
  "6160", // 保険料
  "6162", // 租税公課
  "6164", // 他手数料
  "6165", // 交際接待費
  "6166", // 会議費
  "6167", // 広告宣伝費
  "6168", // 諸会費
  "6171", // 地代家賃
  "6172", // 借家料
  "6173", // 賃借料
  "6174", // 保守料
  "6177", // 図書研修費
  "6178", // 減価償却費
  "6188", // 雑費
  "6189", // 事故賠償費
] as const;

/** 勘定科目が拠点別経費按分対象か */
export function isLocationExpenseProrationAccount(code: string): boolean {
  return (LOCATION_EXPENSE_PRORATION_CODES as readonly string[]).includes(code);
}
