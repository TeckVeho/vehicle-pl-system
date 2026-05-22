# Issue #57

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/vehicle-pl-system
repo: vehicle-pl-system
issue_url: https://github.com/TeckVeho/vehicle-pl-system/issues/57
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
workspace_root: .
frontend_path: .
backend_path: ./backend
migrations_path: ./backend/prisma/migrations
api_docs_path:
tests_path: ./backend/src
```

## Metadata

| Field | Value |
|--------|--------|
| **Title** | feat: 拠点スプレッドシート設定でフォルダ指定からスプレッドシート一覧を自動取得する |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/vehicle-pl-system/issues/57 |
| **Created** | 2026-05-20T09:14:59Z |
| **Updated** | 2026-05-21T05:13:16Z |
| **Assignees** | tungnt183855 |
| **Labels** | backend, frontend, middle priority |

## Body

### Description

#### 日本語

「拠点スプレッドシート設定」画面において、拠点ごとに Spreadsheet の URL／ID を手入力する運用を見直し、**Google Drive のフォルダ（URL またはフォルダ ID）だけを入力**すれば、そのフォルダ直下（または運用で定める範囲）に存在する **Google スプレッドシートの一覧（ファイル名・ID）を自動取得**し、拠点との紐付けに利用できるようにする。

#### Tiếng Việt

Trên màn hình cấu hình spreadsheet theo cơ sở, thay đổi để không cần dán URL/ID Sheets từng cơ sở như hiện tại; người dùng chỉ cần nhập **thư mục Google Drive (URL hoặc folder ID)** thì hệ thống **tự đọc danh sách Google Spreadsheet trong thư mục** (tên file, ID) để phục việc gán cho từng cơ sở.

### Requirements

#### 日本語

- 画面上から **フォルダを指定**（共有ドライブ含む運用がある場合は仕様として明記）し、「一覧取得」等の操作で **スプレッドシートのみ**を列挙できること。
- 取得結果を **現在の拠点テーブル**と組み合わせ、ユーザーが **どのファイルをどの拠点に紐付けるか**を設定できる UI にすること（**ファイル名のみの自動マッピング**を行う場合は、命名規約・取り込み順・競合時の扱いを要件で定義）。
- 既存の保存 API（`/api/locations/:id` の `spreadsheetId`）との **互換を維持**し、最終的に保存される値は従来どおり Sheets の **ファイル ID** とすること。
- サービスアカウント連携・権限エラー時は **利用者が原因を把握できるエラー表示**になること（既存の `drive.readonly`・フォルダ共有前提はドキュメント化）。
- マスタ権限・画面アクセスは現行仕様と同等とする。

#### Tiếng Việt

- Người dùng chọn **thư mục** trên UI và có thể liệt kê **chỉ Google Spreadsheet** trong phạm vi đã thống nhất.
- Kết hợp với bảng cơ sở hiện có để **gán file nào cho cơ sở nào**; nếu **tự map theo tên file** thì quy ước đặt tên, thứ tự, trùng tên phải được quy định.
- **Tương thích** API lưu hiện tại (`spreadsheetId` là file ID).
- Lỗi quyền / SA phải hiển thị rõ cho người dùng; ghi chú về share folder với service account.
- Quyền MASTER / truy cập màn hình giữ như hiện tại.

### Acceptance Criteria

#### 日本語

- [ ] フォルダ指定のみ（＋取得操作）で、対象フォルダ内の **スプレッドシート一覧**（少なくともファイル名と ID）が画面に表示される。
- [ ] 取得した一覧を用いて、各拠点の `spreadsheetId` を設定・保存でき、既存の売上取り込み等と **引き続き連携できる**。
- [ ] Drive API／認証設定が不正なとき、**適切なエラー**になる（開発者がログで追える程度の情報）。
- [ ] アシストのみで **完全自動だけ**とする場合、**命名規約と例外時の手動変更**が受け入れ条件に織り込まれている。
- [ ] ヘルプ文言が「フォルダと共有」の前提を踏まえ、**サービスアカウントへの共有**など既存運用と矛盾しない。

#### Tiếng Việt

- [ ] Chỉ định thư mục (+ thao tác tải) hiển thị **danh sách Spreadsheet** (tối thiểu tên + ID).
- [ ] Gán và lưu `spreadsheetId` từng cơ sở, **tích hợp cũ vẫn chạy**.
- [ ] Lỗi API/auth hiển thị hợp lý và có thể trace.
- [ ] Nếu chỉ auto mapping theo tên: có quy ước và xử lý chỉnh tay khi lệch.
- [ ] Copy hướng dẫn phù hợp với **share folder cho service account**.

### Technical Context

- **画面:** `src/app/locations/page.tsx` — 見出し「拠点スプレッドシート設定」。現状は各行で URL または Sheets ID を `extractSheetId` して `PATCH /api/locations/:id`。
- **API:** `backend/src/routes/locations.ts` — `PATCH` で `spreadsheetId` を更新。**フォルダ内一覧用の GET**（または既存パスへの拡張）を新設する想定。
- **Drive:** `backend/src/lib/google-drive-client.ts` — サービスアカウント・`drive.readonly`。`files.list` で `mimeType`/親フォルダを絞り、スプレッドシートだけ返す関数を追加する選択肢。**フォルダ ID の抽出**（URL 入力対応）はフロントまたは共通ユーティリティで統一してよい。
- **検証:** `backend/scripts/drive-folder-smoke.ts` など既存 Drive 検証がある場合は流用。

### Constraints

#### 日本語

- 共有ドライブ（Shared drive）対応が必要かどうかは実装前に確認し、必要なら `supportsAllDrives` 等の既存実装パターンに合わせる。
- 「フォルダ内の全自動マッピング」は **運用ガイドラインなしには行わず**、少なくとも初版はドロップダウン等での **人的確認が可能な UI** とする前提を推奨（自動マッチはオプション扱いでもよい）。

#### Tiếng Việt

- Xác nhận Shared drive và tham khảo pattern API hiện có.
- Không chỉ auto map hoàn toàn nếu chưa có quy ước; nên có UI chọn/xác nhận ở bản đầu.

### Dependencies

#### 日本語

- 既存の Google サービスアカウント設定・環境変数 (`GOOGLE_SERVICE_ACCOUNT_JSON`)、および対象フォルダの共有設定が整っていることが前提。
- （任意）インフラ側で Drive API の割り当て・クォータに問題がないこと。

#### Tiếng Việt

- Cấu hình service account và quyền share thư mục phải sẵn sàng.

## Implementation checklist

- [ ] **Backend / Drive client**: Add `listSpreadsheetsInFolder(folderId)` in `backend/src/lib/google-drive-client.ts` using `files.list` with `mimeType` filter and parent folder; handle Shared drive via `supportsAllDrives` if required.
- [ ] **Backend / API**: New GET endpoint (e.g. `/api/drive/folders/:folderId/spreadsheets` or query-param variant) returning `{ id, name }[]`; user-friendly errors for auth/permission failures.
- [ ] **Frontend / folder input**: Folder URL or ID input +「一覧取得」action on `src/app/locations/page.tsx`; extract folder ID from URL (shared utility).
- [ ] **Frontend / mapping UI**: Display fetched spreadsheets; per-location dropdown (or similar) for manual assignment; optional filename-based auto-suggest with clear conflict handling.
- [ ] **Frontend / save flow**: Keep existing `PATCH /api/locations/:id` with `spreadsheetId` (file ID only); no breaking changes to revenue import integration.
- [ ] **Help copy**: Update help text for folder sharing with service account (`client_email` as Viewer).
- [ ] **Tests**: Add backend tests under `./backend/src` for Drive list helper and new route (mock Drive client).
- [ ] **Smoke / manual**: Reuse or extend `backend/scripts/drive-folder-smoke.ts` if present for manual verification.

## Notes / review

- **Project V2**: Single project detected — `Izumi_Issue` (`PVT_kwDOCjwUv84Ajq0M`) for `/breakdown` child issue assignment.
- **Frontend layout**: Next.js app at repo root (`frontend_path: .`); pages under `src/app/`.
- **OpenAPI**: No `openapi.yaml` / swagger artifact detected; leave `api_docs_path` empty.
- **Current UI**: Locations page uses per-row manual URL/ID input with `extractSheetId`; MASTER role required via `canManageMaster`.
- **Recommended v1 scope**: Manual dropdown assignment from fetched list; auto-match by filename as optional enhancement only if naming convention is documented.
