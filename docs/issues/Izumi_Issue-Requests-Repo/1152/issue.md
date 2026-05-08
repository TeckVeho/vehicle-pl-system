# Issue #1152

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1152
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
workspace_root: .
frontend_path: .
backend_path: ./backend
migrations_path: ./backend/prisma/migrations
api_docs_path:
tests_path: ./backend/src
```

**Development workspace (implementation repo):** `TeckVeho/vehicle-pl-system` (local clone). Paths above are relative to that repository root.

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [BE-B] P0: 売上スプレッドシート連携 - 売上データ取得・パース・Location API / P0: Liên kết Spreadsheet - Đọc dữ liệu, Parse, Location API |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1152 |
| **Created** | 2026-05-06T05:00:52Z |
| **Updated** | 2026-05-07T05:57:47Z |
| **Assignees** | tungnt183855 |
| **Labels** | backend, enhancement, Child issue |
| **Parent** | #953 |

## Body

### 日本語 / Japanese

#### 親Issue
Parent: #953

#### 説明
`getRevenueFromSpreadsheets` の本実装を行い、各拠点のスプレッドシートから売上データを取得・パースして損益計算書に反映する。また、拠点ごとのスプレッドシートIDをDBで管理するためのスキーマ変更・マイグレーション・管理APIも含む。

**前提**: BE-A（Google Sheets 接続基盤）が完了し `getSheetsClient()` が利用可能であること。

#### 要件

##### 1. Prismaスキーマ + マイグレーション
- `Location` モデルに `spreadsheetId String?` フィールドを追加
- `prisma migrate dev --name add-location-spreadsheet-id` を実行

##### 2. `getRevenueFromSpreadsheets` 本実装 (`backend/src/lib/spreadsheet-revenue.ts`)
- スタブを以下のロジックで置き換える:
  1. `prisma.location.findUnique` で `Location.spreadsheetId` を取得
  2. 未設定なら `console.warn` → 空 Map を返す（ゼロ表示 fallback）
  3. `prisma.vehicle.findMany` で `vehicleId → vehicleNo` のマッピングを構築
  4. `prisma.accountItem.findMany` で `accountItemId → name` のマッピングを構築
  5. `getSheetsClient()` を使い、タブ名 `{yearMonth}`、範囲 `A1:ZZ` を読み取る
  6. ヘッダー行（1行目）から勘定科目列インデックスを構築
  7. データ行（2行目以降）をパース: `vehicleNo` → `vehicleId`、列ヘッダー名 → `accountItemId`、金額
  8. 戻り値 `Map<"vehicleId-accountItemId", amount>` を返す
- 取得失敗時（API エラー・タブ不在等）は `console.error` → 空 Map（ゼロ表示）

**シート形式（本タスクで確定する仕様）:**
- タブ名: `YYYY-MM`（例: `2026-03`）
- 1行目ヘッダー: `vehicleNo` | `山崎製パン` | `ヤマザキ物流` | ...（勘定科目名と一致）
- 2行目以降: `001-001` | `1500000` | `200,000` | ...
- 金額はカンマ区切り / 数値どちらも対応

**フォールバック仕様（DoD 5 より）:**
| ケース | 挙動 |
|--------|------|
| `spreadsheetId` 未設定 | `console.warn` → 空 Map → ゼロ表示 |
| Sheets API エラー | `console.error` → 空 Map → ゼロ表示 |
| `getSheetsClient()` でエラー | catch → 空 Map → ゼロ表示 |

> ⚠️ **PM確認待ち**: fallback を「MonthlyRecord 参照」や「画面警告」に変更する場合は仕様確定後に追加対応。

##### 3. `PATCH /api/locations/:id` エンドポイント (`backend/src/routes/locations.ts`)
- MASTER ロール必要
- Body: `{ spreadsheetId: string | null }`
- 成功時: 更新後の `Location` オブジェクトを返す

##### 4. ユニットテスト (`backend/src/lib/spreadsheet-revenue.test.ts`)
- `vi.mock("../google-sheets-client.js")` で `getSheetsClient` をモック
- テストケース:
  - `spreadsheetId` 未設定 → 空 Map（warn を確認）
  - Sheets API エラー → 空 Map（error を確認、例外 throw しない）
  - 正常データ: vehicleNo → vehicleId / 勘定科目名 → accountItemId / 金額のマッピング
  - カンマ区切り金額 `"1,500,000"` の正しいパース
  - ヘッダーに存在しない vehicleNo はスキップ

##### 技術詳細 — 変更ファイル一覧
| ファイル | 種別 | 内容 |
|---------|------|------|
| `backend/prisma/schema.prisma` | 変更 | `Location.spreadsheetId String?` 追加 |
| `backend/prisma/migrations/...` | 新規 | マイグレーション SQL |
| `backend/src/lib/spreadsheet-revenue.ts` | 変更 | スタブ → 本実装 |
| `backend/src/lib/spreadsheet-revenue.test.ts` | 新規 | ユニットテスト |
| `backend/src/routes/locations.ts` | 変更 | PATCH エンドポイント追加 |

`income-statement.ts` / `dashboard.ts` は変更不要（caller 済み）。

`getSheetsClient` の import:
```typescript
import { getSheetsClient } from "./google-sheets-client.js";
```

##### 受け入れ基準
- [ ] `Location` テーブルに `spreadsheetId` カラムが存在する（マイグレーション済み）
- [ ] `PATCH /api/locations/:id` で spreadsheetId を設定・解除できる（MASTER ロール）
- [ ] `GET /api/income-statement?yearMonth=...&locationId=...` の `records` に正しい売上金額が返る
- [ ] `GET /api/dashboard/summary?yearMonth=...` の `netRevenue` に売上が反映される
- [ ] `spreadsheetId` 未設定・API エラー時に例外なく動作し、当該売上セルがゼロ表示になる
- [ ] ユニットテスト（`spreadsheet-revenue.test.ts`）が `npm test` で全件パスする
- [ ] 既存機能（income-statement, dashboard, export CSV）に破壊的変更なし

##### 依存関係
- **ブロッカー**: BE-A（Google Sheets 接続基盤）が完了し `getSheetsClient()` が利用可能であること
- FE issue（拠点設定UI）はこの issue 完了後に実施可能

---

### Tiếng Việt / Vietnamese

(See GitHub issue body for full Vietnamese section — same scope as Japanese.)

## Implementation checklist

1. [ ] Confirm BE-A / `getSheetsClient()` is available in `backend`.
2. [ ] Prisma: add `Location.spreadsheetId String?`; run `prisma migrate dev --name add-location-spreadsheet-id`.
3. [ ] Implement `getRevenueFromSpreadsheets` in `backend/src/lib/spreadsheet-revenue.ts` per sheet format and fallback rules.
4. [ ] Add `PATCH /api/locations/:id` (MASTER, body `{ spreadsheetId: string | null }`) in `backend/src/routes/locations.ts`.
5. [ ] Add `backend/src/lib/spreadsheet-revenue.test.ts` with mocks and cases from issue.
6. [ ] Run backend tests; verify income-statement and dashboard API behavior manually or via existing tests.
7. [ ] No breaking changes to income-statement, dashboard, CSV export.

## Notes / review

- **GitHub Project V2:** Issue is on project **Izumi_Issue** (`PVT_kwDOCjwUv84Ajq0M`). Use this id when `/breakdown` adds child issues to the same board.
- **Blocker:** Implementation depends on `getSheetsClient()` from the Google Sheets foundation task (BE-A).
- **PM follow-up:** Fallback behavior may change (MonthlyRecord / UI warning) after product confirmation.
- **`income-statement.ts` / `dashboard.ts`:** Explicitly out of scope for edits per issue; callers already wired.

## Branch (local)

- Created: `1152-feat-spreadsheet-revenue-location-api` (links to GitHub issue #1152 via branch name — **not committed**).
