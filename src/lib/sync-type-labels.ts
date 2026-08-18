export const SYNC_TYPE_LABELS: Record<string, string> = {
  monthly_records: "月次損益データ",
  daily_revenue: "日次売上",
  daily_operating: "日次稼働",
  driver_assignments: "乗務記録（タイムシート）",
  atmtc_transactions: "ATMTC配送連携",
  spreadsheet_revenue: "Drive 売上スナップショット",
  account_items: "勘定科目マスタ",
  users: "ユーザー",
};

export function formatSyncType(type: string): string {
  return SYNC_TYPE_LABELS[type] ?? type;
}
