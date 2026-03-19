/**
 * 各拠点のスプレッドシートから売上データを参照するモジュール
 *
 * 各拠点のスプレッドシートで売上を管理しているため、そちらのデータを参照する。
 * 参照ロジックは別途作成予定。
 *
 * @see docs/external-integration-spec.md
 * @see docs/account-item-calculation-spec.md
 */

export interface GetRevenueFromSpreadsheetsParams {
  /** 拠点ID（Location.id） */
  locationId: string;
  /** 対象年月（YYYY-MM） */
  yearMonth: string;
  /** 対象車両ID一覧 */
  vehicleIds: string[];
  /** 売上科目の勘定科目ID一覧（category=revenue のもの） */
  revenueAccountItemIds: string[];
}

/**
 * 各拠点のスプレッドシートから売上データを取得する。
 *
 * 戻り値は Map<"vehicleId-accountItemId", amount> 形式。
 *
 * TODO: 各拠点のスプレッドシート（Google Sheets 等）への接続ロジックを実装
 * - 拠点ごとのスプレッドシートURL/IDのマッピング
 * - 認証（サービスアカウント等）
 * - データ取得・パース（vehicleNo + 勘定科目 → 金額）
 */
export async function getRevenueFromSpreadsheets(
  _params: GetRevenueFromSpreadsheetsParams
): Promise<Map<string, number>> {
  // スタブ: 空の Map を返す。実装時は各拠点スプレッドシートから取得したデータを返す
  return new Map();
}
