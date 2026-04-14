# Issue #1010: IC: Integrate ATMTC Transaction Data into IC - Implementation Plan

## 概要 (Overview)

**要件（issue #1010 + 連携仕様）:** ATMTC が保持する **ドライバー・車両・コースの組み合わせトランザクション（乗車記録に相当）** を **IC（イズミクラウド）経由で PL（VPL / vehicle-pl-system）へ逆連携**し、PL 上で確認できること。エラー時は適切なメッセージ、成功時は PL への反映確認、データ更新時の自動再連携が求められる。関連: [#959](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/959)（マスタ紐づき）、[external-integration-spec.md](https://github.com/TeckVeho/vehicle-pl-system/blob/main/docs/external-integration-spec.md) §4（連携順序・タイムシート周り）。

**ATMTC ↔ IC（日次トランザクション）— 合意（PM/関係者・技術仕様追記済み、コード未実装）:**

1. **通信:** Izumi Cloud（IC）が **少なくとも 1 回/日**、ATMTC の **エクスポート API** を呼ぶ。**HTTP メソッド: `GET`。** パス形: `api/izumi/export/{startDate}/{endDate}`。
2. **日付パスパラメータ:** `{startDate}` / `{endDate}` はともに **`YYYY-MM-DD`**（例: `2026-04-08`）。`config/atmtc.php` にベース URL を既存の IC→ATMTC マスタ連携と揃えて定義。
3. **認証:** **IC → ATMTC マスタ同期（dept / 車両 / ドライバー / course 等）と同一方式**——`AtmtcImportClient` が使う **`base_url`・API キーヘッダー**（`config/atmtc.php`）と同じ方針で export リクエストに付与する。
4. **応答:** **1 ファイル CSV**。**1 行目はヘッダー**。**列名・列順**はサンプル **`vehicle-pl-system/delivery_data_results.xlsx`** および DDL 断片 **`vehicle-pl-system/table_atmtc.txt`** と一致することを前提に実装（本番で差異があれば ATMTC 確定版に合わせドキュメント更新）。
5. **CSV エンコーディング:** ATMTC が **UTF-8** または **Shift-JIS 系**のいずれでもよい。**IC 側では既存ヘルパー `Helper\Common::setInputEncoding`**（`app/Helpers/Common.php` L21–36：`mb_detect_encoding` → `config('excel.imports.csv.input_encoding')` 設定）で取込前に判定する。**レスポンス本文を一時ファイル／ストリームに渡す形で既存パターンに合わせて呼び出す**（取込フローは実装時に `CourseController` 等の CSV 取込と整合）。
6. **Idempotency（`id_atmtc`）:** **`id_atmtc` が ATMTC 側 ID として常に付与される前提で、`atmtc_delivery_data_results` に同じ `id_atmtc` が既に存在する行は再取込時も INSERT しない**（スキップ）。重複蓄積を防ぐ。
7. **IC 側ストレージ:** 上記 CSV をパースした行を、ATMTC と揃えたスキーマで **MySQL テーブル `atmtc_delivery_data_results`** に格納（Laravel `cloud` migration + モデル）。**カラム一覧**は本 plan **§BE 1.2.2**。以降 VPL 連携はこのテーブルをソースにできる。

**現状（VPL）:**

- **日次走行・乗車回数** は `DailyOperatingRecord`（`vehicleId` + `date` + `runCount` + `isOperating`）。[BE-VPL] `backend/prisma/schema.prisma` L215–228。[BE-VPL] `POST /api/daily-operating/sync` は `vehicleExternalId` / `vehicleId` で車両を引き、`runCount` を upsert 後に `runSalaryRunCountAllocation` を実行（`backend/src/routes/daily-operating.ts` L26–120、`DataSyncLog` L122–130）。
- **日次乗務（誰がどの車で走ったか）** は `DailyDriverAssignment`（`driverId` + `vehicleId` + `date`、一意制約は同じ組み合わせ 1 行/日）。[BE-VPL] `backend/prisma/schema.prisma` L155–169。[BE-VPL] `POST /api/driver-assignments/sync` は `requireRole(ROLES.MASTER)`（`backend/src/routes/driver-assignments.ts` L29）。
- **ルーティング:** [BE-VPL] `backend/src/routes/index.ts` L35–38 で `dailySummaryRouter` / `dailyOperatingRouter` / `driverAssignmentsRouter` をマウント。**`daily-operating` には `requireRole` が付いておらず**、`driver-assignments` はルートファイル側で MASTER 制御。結果として `daily-operating/sync` は認証済みユーザーなりすましリスクが相対的に高い — **IC からは MASTER JWT 前提だが、`daily-operating` も `driver-assignments` と揃えて MASTER 制限を推奨**。
- **[BE-IC]** 既に `app/Services/Atmtc/AtmtcImportClient.php`（IC → ATMTC マスタ CSV POST）と `config/atmtc.php` 連携あり。**本件の ATMTC → IC トランザクション取得は逆方向**のため、別クライアント（新規クラス）で実装する（既存 `AtmtcImportClient` とは責務分離）。
- **UI:** 「日次連携データ集計」は売上按分の日次金額表示が中心（`src/app/daily-summary/page.tsx` → `GET /api/daily-summary`）。`daily-summary.ts` 内で `dailyOperating` を Map 化（L133–147）するが **JSON レスポンスに runCount を載せていない**ため、FE では ATMTC/走行回数は **未表示**。

**目標状態:**

1. **（合意済）** IC が日次で **`GET`** `api/izumi/export/{startDate}/{endDate}`（日付 **`YYYY-MM-DD`**・**認証はマスタ同期と同型**）を呼び、CSV を **`Helper\Common::setInputEncoding`** で解釈しつつ **`atmtc_delivery_data_results`** へ取り込む（**`id_atmtc` 既存ならスキップ**）。
2. IC が当該テーブル（または集計結果）から **VPL 既存 API を呼び出す**、または **1 リクエストで乗務＋走行回数をまとめて反映する新 API** を VPL に追加する（二重メンテ防止のため後者を推奨）。
3. PL で「ATMTC 経由の乗車記録が来ていること」を **連携ログ or 一覧 UI** で確認可能。失敗行は運用で追えるようにする（IC 側テーブルで行単位の再処理・照合も可能）。

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: **[FE-Web / vehicle-pl-system]** `src/app/sync-logs/page.tsx`

##### 1.1.1. 連携種別ラベル拡張

**既存コード** (L19–25): `SYNC_TYPE_LABELS` に `daily_operating` 等のみ。

**変更内容:**

- ATMTC 取り込み用に `DataSyncLog.syncType` の新値（例: `atmtc_transactions` / `atmtc_daily_bundle`）をラベル表示に追加。
- 文言は日英どちらかプロダクト基準に合わせる（例: 「ATMTC 乗車トランザクション」）。

##### 1.1.2. （任意）エラー詳細の表示

VPL が **部分成功 + errors[]** を返す API を追加する場合、IC 側でログ保存し、将来 FE で「最終エラー」を見られるよう API と FE を拡張する — **MVP は VPL の `DataSyncLog` + メッセージで足りるか判断**。

---

#### 1.2. File: **[FE-Web / vehicle-pl-system]** `src/app/daily-summary/page.tsx` / `src/components/daily-summary/DailySummaryTable.tsx`

##### 1.2.1. ATMTC / 乗車回数の可視化（DoD「PL で確認」向け）

**既存コード:** `DailySummaryTable` は車両列×日の **金額** のみ表示（`src/components/daily-summary/DailySummaryTable.tsx` L68–127：日別セルは `formatCurrency(dailyAmountByVehicleByDay)`）。`runCount` は API 応答に含まれていない（`daily-summary.ts` は内部で `dailyOperating` を読み Map 化するが L133–147、返却 JSON は売上按分結果中心）。

**変更内容（案）:**

- **案 A（軽量）:** `GET /api/daily-summary` に **オプション** で `dailyOperating` の `runCount` / `isOperating` を載せるフラグを追加し、別タブまたは折りたたみで「走行回数」行を表示。
- **案 B:** 新規ページ `src/app/atmtc-boarding/page.tsx`（仮）で `yearMonth` / 拠点フィルタし、`DailyDriverAssignment` + `DailyOperatingRecord` を専用 GET で一覧（ATMTC 由来なら `source` 列が必要 → BE でフィールド追加）。

**すり合わせ:** プロダクトが「既存の日次画面で十分」なら案 A、監査・問い合わせ対応なら案 B。

---

#### 1.3. File: **[FE-Web / vehicle-pl-system]** `src/components/layout/Header.tsx`

##### 1.3.1. ナビゲーション

**既存コード** (L31): `/daily-summary` は `hidden: true` 等で制限されている可能性あり。

**変更内容:**

- ATMTC 確認用ページを追加する場合、role 可視性ルールに沿ってリンクを追加。

---

#### 1.4. File: **[FE-IC / cloud]** `resources/js/pages/...`（データ連携画面がある場合）

##### 1.4.1. IC 側ジョブの可視化（任意）

[#959 plan](../959/plan.md) と同様、**IC ← ATMTC（トランザクション）** のジョブ行・最終実行時刻を出す。パスは既存の DataConnect 系に合わせる（リポジトリ内の実ページ名は実装時に特定）。

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: **[External / ATMTC]**（本 workspace 外）

##### 1.1.1. エクスポート API + CSV 契約（合意済み・技術追記済み）

| 項目 | 合意内容 |
|------|-----------|
| **HTTP** | **`GET`** |
| **URL 形** | `{baseUrl}/api/izumi/export/{startDate}/{endDate}` |
| **startDate / endDate** | **`YYYY-MM-DD`**（例 `2026-04-08`） |
| **認証** | **IC → ATMTC マスタ同期と同一**（`config/atmtc.php` の base URL・API キーヘッダー等、`AtmtcImportClient` と整合） |
| **応答** | **CSV 1 ファイル**、**1 行目 = ヘッダー** |
| **列** | **`vehicle-pl-system/delivery_data_results.xlsx`** および **`vehicle-pl-system/table_atmtc.txt`** と一致前提（本番確定後に固定） |
| **文字コード** | UTF-8 または Shift_JIS 系を許容。IC 側は **`Helper\Common::setInputEncoding`**（`app/Helpers/Common.php`）で検出・取込設定 |
| **Idempotency** | **`id_atmtc` が既に DB にあれば INSERT しない**（重複行を作らない） |

**VPL マッピング用の論理キー（実装時）:** `driver_code`, `department_code`, `course_code`, `delivery_date`, **`id_atmtc`**, `plate_number_vehicle`, `report_delivery_id` 等。

---

#### 1.2. File: **[BE-IC / cloud]** 新規（仮）`app/Services/Atmtc/AtmtcDeliveryExportClient.php`（クラス名は実装時に調整可）

##### 1.2.1. ATMTC エクスポート取得 + CSV パース（IC ← ATMTC）

**既存（参考）:** `app/Services/Atmtc/AtmtcImportClient.php` は **IC → ATMTC マスタ CSV POST**。本件は **`GET` で CSV を取得**するため **別クラス**（例: `AtmtcDeliveryExportClient`）。**認証ヘッダー・base URL はマスタ連携と同じ `config/atmtc.php` を流用**。

**変更内容:**

- **`GET`** `{base}/api/izumi/export/{startDate}/{endDate}`、`startDate`/`endDate` は **`YYYY-MM-DD`** を URL にそのまま埋め込む（要: URL エンコード方針の確認）。
- ジョブが **最低 1 回/日**、対象期間を決める（例: 前日のみ `start=end`）。
- レスポンスボディを CSV として保存またはストリームし、**`Helper\Common::setInputEncoding`**（`app/Helpers/Common.php` L21–36）でエンコーディングを判定してから Excel/CSV インポートと同様にパース。
- **各行投入前:** **`id_atmtc` が非 null かつ DB に既存ならスキップ**（合意どおり再取込でも重複 INSERT しない）。`id_atmtc` が null の行のみ別ルール（ログ落とし・スキップ or 複合キー）は実装タスクで確定。
- タイムアウト・リトライ・失敗ログ。
- パース後 **1.2.2** の **`atmtc_delivery_data_results`** へ書き込み（新規行のみ）。

---

#### 1.2.2. File: **[BE-IC / cloud]** `database/migrations/..._create_atmtc_delivery_data_results_table.php` & `app/Models/AtmtcDeliveryDataResult.php`

##### 1.2.2.1. テーブル `atmtc_delivery_data_results`（ATMTC 同型）

**目的:** CSV 1 行 ≒ DB 1 行（IC 上のスナップショット / 監査・再処理用）。**参照:** `vehicle-pl-system/table_atmtc.txt`。

| カラム | 備考（原 DDL の要点） |
|--------|------------------------|
| `id` | IC 側 PK（bigint unsigned, AI） |
| `id_atmtc` | ATMTC 側 ID（bigint、**取込時は NOT NULL を想定**）— **合意: DB に同一 `id_atmtc` が既にあれば INSERT スキップ** |
| `department_code` | varchar(255) nullable |
| `delivery_company_code` | varchar(255) nullable |
| `delivery_date` | date **NOT NULL** |
| `start_time`, `end_time` | time nullable |
| `driver_code`, `driver_name` | varchar(255) nullable |
| `delivery_destination_code`, `delivery_destination`, `delivery_destination_2` | varchar(255) nullable |
| `delivery_delay_status` | varchar(255) nullable |
| `estimated_arrival_time` | varchar(255) nullable |
| `distance`, `address`, `phone` | varchar(255) nullable |
| `report_time`, `report_location` | varchar(255) nullable |
| `course_code`, `course_name` | varchar(255) nullable |
| `quantity` | int nullable |
| `truck_temperature`, `key_collecting` | varchar(255) nullable |
| `the_number_of_blue_orikon`, `the_number_of_order_slip` | varchar(255) nullable |
| `report_delivery_id`, `latitude`, `longitude` | varchar(255) nullable |
| `vehicle_id` | int nullable（ATMTC 側） |
| `model_vehicle`, `vehicle_type_code` | varchar(255) nullable |
| `order_number` | int nullable |
| `delivery_skip_status` | int NOT NULL default 0（0: no, 1: yes） |
| `is_update` | int nullable |
| `created_at`, `updated_at`, `deleted_at` | timestamp nullable |
| `operation`, `operation_pickup`, `operation_collecting`, `operation_return`, `operation_coleslaw` | int nullable |
| `plate_number_vehicle` | varchar(100) nullable |
| `estimated_arrival_time_master` | varchar(255) nullable |
| `remarks` | varchar(255) nullable |
| `number_delivery` | int nullable |

**インデックス（実装時）:** **`id_atmtc` に UNIQUE**（合意どおり重複禁止・存在チェック高速化）。`delivery_date` など検索用インデックスは任意。

---

#### 1.3. File: **[BE-IC / cloud]** `app/Services/Vpl/VplClient.php`

##### 1.3.1. 新 VPL エンドポイント呼び出し

**既存コード:** `post(string $path, array $payload)` L121–124 がエントリ、`request()` L137–161 で Bearer + **401 時 `forgetToken()` 再認証**（L157–161）。

**変更内容:**

- 薄いラッパー追加例: `postAtmtcTransactionsSync(array $body): array` → 内部で `$this->post('/api/atmtc-transactions/sync', $body)`（VPL 側パス確定後に固定）。
- エラー戻り `_error` / `_status`（L174–178）の扱いを呼び出し側コマンドでログ化。

---

#### 1.4. File: **[BE-IC / cloud]** Data Connection + Repository（マスタ同期と同パターン）／補助 Command

##### 1.4.1. 方針（`#1043` plan-dataconnection-migration.md に合意反映済み・実装は別タスク）

- **本番スケジュール:** `data_connections` に 1 件登録し、**`DataconnectionCommand` / `DataConnectionJob`** で日次実行（**`AtmtcMasterDataConnectionSeeder` と同型の登録**）。詳細は **`docs/issues/Izumi_Issue-Requests-Repo/1043/plan-dataconnection-migration.md`**。
- **System `from` / `to`:** マスタと**同じ向きのまま**（**入れ替えない**）。実データは ATMTC export → IC DB だが、接続メタはマスタと揃える。**`remark` / 接続名**で取り違え防止。
- **Export 日付範囲（合意）:** **`$effective` = `$date` が無ければアプリ timezone の今日**。**`startDate`** = `$effective` の **7 日前**、**`endDate`** = **`$effective`**（`startDate` ≤ `endDate`、`Y-m-d`）。区間は両端 **inclusive**（暦日で **8 日分**: effective−7 … effective）。詳細は `#1043` の `plan-dataconnection-migration.md`。
- **CSV 監査:** マスタ同様、**接続ログ用に raw CSV を保存**。
- **Artisan `atmtc:import-delivery-export`:** **廃止**（Data Connection 移行後）。手動は **`schedule:data_connection {id} {date}`** のみ。
- **service_class_name:** 新規 `Repository\…@method`（**`AtmtcMasterSyncRepository` から分離**）。

##### 1.4.2. 処理フロー（合意順・変更なし）

1) `api/izumi/export/{startDate}/{endDate}` 呼び出し → CSV 取得  
2) パース → **`atmtc_delivery_data_results`** へ取込  
3) （後続）集計・マッピング → **VPL API**（既存 `daily-operating` / `driver-assignments` または新規集約エンドポイント）

**頻度:** 最低 1 回/日（`data_connections.frequency` / `time_at` で調整）。失敗時は `Log` + `DataItem` 系の既存パターンに合わせて再実行可能設計。

---

#### 1.5. File: **[BE-VPL / vehicle-pl-system]** `backend/prisma/schema.prisma`

##### 1.5.1. （任意）データソース列

**既存:** `DailyDriverAssignment` にソース列なし（`backend/prisma/schema.prisma` L155–169）。

**変更内容:**

- `source String @default("timesheet")` または `dataSource` を追加し、ATMTC 連携行を `atmtc` で upsert（タイムシートとの上書きポリシーは **「日付×車両×ドライバーで最後の連携が勝つ」** 等をドキュメント化）。
- **スキーマ変更なしの MVP:** 連携ログのみ `DataSyncLog.source = "ATMTC"` 等で区別し、行レベル監査は後追い。

---

#### 1.6. File: **[BE-VPL / vehicle-pl-system]** 新規 `backend/src/routes/atmtc-transactions.ts`（推奨）

##### 1.6.1. `POST /api/atmtc-transactions/sync`（ルート名は要確定）

**目的:** IC から **1 リクエスト**で以下を原子的に近い形で実行。**入力ソース:** IC 上の **`atmtc_delivery_data_results`** 行を事前に **`driverExternalId` / `vehicleExternalId` / `date` 等に正規化**したペイロードとするか、または VPL 内で生データを受け取る設計は **別途すり合わせ**（推奨は IC でマスタ紐付け後に VPL へ送る）。

1. 入力レコードごとに `driverExternalId` / `vehicleExternalId` を CSV 列（`driver_code`, `plate_number_vehicle`, `department_code` 等）から解決（`driver-assignments.ts` のドライバー L72–91、車両 L93– 以降 と同パターン）。
2. `DailyDriverAssignment` を upsert（`@@unique([driverId, vehicleId, date])`）。
3. 同一 `(vehicleExternalId|vehicleId, date)` に対し **トランザクション件数**（CSV 行数または `quantity` 集計ルール）から `runCount` を集計し `DailyOperatingRecord` を upsert（`daily-operating.ts` L96–114 の upsert ブロックをサービス関数化して再利用）。
4. 既存どおり `runSalaryRunCountAllocation(yearMonth, locId)` を呼ぶ（`daily-operating.ts` L118–119）。
5. `DataSyncLog` 作成（`source`: 「ATMTC」、`syncType`: 新キー）。

**認証:** `requireRole(ROLES.MASTER)` をルートに付与（`index.ts` で `driver-assignments` と同様にマウント）。

**変更内容:**

- リクエストボディ例:
  - `yearMonth`, `departmentId` | `locationId`, `records: [{ driverExternalId, vehicleExternalId, date, courseExternalId?, externalTransactionId? }]`  
  - `externalTransactionId` は **`id_atmtc`** または `report_delivery_id` の利用を検討
- **競合:** 同一 `(vehicleId, date)` に複数ドライバーが来た場合は **CSV の業務定義**（1 車両 1 日 1 ドライバーか）で決定。
- **idempotency:** IC テーブルは **`id_atmtc` 既存スキップ**（上記）。IC → VPL 再送は別途バッチ ID または VPL 側の扱いでスキップ可能にする。

---

#### 1.7. File: **[BE-VPL / vehicle-pl-system]** `backend/src/routes/index.ts`

##### 1.7.1. ルーター登録

**既存コード** (L32–50): 例として L35–38 は `"/daily-summary"`、`"/daily-operating"`、`"/driver-assignments"`。`"/import"` のみ L48 で `requireRole(ROLES.EDIT_PL)`。

**変更内容:**

- `apiRouter.use("/atmtc-transactions", requireRole(ROLES.MASTER), atmtcTransactionsRouter);` を適切な行に追加（パス例）。

---

#### 1.8. File: **[BE-VPL / vehicle-pl-system]** `backend/src/routes/daily-operating.ts`

##### 1.8.1. 権限・共通化

**既存コード** (L26–141): `POST /sync` にルート個別の `requireRole` なし（グローバル `requireAuth` のみ）。

**変更内容:**

- **推奨:** `POST /sync` を `requireRole(ROLES.MASTER)` で保護（ルート定義に付与、または `index.ts` で `dailyOperatingRouter` をラップ）。タイムシート連携が非 MASTER トークンに依存している場合はルート分離で対応。
- `atmtc-transactions` から呼ぶ内部関数 `_upsertDailyOperatingBatch(...)` を `daily-operating.ts` または `lib/` に抽出し二重実装を避ける。

---

#### 1.9. File: **[BE-VPL / vehicle-pl-system]** `backend/src/routes/daily-summary.ts`

##### 1.9.1. （案 A 採用時）応答に runCount を含める

**既存コード** (L92–97, L133–148): `dailyOperating` を読み、内部 Map に格納。

**変更内容:**

- クエリ `includeOperating=false|true` 等で、車両×日の `runCount` をフロントが表示できるよう JSON を拡張。

---

#### 1.10. File: **[Docs / vehicle-pl-system]** `docs/external-integration-spec.md` & `docs/driver-allocation-api.md`

##### 1.10.1. 仕様追記

- 連携順序に **「ATMTC CSV export（`api/izumi/export/...`）→ IC `atmtc_delivery_data_results` →（変換）→ VPL」** を追記（§4 の [7][8] 近辺）。
- 新 API の JSON 例・認証（MASTER）・`departmentId` の扱いを並記。
- ワークスペース参照: `table_atmtc.txt`, `delivery_data_results.xlsx` を README または spec からリンク（任意）。

---

## 実装順序 (Implementation Order)

1. **IC: `atmtc_delivery_data_results` + Export 取込**（依存: CSV 列順・日付形式の最終確認）

    - migration / モデル、**`AtmtcDeliveryExportClient`**（仮）で export API → CSV → DB。**最低 1 回/日** は **`DataConnection` + 専用 Repository** を第一候補とし、専用 Artisan のみの schedule は廃止または手動専用に縮小（`#1043` の `plan-dataconnection-migration.md`）。

2. **マッピング仕様確定（IC 内）**（依存: 1）

    - `driver_code` / `plate_number_vehicle` / `department_code` → IC マスタ（#959）→ VPL `externalId` 対応表。**`runCount` 集計ルール**（行数 vs `quantity`）を固定。

3. **Backend（VPL）: 集約 sync API + DataSyncLog**（依存: 2）

    - `atmtc-transactions` ルート、（任意）Prisma `source`、内部で driver-assignments / daily-operating ロジック再利用。

4. **Backend（IC）: VplClient 呼び出し**（依存: 1 + 3）

    - `atmtc_delivery_data_results`（または変換済みペイロード）→ VPL API。ログ・リトライ。

5. **Backend（VPL）: daily-summary 拡張 or 新 GET**（依存: 3）

    - FE が確認できる最小データ。

6. **Frontend（VPL）: sync-logs ラベル + 確認 UI**（依存: 5）

7. **Frontend（IC）: 任意の連携画面**（依存: 4）

8. **統合テスト**

    - ATMTC export モック（CSV fixture）→ IC `atmtc_delivery_data_results` → VPL DB で `DailyDriverAssignment` / `DailyOperatingRecord` / 配賦を確認。

---

## 見積もり工数 (Estimated Effort)

- **Backend（VPL）**: 12–24 時間

    - 集約 API + ログ + 権限整理: 6–12h
    - Prisma ソース列・マイグレーション（採用時）: 2–4h
    - daily-summary / 新 GET: 4–8h

- **Backend（IC）**: 14–26 時間

    - **`atmtc_delivery_data_results` migration / モデル、Export クライアント、CSV 取込、日次ジョブ:** 10–18h（合意済みスコープ）
    - VplClient 拡張・VPL 送信: 前述に含むまたは +2–4h
    - 設定・監視: 2–4h

- **Frontend（VPL）**: 6–14 時間

    - sync-logs: 1–2h
    - runCount / ATMTC 一覧 UI（案による）: 5–12h

- **Frontend（IC）**: 0–8 時間（任意）

**合計**: 約 **32–72 時間**（IC 取込テーブル・CSV 込み、UI の厚さとスキーマ拡張で変動）

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

    - ATMTC バッチが大きい場合は **日付範囲を分割**、`id_atmtc` **UNIQUE + 存在チェック**で不要 INSERT を避ける。VPL ではチャンク・トランザクションまたはバルク upsert を検討。

2. **UX 考慮:**

    - 成功/失敗メッセージは issue 要件。IC 管理画面または VPL のトーストはロール（DX 等）に限定する想定。

3. **データ整合性:**

    - マスタ同期順は [external-integration-spec.md §4](https://github.com/TeckVeho/vehicle-pl-system/blob/main/docs/external-integration-spec.md) に従い、**車両・ドライバーの `externalId` が揃ってから** トランザクション投入。
    - **`atmtc_delivery_data_results`** は **生データ保持**（監査・再処理用）。VPL へは変換後のみ送るか、二重管理を避ける方針を README に残す。
    - タイムシート由来と ATMTC 由来が同一キーにぶつかる場合の **優先ルール** を明示。

4. **既存機能との互換性:**

    - `runSalaryRunCountAllocation` を必ず既存フローで呼び、配賦済み `MonthlyRecord` と日次サマリーの整合を維持。
    - `daily-operating/sync` に MASTER 制限を追加する場合、イズミクラウドのタイムシート連携が使う認証ユーザーが **MASTER** であることを確認。

---

## Workspace roots（本 plan のファイル参照）

| Root | Path | 備考 |
|------|------|------|
| **FE-Web / BE-VPL** | `d:/CtyVeHo/izumi/vehicle-pl-system` | 本 issue の PL 実装・docs ルート |
| **BE-IC** | `d:/CtyVeHo/izumi/cloud` | ATMTC 取得・VPL API 呼び出し |
| **BE-Timesheet（参考）** | `d:/CtyVeHo/izumi/izumi-timesheet-v2` | 主対象外。仕様が timesheet に触れる場合のみ参照 |

---

## Plan メタ（workspace-plan）

- **Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010  
- **更新:** 2026-04-09 — 技術合意追記: **`GET`**、`YYYY-MM-DD`、**認証＝マスタ同期と同一**、CSV は **ヘッダー行 + 列は xlsx/table 参照**、エンコーディングは **`Helper\Common::setInputEncoding`**、**`id_atmtc` 既存なら INSERT スキップ**・**`id_atmtc` UNIQUE** 推奨。  
- **Git:** `plan.md` の保存および docs sync のみ。**コミットしない**（`/pr` まで保留）。
