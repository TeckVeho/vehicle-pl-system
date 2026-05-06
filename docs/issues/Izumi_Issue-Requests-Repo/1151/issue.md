# Issue #1151: [BE-A] P0: 売上スプレッドシート連携 - Google Sheets接続基盤・認証クライアント / P0: Liên kết Spreadsheet - Hạ tầng kết nối Google Sheets, Auth Client

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1151
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
workspace_root: .
frontend_path: .
backend_path: ./backend
migrations_path: ./backend/prisma/migrations
api_docs_path:
tests_path: ./backend
```

**Path detection notes**

- `frontend_path: .` — root `package.json` is Next.js 15 + React (`src/app/`).
- `backend_path: ./backend` — Express + Prisma backend (`backend/package.json`, `backend/prisma/schema.prisma`).
- `migrations_path` — Prisma convention; directory may appear after first `prisma migrate` (no `migrations/` folder in repo at scan time).
- `tests_path: ./backend` — Vitest + co-located `*.test.ts` under `backend/src/` (and `backend/src/__tests__/`).

## Metadata

| Field | Value |
|--------|--------|
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1151 |
| **Created** | 2026-05-06T05:00:41Z |
| **Updated** | 2026-05-06T05:00:41Z |
| **Labels** | backend, enhancement, Child issue, sp:3 |

**Assignees:** *(none)*

## Parent issue

Parent: **[#953](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/953)** (`backend` revenue spreadsheet integration).

## Summary

Introduce **`googleapis`**, implement **`backend/src/lib/google-sheets-client.ts`** as a reusable **singleton** Google Sheets API client using **service account** credentials from **`GOOGLE_SERVICE_ACCOUNT_JSON`**, **read-only** `spreadsheets` scope only, throw when env missing, update **`backend/.env.example`**, and add **`backend/src/lib/google-sheets-client.test.ts`** (Vitest) covering missing env, invalid JSON, and happy path with mocked `google.sheets`.

## Body (from GitHub)

See issue URL for full Japanese / Vietnamese requirements, acceptance criteria, and example implementation.

## Implementation checklist

- [ ] `cd backend && npm install googleapis` — dependency in `backend/package.json`
- [ ] New `backend/src/lib/google-sheets-client.ts` — `getSheetsClient()` export, singleton, `GOOGLE_SERVICE_ACCOUNT_JSON`, scope `https://www.googleapis.com/auth/spreadsheets.readonly` only
- [ ] Missing `GOOGLE_SERVICE_ACCOUNT_JSON` → throw `Error` with clear message
- [ ] Update `backend/.env.example` — variable + setup / sharing notes
- [ ] New `backend/src/lib/google-sheets-client.test.ts` — missing env, bad JSON, valid JSON + mock `google.sheets`
- [ ] `npm test` (backend) passes

## Acceptance criteria (from issue)

- [ ] `googleapis` installed and listed in `package.json`
- [ ] `getSheetsClient()` exported from `google-sheets-client.ts`
- [ ] Valid `GOOGLE_SERVICE_ACCOUNT_JSON` → client returned
- [ ] Unset `GOOGLE_SERVICE_ACCOUNT_JSON` → appropriate error
- [ ] All unit tests pass via `npm test`
- [ ] `.env.example` documents `GOOGLE_SERVICE_ACCOUNT_JSON`

## Notes / review

- **Dependency:** none — can be implemented before BE-B (revenue read/parse).
- **Scope:** connection/auth layer only; no spreadsheet business logic in this issue.
- **Security:** service account JSON in env — avoid logging; document sharing spreadsheet with SA email as viewer.

### Architecture note (production revenue files)

**Production** integration under parent **[#953](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/953)** targets **native `.xlsx` files** stored on **Google Drive**. The runtime flow is **Google Drive API** (`files.get` with `alt=media` or equivalent) to **download the current file bytes**, then **server-side parsing** (e.g. SheetJS / ExcelJS)—not “Open with Google Sheets” conversion.

- **Drive API** uses a **Drive file ID** per workbook and typically scope **`https://www.googleapis.com/auth/drive.readonly`** on the same service account (`GOOGLE_SERVICE_ACCOUNT_JSON`). Share the **folder or each file** with the service account **`client_email`** so downloads succeed.
- **Pull model:** each backend request (or a scheduled refresh) **fetches the latest revision** Google has stored; optional Drive **watch/push** channels can trigger **earlier refetches**, but there is no streaming API for live cell edits into the server.
- **This issue (#1151)** still delivers the **`googleapis` + service-account singleton** pattern via **`getSheetsClient()`** (`spreadsheets.readonly`). That remains useful if any workflow uses **Google Sheets–native** documents; **Drive download + `.xlsx` parse** is implemented in **#953 / BE-B**, not here.

---

*Document generated for `/plan`, `/breakdown`, `/dev` — do not commit unless the team workflow requires it.*
