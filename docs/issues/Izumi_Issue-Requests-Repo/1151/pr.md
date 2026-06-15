# Pull Request: vehicle-pl-system → `develop`

**Issue:** [TeckVeho/Izumi_Issue-Requests-Repo#1151](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1151)

Closes TeckVeho/Izumi_Issue-Requests-Repo#1151

## Summary

Adds a **Google Drive API** connection layer for the revenue `.xlsx` path described under parent [#953](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/953): singleton **`getDriveClient()`** with **`GOOGLE_SERVICE_ACCOUNT_JSON`**, scope **`drive.readonly` only**, clear error when env is missing/blank, **`resetGoogleDriveClientForTests()`** for isolation, and **Vitest** coverage mirroring the existing Sheets-style tests. Updates **`backend/.env.example`** with Drive-first and optional Sheets notes. Adds optional **`drive:smoke`** script for folder listing smoke checks.

Local planning docs (`plan.md`, `dev.md`) treat **Drive as the primary connector** for production files. The GitHub issue text still requires **`google-sheets-client.ts` + `getSheetsClient()`**; that module is **not** added in this branch—only **Drive** client + `.env.example` notes for optional Sheets. Close or extend #1151 if Sheets implementation must land in the same PR.

## Changes

- `backend/src/lib/google-drive-client.ts` — singleton Drive v3 client, readonly scope
- `backend/src/lib/google-drive-client.test.ts` — missing/whitespace env, invalid JSON, mocked happy path
- `backend/package.json` / `package-lock.json` — `googleapis` and `drive:smoke` script
- `backend/.env.example` — `GOOGLE_SERVICE_ACCOUNT_JSON`, Drive + optional Sheets documentation
- `backend/scripts/drive-folder-smoke.ts` — optional smoke helper
- `docs/issues/Izumi_Issue-Requests-Repo/1151/*` — issue/plan/dev tracking

## Screenshots

No UI changes; no screenshots.

## Evidence

### 1. Backend Testing

**Command:**

```bash
cd backend && npx vitest run
```

**Result:**

- SUCCESS — 9 test files passed, **80** tests passed
- Includes **4** tests in `src/lib/google-drive-client.test.ts`

### 2. Build Verification

**Command:**

```bash
cd backend && npm run build
```

**Result:**

- SUCCESS — `tsc` completed with no errors

### 3. Type Checking

**Command:**

```bash
cd backend && npx tsc --noEmit --strict
```

**Result:**

- SUCCESS — no output (clean strict check)

### 4. Code Linting

**Command:**

```bash
cd backend && npx eslint src
```

**Result:**

- Not run — no ESLint config present under `backend/` in this workspace

---

**Note:** `docs/issues/Izumi_Issue-Requests-Repo/953/` local artifacts are **not** part of this PR; stage only issue **1151** and the backend files above when committing.
