# Development log — Issue #955

**Issue:** [003] P2: 自動テストの導入  
**Repository:** TeckVeho/Izumi_Issue-Requests-Repo (work in `vehicle-pl-system`)  
**Branch context:** `955-feat-automated-tests` (local; not committed by this workflow)

## Approach

- **TDD-style:** Vitest + Supertest, `createApp()` extraction, unit tests (pure libs + Prisma-mocked allocators), HTTP contract tests for sync routes.
- **Scope:** Backend only (`backend/`). No CI, no root `package.json` test script (per issue).
- **plan.md alignment:** Extended mocks and cases to match §1.4–§1.9 (see below).

## Implemented

| Area | Detail |
|------|--------|
| **Tooling** | `vitest`, `supertest`, `@types/supertest`; scripts `npm test` / `npm run test:watch` |
| **App split** | `src/app.ts` exports `createApp()`; `src/index.ts` only listens |
| **Pure unit tests** | `salary-daily-proration.test.ts`, `location-expense-proration.test.ts`, `calc.test.ts` |
| **§1.7** | `account-item-filter.test.ts` — `accountItemEffectiveWhere` shape |
| **§1.8** | `driver-allocation.test.ts` — full prisma stub: no items, no vehicles, zero amounts, **two-vehicle same-day weight split** (50/50) |
| **§1.8 (second module)** | `salary-run-count-allocation.test.ts` — no salary items, no vehicles, **happy-path run-count allocation** (2 vs 3 runs split), zero-runcount, zero-amount edges |
| **§1.8 (third module)** | `location-expense-allocation.test.ts` — no items, no expenses, no vehicles, **equal split across 2 vehicles**, multi-location/multi-item, zero-amount skip |
| **§1.9** | `sync-routes.contract.test.ts` — **8 sync routes covered**: vehicles (401/403/400/200/Bearer), courses (400/200), driver-assignments (400 variants), **drivers** (400/200/401/403), **driver-monthly-amounts** (400 variants/200), **daily-operating** (400 variants/200), **location-monthly-expenses** (400 variants/200/401/403), **vehicle-monthly-costs** (400 variants/200/401/403) |
| **TS build** | `tsconfig.json` excludes `**/*.test.ts` |

## Commands run

```bash
cd backend && npm test
cd backend && npm run build
```

**Results:** 69 tests passed; `tsc` succeeded.

## Test summary

| Test file | Tests |
|-----------|-------|
| `salary-daily-proration.test.ts` | 8 |
| `location-expense-proration.test.ts` | 2 |
| `calc.test.ts` | 7 |
| `account-item-filter.test.ts` | 1 |
| `driver-allocation.test.ts` | 4 |
| `salary-run-count-allocation.test.ts` | 5 |
| `location-expense-allocation.test.ts` | 6 |
| `sync-routes.contract.test.ts` | 36 |
| **Total** | **69** |

## Decisions

- **Vitest** over Jest for native ESM alignment with `"type": "module"` and `NodeNext`.
- **Contract tests** use a **partial `prisma` stub** for routes under test; allocation modules called by routes are fully mocked to isolate route-level validation.
- **JWT** in tests uses the same default secret as `auth.ts` (`dev-secret-change-in-production`).

## Not done (out of scope or follow-up)

- CI wiring (explicitly out of issue scope).
- Coverage thresholds / `@vitest/coverage-v8`.
- Integration tests with real test DB (plan optional stretch).

## Git

- **No commits** were made (per `/dev` rules). All changes remain unstaged/uncommitted for `/test` and review.
