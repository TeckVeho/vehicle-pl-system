# エンジニア向け残タスクリスト

車両別損益計算システム（IZUMI）の開発残タスクを、優先度・受け入れ条件・参照ファイルパス付きでまとめたドキュメントです。インフラ・デプロイ作業は含みません。

---

## P0: 売上スプレッドシート連携

### 現状

- [`backend/src/lib/spreadsheet-revenue.ts`](backend/src/lib/spreadsheet-revenue.ts) の `getRevenueFromSpreadsheets` は **常に空の Map を返すスタブ**（TODO コメントあり）
- [`backend/src/routes/income-statement.ts`](backend/src/routes/income-statement.ts) では、該当勘定科目（山崎製パン〜関東運輸）について **MonthlyRecord を読まず**（L170–188）、スプレッドシート結果のみをマージしている
- そのため、**連携未実装の間は当該売上セルに数値が載らない**（インポートで MonthlyRecord に入れても表示されない）

### タスク

1. **参照元の確定**: Google Sheets または別システムのスプレッドシート URL/ID を決定
2. **接続ロジックの実装**:
   - 拠点（Location）→ スプレッドシート ID/URL のマッピング（DB または設定）
   - 認証（サービスアカウント等）
   - データ取得・パース（vehicleNo + 勘定科目 → 金額）
3. **`getRevenueFromSpreadsheets` の本実装**: 上記を `spreadsheet-revenue.ts` に組み込み
4. **未接続・取得失敗時の挙動の仕様化**:
   - MonthlyRecord フォールバック / 画面警告 / ゼロ表示のいずれかを明文化
   - `income-statement.ts` および `dashboard.ts` と整合させる

### 受け入れ条件

- 指定年月・拠点で、スプレッドシート参照対象の売上科目に正しい金額が表示される
- 取得失敗時は仕様に準拠した挙動（フォールバック or 警告）

### 参照ドキュメント

- [account-item-calculation-spec.md](account-item-calculation-spec.md) 2.1 売上科目
- [external-integration-spec.md](external-integration-spec.md) 1.2 データ連携元

---

## P1: 外部システム連携の受入準備

### 現状

- sync API は実装済みだが、外部システム側が連携を開始するための**本システム側の準備**が未整備の可能性がある
- [external-integration-spec.md](external-integration-spec.md) に仕様はあるが、連携テスト環境・エラー仕様の明文化が不足している場合がある

### タスク

1. **API 仕様の整備**: 外部システム側のエンジニアが参照しやすい形で sync API の仕様・サンプルリクエストを整備（external-integration-spec の見直し・補足）
2. **連携テスト環境の提供**: 外部システムが連携テストを実行できる環境（認証情報・エンドポイント）の提供。ローカル or 既存環境での検証をスコープとする
3. **エラー・リトライ仕様の明文化**: 400/401/403/500 のレスポンス形式、リトライ推奨方針、部分失敗時の扱いを仕様書に記載
4. **連携検証・調整**: イズミクラウド・タイムシート等との接続テスト、データ形式・必須項目のすり合わせ

### 受け入れ条件

- 外部システム側のエンジニアが仕様書のみで連携実装を進められる
- 連携テストを実行できる環境・認証情報が提供されている

### 参照ドキュメント

- [external-integration-spec.md](external-integration-spec.md) 外部連携仕様
- [department-id-standard.md](department-id-standard.md) 部門 ID の運用

---

## P2: 自動テストの導入

### 現状

- ルート・バックエンドの `package.json` に **Vitest/Jest 等のテストランナー設定が無い**

### タスク

1. **テストランナー導入**: バックエンドに Vitest または Jest を導入し、`npm test` で実行可能にする
2. **ユニットテスト**: 配賦・按分ロジック（[`backend/src/lib/`](backend/src/lib/) の `driver-allocation`、`location-expense-allocation`、`salary-daily-proration` 等）のユニットテスト
3. **契約テスト**: 主要 sync ルート（vehicles 等）の API 契約テスト

### 受け入れ条件

- `npm test` でローカル実行可能
- 上記ロジックの主要パスがカバーされている

### スコープ外

- CI への組み込み、デプロイパイプラインは含まない（インフラに触れない範囲）

---

## 付録: 連携元システムと連携データ一覧（現行仕様）

仕様書（[external-integration-spec.md](external-integration-spec.md)）に基づく連携元とデータの一覧です。連携元システムの誤りがあれば仕様書と本一覧をあわせて修正してください。

### 連携元システム一覧

| 連携元システム | 種別 | 説明 |
|---------------|------|------|
| **イズミクラウド** | マスタ・トランザクション | 自社システム。ユーザー、部門、車両、コース、ドライバー、車両月次費用のマスタを連携。経理データ（月次損益の経費等）は CSV/bulk で連携 |
| **タイムシート** | トランザクション | 日次乗務記録、ドライバー別月次金額、日次稼働・走行データを連携 |
| **各拠点のスプレッドシート** | 売上データ | 売上（山崎製パン〜関東運輸）は各拠点でスプレッドシート管理。本システムが参照する形（P0 で実装予定） |
| **PCA**（イズミクラウド経由） | マスタ | 拠点別月額経費（旅費交通費・消耗品・修繕費等 20 科目）をイズミクラウド経由で連携 |
| **ATMTC** | 紐づきデータ | ドライバーとコースの紐づきを取得。**イズミクラウド経由**で本システムに連携（ATMTC から直接は連携しない） |
| **ITP** | 車両月次費用の一部 | 燃費・道路使用料の生データ。**イズミクラウド経由**で vehicle-monthly-costs/sync に含めて連携 |

### 連携データ一覧（システム別）

| 連携元 | データ | 連携方式 | 備考 |
|--------|--------|----------|------|
| イズミクラウド | ユーザー | POST /api/users/sync | user_id（externalId）で識別 |
| イズミクラウド | 部門（Department） | 本システム内で管理 | department id でイズミクラウドと整合 |
| イズミクラウド | 車両 | POST /api/vehicles/sync | 部門・コース紐づけ |
| イズミクラウド | コース | POST /api/courses/sync | 車両番号と 1:1 対応 |
| イズミクラウド | ドライバー | POST /api/drivers/sync | ATMTC の紐づきを経由 |
| イズミクラウド | 車両月次費用 | POST /api/vehicle-monthly-costs/sync | 償却・リース・自賠責・賦課税。ITP 由来の燃費・道路使用料含む |
| イズミクラウド等 | 月次損益（経費等） | POST /api/import または records/bulk | 経理データ。売上はスプレッドシート参照 |
| タイムシート | 日次乗務記録 | POST /api/driver-assignments/sync | ドライバー配賦の入力。連携後に配賦計算を実行 |
| タイムシート | ドライバー別月次金額 | POST /api/driver-monthly-amounts/sync | 乗務員給料等の配賦元 |
| タイムシート | 日次稼働・走行 | POST /api/daily-operating/sync | 回数単価・月額単価計算用 |
| 各拠点スプレッドシート | 売上（山崎製パン〜関東運輸） | 本システムが参照（getRevenueFromSpreadsheets） | P0 で実装予定。現状スタブ |
| PCA（イズミクラウド経由） | 拠点別月額経費 | POST /api/location-monthly-expenses/sync | 旅費交通費・消耗品・修繕費等 20 科目。車両数で按分 |

---

## 関連ドキュメント

| ドキュメント | 内容 |
|--------------|------|
| [progress-report.md](progress-report.md) | 実装済み機能・進捗一覧 |
| [system-overview.md](system-overview.md) | システム概要・アーキテクチャ |
| [external-integration-spec.md](external-integration-spec.md) | 外部連携 API 仕様 |
| [external-system-implementation-checklist.md](external-system-implementation-checklist.md) | 各外部システム側で実装が必要な内容 |
| [account-item-calculation-spec.md](account-item-calculation-spec.md) | 勘定科目ごとの取得・計算方法 |
