# Issue #59 — Development Log

**Issue:** [FE] 拠点SS設定: フォルダ指定UI・ドロップダウン紐付け  
**URL:** https://github.com/TeckVeho/vehicle-pl-system/issues/59  
**Parent:** [#57](https://github.com/TeckVeho/vehicle-pl-system/issues/57)  
**Backend dependency:** [#58](https://github.com/TeckVeho/vehicle-pl-system/issues/58) — `GET /api/drive/spreadsheets`  
**Date:** 2026-05-21

---

## Requirements Summary

- `extractFolderId()` utility for Drive folder URL / raw ID
- Top-of-page folder input +「一覧取得」→ `GET /api/drive/spreadsheets?folderId=`
- Collapsible list of fetched spreadsheets (name + ID)
- Per-location `Select` when list fetched; `Input` fallback otherwise
- Optional「名前で自動提案」— partial match on `location.name` in file name (no auto-save)
- Updated help text (SA folder share Viewer, manual fallback)
- User-facing Japanese errors from API (403, 404, 503, 502)
- Preserve `handleSave`, `handleClear`, `extractSheetId`, `PATCH /api/locations/:id`, dirty/save states

---

## Approach

**Direct implementation** (no FE Vitest). Followed parent plan `docs/issues/vehicle-pl-system/57/plan.md` § FE and patterns from `users/page.tsx` (Select), `dashboard/page.tsx` (`<details>` collapsible).

---

## Changes Made

### 1. `src/lib/google-drive-id.ts` (new)

- `extractFolderId(input)`:
  - `/folders/{id}` (including `/drive/u/N/folders/`)
  - `open?id={id}`
  - Trimmed raw ID fallback

### 2. `src/app/locations/page.tsx`

- **Folder section** (`canEdit` only): folder `Input`,「一覧取得」, loading spinner, error display, success count + `<details>` file list
- **State:** `folderInput`, `folderFetchState`, `folderFetchError`, `availableSpreadsheets`, `manualInputRows`, `suggestSummary`
- **Row UI:** `Select` when list loaded; per-row「手入力で設定」/「一覧から選択」toggle; orphan saved ID shown in dropdown
- **UX:** `(他拠点で使用中)` via other locations’ draft `value`
- **「名前で自動提案」:** `spreadsheet.name.includes(location.name)` — updates row values only, user must save
- **Help text:** SA Viewer share + manual URL/ID fallback
- **Column header:** 「スプレッドシート」
- Unchanged: `extractSheetId`, `handleSave`, `handleClear`, `isDirty`, PATCH body

### 3. `docs/issues/vehicle-pl-system/59/issue.md`

- Issue metadata stub for `/dev` workflow

---

## Validation

```bash
cd /var/www/html/vehicle-pl-system && npm run lint
```

- `locations/page.tsx`: no errors after renaming `shouldUseDropdown` (avoid `use*` hook lint false positive)
- Pre-existing warnings in other files unchanged

**Manual / integration (requires running app + BE #58):**

1. MASTER user opens `/locations`
2. Share Drive folder with SA; paste folder URL →「一覧取得」
3. Confirm count + collapsible list
4. Select spreadsheet per location →「保存」→ DB `spreadsheetId` = file ID
5. Without fetch: manual `Input` still works
6. Trigger 403 (unshared folder) → Japanese error from API
7.「名前で自動提案」→ dirty rows, no auto-save
8. Save/clear/dirty badges unchanged

---

## Troubleshooting (2026-05-21)

**Symptom:** Folder `16kmJirn_UeDcTix2RowdEwxxKRItTh9T` returned「0 件」despite 13 files visible in Drive.

**Cause:** Folder contains only uploaded `.xlsx` (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`), not native Google Sheets. v1 API query filtered `mimeType = spreadsheet` only.

**Fix (BE):** `listSpreadsheetsInFolder` now includes both native Sheets and `.xlsx` (same as `downloadDriveFileAsXlsxBuffer`). Smoke: 13 files listed after change.

**Note:** Dropdown lists all spreadsheet-like files in the folder (損益計算資料 + 運行表, etc.). User selects the correct file per location.

---

## Git Status

Changes intentionally **left uncommitted** per `/dev` workflow — ready for `/test` and review.
