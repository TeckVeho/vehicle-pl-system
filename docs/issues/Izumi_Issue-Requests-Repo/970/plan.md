# Issue #970: [BE] VPL同期: location-monthly-expenses・PCA / Chi phí điểm & PCA - Implementation Plan

## 概要 (Overview)

**要件:** izumi-cloud（Laravel）で `pl_pca_data`（PCA）から月次データを集約し、VPL の `POST /api/location-monthly-expenses/sync` に `yearMonth` + `expenses[]`（`departmentId` = LOC、`accountItemCode`、`amount`）を送信する。親 Issue #956 Phase 3、`ic-sync-field-mapping.md` §6 に沿う。

**現状:** VPL 側の受信 API は vehicle-pl-system `backend/src/routes/location-monthly-expenses.ts` に実装済み。izumi-cloud には `VehicleMonthlyCostSyncService` + `VplSyncCommand` のトランザクション同期パターン（#969）がある。本件は同パターンで **`LocationMonthlyExpenseSyncService`** を新規追加し、**`location-monthly-expenses`** エンティティを `vpl:sync` に登録する。

**PCA → 勘定コード:** `issue.md` およびコード調査により、PCA の `account_item_code` は VPL 按分対象コード（`LOCATION_EXPENSE_PRORATION_CODES` と同一形式）で保存されている想定。**19 コード**に該当する行のみ送り、それ以外はスキップ＋警告ログ（例外にしない）。

---

## FE (Frontend)

### 1. Files need to edit:

**本 Issue では UI の変更は不要。** Next.js（`frontend_path: .`）の画面改修はスコープ外。VPL 側の拠点別経費表示は既存の `location-monthly-expenses` 連携結果に依存するのみ。

#### 1.1. （参考）確認のみ

- 連携後の按分・ダッシュボード挙動を手動確認する場合は既存画面を利用。コード変更は発生しない想定。

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `izumi-cloud/app/Services/Vpl/LocationMonthlyExpenseSyncService.php`（新規）

##### 1.1.1. Service skeleton and `buildPayload(string $yearMonth)`

`VehicleMonthlyCostSyncService`（`VehicleMonthlyCostSyncService.php` 31–153 行付近）と同様の構成。

**既存コード（参照）** (`VehicleMonthlyCostSyncService.php` 40–46 行):

- `buildPayload` が `['yearMonth', 'costs', 'skipped']` を返す。

**変更内容:**

- **新規クラス** `LocationMonthlyExpenseSyncService`：
  - `__construct()`：`config('vpl.log_channel', 'vpl-sync')` をログチャンネルに（31–38 行と同パターン）。
  - `buildPayload(string $yearMonth)`：
    - `PLPCAData` を `whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$yearMonth])` で取得。
    - **`department_id` + `account_item_code` ごとに `cost` を SUM**（issue.md の仕様）。DB 側で `groupBy` + `selectRaw`、またはコレクションで `groupBy` 後に `sum`。
    - `account_item_code` が **VPL 有効 19 コード**（`vehicle-pl-system/backend/src/lib/location-expense-proration.ts` 9–30 行の `LOCATION_EXPENSE_PRORATION_CODES` と**同一配列を PHP 定数で保持**）に含まれる行だけ `expenses[]` に載せる。含まれないコードは **`$skipped[]`** に理由付きで追加し、**warning ログ**（`CourseSyncService` と同様に `CourseSyncService::toDepartmentCode` を利用する前後でフィルタしてもよい）。
    - 各行：`departmentId` = `CourseSyncService::toDepartmentCode($department_id)`（文字列 `LOCxxx`）。
    - `accountItemCode` = PCA の文字列をそのまま（6150 系）。`amount` = `(float)` 集約後コスト。
  - 返却形：`['yearMonth' => $yearMonth, 'expenses' => [...], 'skipped' => [...]]`。

##### 1.1.2. `sync(VplClient $client, string $yearMonth)`

**既存コード（参照）** (`VehicleMonthlyCostSyncService.php` 162–216 行):

- `buildPayload` → 空なら early return。
- `$client->post('/api/vehicle-monthly-costs/sync', [...])`。
- `_error` なら `RuntimeException`。

**変更内容:**

- エンドポイントを **`/api/location-monthly-expenses/sync`** に変更。
- POST ボディ：`['yearMonth' => ..., 'expenses' => ...]`（VPL 契約は `location-monthly-expenses.ts` 32–40 行）。
- VPL 応答は `success`, `upserted`, `allocation`, 任意で `errors`（122–127 行）。`VehicleMonthlyCostSyncService` の `synced` キーとは異なるため、**`sync()` の集計は `upserted` または送信件数に合わせてログ**する（例外にしない）。

#### 1.2. File: `izumi-cloud/app/Console/Commands/VplSyncCommand.php`

##### 1.2.1. Entity 登録と `--year-month` 検証の拡張

**現在の実装** (`VplSyncCommand.php`):

- **30–33 行:** `signature` に `vehicle-monthly-costs` のみ。
- **50 行:** エラーメッセージに有効エンティティ一覧。
- **103–105 行:** `$transactionEntities = ['vehicle-monthly-costs'];`
- **54–65 行:** `vehicle-monthly-costs` 選択時のみ `--year-month` 検証・デフォルト前月。
- **121–146 行:** `syncEntity` で `vehicle-monthly-costs` 分岐。

**変更内容:**

- `use App\Services\Vpl\LocationMonthlyExpenseSyncService;` を追加。
- `$transactionEntities` に **`'location-monthly-expenses'`** を追加。
- `signature` / `$description` に `location-monthly-expenses` と `--year-month=` の説明を追記。
- **year-month 検証:** `in_array('vehicle-monthly-costs', $entities)` に加え、**`location-monthly-expenses`** が含まれる場合も同じバリデーション・デフォルト前月を適用。
- **`syncEntity`:** `vehicle-monthly-costs` ブロックの直後（または並列）に `location-monthly-expenses` 用ブロックを追加。`LocationMonthlyExpenseSyncService` の `buildPayload` / `sync` を **vehicle-monthly-costs と同じ dry-run ログ形式**で呼ぶ。
- **50 行のエラーメッセージ**を `location-monthly-expenses` 対応に更新。

##### 1.2.2. `displayResult` と skipped 表示（任意調整）

**現在の実装** (`VplSyncCommand.php` 197–200 行):

- verbose 時、`$skip['id']` と `$skip['reason']` を前提。

**変更内容:**

- PCA スキップが `id` を持たない場合は **`id` に `account_item_code` や連番を入れる**か、`displayResult` をエンティティ別にフォーマット変更。最低限、verbose で読める形に統一。

#### 1.3. File: `izumi-cloud/tests/Unit/Vpl/LocationMonthlyExpenseSyncServiceTest.php`（新規）

##### 1.3.1. PHPUnit ケース

**参照:** `tests/Unit/Vpl/VehicleMonthlyCostSyncServiceTest.php` のファクトリ・DB セットアップパターン。

**変更内容:**

- `buildPayload`：モックまたは SQLite/テスト DB に `pl_pca_data` 行を投入し、
  - 19 コード内 → `expenses` に期待どおり（集約 SUM、`departmentId` LOC）。
  - 非対象コード → `skipped` に入り `expenses` に出ない。
  - 同一月・同一部門・同一科目の複数行 → 金額が合算されること。
- `sync`：HTTP を `Http::fake` または `VplClient` をモックして `POST` ボディが期待通りか検証（プロジェクト既存の VPL テスト慣習に合わせる）。

#### 1.4. File: `vehicle-pl-system/backend/src/routes/location-monthly-expenses.ts`（参照・本 Issue では原則変更なし）

##### 1.4.1. 契約の固定化

**現在の実装** (27–127 行): `yearMonth` / `expenses` の検証、`LOCATION_EXPENSE_PRORATION_CODES` による科目チェック、Location 解決、`upserted` + `allocation` 応答。

**変更内容:**

- **コード変更は不要**（Issue #970 の実装対象は izumi-cloud）。不整合があれば別 Issue で VPL 側を修正。
- izumi-cloud の送信ペイロードは **必ず** `departmentId`（`Location.code` と一致する LOC 文字列）と **19 コードのみ**に絞ることで、VPL 側の `errors`（76–81 行）を抑止する。

---

## 実装順序 (Implementation Order)

1. **Backend（izumi-cloud）実装**（VPL 受信 API は既存のため依存解消済み）

   - `LocationMonthlyExpenseSyncService`：`buildPayload` → `sync`
   - `VplSyncCommand`：`location-monthly-expenses` + `--year-month` 連動
   - `LocationMonthlyExpenseSyncServiceTest`

2. **Frontend 実装**

   - 該当なし（N/A）

3. **統合テスト**

   - Staging で `php artisan vpl:sync --entity=location-monthly-expenses --year-month=YYYY-MM`（必要なら `--dry-run`）→ VPL の `upserted` / `allocation` / `dataSyncLog` を確認
   - VPL DB に `Location.code` が IC の LOC と一致していること（#967/#968 系のコース同期と整合）

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 6–10 時間

  - 新規 Service（クエリ集約・LOC・19 コードフィルタ）: 3–5 時間
  - `VplSyncCommand` 改修・dry-run・ログ: 1–2 時間
  - PHPUnit（DB または fake）: 2–3 時間

- **Frontend**: 0 時間

**合計**: 6–10 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

   - `pl_pca_data` は `date`・`department_id`・`account_item_code` に index あり（migration）。月次 1 回バッチ想定で、**SQL で GROUP BY + SUM** する方が大量行時に有利。

2. **UX 考慮:**

   - 画面変更なし。運用は Artisan / スケジューラでの実行が主。

3. **データ整合性:**

   - VPL は `departmentId` で `Location` を引く（67–73 行）。IC の `CourseSyncService::toDepartmentCode` が VPL の `Location.code` と一致していることが前提。不一致時は VPL が `errors` に「Location not found」を積む（84–86 行）— IC 側で部門マスタを確認すること。

4. **既存機能との互換性:**

   - `VplSyncCommand` のデフォルト（マスタのみ同期）の挙動を壊さない。`location-monthly-expenses` は **明示 `--entity`** 時のみ（`vehicle-monthly-costs` と同様）。
   - PHP の **19 コード定数**は `location-expense-proration.ts` と**常に同期**すること。変更時は両リポジトリまたはコメントで追跡可能にする。

5. **応答形式の差:**

   - `vehicle-monthly-costs/sync` は `synced` キー、`location-monthly-expenses/sync` は `upserted` + `allocation`。Service のログ・戻り値のキーを混同しない。
