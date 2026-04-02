# Issue #969 — Development Notes

## Implementation Summary

Implemented `VehicleMonthlyCostSyncService` to sync vehicle monthly costs from Izumi Cloud to VPL via `POST /api/vehicle-monthly-costs/sync`.

### Key Finding: ITP is NOT an external API

During investigation, discovered that ITP data in izumi-cloud is **not fetched via REST API**. Instead, the existing `VehicleServiceRepository` uses ChromeDriver/Selenium to scrape `itpv3.transtron.fujitsu.com`, download CSV/ZIP files, and import them into the `vehicle_itp_data` database table.

**Impact:** The `VehicleMonthlyCostSyncService` only needs to query the `vehicle_itp_data` table — no external HTTP calls needed for ITP. This significantly simplified implementation.

---

## Files Changed

### [NEW] `app/Services/Vpl/VehicleMonthlyCostSyncService.php`

Transforms IC database data → VPL `vehicle-monthly-costs/sync` payload.

**Data sources (all from IC database — no external calls):**

| VPL Field | IC Table | Query |
|-----------|----------|-------|
| `leaseDepreciation` (6191) | `vehicle_mahoujin_data` | `type='lease'` → `SUM(cost)` |
| `vehicleDepreciation` (6192) | `vehicle_mahoujin_data` | `type='vehicle'` → `SUM(cost)` |
| `vehicleLease` (6193) | `vehicle_costs` | → `maintenance_lease` |
| `insuranceCost` (6194) | `vehicle_data_orc_ai` | Latest record → `insurance_fee` |
| `taxCost` (6195) | `vehicle_costs` | → `car_tax` |
| `fuelEfficiency` | `vehicle_itp_data` | `type='km_l'` → `cost` |
| `roadUsageFee` | `vehicle_itp_data` | `type='etc'` → `cost` |

**N+1 prevention:** All cost data preloaded in batch queries before vehicle loop (Mahoujin, VehicleCost, VehicleDataORCAI, VehicleITPData grouped by `vehicle_id`).

**Interface:** Unlike master data services that use `buildPayload()` with no args, this service requires `yearMonth` parameter:
- `buildPayload(string $yearMonth): array`
- `sync(VplClient $client, string $yearMonth): array`

### [MODIFIED] `app/Console/Commands/VplSyncCommand.php`

- Added `--year-month=` option for `vehicle-monthly-costs` entity
- Added `vehicle-monthly-costs` as a **transaction entity** (not synced by default with `vpl:sync`)
- Must be explicitly requested: `php artisan vpl:sync --entity=vehicle-monthly-costs --year-month=2026-03`
- Defaults to previous month if `--year-month` is omitted
- Validates YYYY-MM format

**Design decision:** `vehicle-monthly-costs` is NOT included in default `vpl:sync` (no `--entity`). Master data (users, courses, vehicles, drivers) syncs by default; transaction data must be explicitly requested because it requires a `yearMonth` parameter.

### [NEW] `tests/Unit/Vpl/VehicleMonthlyCostSyncServiceTest.php`

10 unit tests, 35 assertions:

| Test | Assertions |
|------|-----------|
| Full payload with all data | 9 — all 7 fields + vehicleExternalId + departmentId |
| ITP data present | 4 — fuelEfficiency and roadUsageFee are not null |
| Missing ITP data → null, not skip | 5 — vehicle included, fuel/road = null, costs present |
| Missing VehicleCost → zero, not skip | 7 — all costs = 0, ITP = null |
| Department code format LOC xxx | 1 |
| No department → skip | 1 |
| vehicleExternalId is string | 2 |
| Insurance fee strips non-numeric | 1 — `¥12,345` → `12345` |
| Multiple mahoujin records SUM | 2 |
| Service instantiation | 1 |

---

## Test Results

```
PHPUnit 11.5.55

OK (27 tests, 65 assertions)

Vehicle Monthly Cost Sync Service:
 ✔ Full payload with all data
 ✔ Itp data present fuel and road not null
 ✔ Missing itp data returns null not skip
 ✔ Missing vehicle cost returns zero not skip
 ✔ Department code format loc xxx
 ✔ No department returns null entry
 ✔ Vehicle external id is string cast
 ✔ Insurance fee strips non numeric
 ✔ Multiple mahoujin records sum correctly
 ✔ Service can be instantiated
```

All existing tests (CourseSyncService, DriverSyncService, UserSyncService, VehicleSyncService) continue to pass — no regressions.

---

## Usage

```bash
# Sync vehicle monthly costs for March 2026
php artisan vpl:sync --entity=vehicle-monthly-costs --year-month=2026-03

# Dry run (build payload, log it, don't send)
php artisan vpl:sync --entity=vehicle-monthly-costs --year-month=2026-03 --dry-run

# Default: uses previous month
php artisan vpl:sync --entity=vehicle-monthly-costs
```

---

## Architecture Notes

- **ITP data flow:** `ITP web portal → ChromeDriver scrape → CSV → vehicle_itp_data table → VehicleMonthlyCostSyncService → VPL`
- **fuelEfficiency / roadUsageFee are raw data:** VPL self-calculates 燃料費(6175) and 道路使用料(6176) using `LocationCalculationParameter`
- **Mahoujin table name:** Model references `vehicle_mahoujin_data` (not `mahoujins` as shorthand in issue.md)
- **insurance_fee sanitization:** VehicleDataORCAI stores insurance_fee as string with currency symbols; stripped via FILTER_SANITIZE_NUMBER_INT
