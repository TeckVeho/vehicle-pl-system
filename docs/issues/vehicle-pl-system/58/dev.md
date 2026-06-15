# Issue #58 — Development Log

**Issue:** [BE] 拠点SS設定: Driveフォルダ内Spreadsheet一覧API  
**URL:** https://github.com/TeckVeho/vehicle-pl-system/issues/58  
**Parent:** [#57](https://github.com/TeckVeho/vehicle-pl-system/issues/57) — 拠点スプレッドシート設定（Frontend は別 issue）  
**Branch:** (uncommitted — dev phase)  
**Date:** 2026-05-21

---

## Requirements Summary

Implement backend layer for location spreadsheet configuration:

- `listSpreadsheetsInFolder(folderId)` in `google-drive-client.ts`
- `GET /api/drive/spreadsheets?folderId=` (MASTER only)
- Error mapping: SA unset → 503, permission → 403, not found → 404, other → 502
- Register `/drive` router in `routes/index.ts`
- Unit + route contract tests
- Optional smoke script mode + `.env.example` comments
- **No changes** to `PATCH /api/locations/:id` or Prisma schema

---

## Approach

**Direct implementation** (not TDD-first): followed existing patterns from `listSharedPlSpreadsheetFileRefs`, `sync-routes.contract.test.ts`, and parent plan `docs/issues/vehicle-pl-system/57/plan.md` § BE.

---

## Changes Made

### 1. `backend/src/lib/google-drive-client.ts`

- Added `listSpreadsheetsInFolder(folderId)`:
  - Throws on empty `folderId`
  - Reuses `listDriveFilesByQuery` with query:
    `'{folderId}' in parents and trashed = false and mimeType = 'application/vnd.google-apps.spreadsheet'`
  - Sorts results by name ascending (`localeCompare("ja")`)
  - Shared drive support inherited from existing list helper

### 2. `backend/src/routes/drive.ts` (new)

- `GET /spreadsheets` with Zod query validation (`folderId` required)
- `mapDriveSpreadsheetsError()` maps Google / internal errors to user-facing JSON
- Server log prefix: `[drive/spreadsheets]`

### 3. `backend/src/routes/index.ts`

- Registered: `apiRouter.use("/drive", requireRole(ROLES.MASTER), driveRouter)`

### 4. Tests

- `google-drive-client.test.ts`: empty folderId, query shape, pagination, error propagation
- `drive.test.ts`: auth 401, role 403, missing param 400, success 200, 503/403/404/502 error mapping

### 5. `backend/scripts/drive-folder-smoke.ts`

- Added `--spreadsheets-only` flag using `listSpreadsheetsInFolder`

### 6. `backend/.env.example`

- Documented folder sharing for location spreadsheet settings UI (`GET /api/drive/spreadsheets`)

---

## Validation

```bash
cd backend && npm test
```

**Result:** 12 test files, **120 tests passed** (including 8 new drive route tests + 4 new client tests).

---

## API Contract

```
GET /api/drive/spreadsheets?folderId={id}
Authorization: MASTER role (DX / DX管理者)

200 OK
{ "spreadsheets": [{ "id": string, "name": string }] }

400 — folderId is required
403 — フォルダがサービスアカウントと共有されていない可能性があります
404 — フォルダが見つかりません
503 — サービスアカウントが設定されていません
502 — Drive API の取得に失敗しました
```

---

## Out of Scope (per issue)

- Frontend (`/locations` UI) — separate FE issue
- `PATCH /api/locations/:id` changes
- Prisma schema changes
- `.xlsx` upload files in folder (v1 = native Google Sheets only)

---

## Manual Smoke (optional)

```bash
cd backend
npm run drive:smoke -- --spreadsheets-only <FOLDER_ID>
```

Requires `GOOGLE_SERVICE_ACCOUNT_JSON` and folder shared with SA `client_email` as Viewer.

---

## Git Status

Changes intentionally **left uncommitted** per `/dev` workflow — ready for `/test` and review.
