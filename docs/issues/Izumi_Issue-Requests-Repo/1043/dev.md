# Dev log — Issue #1043 (child of #1010)

## Context

- **GitHub:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1043  
- **Parent:** #1010  
- **Scope:** BE IC — ATMTC `GET` export CSV → bảng `atmtc_delivery_data_results` (plan §BE 1.2.x).

## Branch (code repo)

- **Repo:** `d:/CtyVeHo/izumi/cloud`  
- **Branch:** `1043-feat-atmtc-delivery-export`

## Đã triển khai

| Thành phần | Đường dẫn |
|------------|-----------|
| Migration (bảng gốc) | `database/migrations/2026_04_09_100000_create_atmtc_delivery_data_results_table.php` |
| Migration (IC refs + đổi tên cột ATMTC) | `database/migrations/2026_04_13_134539_add_ic_reference_columns_to_atmtc_delivery_data_results_table.php` |
| Model | `app/Models/AtmtcDeliveryDataResult.php` |
| Resolver map IC | `app/Services/Atmtc/AtmtcDeliveryDataResultIcResolver.php` — gọi từ import sau khi map từng dòng CSV |
| ATMTC export + import | `app/Services/Atmtc/AtmtcDeliveryExportService.php` (`Http::sink`, `Common::setInputEncoding`, Laravel Excel) + `app/Imports/AtmtcDeliveryDataResultsImport.php` |
| Data Connection handler | `app/Repositories/AtmtcDeliveryExportRepository.php` — `syncDeliveryExportFromAtmtc` (gọi bởi `DataConnectionJob`) |
| Seeder kết nối | `database/seeders/AtmtcDeliveryExportDataConnectionSeeder.php` — `data_code` **ICL_1039**, `dailyAt` **02:45**; chạy: `php artisan db:seed --class=AtmtcDeliveryExportDataConnectionSeeder` |
| Lịch | Vòng lặp `DataConnection` sẵn có trong `routes/console.php` (không còn schedule riêng `atmtc:import-delivery-export`) |
| Thủ công | `php artisan schedule:data_connection {id} {date?}` — `date` bỏ trống = hôm nay |
| Config / hằng | `ATMTC_EXPORT_PATH_PREFIX` trong `app/constants.php`; `export_path_prefix` + map `delivery_department_code_to_ic_department_id` trong `config/atmtc.php` |
| Tests | `tests/Unit/AtmtcDeliveryExportServiceTest.php`, **`AtmtcDeliveryExportRepositoryTest.php`**, `AtmtcDataConnectionCsvStorageTest.php` |
| Testing bootstrap | `tests/CreatesApplication.php` — ép sqlite `:memory:` khi PHPUnit đã set; trước `bootstrap()` xóa `bootstrap/cache/config.php` khi `APP_ENV=testing` (tránh config cache cũ thiếu key mới) |

### Encoding

- GET CSV qua **`Http::sink`** ghi một file tạm; **lưu audit** = đọc file → `AtmtcDataConnectionCsvStorage`; **import** = `Common::setInputEncoding` (path) + `Excel::import` cùng path — không còn chuỗi body trung gian / file tạm thứ hai cho decode UTF-8.

### CSV header

- Bắt buộc có cột **`delivery_date`** và cột ID ATMTC: trên CSV thường là **`id`**; importer map vào cột DB **`id_atmtc`**. Header **`id_atmtc`** vẫn được chấp nhận. Cột khác snake_case khớp `fillable`.
- Cột **`vehicle_id`** trên CSV (ID xe phía ATMTC) được lưu ở DB dưới tên **`atmtc_vehicle_id`**; trường này **`$hidden`** trên model để không lộ khi `toArray()`/JSON (ưu tiên dùng **`ic_vehicle_id`** cho xe IC).

### Tham chiếu IC (`ic_department_id`, `ic_employee_id`, `ic_vehicle_id`)

Điền **mỗi lần insert** trong `AtmtcDeliveryDataResultsImport` (sau khi có `department_code`, `driver_code`, `course_code`, `plate_number_vehicle`, `delivery_date` từ dòng CSV). Không có trong CSV; nếu không match được master IC thì để **null** (import vẫn thành công).

| Cột DB | Nguồn CSV / điều kiện | Logic |
|--------|------------------------|--------|
| **`ic_department_id`** | `department_code` | Tra **`config('atmtc.delivery_department_code_to_ic_department_id')`**. Nội dung đồng bộ với bảng map nội bộ parent #1010: `docs/issues/Izumi_Issue-Requests-Repo/1010/map_dpm.txt` (cột trái = `departments.id` IC, cột phải = mã ATMTC). |
| **`ic_employee_id`** | `driver_code` | `employees.employee_code` = `driver_code` (trim; thử thêm so khớp kiểu số nếu cột DB là integer). |
| **`ic_vehicle_id`** | `course_code` + `plate_number_vehicle` + `delivery_date` | (1) Tồn tại `courses` với `course_code` khớp (soft delete mặc định loại bản ghi đã xóa). (2) `vehicle_no_number_plate_history.no_number_plate` = `plate_number_vehicle`, join `vehicles` cùng **`vehicles.department_id` = `courses.department_id`**, `vehicles.deleted_at` null. (3) Chọn bản ghi lịch sử **mới nhất** thỏa **`history.date` ≤ cuối ngày `delivery_date`** (timezone app). |

**Lưu ý triển khai:** sau khi thêm key vào `config/atmtc.php`, môi trường đã chạy `php artisan config:cache` cần **`config:clear` hoặc `config:cache` lại**; PHPUnit đã xử lý cache file trong `CreatesApplication` (xem bảng trên).

## Kiểm thử đã chạy

```bash
cd cloud && php artisan test --compact tests/Unit/AtmtcDeliveryExportRepositoryTest.php tests/Unit/AtmtcDeliveryExportServiceTest.php tests/Unit/AtmtcDataConnectionCsvStorageTest.php
```

Kết quả gần nhất (sau thêm test map IC): **11 passed**, **53 assertions** (Service + Repository + CsvStorage) — chạy lại lệnh trên để xác nhận máy local.

## Việc còn lại / lưu ý

- Chạy migration trên môi trường dev/stg: `php artisan migrate`  
- **Seed** kết nối delivery (sau khi có `System` イズミクラウド): `php artisan db:seed --class=AtmtcDeliveryExportDataConnectionSeeder`  
- Xác nhận ATMTC production trả CSV đúng header / timezone; chỉnh **`time_at`** trên `data_connections` nếu PM yêu cầu.  
- **Không** `git commit` trong phiên workspace-dev (theo luật workflow).

## Data Connection (đã code)

- Chi tiết quy ước ngày / lưu CSV / metadata: `plan-dataconnection-migration.md`.  
- **`is_import`:** seeder đặt `0` (cùng master); rà UI sau để chỉnh nếu cần.

## Tài liệu tham chiếu nội bộ

- `docs/issues/Izumi_Issue-Requests-Repo/1010/plan.md`  
- `docs/issues/Izumi_Issue-Requests-Repo/1010/breakdown/breakdown.md`  
- `docs/issues/Izumi_Issue-Requests-Repo/1010/map_dpm.txt` — map `department_code` ATMTC → `departments.id` (đã phản ánh trong `config/atmtc.php`)  
- `docs/issues/Izumi_Issue-Requests-Repo/1043/plan-dataconnection-migration.md`
