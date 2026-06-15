# Pull Request

Closes TeckVeho/Izumi_Issue-Requests-Repo#1152

## Summary

Implements spreadsheet revenue integration for locations: Prisma schema and migrations for `Location` Drive/spreadsheet fields and Phase 2 snapshot models (`LocationDriveSyncMeta`, `DriveSpreadsheetRevenueLine`), `getRevenueFromSpreadsheets` and related Drive download/parse logic, `PATCH /api/locations/:id` for spreadsheet metadata (MASTER), spreadsheet revenue sync route and daily sync script, income-statement and sync-logs UI updates, and backend tests for spreadsheet revenue / Drive client. Aligns with issue #1152 (and planned Phase 2 snapshot behavior in local `plan.md`).

## Key changes

- **Database:** Prisma schema updates; new migration SQL under `backend/prisma/migrations/`.
- **Core:** `spreadsheet-revenue.ts`, `spreadsheet-revenue-log.ts`, `google-drive-client.ts` (+ tests).
- **API:** `locations.ts`, `spreadsheet-revenue.ts`, route registration in `index.ts`.
- **Scripts:** `spreadsheet-revenue-daily-sync.ts`, updates to `drive-folder-smoke.ts`.
- **Frontend:** `income-statement/page.tsx`, `sync-logs/page.tsx`.
- **Config:** `.gitignore`, `backend/.env.example`, `backend/package.json`.

## Screenshots

No PNG/JPG/WebP/GIF files are present under `docs/issues/Izumi_Issue-Requests-Repo/1152/evidence/`. Add images there and push if visual evidence is required.

## Evidence

### Test Execution Summary

⚠️ **No test results available**

- Tests have not been executed in this session, or results were not saved to the evidence file.
- To record results, run the project test/build commands and save output to `docs/issues/Izumi_Issue-Requests-Repo/1152/evidence/test-results.json` (for example after `/test`).
- File `docs/issues/Izumi_Issue-Requests-Repo/1152/evidence/test-results.json` was not found at PR preparation time.

### 1. Backend Testing

**Command:**

```bash
cd backend && yarn test:unit
```

**Result:**

Not run for this PR body (no `test-results.json`). Run locally and update the evidence file if counts are needed.

### 2. Build Verification

**Command:**

```bash
yarn build
```

**Result:**

Not run for this PR body.

### 3. Type Checking

**Command:**

```bash
npx tsc --noEmit --strict
```

**Result:**

Not run for this PR body.

### 4. Code Linting

**Command:**

```bash
npx eslint src
```

**Result:**

Not run for this PR body.
