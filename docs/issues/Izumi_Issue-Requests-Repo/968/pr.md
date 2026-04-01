# PR: [BE] VPL同期 vehicles・drivers (Issue #968)

Closes TeckVeho/Izumi_Issue-Requests-Repo#968

## Summary

- **Izumi Cloud (Laravel):** `VehicleSyncService` và `DriverSyncService` gọi VPL `POST /api/vehicles/sync` và `POST /api/drivers/sync`; đăng ký `vehicles` / `drivers` trong `vpl:sync` (thứ tự users → courses → vehicles → drivers). Cập nhật `Vehicle::latestNumberPlateHistory` (`latestOfMany('date')`), PHPUnit cho VPL unit tests, `VplClient` / `VehicleSyncService` hiển thị lỗi HTTP chi tiết, `VplSyncCommand` in `errors` từ VPL khi có.
- **VPL backend (Express/Prisma):** `POST /api/vehicles/sync` xử lý lỗi từng dòng, chuẩn hóa `tonnage` (tránh NaN), upsert theo `externalId` rồi theo `(locationId, vehicleNo)` để tránh unique constraint, trả `errors[]` khi một số dòng lỗi.

## Key files

| Area | Paths |
|------|--------|
| IC sync | `izumi-cloud/app/Services/Vpl/VehicleSyncService.php`, `DriverSyncService.php`, `VplClient.php`, `VplSyncCommand.php`, `app/Models/Vehicle.php` |
| Tests | `izumi-cloud/tests/Unit/Vpl/VehicleSyncServiceTest.php`, `DriverSyncServiceTest.php` |
| VPL API | `backend/src/routes/vehicles.ts` |

## Evidence

### 1. Backend (VPL)

**Command:**
```bash
cd backend && npm run build
```

**Result:** TypeScript compiles successfully (`tsc`).

### 2. Izumi Cloud — PHPUnit (Vpl unit tests)

**Command:**
```bash
cd izumi-cloud && ./vendor/bin/phpunit tests/Unit/Vpl/ --no-configuration
```

**Result:** ⚠️ Run locally when `vendor/` is installed. If `phpunit.xml` + SQLite driver is used, ensure `pdo_sqlite` is available; alternatively `--no-configuration` as above. No `evidence/test-results.json` committed for this PR.

### 3. Manual verification

- `php artisan vpl:sync --entity=vehicles` against running VPL: expect synced count; VPL row errors printed when `errors` present in JSON response.

## Screenshots

None.
