# Issue #968 — Dev Notes: VPL同期 vehicles・drivers

> **Status:** ✅ Implementation Complete & Verified  
> **Date:** 2026-04-01  
> **Assignee:** tungnt183855  
> **Parent Issue:** #956  
> **Depends on:** Issue #967 (users, courses sync — foundation)

---

## Overview

Triển khai đồng bộ **vehicles** và **drivers** từ Izumi Cloud (Laravel) → VPL backend, theo pattern đã thiết lập ở Issue #967 (users, courses). Bao gồm:

- `VehicleSyncService.php` — `POST /api/vehicles/sync`
- `DriverSyncService.php` — `POST /api/drivers/sync`
- Đăng ký 2 entity mới vào `VplSyncCommand.php`
- PHPUnit unit tests cho cả 2 service

---

## Files Changed

### New Files

| File | Mô tả |
|------|--------|
| `izumi-cloud/app/Services/Vpl/VehicleSyncService.php` | Service đồng bộ vehicles: build payload + gọi VPL API |
| `izumi-cloud/app/Services/Vpl/DriverSyncService.php` | Service đồng bộ drivers: build payload + gọi VPL API |
| `izumi-cloud/tests/Unit/Vpl/VehicleSyncServiceTest.php` | 6 test cases cho VehicleSyncService |
| `izumi-cloud/tests/Unit/Vpl/DriverSyncServiceTest.php` | 4 test cases cho DriverSyncService |

### Modified Files

| File | Thay đổi |
|------|----------|
| `izumi-cloud/app/Console/Commands/VplSyncCommand.php` | Thêm `vehicles`, `drivers` vào `resolveEntities()`, `syncEntity()` switch case, cập nhật `$signature` và docblock comments |

---

## Implementation Details

### 1. VehicleSyncService

**Endpoint:** `POST /api/vehicles/sync`  
**Mapping reference:** `ic-sync-field-mapping.md` §3

#### Field Mapping

| VPL Field | IC Source | Transform |
|-----------|----------|-----------|
| `departmentId` | `vehicles.department_id` | `CourseSyncService::toDepartmentCode(id)` → `"LOCxxx"` |
| `vehicleNo` | `latestNumberPlateHistory` → `PlateHistory.no_number_plate` | `trim((string) $plate->no_number_plate)` |
| `serviceType` | `vehicles.truck_classification` | `(string)` cast |
| `tonnage` | `vehicles.tonnage` | `(float)` cast |
| `externalId` | `vehicles.id` | `(string)` cast |
| `courseExternalId` | N/A | Luôn `null` (IC thiếu `course_id` column trên bảng `vehicles`) |

#### Key Design Decisions

- **N+1 Prevention:** Eager-load `Vehicle::with('latestNumberPlateHistory')` (500+ vehicles expected)
- **Department mapping:** Preload toàn bộ `Department::all()->keyBy('id')` để avoid N+1 khi resolve LOC code
- **SoftDeletes filter:** `whereNull('deleted_at')` — chỉ sync vehicles chưa bị xóa
- **Skip logic:**
  - Vehicles không có department → skip + ghi lý do
  - Vehicles không có biển số (null `latestNumberPlateHistory` hoặc empty string) → skip
- **Deduplication:** Deduplicate theo `departmentId + vehicleNo` composite key, giữ record có `externalId` cao nhất (newest IC record). Log warning khi phát hiện duplicate.
- **Tái sử dụng:** `CourseSyncService::toDepartmentCode()` — static method đã implement từ Issue #967

#### VPL Response Format

```json
{ "synced": N, "results": [...] }
```

Dùng `$response['synced']` để xác định số lượng đã sync thành công.

---

### 2. DriverSyncService

**Endpoint:** `POST /api/drivers/sync`  
**Mapping reference:** `ic-sync-field-mapping.md` §4

#### Field Mapping

| VPL Field | IC Source | Transform |
|-----------|----------|-----------|
| `departmentId` | `employees.final_department_id` (priority 1) hoặc `departments()` M-N relation (priority 2) | `CourseSyncService::toDepartmentCode(id)` → `"LOCxxx"` |
| `code` | `employees.employee_code` | `(string)` cast (DB type = integer) |
| `name` | `employees.name` | Map trực tiếp, fallback `(string) employee_code` nếu null |
| `externalId` | `employees.id` | `(string)` cast |

#### Department Resolution Priority

```
1. employees.final_department_id   ← Ưu tiên, direct column, không cần extra query
2. departments()->first()->id      ← Fallback qua M-N pivot (employee_department)
3. null                            ← Skip employee, ghi lý do vào skipped[]
```

Method `resolvePrimaryDepartmentId(Employee $employee): ?int` — public method, có thể test trực tiếp.

#### Key Design Decisions

- **N+1 Prevention:** Eager-load `Employee::with('departments')` cho fallback resolution
- **Active employees filter:** `retirement_date IS NULL OR retirement_date > today` — bỏ qua nhân viên đã nghỉ việc
- **Skip logic:**
  - Employees thiếu `employee_code` → skip
  - Employees không resolve được department → skip

#### VPL Response Format

```json
{ "success": true, "results": [...] }
```

> **Lưu ý:** Response format khác với vehicles (`{ synced: N }`). Driver service dùng `count($response['results'])` để xác định số lượng synced.

---

### 3. VplSyncCommand Updates

#### `resolveEntities()`

```php
$all = ['users', 'courses', 'vehicles', 'drivers'];
```

Sync order: `users → courses → vehicles → drivers` (courses trước vehicles để đảm bảo data consistency).

#### `syncEntity()` — switch case

Thêm 2 case mới:

```php
case 'vehicles':
    $service = new VehicleSyncService();
    break;
case 'drivers':
    $service = new DriverSyncService();
    break;
```

#### CLI Usage

```bash
php artisan vpl:sync                    # sync all (users → courses → vehicles → drivers)
php artisan vpl:sync --entity=vehicles  # sync vehicles only
php artisan vpl:sync --entity=drivers   # sync drivers only
php artisan vpl:sync --dry-run          # build payload, log, nhưng không gửi API
```

---

## Test Coverage

### VehicleSyncServiceTest — 6 tests, 11 assertions

| Test | Mục đích |
|------|----------|
| `test_department_code_formats_loc_prefix` | Verify `toDepartmentCode()` format LOC001, LOC022, LOC999 |
| `test_vehicle_with_plate_builds_correct_payload_entry` | Verify field mapping đầy đủ (departmentId, vehicleNo, serviceType, tonnage, externalId) |
| `test_vehicle_without_plate_is_skipped` | Vehicle không có `latestNumberPlateHistory` → null vehicleNo → skip |
| `test_vehicle_with_empty_plate_string_is_skipped` | Plate = "" → falsy → skip |
| `test_vehicle_course_external_id_is_always_null` | `courseExternalId` luôn null (IC thiếu course_id column) |
| `test_department_code_used_directly_for_vehicle` | Department id=5 → LOC005 |

### DriverSyncServiceTest — 4 tests, 10 assertions

| Test | Mục đích |
|------|----------|
| `test_driver_resolves_primary_dept_from_final_department_id` | Priority 1: `final_department_id` được dùng nếu có |
| `test_driver_resolves_primary_dept_from_relation_fallback` | Priority 2: fallback `departments()->first()` khi `final_department_id` = null |
| `test_driver_unresolvable_department_returns_null` | Không có cả 2 → return null |
| `test_driver_payload_fields_mapping` | Verify mapping code, name, externalId, departmentId |

### Test Execution Result

```
PASS  Tests\Unit\Vpl\DriverSyncServiceTest (4 tests)
PASS  Tests\Unit\Vpl\VehicleSyncServiceTest (6 tests)

Tests:    10 passed (21 assertions)
Duration: 0.58s
```

---

## Architecture Notes

### Pattern Consistency

Cả `VehicleSyncService` và `DriverSyncService` đều tuân theo pattern thiết lập bởi Issue #967:

```
SyncService
├── __construct()          → Đặt log channel từ config('vpl.log_channel')
├── buildPayload(): array  → Query DB, transform, trả về ['entity_key' => [...], 'skipped' => [...]]
├── sync(VplClient): array → Gọi buildPayload() + VplClient::post() + trả về summary
└── log(level, msg, ctx)   → Ghi log qua dedicated channel
```

### Dependencies

```
VehicleSyncService
├── Vehicle model (with latestNumberPlateHistory relation)
├── Department model (for LOC code lookup)
└── CourseSyncService::toDepartmentCode() (static, reused)

DriverSyncService
├── Employee model (with departments relation)
└── CourseSyncService::toDepartmentCode() (static, reused)
```

### Shared Utility

`CourseSyncService::toDepartmentCode(int $id): string` — format `"LOC" . str_pad($id, 3, '0', STR_PAD_LEFT)`. Được tái sử dụng bởi cả 3 service (courses, vehicles, drivers).

---

## Known Limitations / Future Considerations

1. **`courseExternalId` cho vehicles:** Hiện luôn `null` vì IC thiếu `course_id` column trên bảng `vehicles`. Nếu tương lai IC thêm cột này, cần cập nhật mapping.
2. **`employee_course` pivot table:** Tồn tại trong DB, có thể dùng cho driver-course mapping trong tương lai (ngoài scope issue này vì VPL `drivers/sync` API không nhận `courseExternalId`).
3. **Employee department resolution:** Đang dùng `final_department_id` (ưu tiên). Cần confirm với team IC đây có phải department chính không. Nếu business logic thay đổi, cập nhật `resolvePrimaryDepartmentId()`.
4. **Test approach:** Tests hiện tại verify transform logic trên model instances (không query DB). Integration test với DB thực sẽ cần `RefreshDatabase` trait và factory/seeder.
