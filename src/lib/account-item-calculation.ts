/**
 * 勘定科目ごとの取得・計算方法を返すユーティリティ
 * docs/account-item-calculation-spec.md に基づく
 */

export interface AccountItemForCalc {
  code: string;
  name: string;
  category: string;
  isSubtotal: boolean;
  isDriverRelated: boolean;
  revenuePricingType?: string | null;
}

export interface CalculationLogic {
  /** 取得方法（手動入力、イズミクラウド連携、ドライバー配賦、集計など） */
  method: string;
  /** 計算ロジックの詳細説明 */
  detail: string;
}

/** イズミクラウド連携の科目コード */
const IZUMI_CLOUD_CODES = ["6191", "6192", "6193", "6194", "6195"];

/** 拠点別経費按分（別システム連携）の科目コード */
const LOCATION_EXPENSE_PRORATION_CODES = [
  "6150", "6151", "6154", "6156", "6159", "6160", "6162", "6164",
  "6165", "6166", "6167", "6168", "6171", "6172", "6173", "6174",
  "6177", "6178", "6188", "6189",
];

/** 日次単価按分のドライバー配賦科目名（業務給料は手動インポートのため除外） */
const DRIVER_DAILY_PRICE_NAMES = ["乗務員給料", "通勤手当"];

/** 乗務日数按分のドライバー配賦科目名（福利厚生費は手動インポートのため除外） */
const DRIVER_WORK_DAY_NAMES = ["法定福利費"];

/** 手入力のみの売上科目名（CSV/API一括不可） */
const MANUAL_ONLY_REVENUE_NAMES = ["その他", "不動産収入", "人材派遣収入"];

/**
 * 勘定科目の計算ロジックを取得する
 */
export function getCalculationLogic(
  item: AccountItemForCalc
): CalculationLogic {
  // 集計・サマリー科目
  if (item.isSubtotal) {
    return getSubtotalLogic(item.code);
  }

  // 業務給料・福利厚生費は手動インポート（ドライバー配賦ではない）
  if (
    item.code === "6139" ||
    item.name === "業務給料" ||
    item.code === "6149" ||
    item.name === "福利厚生費"
  ) {
    return {
      method: "手動入力",
      detail: "CSVインポート / API一括 / 画面編集",
    };
  }

  // イズミクラウド連携（車両月次費用）
  if (IZUMI_CLOUD_CODES.includes(item.code)) {
    return {
      method: "イズミクラウド連携",
      detail:
        "VehicleMonthlyCost を優先（MonthlyRecord は参照しない）。POST /api/vehicle-monthly-costs/sync で連携",
    };
  }

  // ドライバー配賦科目
  if (item.isDriverRelated) {
    if (DRIVER_DAILY_PRICE_NAMES.includes(item.name)) {
      return {
        method: "ドライバー配賦",
        detail:
          "乗車回数ベース配賦。各ドライバーの1日単価×乗車回数を車両別に累計し MonthlyRecord に保存。driver-assignments / driver-monthly-amounts / daily-operating の sync 時に実行",
      };
    }
    if (DRIVER_WORK_DAY_NAMES.includes(item.name)) {
      return {
        method: "ドライバー配賦",
        detail:
          "乗務日数按分。1日複数車両に乗務した場合は 1/車両数 で按分。タイムシート連携後、車両別に按分",
      };
    }
    return {
      method: "ドライバー配賦",
      detail:
        "タイムシート連携後、車両別に按分。POST /api/driver-monthly-amounts/sync、POST /api/driver-assignments/sync",
    };
  }

  // 売上科目：手入力のみ（スプレッドシート参照対象外）
  if (item.category === "revenue" && MANUAL_ONLY_REVENUE_NAMES.includes(item.name)) {
    return {
      method: "手入力のみ",
      detail: "画面編集のみ（CSV/API一括不可）",
    };
  }

  // 売上科目：スプレッドシート参照（山崎製パン〜関東運輸）
  if (item.category === "revenue") {
    return {
      method: "スプレッドシート参照",
      detail: "各拠点のスプレッドシートから参照",
    };
  }

  // 経費科目：拠点別経費連携（車両数按分）
  if (item.category === "expense" && LOCATION_EXPENSE_PRORATION_CODES.includes(item.code)) {
    return {
      method: "拠点別経費連携",
      detail:
        "別システムから拠点ごとに月額連携。各拠点の車両数で按分。POST /api/location-monthly-expenses/sync",
    };
  }

  // 燃料費（ITP連携＋計算）
  if (item.code === "6175" || item.name === "燃料費") {
    return {
      method: "ITP連携＋計算",
      detail:
        "イズミクラウド経由でITPから前月の燃費（L）を連携。燃費 × 燃料単価（拠点別パラメータ）で算出。表示月は前月データを使用",
    };
  }

  // 道路使用料（ITP連携＋計算）
  if (item.code === "6176" || item.name === "道路使用料") {
    return {
      method: "ITP連携＋計算",
      detail:
        "イズミクラウド経由でITPから前月の道路使用料を連携。道路使用料 × 使用料割引率（拠点別パラメータ）で算出。表示月は前月データを使用",
    };
  }

  // 経費科目：手動入力
  return {
    method: "手動入力",
    detail: "CSVインポート / API一括 / 画面編集",
  };
}

function getSubtotalLogic(code: string): CalculationLogic {
  switch (code) {
    case "SUBTOTAL_REV":
      return { method: "集計", detail: "売上科目（revenue）の合計" };
    case "SUBTOTAL_EXP":
      return { method: "集計", detail: "経費科目（expense）の合計" };
    case "SUBTOTAL_GROSS":
      return { method: "集計", detail: "純売上高 − 自車原価計" };
    case "SUMMARY_REV":
      return { method: "集計", detail: "純売上高と同値" };
    case "SUMMARY_EXP":
      return { method: "集計", detail: "自車原価計と同値" };
    case "SUMMARY_GROSS":
      return { method: "集計", detail: "自車粗利益と同値" };
    default:
      return { method: "集計", detail: "他科目の合計・差額から算出" };
  }
}
