# Kế hoạch: Đưa đồng bộ ATMTC delivery export vào Data Connection (thống nhất với master)

**Trạng thái:** Phần kỹ thuật đã triển khai trên **`izumi/cloud`** (repository + seeder + bỏ command cũ); môi trường cần **chạy seeder** và rà **`is_import`** nếu UI yêu cầu.  
**Liên quan:** #1043 (IC import), parent #1010.  
**Tham chiếu code:** `database/seeders/AtmtcMasterDataConnectionSeeder.php`, `Repository\AtmtcMasterSyncRepository`, `App\Jobs\DataConnectionJob`, `schedule:data_connection`.

---

## Quyết định đã chốt (PM / phụ trách)

| Hạng mục | Chốt |
|----------|------|
| **`data_code`** | Dùng **mã ICL mới**, không trùng dải master hiện tại (`ICL_1035`–`ICL_1038`). |
| **`from` / `to` (System)** | **Không đảo** so với master: vẫn cùng cặp `イズミクラウド` → ATMTC (`config('atmtc.system_to_name')`) như `AtmtcMasterDataConnectionSeeder`. Chiều nghiệp vụ ATMTC → DB IC thể hiện bằng **tên kết nối**, **`remark`**, và **Repository** (GET export). |
| **`is_import`** | User **chưa ghi số**. Khi code: đối chiếu filter / báo cáo DataConnect — nếu UI cần phân loại “nhập vào IC” thì có thể phải khác `0` của master; cập nhật một dòng vào bảng này khi đã rà trong code. |
| **Khoảng ngày theo `$date`** | **`$effective` = `$date` ?? ngày hiện tại** (timezone app, ví dụ `Carbon::today(config('app.timezone'))`). **`startDate`** = `$effective` **trừ 7 ngày** (cận dưới, `Y-m-d`). **`endDate`** = **`$effective`** (cận trên, `Y-m-d`). Luôn **`startDate` ≤ `endDate`** khi gọi `api/izumi/export/{start}/{end}`. Hai đầu **inclusive** → đang là **8 ngày lịch** từ (D−7) đến D (neo ngày `$effective`). |
| **Artisan `atmtc:import-delivery-export`** | **Bỏ** sau khi chuyển Data Connection; thủ công chỉ còn **`php artisan schedule:data_connection {id} {date}`** (hoặc tương đương đã có trên IC). |
| **Audit CSV** | **Có** — lưu raw CSV theo **log / storage của connection** giống master (`AtmtcDataConnectionCsvStorage` hoặc pipeline tương đương). |

---

## 1. Mục tiêu

- Quản lý **một kết nối dữ liệu** (`data_connections`) giống bộ master ATMTC: lịch `dailyAt`, vai trò, `DataItem` / lịch sử, UI DataConnect (nếu có).
- **Không** duy trì hai cơ chế song song (schedule riêng `atmtc:import-delivery-export` + DataConnection) sau khi chuyển xong — tránh double import.

---

## 2. Pattern hiện tại (master IC → ATMTC)

| Thành phần | Vai trò |
|-------------|---------|
| `AtmtcMasterDataConnectionSeeder` | `DataConnection::updateOrCreate` theo `data_code`, `service_class_name` = `Repository\AtmtcMasterSyncRepository@…`, `frequency` = `dailyAt`, `time_at`, `type` = `active`, `from`/`to` = `System` |
| `routes/console.php` | Với mọi `DataConnection` `type=active` + `frequency` khớp → `Schedule::command(DataconnectionCommand::class, [$id])` |
| `DataconnectionCommand` | `DataConnectionJob::dispatch($id, $date)` — `$date` mặc định `Y-m-d` **hôm nay** |
| `DataConnectionJob` | `app()->call(service_class_name, [dataConnection, dataItem, date, department_name])` |
| `AtmtcMasterSyncRepository` | `bootstrap` + logic + `persistResult` / `MessageSentEvent`, có thể lưu CSV qua `AtmtcDataConnectionCsvStorage` |

**Lưu ý:** Master đặt `is_import` = `0`. Delivery **thực tế** là dữ liệu từ ATMTC vào IC nhưng **`from`/`to` trên bản ghi giữ như master** (đã chốt). **`remark` + tên connection** phải tránh hiểu nhầm với master sync.

---

## 3. Đề xuất triển khai (sau khi team OK)

### 3.1 Seeder

- Tạo **`AtmtcDeliveryExportDataConnectionSeeder`** (hoặc tên tương tự), **một** bản ghi:
  - `data_code`: **mã mới**, không trùng `ICL_1035`–`ICL_1038` (vd. reserve `ICL_10xx` — PM/DB quyết định).
  - `name`: vd. 「ATMTC 配送実績エクスポ」/ tên tiếng Việt ngắn gọn.
  - `service_class_name`: `Repository\AtmtcDeliveryExportRepository@syncDeliveryExportFromAtmtc` (tách class, **không** nhét thêm vào `AtmtcMasterSyncRepository` để phân biệt chiều dữ liệu).
  - `frequency` / `time_at`: ví dụ `dailyAt` + giờ (có thể trùng khung với các job ATMTC khác nhưng **khác `time_at` vài phút** để tránh spike — giống master 00:00–00:03).
  - `syncRoles`: cùng tập role như master hoặc subset — **cần xác nhận bảo mật**.

### 3.2 Repository mới

- **`Repository\AtmtcDeliveryExportRepository`** (tên có thể chỉnh):
  - Chữ ký method **giữ tương thích job:** `(DataConnection $dataConnection, DataItem $dataItem, string $date, ?string $department_name = null)` — nếu job cho phép `$date` rỗng thì **type nullable** và trong method: **`$effective = $date !== null && $date !== '' ? $date : today`** (theo timezone app).
  - Bên trong:
    1. **`$effective`** = `$date` có giá trị thì parse `Y-m-d`, không thì **hôm nay** (app timezone). **`startDate`** = `$effective` − 7 ngày, **`endDate`** = `$effective`, cả hai format `Y-m-d`. Ghi quy ước trong code + `remark` kết nối.
    2. Gọi **`AtmtcDeliveryExportService`**: `fetchCsvToSink` → file tạm; **`importFromLocalCsvPath`** (`Common::setInputEncoding` + Laravel Excel / `AtmtcDeliveryDataResultsImport`).
    3. **Bắt buộc:** lưu file CSV raw vào storage / log connection giống master (`AtmtcDataConnectionCsvStorage`).
    4. Kết quả: `persistResult` / cập nhật `DataItem` **theo cùng pattern** `AtmtcMasterSyncRepository` (success/fail, `response_body` có thể chứa `{ inserted, skipped, errors }`).

### 3.3 Lịch (schedule)

- **Xóa** (hoặc comment) schedule trực tiếp `AtmtcImportDeliveryExportCommand` trong `routes/console.php`.
- Sau khi seed, kết nối mới **tự** vào vòng lặp `DataConnection` hiện có (cùng file `routes/console.php` đầu file).

### 3.4 Lệnh Artisan độc lập

- **`atmtc:import-delivery-export`:** **Xóa** (command + đăng ký console) sau khi Data Connection hoạt động. Thủ công / stg: **`schedule:data_connection {connection_id} {date}`**.

### 3.5 Test

- Unit test **`AtmtcDeliveryExportService`** + **repository** (+ `AtmtcDataConnectionCsvStorage` nếu cần).
- Thêm (sau code): unit/feature test repository với `DataConnection`/`DataItem` fake hoặc mock `Http`, assert `DataItem.status` / không duplicate schedule.

### 3.6 Tham chiếu IC trên bảng delivery (sau import)

- Chi tiết cột **`ic_department_id`**, **`ic_employee_id`**, **`ic_vehicle_id`**, đổi tên **`vehicle_id` → `atmtc_vehicle_id`**, map config / master: xem **`dev.md`** (issue #1043, mục *Tham chiếu IC*).

---

## 4. Việc còn lại trước khi code

- [ ] Gán **giá trị `is_import` cụ thể** (sau khi rà filter UI/DataConnect).
- [ ] Chốt **`data_code`** thực tế (chuỗi ICL mới).
- [ ] Chốt **`time_at`** `dailyAt` cho connection delivery (tránh trùng giây với 4 job master nếu cùng kiểu lịch).

---

## 5. Tài liệu / issue con

- Cập nhật **`dev.md` / issue #1043** khi bắt đầu code: đã chuyển DataConnection; đã xóa `atmtc:import-delivery-export`.
- Parent **`plan.md` #1010**: mục BE IC — job DataConnection + seeder; cửa sổ export **`[effective−7, effective]`**, `effective = $date ?? today`; audit CSV.

---

*Cập nhật quyết định: bảng đầu tài liệu.*
