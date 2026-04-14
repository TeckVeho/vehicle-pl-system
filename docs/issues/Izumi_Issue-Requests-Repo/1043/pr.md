# Pull Request — Issue #1043

**Tracks:** [TeckVeho/Izumi_Issue-Requests-Repo#1043](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1043)  
**Parent:** [TeckVeho/Izumi_Issue-Requests-Repo#1010](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010)  
**Repository:** TeckVeho/izumi-cloud  
**Base branch:** `develop`

Closes #627  
Closes TeckVeho/Izumi_Issue-Requests-Repo#1043

_Same-repo issue [#627](https://github.com/TeckVeho/izumi-cloud/issues/627) satisfies the `izumi-cloud` PR policy check (Development / linked issue). Cross-repo #1043 remains the specification source._

---

## Summary

Triển khai BE IC cho **ATMTC delivery export**: GET `api/izumi/export/{startDate}/{endDate}`, stream CSV (`Http::sink`), `Common::setInputEncoding` + Laravel Excel import vào **`atmtc_delivery_data_results`**, **`id_atmtc` unique**, skip trùng. Luồng Data Connection + seeder như các commit trước trên nhánh.

Bổ sung **map tham chiếu IC** khi import: **`ic_department_id`** (config + map #1010), **`ic_employee_id`** (`driver_code` → `employees.employee_code`), **`ic_vehicle_id`** (course + biển + `vehicle_no_number_plate_history` + `delivery_date`). CSV **`vehicle_id`** (ATMTC) lưu **`atmtc_vehicle_id`**, ẩn khi serialize model.

## Key changes

- Migration: đổi **`vehicle_id` → `atmtc_vehicle_id`**, thêm **`ic_department_id`**, **`ic_employee_id`**, **`ic_vehicle_id`**.
- **`AtmtcDeliveryDataResultIcResolver`** + cập nhật **`AtmtcDeliveryDataResultsImport`** / model / **`config/atmtc.php`** (`delivery_department_code_to_ic_department_id`).
- Xóa class stub **`AtmtcDeliveryIcIdResolver`** (không dùng).
- **`tests/CreatesApplication.php`**: xóa `bootstrap/cache/config.php` khi `APP_ENV=testing` trước bootstrap.
- Docs **`dev.md`**, **`test.md`**, **`plan-dataconnection-migration.md`** (§3.6).

## Screenshots

N/A (backend only).

## Evidence

### 1. Backend testing (PHPUnit)

**Command:**

```bash
php artisan test --compact tests/Unit/AtmtcDeliveryExportRepositoryTest.php tests/Unit/AtmtcDeliveryExportServiceTest.php tests/Unit/AtmtcDataConnectionCsvStorageTest.php
```

**Result:**

- **Status:** SUCCESS  
- **Tests:** 11 passed  
- **Assertions:** 53  
- **Duration:** ~3.8s (SQLite `:memory:`; môi trường Windows có thể in cảnh báo `iconv`)

Chi tiết: `docs/issues/Izumi_Issue-Requests-Repo/1043/evidence/test_run_summary.txt`, `evidence/test-results.json`.

### 2. Build verification

Không áp dụng (không đổi frontend/build trong scope).

### 3. Type checking

Không áp dụng (PHP/Laravel).

### 4. Code linting

Có thể chạy `vendor/bin/pint --dirty` theo quy ước dự án / CI.

---

## Deployment / ops

- `php artisan migrate`
- Sau khi chỉnh config: `php artisan config:clear` hoặc `config:cache`
- Seed kết nối khi sẵn sàng: `php artisan db:seed --class=AtmtcDeliveryExportDataConnectionSeeder`

## Review focus

- Idempotency `id_atmtc`; header CSV `id` / `id_atmtc`, `delivery_date`.
- Quy tắc **`ic_vehicle_id`** (course + plate + department + ngày).
- Map **`delivery_department_code_to_ic_department_id`** khớp master #1010.
