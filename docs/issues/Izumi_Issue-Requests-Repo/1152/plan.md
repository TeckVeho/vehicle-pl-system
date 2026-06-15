# Issue #1152: 売上スプレッドシート連携（Drive / パース / Location API）+ DB スナップショット

## 概要

Google Drive 上の拠点別「損益計算資料」ワークブックから **売上** を読み取り、ダッシュボード・損益・日次サマリーに反映する。実装は **Drive API + `read-excel-file`**（Sheets `values.get` は使わない）。

このドキュメントは **Phase 1（済）** と **Phase 2（DB スナップショット + 定期同期）** に分ける。

---

## Phase 1（実装済み）

**目的:** 拠点ごとに Drive `fileId` と任意の売上シート名を保持し、リクエストのたびに（従来）ワークブックを取得してパースする。

**主な成果物**

| 領域 | 内容 |
|------|------|
| Prisma | `Location.spreadsheetId`, `Location.spreadsheetRevenueSheet` |
| コア | `backend/src/lib/spreadsheet-revenue.ts` — `getRevenueFromSpreadsheets`（複数レイアウト対応: カレンダー名タブ / 売上明細 / 車両別損益ピボット 等） |
| Drive | `backend/src/lib/google-drive-client.ts` — `downloadDriveFileAsXlsxBuffer` |
| API | `PATCH /api/locations/:id`（`ROLES.MASTER`）で `spreadsheetId` 等を更新 |
| 呼び出し | `income-statement`, `dashboard`, `daily-summary` が上記を呼ぶ |

**手入力専用売上科目**（`MANUAL_INPUT_ONLY_NAMES`）はスプレッドシート対象外。呼び出し元が `revenueFromSpreadsheetIds` 相当の集合で絞り込む。

---

## Phase 2（DB スナップショット + 同期）

**目的:** パース結果を DB に保持し、**ダッシュボード等で同じ 拠点×月 に対し Drive を毎リクエスト叩かない**。運用は **毎日 06:00 Asia/Tokyo** のバッチと、MASTER による即時 **POST 同期**。

### データモデル（Prisma）

- **`LocationDriveSyncMeta`** — `@@unique([locationId, yearMonth])`  
  最終同期: `driveFileId`, `revenueSheetTab`, `status`（`success` / `failed`）, `errorMessage`, `syncedAt`, `recordCount`
- **`DriveSpreadsheetRevenueLine`** — `locationId`, `yearMonth`, `vehicleId`, `accountItemId`, `amount`  
  `@@unique([locationId, yearMonth, vehicleId, accountItemId])`, `@@index([locationId, yearMonth])`  
  成功時のみ **当該 (locationId, yearMonth) の行を全削除後に再作成**（`amount ≠ 0` のみ永続化）。

### 読み取りポリシー（`getRevenueFromSpreadsheets`）

1. `LocationDriveSyncMeta.status === "success"` のとき、`DriveSpreadsheetRevenueLine` から要求された `vehicleIds` × `revenueAccountItemIds` を読み `Map` を構築 **（Drive は呼ばない）**。
2. 成功メタがない場合の既定（ロールアウト **A**）: **従来どおり live Drive + パースにフォールバック**。
3. **`SPREADSHEET_REVENUE_SNAPSHOT_ONLY=true`** のとき: メタがなければ **空 Map**（厳格運用・将来 B 相当）。

### 同期ロジック

- **`syncSpreadsheetRevenueForLocationYear(locationId, yearMonth)`**  
  拠点の全車両 + Phase 1 と同じ「スプレッドシート対象売上科目」（有効期間・手入力除外あり）でパース → トランザクションで行差し替え + メタ更新。
- 成功時のみ **`DataSyncLog`** に一行追加: `source: "Google Drive"`, `syncType: "spreadsheet_revenue"`（連携記録画面のラベル: **Drive 売上スナップショット**）。
- 失敗時: メタを `failed` に更新。**直前に成功していた行は削除しない**（最後の正常スナップショットを保持）。読み取りは A のため失敗メタ下では live フォールバックで整合を取りうる。

### API・バッチ

| 手段 | 説明 |
|------|------|
| `POST /api/spreadsheet-revenue/sync` | `ROLES.MASTER`。Body（Zod 任意）: `yearMonth?: YYYY-MM`, `locationId?: string`。省略時は JST 当月・全拠点。拠点ごとに部分成功を許容し要約 JSON を返す。 |
| `backend/scripts/spreadsheet-revenue-daily-sync.ts` | `npx tsx --env-file=.env scripts/spreadsheet-revenue-daily-sync.ts`。既定は **JST 当月 + 前月**。`SPREADSHEET_REVENUE_SYNC_YEAR_MONTHS=2026-05,2026-04` で上書き。Cron は 06:00 JST でこのスクリプトまたは内部 URL を叩く想定。 |

**セキュリティ:** 同期 API は認証 + MASTER。外部 Cron から叩く場合はネットワーク制限またはシークレットヘッダを推奨。

### 制約・拡張

- **同一 (拠点, 月) で「アクティブ」な Drive ファイルは 1 本**（`spreadsheetId` または名前自動解決）。将来、同一月に複数ファイルが必要なら別テーブル（例: `LocationDriveFile`）でメタと選択ルールを拡張する。
- パース内部キーは `` `${vehicleId}-${accountItemId}` ``。**Prisma の id に `-` を含めない**（通常の cuid で成立）。

---

## パフォーマンス・運用上の注意

- Phase 2 後、スナップショットが揃えば **ダッシュボード等の N 拠点ループでも Drive への同時アクセスは発生しない**（読み取りは DB）。
- シート修正から **次回同期まで** PL 表示はスナップショットとずれる可能性あり。必要なら UI で `syncedAt` を示す拡張を別途検討。
- Drive エクスポートのクォータ・サイズ制限は Phase 1 の注意と同様。

---

## 実装順序（Phase 2）

1. Prisma マイグレーション（上記 2 モデル）
2. `spreadsheet-revenue.ts` — パース共通化、`syncSpreadsheetRevenueForLocationYear`、`getRevenueFromSpreadsheets` のスナップショット優先
3. `POST /api/spreadsheet-revenue/sync` と `routes/index.ts` 登録
4. 日次スクリプト
5. Vitest（読取・同期）、連携記録 UI の `syncType` ラベル
6. 本ドキュメント更新

---

**Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1152
