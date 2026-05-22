# Issue #57: feat: 拠点スプレッドシート設定でフォルダ指定からスプレッドシート一覧を自動取得する - Implementation Plan

## 概要 (Overview)

[Issue #57](https://github.com/TeckVeho/vehicle-pl-system/issues/57) は **「拠点スプレッドシート設定」** 画面（`/locations`）の運用改善である。

**現状:** 拠点ごとに Google Sheets の URL または file ID を **手入力** し、`PATCH /api/locations/:id` で `spreadsheetId` を保存する（`src/app/locations/page.tsx` L27-33, L81-98）。

**改善後:** 画面上部で **Google Drive フォルダ（URL または folder ID）** を指定し「一覧取得」操作でフォルダ直下の **Google Spreadsheet のみ**（ファイル名 + ID）を取得。取得結果を拠点テーブルと組み合わせ、**ドロップダウン等で拠点ごとに紐付け** → 既存 API で `spreadsheetId`（file ID）を保存。売上取り込み等の下流連携は **変更なし**。

**v1 スコープ（推奨）:**
- 人的確認可能な UI（ドロップダウン選択）を主とする
- ファイル名による自動マッチは **オプション**（「名前で提案」ボタン等）。完全自動のみは行わない
- Shared drive は既存 `listDriveFilesByQuery` の `supportsAllDrives: true` パターンに合わせる

**既存資産の再利用:**
- `backend/src/lib/google-drive-client.ts` — `listDriveFilesByQuery`, `DriveFileRef`, `MIME_GOOGLE_SHEETS`
- `backend/scripts/drive-folder-smoke.ts` — 手動検証の参考
- UI コンポーネント `@/components/ui/select`（`YearMonthPicker.tsx` 等で使用中）

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `src/lib/google-drive-id.ts` (新規)

##### 1.1.1. `extractFolderId` ユーティリティ

フォルダ URL / raw ID の正規化を FE・BE で共通化できるよう、まず FE 側にユーティリティを追加する（BE でも同ロジックを mirror または共有可能なら backend にも追加）。

**変更内容:**

- `extractFolderId(input: string): string` を export
- 対応パターン例:
  - `https://drive.google.com/drive/folders/{id}`
  - `https://drive.google.com/drive/u/0/folders/{id}`（ユーザー番号付き）
  - `https://drive.google.com/open?id={id}`（folder として扱う場合）
  - 空白トリム後の raw folder ID（英数字 `-_`）
- マッチしない場合は trim した入力をそのまま返す（BE 側で 404/403 エラーに委ねる）
- 単体テストは任意（Vitest 未導入の FE では手動確認で可）

---

#### 1.2. File: `src/app/locations/page.tsx`

##### 1.2.1. フォルダ指定 + 一覧取得セクション（画面上部）

拠点テーブルの **上** に、フォルダ入力と取得操作 UI を追加する。

**既存コード** (line 186-204):

- 見出し「拠点スプレッドシート設定」と説明文のみ
- 説明は Sheets URL/ID の手入力前提

**変更内容:**

- state 追加:
  - `folderInput: string` — フォルダ URL/ID 入力
  - `folderFetchState: "idle" | "loading" | "success" | "error"`
  - `folderFetchError: string | null`
  - `availableSpreadsheets: { id: string; name: string }[]`
- UI ブロック（`canEdit` 時のみ操作可能）:
  - `Input` — フォルダ URL/ID
  - `Button`「一覧取得」— `handleFetchSpreadsheets`
  - ローディング中は `Loader2` spinner
  - 取得成功時: 取得件数 + 折りたたみ可能な一覧（ファイル名, ID の monospace 表示）
  - 失敗時: `AlertCircle` + ユーザー向けメッセージ（権限不足・フォルダ未共有・設定不備等）
- `handleFetchSpreadsheets`:
  ```ts
  const folderId = extractFolderId(folderInput);
  const res = await fetchApi(`/api/drive/spreadsheets?folderId=${encodeURIComponent(folderId)}`);
  ```
- 説明文（L200-203）を更新:
  - フォルダを SA（`client_email`）に **閲覧者** で共有する前提
  - 従来の URL/ID 直接入力も **フォールバック** として残す旨

##### 1.2.2. 拠点行 — ドロップダウン選択 UI

一覧取得後、各行の Sheets 入力を **Select** に切り替え（または Select + 手入力の併用）。

**既存コード** (line 250-259):

- 全行 `Input` で URL/ID を自由入力
- `extractSheetId` で保存時に ID 抽出

**変更内容:**

- `availableSpreadsheets.length > 0` のとき:
  - `@/components/ui/select` でファイル選択
  - 先頭 option: `未選択`（value `""`）
  - 各 option: `{ name }` 表示、value = `{ id }`
  - 同一 file を複数拠点に割当可能（issue 要件上禁止していない）だが、選択済み ID に `(他拠点で使用中)` バッジを付けると UX 向上
- 一覧未取得時 or 手動上書きモード: 既存 `Input` を維持（トグル「手入力」リンクでも可）
- `rowStates[loc.id].value` は引き続き **file ID**（または URL）を保持 — `handleSave` / `PATCH` フローは **変更なし**
- Select 変更時: `handleValueChange(loc.id, selectedId)` で ID をセット

##### 1.2.3. （オプション）名前による自動提案

**変更内容:**

- 「名前で自動提案」ボタン（一覧取得成功後、`canEdit` 時）
- ルール（v1 最小）:
  - `spreadsheet.name` に `location.name` が **部分一致** する最初の 1 件を提案
  - 複数マッチ / 0 件はスキップし、結果サマリを toast またはインライン表示（例: 「3 拠点を提案、2 拠点は手動が必要」）
- **自動保存はしない** — ユーザーが各行を確認してから「保存」

##### 1.2.4. 保存・クリア・状態表示

**既存コード** (line 81-184, 274-320):

- `handleSave`, `handleClear`, `isDirty`, 状態バッジ（設定済/未設定/保存中）

**変更内容:**

- ロジックは基本維持。Select 選択後も `isDirty` が正しく動くよう `value` は常に file ID に正規化
- テーブル列ヘッダを「Sheets ID / URL」→「スプレッドシート」等に変更（Select 主体の UI に合わせる）

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `backend/src/lib/google-drive-client.ts`

##### 1.1.1. `listSpreadsheetsInFolder(folderId)` 追加

フォルダ直下の Google Spreadsheet のみを列挙する公開関数を追加する。

**現在の実装** (line 121-142, 165-173):

- `listDriveFilesByQuery` — ページネーション付き `files.list`、`supportsAllDrives: true`
- `listSharedPlSpreadsheetFileRefs` — env フォルダ + ファイル名マーカー `損益計算資料` で絞り込み（本 issue とは別用途）

**変更内容:**

- 新規 export:
  ```ts
  export async function listSpreadsheetsInFolder(
    folderId: string
  ): Promise<DriveFileRef[]>
  ```
- `folderId` が空のとき throw: `[google-drive] listSpreadsheetsInFolder: empty folderId`
- Drive query:
  ```
  '{folderId}' in parents and trashed = false and mimeType = '{MIME_GOOGLE_SHEETS}'
  ```
- `listDriveFilesByQuery(drive, q)` を再利用
- 返却: `{ id, name }[]`、名前昇順ソート（UI 安定のため）
- **Shared drive:** 既存 `listDriveFilesByQuery` の `supportsAllDrives` / `includeItemsFromAllDrives` で対応済み
- **注記:** 運用で Drive 上の `.xlsx` アップロードも拠点 Sheet として使う場合、v2 で `mimeType = MIME_XLSX` を OR 条件に追加可能（v1 は issue 要件どおり native Spreadsheet のみ）

##### 1.1.2. （任意）`extractDriveFolderId(input: string)`

BE でも folder URL を受け付ける場合、FE と同ロジックの helper を追加。v1 では FE が ID を渡す前提でも可。

---

#### 1.2. File: `backend/src/routes/drive.ts` (新規)

##### 1.2.1. `GET /api/drive/spreadsheets?folderId=`

フォルダ内スプレッドシート一覧 API。

**変更内容:**

- `driveRouter = Router()`
- `GET /spreadsheets`:
  - `requireRole(ROLES.MASTER)` — locations PATCH と同等
  - query: `folderId`（必須、Zod `z.string().trim().min(1)`）
  - `listSpreadsheetsInFolder(folderId)` 呼び出し
  - 成功: `200` + `{ spreadsheets: DriveFileRef[] }`
  - エラーマッピング:
    | Google / 内部 | HTTP | ユーザー向け `error` |
    |---|---|---|
    | `GOOGLE_SERVICE_ACCOUNT_JSON` 未設定 | 503 | サービスアカウントが設定されていません |
    | 403 / insufficient permissions | 403 | フォルダがサービスアカウントと共有されていない可能性があります |
    | 404 / not found | 404 | フォルダが見つかりません |
    | その他 | 502 | Drive API の取得に失敗しました |
  - サーバログ: `console.error` に `[drive/spreadsheets]` プレフィックス + 元エラー（開発者 trace 用）

---

#### 1.3. File: `backend/src/routes/index.ts`

##### 1.3.1. drive ルーター登録

**現在の実装** (line 50):

- `apiRouter.use("/locations", locationsRouter);`

**変更内容:**

- `import { driveRouter } from "./drive.js";`
- `apiRouter.use("/drive", requireRole(ROLES.MASTER), driveRouter);`
- 認証は既存 `requireAuth` ミドルウェアでカバー済み

---

#### 1.4. File: `backend/src/lib/google-drive-client.test.ts`

##### 1.4.1. `listSpreadsheetsInFolder` テスト追加

**既存パターン** (line 281-324):

- `listSharedPlSpreadsheetFileRefs` の mock テスト — `filesListMock`, `supportsAllDrives: true`

**変更内容:**

- empty folderId → throw
- 正常系: query に `mimeType = 'application/vnd.google-apps.spreadsheet'` が含まれること
- ページネーション: 2 ページ分を結合
- Drive API エラーがそのまま reject されること

---

#### 1.5. File: `backend/src/routes/drive.test.ts` (新規、または contract test へ追加)

##### 1.5.1. Route 契約テスト

**変更内容:**

- `supertest` + mock `listSpreadsheetsInFolder`
- MASTER 未認証 → 401/403
- `folderId` 欠落 → 400
- 正常 → 200 + body shape
- mock throw 403 → 適切な JSON error

---

#### 1.6. File: `backend/scripts/drive-folder-smoke.ts`

##### 1.6.1. スプレッドシート一覧モード追加（任意）

**現在の実装** (line 37-43):

- フォルダ内 **全ファイル** を list（mime フィルタなし）

**変更内容:**

- CLI フラグ `--spreadsheets-only` または `listSpreadsheetsInFolder` 呼び出しを追加
- 手動 QA: `npm run drive:smoke -- <FOLDER_ID>` で Spreadsheet のみ表示確認

---

#### 1.7. File: `backend/.env.example`

##### 1.7.1. ヘルプコメント更新

**変更内容:**

- 拠点設定画面からフォルダ指定で一覧取得する運用を追記
- 対象フォルダを SA `client_email` に **Viewer** で共有する手順を明記（既存 Drive コメントと整合）

---

## 実装順序 (Implementation Order)

1. **Backend 実装**（Frontend の前提）

   - 1.1.1 `listSpreadsheetsInFolder` in `google-drive-client.ts`
   - 1.4.1 ユニットテスト
   - 1.2.1 `drive.ts` route + 1.3.1 router 登録
   - 1.5.1 route テスト
   - 1.6.1 smoke script 更新（任意）
   - 1.7.1 `.env.example` 更新

2. **Frontend 実装**（Backend API 完成後）

   - 1.1.1 `extractFolderId` utility
   - 1.2.1 フォルダ入力 + 一覧取得 UI
   - 1.2.2 ドロップダウン選択
   - 1.2.3 名前自動提案（オプション、時間があれば）
   - 1.2.4 説明文・列ヘッダ更新

3. **統合テスト**

   - フォルダ未共有 → FE に 403 メッセージ表示
   - 一覧取得 → 拠点を Select → 保存 → `GET /api/locations` で `spreadsheetId` 反映
   - 保存後: 既存売上 sync（`spreadsheet-revenue`）が従来どおり file ID で動作
   - 非 MASTER ユーザー: `/locations` → forbidden（既存挙動維持）
   - `npm test`（backend）全件パス

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 3–4 時間

  - `listSpreadsheetsInFolder` + テスト: 1–1.5h
  - `GET /api/drive/spreadsheets` + エラーマッピング + route テスト: 1.5–2h
  - smoke / `.env.example`: 0.5h

- **Frontend**: 4–5 時間

  - `extractFolderId` + フォルダ取得 UI: 1.5h
  - 行ごと Select + 既存 save フロー統合: 2h
  - 名前自動提案（オプション）+ ヘルプ文案: 0.5–1.5h

**合計**: 7–9 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

   - フォルダ直下のみ（非再帰）— issue 要件「フォルダ直下（または運用で定める範囲）」に合わせ v1 は **直接の子** のみ
   - `pageSize: 100` + ページネーションで大量ファイルにも対応（既存 `listDriveFilesByQuery`）
   - 一覧結果は FE state のみ保持（DB 永続化不要）

2. **UX 考慮:**

   - 一覧取得前も手入力で設定可能（後方互換・例外対応）
   - 権限エラーは「SA にフォルダを共有してください」等、**原因が分かる日本語**
   - 同一 file の重複割当は警告表示のみ（ブロックは v1 では任意）
   - Select 内の長いファイル名は truncate + title tooltip

3. **データ整合性:**

   - 保存値は従来どおり `Location.spreadsheetId`（Sheets file ID）
   - フォルダ ID は DB に保存しない（画面セッションのみ）
   - PATCH API・Prisma schema **変更不要**

4. **既存機能との互換性:**

   - `spreadsheet-revenue.ts` / scheduler — `Location.spreadsheetId` 経由の連携は無変更
   - `listSharedPlSpreadsheetFileRefs`（損益計算資料 + env フォルダ）— 別経路、影響なし
   - `locationsRouter.patch` — 変更なし
   - Shared drive: 既存 `supportsAllDrives: true` パターンを踏襲

5. **API 設計メモ:**

   - エンドポイント案: `GET /api/drive/spreadsheets?folderId={id}`
   - 代替案 `GET /api/locations/spreadsheets?folderId=` も可能だが、Drive 操作は `/api/drive` に分離する方が責務が明確

6. **セキュリティ:**

   - MASTER 権限必須（locations 設定画面と同等）
   - folderId はユーザー入力 — Drive query は `driveQueryLiteral` でエスケープ済み
   - SA は `drive.readonly` のみ（書き込み不可）
