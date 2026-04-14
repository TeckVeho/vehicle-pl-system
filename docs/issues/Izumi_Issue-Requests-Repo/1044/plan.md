# Issue #1044: [BE] VPL集約API・IC→VPL同期・daily-operating整理 / API VPL và đồng bộ IC→VPL - Implementation Plan

**Parent:** [#1010](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010) — *IC: Integrate ATMTC Transaction Data into IC*  
**参照 plan親:** `docs/issues/Izumi_Issue-Requests-Repo/1010/plan.md` §BE **1.6–1.9**、親の **実装順 3–4**（VPL 集約 API → ICから VPL 呼び出し）。

**PM 確認（#1044）:** [`pm-confirm-1044-vpl-atmtc-sync.md`](./pm-confirm-1044-vpl-atmtc-sync.md) / [EN](./pm-confirm-1044-vpl-atmtc-sync.en.md)。要点: **B1.1** 1 行 = 1 run、車両×日に集計。**B2.1** 未マッチ行はスキップ、部分成功 + **翌営業日前**に担当者へ件数・明細通知。**B3.1** 同一車両×日に複数ドライバ可、行単位の帰属は維持。**B4.2** ATMTC 経路では **`runSalaryRunCountAllocation` のみ** — timesheet 同等の **`runDriverAllocation` は呼ばない**（二重計上回避、財務レビュー後に拡張検討）。**B5.1** 全 caller は **MASTER**（管理者）で呼び出す。**B5.2/B5.3** は例外・IT 確認が出た場合のみ。**B6.2** 本リリースでは **daily-summary に runCount 非表示**、**DataSyncLog** で突合。

---

## 概要 (Overview)

**要件（#1044）:** ATMTC 由来データ（IC 上では `atmtc_delivery_data_results` に格納済みを想定）を、**1 回の VPL API** で **DailyDriverAssignment** と **DailyOperatingRecord** に反映し、**乗車回数ベースの給与配賦**として **`runSalaryRunCountAllocation` のみ**（**`runDriverAllocation` は呼ばない** — PM B4.2）と **DataSyncLog** を実行する。IC は **`VplClient`**（**MASTER** 相当の VPL 認証 — PM **B5.1**）から当該エンドポイントを呼ぶ。**`daily-operating.ts` の upsert ロジックは lib 化**し、`POST /api/daily-operating/sync` を **`requireRole(ROLES.MASTER)`** で保護（`driver-assignments/sync` と同様）。**B5.2**（旧システム用別経路）が必要になった場合のみルート分離を検討。

**現状（コードベース）:**

- **[BE-VPL]** `backend/src/routes/daily-operating.ts` L26–141: `POST /sync` は車両解決・`dailyOperatingRecord.upsert`（L99–114）・`runSalaryRunCountAllocation`（L118–119）・`DataSyncLog`（L122–130）。**`requireRole` なし。**
- **[BE-VPL]** `backend/src/routes/driver-assignments.ts` L29–169: `POST /sync` は **`requireRole(ROLES.MASTER)`**、assignment upsert 後に `runDriverAllocation` + `runSalaryRunCountAllocation`、`DataSyncLog`。
- **[BE-VPL]** `backend/src/routes/index.ts` L35–38: `daily-operating` / `driver-assignments` マウント。集約ルートは **未登録**。
- **[BE-VPL]** `backend/src/routes/daily-summary.ts` L92–97, L133–147: `dailyOperating` は内部 Map のみ利用。**レスポンス JSON（L370–376）に `runCount` は含めていない。**
- **[BE-IC]** `app/Services/Vpl/VplClient.php` L121–124: `post($path, $payload)` で VPL へ JSON POST。専用ラッパーは未実装。
- **[BE-IC]** `atmtc_delivery_data_results` / `AtmtcDeliveryDataResult` / `AtmtcDeliveryExportRepository` 等は **別タスクで実装済みまたは進行中**（本 plan では **VPL 契約とマッピング**に焦点）。

**目標状態:**

1. VPL: **`POST /api/atmtc-transactions/sync`**（MASTER）、`index.ts` 登録。
2. VPL: daily operating の **共通化** + **`daily-operating/sync` の MASTER 化**（必要ならタイムシート用にルート分離）。
3. IC: **`VplClient`** に新メソッド、**IC 内サービス**でテーブル行→正規化ペイロード→VPL。**`driver_code` 等 → `externalId`、`runCount` 集計ルールをコード化**。
4. **`GET /api/daily-summary` の `runCount` 露出** — **後続リリース**（PM B6.2、本 sprint ではスコープ外）。
5. **Vitest（VPL）/ PHPUnit（IC）** で契約・マッピングを検証。

---

## FE (Frontend)

Issue #1044 は **BE 中心**。UI は親 #1010 plan に沿った **最小対応**のみ記載。

### 1. Files need to edit:

#### 1.1. File: **[FE-Web / vehicle-pl-system]** `src/app/sync-logs/page.tsx`

##### 1.1.1. 連携種別ラベル（`DataSyncLog.syncType` 新値）

**既存コード** (L19–25): `SYNC_TYPE_LABELS` に `daily_operating` 等。

**変更内容:**

- VPL が `DataSyncLog.syncType` に保存する **ATMTC 用キー**（例: `atmtc_transactions` — 実装時に VPL 側と完全一致させる）を **ラベル追加**（日英はプロダクト基準）。

---

#### 1.2. File: **[FE-Web / vehicle-pl-system]** `src/app/daily-summary/page.tsx` / `src/components/daily-summary/DailySummaryTable.tsx`（**後続**・PM B6.2）

##### 1.2.1. `runCount` の表示

**本 sprint では実装しない。** 将来、`GET /api/daily-summary` に **オプション**で車両×日の `runCount` / `isOperating` を載せたうえで、テーブルまたはツールチップ表示（親 #1010 plan 案 A）を検討する。

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: **[BE-VPL / vehicle-pl-system]** 新規 `backend/src/lib/daily-operating-records-sync.ts`（仮）

##### 1.1.1. Upsert ロジックの抽出

**現在の実装:** `backend/src/routes/daily-operating.ts` L55–116（ループ内の車両解決 L70–89、`runCount` / `isOperating` 正規化 L96–97、`prisma.dailyOperatingRecord.upsert` L99–114）。

**変更内容:**

- 公開関数例: `syncDailyOperatingRecordsFromRows(params: { yearMonth: string; locationId: string | null; records: OperatingRow[] }): Promise<{ upserted: number; errors: string[] }>`  
  - `OperatingRow` は既存 body 形状（`vehicleId` / `vehicleExternalId`, `date`, `runCount`, `isOperating`）に合わせる。
- **単一箇所**で upsert とエラー文字列生成を行い、`daily-operating`ルートと `atmtc-transactions` ルートの両方から呼ぶ。
- 同一動作（`yearMonth` と `date` の整合チェック L63–67 等）を維持。

---

#### 1.2. File: **[BE-VPL / vehicle-pl-system]** `backend/src/routes/daily-operating.ts`

##### 1.2.1. lib 呼び出しへの置換 + MASTER 制限

**現在の実装** (L26–141): ハンドラ全体がインライン。

**変更内容:**

- `POST /sync` に **`requireRole(ROLES.MASTER)`** を付与（`driver-assignments.ts` L29 と同パターン）。`Router` 先頭で `import { requireRole, ROLES } from "../lib/auth.js"`。
- ループ本体を **§1.1 の lib関数**に委譲。`runSalaryRunCountAllocation`（L118–119）と `DataSyncLog`（L122–130）はルートに残すか、lib の「後処理フック」で共通化するかは実装時に選択（重複最小化を優先）。
- **互換性:** 非 MASTER からの既存呼び出しがある場合は、**別パス**（例: `POST /api/daily-operating/sync-timesheet`）を切るか、呼び出し元を MASTER 化（issue推奨は MASTER）。

---

#### 1.3. File: **[BE-VPL / vehicle-pl-system]** 新規 `backend/src/routes/atmtc-transactions.ts`

##### 1.3.1. `POST /sync` — 集約 ATMTC 同期

**目的（親 plan §1.6.1 と整合）:** 1 リクエストで **assignment upsert** + **車両×日の runCount 集計 upsert** + **配賦** + **DataSyncLog**。

**変更内容:**

- ルーター: `export const atmtcTransactionsRouter = Router();`
- `atmtcTransactionsRouter.post("/sync", requireRole(ROLES.MASTER), handler)`
- **リクエストボディ（案）:**  
  - `yearMonth`, `locationId?` | `departmentId?`（`daily-operating.ts` L31–36 と同様に `department_code` → Location）  
  - `records: Array<{ driverExternalId?, vehicleExternalId?, driverId?, vehicleId?, date: string, weight?: number }>`  
  - IC 側で正規化済みとし、**VPL は主に ID 解決 + upsert + 集計**の観点。
- **ドライバー /車両解決:** `driver-assignments.ts` L72–111 と同パターン（`externalId` /内部 id、`locationId` フィルタ）。
- **DailyDriverAssignment:** `driver-assignments.ts` L123–138 と同等の upsert。
- **runCount 集計:** 同一 `(vehicleId, date)` で IC が定めた **`weight`（既定 1）を合計**、または「1 行 = 1 run」で件数加算。**ルールは IC のマッパーと同一の定数・関数でドキュメント化**（親 plan: 行数 vs `quantity` のすり合わせ）。
- **DailyOperatingRecord:** 集計結果ごとに `isOperating: true`（または業務ルールに応じ）で §1.1 lib を呼ぶ、または lib に「merge runCount」オプションを追加して上書きポリシー（合算 vs 最大）を固定。
- **後処理:** `runSalaryRunCountAllocation(yearMonthStr, locId)` のみ — `daily-operating.ts` L118–119 と同様。**`runDriverAllocation` は呼ばない**（PM B4.2 / timesheet フル配賦との二重計上回避）。
- **DataSyncLog:** `source`: 「ATMTC」等、`syncType`: 新キー（例 `atmtc_transactions`）、`recordCount` は処理行数または assignment 件数で定義。

**エラー応答:** 部分成功時は既存 `daily-operating` と同様 **`errors[]`** を返す形を踏襲（L136–137）。**IC 側:** スキップ件について PM B2.1 に沿い、**翌営業日前**に担当者へ通知できるようログ・キュー設計を検討する。

---

#### 1.4. File: **[BE-VPL / vehicle-pl-system]** `backend/src/routes/index.ts`

##### 1.4.1. ルーター登録

**現在の実装** (L1–7, L35–38): `dailyOperatingRouter` 等をマウント。

**変更内容:**

- `import { atmtcTransactionsRouter } from "./atmtc-transactions.js";`
- `apiRouter.use("/atmtc-transactions", requireRole(ROLES.MASTER), atmtcTransactionsRouter);`（親 plan §1.7.1。**ルートファイル側ではなく index で MASTER を付ける**方針でも可）。

---

#### 1.5. File: **[BE-VPL / vehicle-pl-system]** `backend/src/routes/daily-summary.ts`

##### 1.5.1. クエリで `runCount` を JSON に含める（**後続リリース**・PM B6.2）

**本 sprint ではスコープ外。** 将来実装する場合のメモ:

**現在の実装** (L29–31, L133–147, L370–376): `dailyByVehicle` は内部のみ。レスポンスに未含有。

**変更内容（将来）:**

- 例: `?includeDailyOperating=1` または `includeRunCounts=true` のとき、`dailyByVehicle` を **シリアライズ可能な形**（`Record<vehicleId, Record<date, { runCount, isOperating }>>`）で `res.json` に追加。
- **MASTER のみ**等の制限は要件次第（現状 `daily-summary` に `requireRole` なし — 露出する場合は情報漏えいに注意）。

---

#### 1.6. File: **[BE-VPL / vehicle-pl-system]** `backend/src/__tests__/sync-routes.contract.test.ts`

##### 1.6.1. 契約テスト追加

**既存** (L335–385): `daily-operating/sync` の 400/200。`driver-assignments` は MASTER 必須。

**変更内容:**

- `POST /api/atmtc-transactions/sync`: **401/403**（非 MASTER）、**400**（バリデーション）、**200**（空 / モック upsert）。`prismaMock` に `dailyDriverAssignment.upsert` 等を追加。
- `daily-operating/sync`:** MASTER 必須にした場合、**ロールなしで 403** となるケースを追加。

---

#### 1.7. File: **[BE-IC / cloud]** `app/Services/Vpl/VplClient.php`

##### 1.7.1. VPL 新エンドポイント用ラッパー

**現在の実装** (L121–124): 専用 `post`。

**変更内容:**

- 例: `postAtmtcTransactionsSync(array $payload): array` → `$this->post('/api/atmtc-transactions/sync', $payload)`（VPL の最終パスに合わせる）。**戻り値**の `_error` / `_status`（L174–178）を呼び出し側でログ化。

---

#### 1.8. File: **[BE-IC / cloud]** 新規または既存サービス（例）`app/Services/Vpl/AtmtcToVplSyncService.php` / Repository から呼ぶ

##### 1.8.1. `atmtc_delivery_data_results` → VPL ペイロード

**入力:** `App\Models\AtmtcDeliveryDataResult` のコレクション（期間指定でクエリ）。

**変更内容:**

- **マッピング（コード化）:**  
  - `driver_code` → VPL `driverExternalId`（IC マスタと #959 連携の `externalId` 規約に合わせる）  
  - `plate_number_vehicle` または ATMTC `vehicle_id` → VPL `vehicleExternalId`（既存 VPL Vehicle `externalId` と一致させる）  
  - `delivery_date` → `date`（`Y-m-d`）  
  - `department_code` → VPL の `departmentId`（既存 sync と同様、Location `code` 経由）
- **runCount 集計:** 例 — 同一 VPL キー（vehicle external + date）で **行数をカウント**、または `quantity` の合計。 **VPL §1.3 と同一ルール**を定数または共有ドキュメントで固定。
- **重複 /再実行:** IC側で `id_atmtc` スキップ済みでも、VPL へは「冪等に近い」再送があり得るため、**集約結果が上書きでよいか**を仕様化（通常は runCount 合算でよい）。
- 呼び出し: `VplClient::postAtmtcTransactionsSync`。**Data Connection:** `Repository\AtmtcToVplDataConnectionRepository@syncAtmtcTransactionsToVpl` + seeder `AtmtcToVplDataConnectionSeeder`（`data_code` **ICL_1044**, `dailyAt` 例 03:15）。手動は `php artisan schedule:data_connection {id} {Y-m-d}` または `vpl:sync --entity=atmtc-transactions`。

---

#### 1.9. File: **[BE-IC / cloud]** `tests/Unit/...`（新規または既存に追加）

##### 1.9.1. マッピング・ペイロードの PHPUnit

**変更内容:**

- 代表行（fixture）から期待する JSON ペイロードを **スナップショットまたは配列 assert**。
- `VplClient` は HTTP をモック（`Http::fake`）し、`/api/atmtc-transactions/sync` が呼ばれることを検証。

---

## 実装順序 (Implementation Order)

1. **Backend（VPL）: lib 抽出 + `daily-operating` リファクタ**（依存: なし）  
   - §1.1, §1.2。既存契約テストが通ることを維持。

2. **Backend（VPL）: `atmtc-transactions` ルート + `index.ts` 登録**（依存: 1）  
   - §1.3, §1.4, §1.6。

3. **Backend（IC）: マッピングサービス + `VplClient` ラッパー**（依存: 2、`atmtc_delivery_data_results` が読めること）  
   - §1.7, §1.8, §1.9。API 契約は **2 と並行**に固定可能。

4. **Backend（VPL）: `daily-summary` の `runCount` 露出** — **後続**（PM B6.2、依存: 1）  
   - §1.5。

5. **Frontend（VPL）: sync-logs ラベル**（依存: なし / 2 と並行可）  
   - FE §1.1。**daily-summary UI（FE §1.2）は後続**（B6.2）。

6. **統合テスト**  
   - IC fixture行 → VPLモックまたはステージングで `DailyDriverAssignment` / `DailyOperatingRecord` / 配賦結果を確認。

---

## 見積もり工数 (Estimated Effort)

- **Backend（VPL）:** 14–22 時間（目安、daily-summary `runCount` は後続のため除外）  
  - lib 化 + MASTER 化 + 集約ルート + Vitest: 12–18h  
  - すり合わせ・エッジケース: 2–4h
- **Backend（IC）:** 12–18 時間  
  - マッピング・集計ルール・サービス: 6–10h  
  - VplClient・PHPUnit・Job/Repository 接続: 6–8h  

- **Frontend（VPL）:** 2–4 時間（ラベル + 任意 UI）

**合計:** 30–46 時間（親ラベル **sp:14** と整合の目安）

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:** 1 日あたり行数が多い場合、IC 側で **事前集計**して VPL へ送る行数を減らす。VPL 側は **トランザクション / バッチ**で Prisma 負荷を抑える。

2. **UX 考慮:** sync-logs の `syncType` 表示で運用が追いやすくする。部分失敗時は `errors[]` を IC ログに残す。

3. **データ整合性:** 同一 `(vehicleId, date)` に複数ドライバーが来る場合の業務ルール（親 plan §1.6）。**最後の連携が勝つ**等、タイムシートと ATMTC の優先順位を文書化。

4. **既存機能との互換性:** **B5.1** により caller は **MASTER** 必須。非 MASTER の旧連携がある場合は **credential 切替**または PM が **B5.2** で別経路を認めた場合のみルート分離。

5. **親 issue との役割分担:** #1010 は ATMTC → IC 取込まで。#1044 は **IC → VPL** と **VPL 内部整理**。結合テストは **IC テーブルにデータがあること**が前提（issue 本文の依存関係どおり）。

---

## ワークスペース同期 (Doc sync)

本 plan 保存後、`docs/issues/Izumi_Issue-Requests-Repo/1044/` を以下へコピーすること（multi-root）:

- **[BE-VPL / vehicle-pl-system]**（docs root）
- **[BE-IC / cloud]**
- （任意）**izumi-timesheet-v2** — 本 issue の実装範囲外だがワークスペースルールに従い同期可

** なお `git commit` は行わない（/pr まで未コミット）。**
