# 売上スプレッドシート取込・計算仕様書

拠点別スプレッドシート（xlsx）から売上データを取り込み、車両損益計算書（VPL）の売上科目に反映するための実装仕様です。

---

## 1. 概要

### 1.1 背景と目的

売上データは各拠点でスプレッドシート（xlsx）により管理されている。現状の `getRevenueFromSpreadsheets` 関数はスタブのみで、実際の取得ロジックは未実装（`TODO`）となっている。本仕様書は、以下を定義することを目的とする。

- xlsx ファイルの構造とパース方法
- 荷主ごとの売上計算ルール（固定月額 vs 日額 × 稼働日数）
- `getRevenueFromSpreadsheets` の実装方針
- 必要なスキーマ追加・変更

### 1.2 対象ファイル

| 項目 | 内容 |
|------|------|
| ファイル形式 | xlsx（Excel）、または CSV エクスポート |
| 命名規則 | `{拠点番号}({拠点名})損益計算資料{年月}.xlsx` |
| シート | 売上明細シートを対象とする |
| 更新頻度 | 月次（対象年月ごとに 1 ファイル） |

---

## 2. xlsx ファイル構造

### 2.1 全体レイアウト

売上明細シートは複数の「荷主セクション」が**横に並んで配置**されている。各セクションは独立した表として構成される。

```
[列方向]
| 山パン セクション | エコー セクション | その他 セクション |
| (A〜G列)        | (I〜P列)        | (R〜X列)        |

[行方向]
行1-3  : 拠点名・合計行（ヘッダー情報）
行4    : 各セクションの列ヘッダー
行5〜16: 各コースのデータ行
行17   : 合計行
行18   : 空行（セクション区切り）
行19-33: 第2グループ（末広製菓, 関東運輸, 遠州トラ 等）
行35-49: 第3グループ（YB荷役, 傭車使用, 菱倉運輸 等）
```

### 2.2 山パンセクション（固定月額型）

| 列 | フィールド名 | 説明 |
|----|------------|------|
| A | コース名 | コース識別コード（例: 3703, 3705） |
| B | 運賃 | 月額契約運賃（円） |
| C | 日額損益用 | 運賃 ÷ 稼働日数（参考値） |
| D | 高速 | 高速代（円、月計） |
| E | 日数 | 稼働日数 |
| F | 高速代 | 高速代合計（円） |
| G | 月額 | 売上月額（＝運賃、固定） |

**計算規則**: 月額売上 = 運賃（列B）をそのまま使用。稼働日数によらず固定。

### 2.3 エコーセクション（日額単価型）

| 列 | フィールド名 | 説明 |
|----|------------|------|
| I | コース名 | ドライバー名 or コース名（例: 長谷川） |
| J | 運賃 | 基本運賃（円） |
| K | 日額損益用 | 運賃 + 加算 ÷ 稼働日数 |
| L | 高速 | 高速代合計 |
| M | 日数 | 稼働日数 |
| N | 時間加算 | 時間加算額（円） |
| O | 距離加算 | 距離加算額（円） |
| P | 月額 | 売上月額（＝日額損益用 × 稼働日数） |

**計算規則**: 月額売上 = 日額損益用 × 実稼働日数。稼働日数は `DailyOperatingRecord` から取得する。

### 2.4 サンプルデータ（2026年2月・神戸営業所）

**山パン（固定月額）**

| コース | 運賃 | 日数 | 日額損益用 | 月額 |
|--------|------|------|----------|------|
| 3703 | 946,000 | 31 | 30,516 | 946,000 |
| 3705 | 946,000 | 31 | 30,516 | 946,000 |
| 3706 | 1,008,000 | 31 | 33,843 | 1,008,000 |
| 3708 | 952,000 | （未入力） | — | 952,000 |
| 3710 | 968,000 | （未入力） | — | 968,000 |
| 3715 | 947,000 | （未入力） | — | 947,000 |

**エコー（日額単価）**

| コース | 運賃 | 日数 | 日額損益用 | 月額 |
|--------|------|------|----------|------|
| 長谷川 | 42,218 | 20 | 48,566 | 971,315 |

---

## 3. 売上計算ルール

### 3.1 荷主別計算方式の分類

| 荷主名 | 計算方式 | 月額売上の算出方法 |
|--------|---------|-----------------|
| 山パン（山崎製パン） | **固定月額** | xlsx の 月額（運賃）列をそのまま使用 |
| エコー（富士エコー） | **日額単価** | 日額損益用 × 実稼働日数 |
| 末広製菓 | 日額単価 | 日額損益用 × 実稼働日数 |
| 関東運輸 | 日額単価 | 日額損益用 × 実稼働日数 |
| 遠州トラック | 日額単価 | 日額損益用 × 実稼働日数 |
| 菱倉運輸 | 日額単価 | 日額損益用 × 実稼働日数 |
| YB荷役 | 個数単価 | 単価 × 個数（別途要確認） |
| 傭車使用 | 実績額 | xlsx の月額をそのまま使用（別途要確認） |

> **注意**: YB荷役・傭車使用は経費（支払い運賃）扱いのため、売上科目ではなく経費科目として扱う可能性がある。勘定科目の category 定義を別途確認すること。

### 3.2 日額損益用の計算式

```
日額損益用 = 月額運賃 ÷ 稼働日数
```

- 稼働日数 = `DailyOperatingRecord` の `isOperating = true` の件数（対象年月・対象車両）
- エコーの場合: 時間加算・距離加算が含まれるため、`(月額運賃 + 時間加算 + 距離加算) ÷ 稼働日数` となる可能性がある（Excel 数式の確認が必要）

### 3.3 日額単価型における月額売上の計算

```
月額売上 = 日額損益用 × 実稼働日数（当月）
```

稼働日数は別システム（タイムシート）から `DailyOperatingRecord` に連携されるため、以下の処理が必要：

```
1. DailyOperatingRecord から yearMonth・vehicleId でフィルタ
2. isOperating = true の件数をカウント → 実稼働日数
3. 月額売上 = 日額損益用 × 実稼働日数
```

---

## 4. スキーマ追加・変更

### 4.1 Location テーブルへの追加フィールド

xlsx ファイル名に含まれる拠点番号（例: `16`）と DB の `Location` を紐づけるため、`Location` に `fileCode` フィールドを追加する。

```prisma
model Location {
  // ... 既存フィールド ...

  fileCode  String? @unique  // xlsx ファイル名の拠点番号（例: "16"）
}
```

| フィールド | 型 | 説明 |
|-----------|----|----|
| `fileCode` | String? @unique | ファイル名先頭の拠点番号（文字列）。未設定の拠点は xlsx 連携対象外 |

**設定例（seed または管理画面で登録）**:

| Location.code | Location.name | fileCode |
|---------------|---------------|----------|
| LOC020 | 神戸 | 16 |
| LOC002 | 横浜第1 | 02 |
| LOC019 | 大阪 | 19 |
| ... | ... | ... |

> `Location.code`（`LOC020` 形式）はイズミクラウドとの連携識別子であり、ファイル番号とは別の体系。`fileCode` を専用フィールドとして分離することで、両者の独立性を保つ。

---

### 4.2 AccountItem テーブルへの追加フィールド

計算方式は**荷主（AccountItem）レベルの概念**であるため、`AccountItem` に 1 フィールドを追加する。Course への変更は不要。

```prisma
model AccountItem {
  // ... 既存フィールド ...

  revenueCalcType  String?  // xlsx売上計算方式: "fixed_monthly" | "daily_rate" | null
}
```

| フィールド | 型 | 値 | 説明 |
|-----------|----|----|------|
| `revenueCalcType` | String? | `"fixed_monthly"` | 月額固定（山パン）|
| `revenueCalcType` | String? | `"daily_rate"` | 日額 × 稼働日数（エコー等）|
| `revenueCalcType` | String? | `null` | 対象外（手入力専用科目など） |

> **Course に追加しない理由**:
> - 計算方式は荷主単位のルールであり、同じ荷主の全コースが同じ方式を使う
> - `dailyRate`（日額単価）は月次で変動するため、マスタに保持するのは不適切。パース時に都度算出する
> - `clientName`（荷主名）は後述のセクション定義（設定定数）で管理する

### 4.3 既存 AccountItem.revenuePricingType との関係

`AccountItem.revenuePricingType` は**日次サマリー表示での按分方法**を制御するフィールドであり、月次売上の計算方法とは別の概念。混同しないこと。

| フィールド | 用途 | 方向 |
|-----------|------|------|
| `AccountItem.revenuePricingType` | 月次→日次の按分表示 | 月次合計 → 各日への配分（既存） |
| `AccountItem.revenueCalcType`（新規） | 月次売上の算出方法 | xlsx の値 or 日額×稼働日数 → 月次合計 |

山パン（固定月額）の場合、`revenueCalcType = "fixed_monthly"` かつ `revenuePricingType = "monthly"` を設定する。

---

## 5. `getRevenueFromSpreadsheets` 実装仕様

### 5.1 関数シグネチャ（現状）

```typescript
// backend/src/lib/spreadsheet-revenue.ts
export async function getRevenueFromSpreadsheets(
  params: GetRevenueFromSpreadsheetsParams
): Promise<Map<string, number>>
```

戻り値: `Map<"vehicleId-accountItemId", amount>`

### 5.2 処理フロー

```
[1] xlsx ファイルの特定
      locationId → Location.fileCode を取得
      yearMonth  → "{fileCode}({拠点名})損益計算資料{YYYY}.{MM}.xlsx" の形式でファイルを検索
      例: fileCode="16", name="神戸", yearMonth="2026-02"
          → "16(神戸)損益計算資料2026.02.xlsx"

[2] xlsx パース
      各セクション（荷主）のヘッダー行を検出
      コース行（データ行）を抽出
      コース名 → vehicleId の解決（Vehicle.vehicleNo or Course.code で照合）

[3] 稼働日数の取得（日額単価型のみ）
      DailyOperatingRecord.isOperating = true の件数
      対象: yearMonth × vehicleId

[4] 月額売上の計算
      固定月額型: xlsx の「月額」列の値をそのまま使用
      日額単価型: xlsx の「日額損益用」列 × 実稼働日数

[5] Map に格納して返却
      key: "vehicleId-accountItemId"
      value: 計算後の月額売上（円）
```

### 5.3 ファイル名パースと拠点特定ロジック

Drive 上のファイルを拠点・年月で特定するための命名規則と解決ロジック。

**ファイル命名規則**

```
{fileCode}({拠点名})損益計算資料{YYYY}.{MM}.xlsx
例: 16(神戸)損益計算資料2026.02.xlsx
```

**正規表現パターン**

```typescript
const FILENAME_PATTERN = /^(\d+)\((.+?)\)損益計算資料(\d{4})\.(\d{2})\.xlsx$/;

function parseRevenueFilename(filename: string) {
  const match = filename.match(FILENAME_PATTERN);
  if (!match) return null;
  return {
    fileCode: match[1],           // "16"
    locationName: match[2],       // "神戸"
    yearMonth: `${match[3]}-${match[4]}`, // "2026-02"
  };
}
```

**拠点の特定ロジック**

```typescript
// locationId → Location.fileCode → ファイル名を生成して Drive 上のファイルを取得
async function resolveFilename(locationId: string, yearMonth: string): Promise<string | null> {
  const location = await prisma.location.findUnique({
    where: { id: locationId },
    select: { fileCode: true, name: true },
  });
  if (!location?.fileCode) return null; // fileCode 未設定の拠点はスキップ

  const [year, month] = yearMonth.split("-");
  return `${location.fileCode}(${location.name})損益計算資料${year}.${month}.xlsx`;
}
```

**逆方向（ファイル名 → locationId の解決）**

ファイル一覧から自動処理する場合は `fileCode` で `Location` を検索する。

```typescript
// fileCode で Location を特定
const location = await prisma.location.findFirst({
  where: { fileCode: parsedFilename.fileCode },
});
```

---

### 5.5 コース名 → vehicleId の解決

xlsx のコース名（例: 3703, 長谷川）と DB の Vehicle/Course を紐づけるため：

```
xlsx コース名
  → Course.code で照合（優先）
  → Vehicle.vehicleNo で照合（フォールバック）
  → 一致しない場合はスキップ（エラーログを出力）
```

### 5.6 エラー処理

| 状況 | 処理 |
|------|------|
| `Location.fileCode` が未設定 | 空の Map を返す（ログ出力）|
| xlsx ファイルが見つからない | 空の Map を返す（ログ出力） |
| コース名が DB に存在しない | 該当行をスキップ（ログ出力） |
| 日数が 0 または未入力 | 日額単価型は 0 として扱う |
| セルに `#ERROR!` / `#DIV/0!` | 0 として扱う |

### 5.7 xlsx セクション検出ロジック

xlsx の各セクションは列方向に並んでいるため、ヘッダー行（行4相当）をスキャンして荷主名を検出する。

```typescript
// セクション定義（拠点ごとに設定ファイルで管理）
const SECTION_DEFINITIONS = [
  {
    clientName: "山パン",
    startCol: 0,        // A列
    revenueCalcType: "fixed_monthly",
    fields: {
      courseName: 0,    // A列: コース名
      monthlyAmount: 6, // G列: 月額
      dayCount: 4,      // E列: 日数
      dailyRate: 2,     // C列: 日額損益用
    },
  },
  {
    clientName: "エコー",
    startCol: 8,        // I列
    revenueCalcType: "daily_rate",
    fields: {
      courseName: 8,    // I列: コース名
      monthlyAmount: 15,// P列: 月額
      dayCount: 12,     // M列: 日数
      dailyRate: 10,    // K列: 日額損益用
    },
  },
  // ... 以下同様
];
```

---

## 6. 車両別・コース別の表示対応

### 6.1 現状の表示単位

損益計算書 API は **Vehicle（車両）を最小単位**として設計されており、`MonthlyRecord` は `vehicleId × accountItemId × yearMonth` をキーとして保持する。

- **車両別表示**: 追加実装不要（既存 API がそのまま対応）
- **コース別表示**: `Vehicle.courseId` による GROUP BY 集計が必要

### 6.2 コース別集計ロジック

1 コース: N 車両の場合、コース別の売上 = そのコースに属する全車両の MonthlyRecord の合計。

```typescript
// コース別集計（フロントエンドまたは API 内で実装）
const courseRevenue = vehicles.reduce((acc, vehicle) => {
  const courseId = vehicle.courseId ?? vehicle.id; // コース未設定時は車両IDを使用
  const amount = recordMap.get(`${vehicle.id}-${accountItemId}`) ?? 0;
  acc.set(courseId, (acc.get(courseId) ?? 0) + amount);
  return acc;
}, new Map<string, number>());
```

### 6.3 表示切替について

現状の損益計算書は Vehicle 単位で列表示しており、1コース＝1車両の運用であればコース別と実質的に同じ。**表示切替の実装は、業務要件が確定してから別途対応する**。

---

## 7. xlsx 取込 API の追加（新規）

スプレッドシートデータの取込を手動トリガーできるよう、API を追加する。

### 7.1 `POST /api/revenue/spreadsheet/sync`

xlsx ファイルをアップロードし、売上データを `MonthlyRecord` に反映する。

| 項目 | 内容 |
|------|------|
| 権限 | EDIT_PL |
| Content-Type | multipart/form-data |

**リクエスト**

| パラメータ | 必須 | 説明 |
|-----------|------|------|
| `file` | ○ | xlsx ファイル |
| `locationId` | ○ | 対象拠点 ID |
| `yearMonth` | ○ | 対象年月（YYYY-MM） |
| `dryRun` | - | `true` の場合は DB に書き込まず検証結果のみ返す |

**レスポンス（成功）**

```json
{
  "success": true,
  "upserted": 18,
  "skipped": 2,
  "warnings": [
    "コース「長谷川」: 稼働日数が 0 のため売上を 0 として登録しました"
  ],
  "errors": []
}
```

**レスポンス（dryRun=true）**

```json
{
  "dryRun": true,
  "preview": [
    {
      "courseName": "3703",
      "vehicleNo": "3703",
      "clientName": "山パン",
      "revenueCalcType": "fixed_monthly",
      "monthlyAmount": 946000
    },
    {
      "courseName": "長谷川",
      "vehicleNo": "xxx",
      "clientName": "エコー",
      "revenueCalcType": "daily_rate",
      "dailyRate": 48566,
      "operatingDays": 20,
      "monthlyAmount": 971320
    }
  ]
}
```

---

## 8. 実装優先順位と依存関係

```
[Phase 1] スキーマ追加
  - Location に fileCode カラムを追加
  - AccountItem に revenueCalcType カラムを追加
  - DB マイグレーション実行
  - Location.fileCode に各拠点のファイル番号を登録（例: 神戸→"16"）
  - AccountItem.revenueCalcType に値を設定（山パン→fixed_monthly、エコー等→daily_rate）

[Phase 2] xlsx パーサー実装
  - backend/src/lib/spreadsheet-revenue.ts の実装
  - セクション定義（SECTION_DEFINITIONS）の定数化
  - セクション検出・コース行パース
  - コース名 → vehicleId の解決

[Phase 3] 稼働日数連携の確認
  - DailyOperatingRecord への isOperating フラグ連携が正しく行われているか確認
  - 日額単価型の計算が正常に動作するか検証

[Phase 4] API 追加
  - POST /api/revenue/spreadsheet/sync の実装
  - dryRun オプションによる事前検証機能

[Phase 5] フロントエンド対応（要件確定後に対応）
  - xlsx アップロード UI（手動トリガーが必要な場合のみ）
  - 車両別 / コース別の表示切替（1コース複数車両の運用が発生した場合のみ）
```

---

## 9. 未確定事項・要確認

| 項目 | 状況 | 確認先 |
|------|------|--------|
| エコーの日額損益用の計算式 | 時間加算・距離加算の詳細仕様が不明 | 担当者 |
| YB荷役・傭車使用の勘定科目区分 | 売上か経費かを確認 | 経理 |
| xlsx の保管場所 | Google Sheets / ローカルファイル / S3 等を確認 | インフラ担当 |
| 拠点ごとのセクション配置 | 神戸以外の拠点で列配置が異なる可能性 | 各拠点担当 |
| 端数処理ルール | 3706 の例で誤差あり（計算式を要確認） | 担当者 |
| コース名と Vehicle.vehicleNo の対応 | 3703 等がそのまま vehicleNo か、別途マッピングが必要か | システム担当 |

---

## 10. 関連ドキュメント

| ドキュメント | 内容 |
|--------------|------|
| [account-item-calculation-spec.md](account-item-calculation-spec.md) | 勘定科目ごとの取得・計算方法（2.1節: 売上科目） |
| [external-integration-spec.md](external-integration-spec.md) | 外部連携 API 仕様全体 |
| [db-schema.md](db-schema.md) | データベーススキーマ |
| [system-overview.md](system-overview.md) | システム概要 |
