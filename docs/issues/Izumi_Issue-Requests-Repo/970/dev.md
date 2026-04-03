# Issue #970 — Development Notes

## Implementation Summary

Implemented `LocationMonthlyExpenseSyncService` in **izumi-cloud** (Laravel) to sync PCA expense data
from `pl_pca_data` to VPL via `POST /api/location-monthly-expenses/sync`.

Also optimized the **VPL receiver endpoint** and allocation logic (vehicle-pl-system) to use bulk SQL
upserts — replacing N individual Prisma upserts that were causing cURL timeout (error 28) on izumi-cloud.

### Key Finding: PCA code = VPL code (no mapping needed)

After analyzing `PCADataImport.php` and `PLServiceRepository.php`, confirmed that `pl_pca_data.account_item_code`
is stored in the **same numeric format** as VPL's `LOCATION_EXPENSE_PRORATION_CODES` (6150, 6151, ..., 6189).

**Impact:** No code-to-code mapping is needed — only **filter** the 20 VPL-valid codes, skip the remaining 31 non-VPL codes.

---

## Files Changed

### izumi-cloud

#### [NEW] `app/Services/Vpl/LocationMonthlyExpenseSyncService.php`

Transforms `pl_pca_data` → VPL `location-monthly-expenses/sync` payload.

**Query strategy:** Single aggregated DB query instead of N individual calls:
```sql
SELECT department_id, account_item_code, SUM(cost) as total_cost
FROM pl_pca_data
WHERE DATE_FORMAT(date, '%Y-%m') = ?
GROUP BY department_id, account_item_code
```

**Field mapping:**

| VPL Field | IC Source | Transform |
|-----------|-----------|-----------|
| `yearMonth` | `--year-month` CLI option | Pass through |
| `expenses[].departmentId` | `pl_pca_data.department_id` | `CourseSyncService::toDepartmentCode($id)` → `LOCxxx` |
| `expenses[].accountItemCode` | `pl_pca_data.account_item_code` | Filter: 20 VPL codes → pass through; 31 non-VPL → skip |
| `expenses[].amount` | `SUM(pl_pca_data.cost)` | `(float)` cast |

**PCA code handling:**
- 20 VPL-valid codes (`6150`–`6189`) → included in payload
- 31 non-VPL codes (`6371`, `7037`–`7420`) → added to `$skipped[]` with reason, logged as warning
- No exception thrown on skip (other records continue processing)

**Interface:** `buildPayload(string $yearMonth): array` + `sync(VplClient $client, string $yearMonth): array`

#### [MODIFIED] `app/Console/Commands/VplSyncCommand.php`

- Added `use` import for `LocationMonthlyExpenseSyncService`
- Added `'location-monthly-expenses'` to `$transactionEntities` (not synced by default)
- Refactored `--year-month` validation to apply to all transaction entities (not just `vehicle-monthly-costs`)
- Added case in `syncEntity()` for `location-monthly-expenses` with `$dataKey = 'expenses'`
- Updated signature description and error messages

**Usage:**
```bash
php artisan vpl:sync --entity=location-monthly-expenses --year-month=2026-03
php artisan vpl:sync --entity=location-monthly-expenses --year-month=2026-03 --dry-run
php artisan vpl:sync --entity=location-monthly-expenses   # defaults to previous month
```

#### [MODIFIED] `app/Services/Vpl/VplClient.php`

- Increased HTTP timeout from `120s` → `300s`

**Reason:** Large monthly expense payloads (thousands of rows) caused cURL error 28 (timeout)
on the Laravel side before the VPL endpoint could finish processing and respond.

#### [NEW] `tests/Unit/Vpl/LocationMonthlyExpenseSyncServiceTest.php`

17 unit tests (no DB required — helper methods simulate `buildPayload` logic in-memory):

| Test Group | Tests |
|------------|-------|
| Valid codes list | 2 — count = 20, matches expected list |
| PCA code filtering | 5 — valid pass through, non-VPL skipped, unknown skipped |
| Department code format | 2 — `LOC001`, `LOC022`, `LOC999` |
| Payload structure | 4 — valid data, mixed codes, empty, all invalid |
| Amount handling | 3 — float cast, decimal, zero included |
| SUM aggregation | 1 — pre-aggregated row produces single entry |
| Skipped entry structure | 1 — has `department_id`, `account_item_code`, `amount`, `reason` |
| Service instantiation | 1 |

---

### vehicle-pl-system (VPL backend)

Root cause of the timeout: the original sync endpoint performed **N individual Prisma upserts** (one per row),
and the allocation logic performed **~17,000 individual upserts**. This caused the izumi-cloud HTTP request
to time out before the response was returned.

Fix: Replace all individual upserts with **chunked bulk `INSERT ... ON DUPLICATE KEY UPDATE`** via raw SQL.

#### [NEW] `backend/src/lib/sql-utils.ts`

Shared utilities for raw SQL bulk operations:
- `esc(val: string): string` — single-quote escaping for MySQL raw SQL
- `generateCuid(): string` — CUID-compatible ID generator matching Prisma's `@default(cuid())`

#### [MODIFIED] `backend/src/routes/location-monthly-expenses.ts`

**Before:** N individual `prisma.locationMonthlyExpense.upsert()` calls + N individual `prisma.location.findUnique()` calls.

**After:**
1. Batch-fetch all relevant locations in **1 query** before the loop
2. Collect all `(locationId, accountItemId, amount)` tuples into array
3. Bulk upsert via raw SQL in **500-row chunks**:

```sql
INSERT INTO LocationMonthlyExpense (id, locationId, accountItemId, yearMonth, amount, createdAt, updatedAt)
VALUES (...),...
ON DUPLICATE KEY UPDATE amount = VALUES(amount), updatedAt = VALUES(updatedAt)
```

#### [MODIFIED] `backend/src/lib/location-expense-allocation.ts`

**Before:** ~17,000 individual Prisma `findUnique` + `upsert` calls per allocation run.

**After:** Collect all allocation rows first, then bulk upsert in **1,000-row chunks** via raw SQL.

---

## Test Results

```
PHPUnit 11.5.55

OK (44 tests, 97 assertions)

Location Monthly Expense Sync Service:
 ✔ Valid account codes contains 20 codes
 ✔ Valid account codes match vpl proration codes
 ✔ Vpl valid code passes through
 ✔ All 20 vpl codes pass through
 ✔ Non vpl code is skipped
 ✔ Multiple non vpl codes are skipped
 ✔ Completely unknown code is skipped
 ✔ Department id format loc xxx
 ✔ Department id various values
 ✔ Payload structure with valid data
 ✔ Payload with mixed valid and invalid codes
 ✔ Empty data returns empty expenses
 ✔ All codes invalid returns empty expenses with skipped
 ✔ Amount is float cast
 ✔ Amount with decimal values
 ✔ Zero amount is included
 ✔ Same department and account produces single entry
 ✔ Skipped entry contains required fields
 ✔ Service can be instantiated
```

All existing tests (CourseSyncService, DriverSyncService, UserSyncService, VehicleSyncService, VehicleMonthlyCostSyncService) continue to pass — no regressions.

---

## Architecture Notes

- **PCA code format:** PCA CSV imports 51 codes; 20 overlap with VPL proration targets (pass-through); 31 are non-VPL (skip)
- **Bulk upsert chunk sizes:** 500 rows/chunk for sync endpoint, 1,000 rows/chunk for allocation — tuned to avoid MySQL max_allowed_packet issues
- **Timeout root cause:** N=17,000+ individual Prisma queries during allocation caused izumi-cloud to time out. Bulk SQL reduces this to ~17 queries
- **`generateCuid()`:** Custom implementation in `sql-utils.ts` matching Prisma's CUID format since raw SQL bypasses Prisma's `@default(cuid())`
