# Issue #955: [003] P2: 自動テストの導入 - Implementation Plan

## 概要 (Overview)

**Requirements:** Add a test runner (Vitest or Jest) under `backend/`, expose **`npm test`**, implement **unit tests** for allocation / proration logic in `backend/src/lib/`, and **API contract tests** for main **sync** routes (`POST .../sync`) such as vehicles, courses, and driver-assignments. **CI and deploy pipelines are out of scope** (per issue).

**Current state:** `backend/package.json` has no `test` script and no Vitest/Jest devDependencies. The API is started from `backend/src/index.ts`, which creates `express()`, mounts middleware and `apiRouter`, and calls `app.listen()` immediately—there is no exported `app` for HTTP testing. Many “allocation” modules (`driver-allocation.ts`, `location-expense-allocation.ts`, `salary-run-count-allocation.ts`, etc.) are **async functions tightly coupled to Prisma**; a smaller set of **pure helpers** exists (e.g. `salary-daily-proration.ts`, `location-expense-proration.ts`, `calc.ts`).

**Target state:** Developers run **`cd backend && npm test`** locally. Tests cover **pure logic** with fast unit tests, and **sync route contracts** (status codes, error shapes for invalid bodies, success JSON shape) using an in-memory Express app and controlled Prisma access (mocked client and/or dedicated test DB—team choice).

---

## FE (Frontend)

### 1. Files need to edit:

**None for this issue.** The GitHub issue explicitly scopes work to the backend and lists no UI. The Next.js app at the repository root (`./`) does not need changes for introducing backend tests.

If the team later wants a root-level script (e.g. `npm test` at monorepo root delegating to `backend`), that can be a follow-up and is **not** required by the issue DoD.

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `backend/package.json`

##### 1.1.1. Add test runner and scripts

Add **`"test"`** (and optionally **`"test:watch"`**) scripts. Prefer **Vitest** (native ESM, aligns with `"type": "module"` and `tsx` usage).

**現在の実装:**

- Scripts only include `dev`, `build`, `start`, and Prisma helpers; no test entry.

**変更内容:**

- Add devDependencies: `vitest`, `supertest`, and `@types/supertest` (for HTTP contract tests).
- Add `"test": "vitest run"` and `"test:watch": "vitest"`.
- Optionally add `vitest` config path via `"test": "vitest run --config vitest.config.ts"` once config exists.

---

#### 1.2. File: `backend/vitest.config.ts` (new)

##### 1.2.1. Vitest configuration for Node + ESM

**変更内容:**

- Set `environment: "node"`, align with `tsconfig` paths / ESM (`module: NodeNext`).
- Include patterns such as `src/**/*.test.ts` or `src/**/__tests__/**/*.ts`.
- If tests import `.js` extensions like production code, ensure `resolve` / `deps` settings match the existing import style (`../lib/foo.js`).

---

#### 1.3. File: `backend/src/index.ts` and/or `backend/src/app.ts` (new)

##### 1.3.1. Export Express `app` without listening (for `supertest`)

**現在の実装** (`backend/src/index.ts` lines 1–23):

- Builds `app`, mounts `/api`, then calls `app.listen(PORT, ...)`.

**変更内容:**

- Extract **`createApp()`** or move middleware + `apiRouter` setup into **`app.ts`** that exports `app` (or a factory).
- Keep `index.ts` as the entry that imports `app`, calls `listen`, and logs—so production behavior is unchanged.
- Contract tests import `app` and use `supertest(app)` without opening a real port.

---

#### 1.4. File: `backend/src/lib/salary-daily-proration.ts`

##### 1.4.1. Unit tests for pure helpers

**現在の実装** (lines 11–52):

- `SALARY_DAILY_PRORATION_CODES`, `isSalaryDailyProrationAccount`, `getPreviousYearMonth`, `getSalaryDailyAmount` — **no Prisma** in this file.

**変更内容:**

- Add `salary-daily-proration.test.ts` (or colocated `*.test.ts`):
  - `getPreviousYearMonth`: boundary cases (January → previous year December).
  - `getSalaryDailyAmount`: run-count path vs fallback when `totalRunCountInMonth === 0`, rounding to 2 decimals.
  - `isSalaryDailyProrationAccount`: membership for `6138` / `6147` and rejection of others.

---

#### 1.5. File: `backend/src/lib/location-expense-proration.ts`

##### 1.5.1. Unit tests for `isLocationExpenseProrationAccount`

**現在の実装** (lines 8–35):

- `LOCATION_EXPENSE_PRORATION_CODES` and `isLocationExpenseProrationAccount`.

**変更内容:**

- Sample codes in/out of the list; ensures regression if codes array changes.

---

#### 1.6. File: `backend/src/lib/calc.ts`

##### 1.6.1. Unit tests for `calcNetRevenue`, `calcTotalExpense`, `calcSalesRatio`, category helpers

**現在の実装** (lines 8–49):

- Pure functions over `Map` / `Set` and simple math.

**変更内容:**

- Table-driven tests for zero net revenue, empty maps, and typical ratios.

---

#### 1.7. File: `backend/src/lib/account-item-filter.ts` (if exports are stable)

##### 1.7.1. Optional unit tests

**変更内容:**

- If the module contains pure predicates or filters, add focused tests; skip or defer if it is only thin Prisma wrappers.

---

#### 1.8. Files: `backend/src/lib/driver-allocation.ts`, `location-expense-allocation.ts`, `salary-run-count-allocation.ts`, etc.

##### 1.8.1. Unit tests with mocked Prisma

**現在の実装:**

- Example: `runDriverAllocation` in `driver-allocation.ts` (from line 13) uses **`prisma.accountItem.findMany`**, **`prisma.vehicle.findMany`**, assignments, driver amounts, and **`prisma.monthlyRecord`** — heavy DB usage.

**変更内容:**

- Use **`vi.mock("../lib/prisma.js")`** (or equivalent) to provide an in-memory fake returning controlled graphs.
- Cover **edge paths** called out in issue: no vehicles, no driver-related items, zero amounts, weight aggregation (lines 57–107 area).
- Prioritize **one representative module fully mocked** (e.g. driver-allocation or salary-run-count-allocation), then extend patterns to others so DoD “main paths” is satisfied without duplicating every DB branch.

---

#### 1.9. Files: `backend/src/routes/vehicles.ts`, `courses.ts`, `driver-assignments.ts`

##### 1.9.1. API contract tests for `POST /sync`

**現在の実装:**

- `vehicles.ts` (lines 30–121): `POST /sync` with `requireRole(ROLES.MASTER)`, validates `vehicles` array, returns `400` when missing, JSON body `{ synced, skippedCount, results }` on success.
- `courses.ts` (lines 22+): `POST /sync` requires `courses` array (`courses` payload key), `400` if not array.
- `driver-assignments.ts` (lines 29+): validates `yearMonth`, `records`, date format, `yearMonth` regex.

**変更内容:**

- With **`createToken`** / **`setAuthCookie`** from `auth.ts` (lines 16–32) or **`Authorization: Bearer`** (see `requireAuth` line 53), authenticate as a user whose **`role`** is in **`ROLES.MASTER`** (`auth.ts` lines 100–111: e.g. `"DX"` or `"DX管理者"`).
- **Prisma** must resolve that user: `requireAuth` loads `prisma.user.findUnique` (`auth.ts` lines 67–70). Options:
  - **Integration:** seed minimal `User` + use real `DATABASE_URL` test database (docker or `.env.test`), or
  - **Mock:** mock `prisma` in auth middleware path (harder); prefer integration for auth + contract.
- Contract assertions:
  - **401** without cookie/token.
  - **403** with valid JWT but role not in `MASTER` (e.g. VIEW-only role).
  - **400** for invalid body (`vehicles` / `courses` / `yearMonth`+`records`).
  - **200** shape keys match existing handlers (do not assert full DB side effects unless using a real DB).

---

### 2. Optional / follow-up (not blocking DoD)

- **`backend/tsconfig.json`**: Add `"types": ["vitest/globals"]` only if using globals; otherwise `import { describe, it, expect } from "vitest"`.
- **Root `package.json`**: `npm test` delegating to backend—optional.
- **Coverage**: `vitest run --coverage` with `@vitest/coverage-v8`—optional unless team mandates.

---

## 実装順序 (Implementation Order)

1. **Backend — 基盤**

   - Add Vitest + Supertest + scripts (`package.json`).
   - Add `vitest.config.ts`.
   - Refactor **`index.ts` / `app.ts`** so `app` is testable.

2. **Backend — ユニット（純粋関数）**

   - `salary-daily-proration.ts`, `location-expense-proration.ts`, `calc.ts` tests (fast, no DB).

3. **Backend — ユニット（Prisma mock）**

   - At least one allocation module (`driver-allocation` or `salary-run-count-allocation`) with mocked `prisma` for main branches.

4. **Backend — API 契約テスト**

   - Auth helper for MASTER user + `POST /api/vehicles/sync`, `/courses/sync`, `/driver-assignments/sync` contract cases (401/403/400/200 shape).

5. **統合テスト**

   - If using a real test DB: run Prisma migrate/push against test schema and one happy-path sync (optional stretch beyond minimal contract tests).

6. **Frontend**

   - None.

---

## 見積もり工数 (Estimated Effort)

- **Backend**: **14–22 時間**

  - Vitest/Supertest + app export + config: **2–3 h**
  - Pure lib tests (`salary-daily-proration`, `location-expense-proration`, `calc`): **3–4 h**
  - Prisma-mocked allocation tests (1–2 modules): **4–8 h**
  - Sync route contract tests + auth fixture: **5–7 h**

- **Frontend**: **0 時間**

**合計**: **14–22 時間**

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

   - Prefer **pure unit tests** and **mocked Prisma** for allocation logic to keep CI/local runs fast; reserve full DB integration for a few smoke tests if needed.

2. **UX 考慮:**

   - Not applicable (no UI). Developer UX: `npm test` / `npm run test:watch` should be documented in backend README or issue notes.

3. **データ整合性:**

   - Contract tests that use a **real database** must use an isolated `DATABASE_URL` (test DB) and avoid touching production data. Clear or reset state between tests if needed.

4. **既存機能との互換性:**

   - Refactoring `index.ts` must **not** change route paths, middleware order, or CORS/cookie behavior; only **extract** `app` creation for testability.
   - JWT secret in tests should match `JWT_SECRET` used when signing tokens (`auth.ts` line 5).

5. **Runner choice:**

   - **Vitest** is recommended over Jest for ESM-first `backend` (`"type": "module"`, `NodeNext`). If Jest is chosen instead, extra ESM config is required—document any deviation.
