# Dev — Issue #967: VPL Sync Users & Courses

## Tổng quan

Issue này triển khai ETL client trên **Izumi Cloud** (Laravel) để đồng bộ dữ liệu **Users** và **Courses** sang hệ thống **VPL** (`vehicle-pl-system`) thông qua REST API.

Reference:
- [ic-sync-field-mapping.md](../956/ic-sync-field-mapping.md) — bảng ánh xạ field chi tiết
- [plan.md](../956/plan.md) — kế hoạch tổng thể Issue #956
- `docs/external-integration-spec.md` — đặc tả API phía VPL

---

## Cấu trúc file mới

```
izumi-cloud/
├── config/
│   └── vpl.php                              # Config VPL (base URL, auth, retry, log)
├── app/
│   ├── Console/Commands/
│   │   └── VplSyncCommand.php               # Artisan command: vpl:sync
│   └── Services/Vpl/
│       ├── VplClient.php                    # HTTP client (JWT, retry, re-auth)
│       ├── UserSyncService.php              # ETL users (Role mapping Spatie → VPL)
│       └── CourseSyncService.php            # ETL courses (LOC padding, name gen)
```

File sửa:
- `config/logging.php` — thêm channel `vpl-sync` (daily, 30 ngày)
- `.env.example` — thêm biến `VPL_*`

---

## Cài đặt

### 1. Cập nhật `.env`

Thêm các biến sau vào file `.env`:

```dotenv
# --- VPL Sync ---
VPL_BASE_URL=http://localhost:4000
VPL_AUTH_IDENTIFIER=admin@example.com   # Email hoặc User ID trên VPL
VPL_AUTH_PASSWORD=password
VPL_TOKEN_TTL=604800
VPL_RETRY_TIMES=3
VPL_RETRY_SLEEP_MS=500
VPL_LOG_CHANNEL=vpl-sync
```

> **Lưu ý:**
> - `VPL_AUTH_IDENTIFIER` chấp nhận cả **email** lẫn **User ID** — hệ thống tự nhận diện.
> - User VPL cần có quyền **MASTER** (role `DX` hoặc `DX管理者`).
> - Lần chạy đầu tiên trên local: dùng tài khoản seed `admin@example.com` / `password` (từ `npx prisma db seed`).

### 2. Clear config cache

```bash
cd izumi-cloud
php artisan config:clear
```

---

## Sử dụng

### Sync tất cả (users → courses theo thứ tự)

```bash
php artisan vpl:sync
```

### Sync riêng từng entity

```bash
php artisan vpl:sync --entity=users
php artisan vpl:sync --entity=courses
```

### Dry-run (chỉ tạo payload, không gửi API)

```bash
php artisan vpl:sync --dry-run
php artisan vpl:sync --entity=users --dry-run
```

### Verbose output (hiển thị chi tiết skipped records)

```bash
php artisan vpl:sync -v
php artisan vpl:sync --entity=users -v --dry-run
```

---

## Kiến trúc

### Data Flow

```
IC Database (MySQL)
    │
    ├── users table + Spatie roles
    │   └── UserSyncService.buildPayload()
    │       └── mapRole() → VALID_ROLES mapping
    │
    ├── courses table + departments table
    │   └── CourseSyncService.buildPayload()
    │       ├── toDepartmentCode() → "LOC001"
    │       └── generateCourseName() → nối course_type/address
    │
    └──▶ VplClient.post('/api/users/sync', payload)
         VplClient.post('/api/courses/sync', payload)
             │
             ├── JWT login (cached, auto-refresh)
             ├── Retry logic (exponential back-off)
             └── 401 → re-auth → retry
```

### Role Mapping (Users)

| IC Role (Spatie `name`) | VPL Role |
|---|---|
| `crew` | `CREW` |
| `clerks` | `事務員` |
| `tl` | `TL` |
| `department_office_staff` | `事業部` |
| `personnel_labor` | `人事労務` |
| `general_affair` | `総務広報` |
| `accounting` | `経理財務` |
| `quality_control` | `品質管理` |
| `sales` | `営業` |
| `site_manager` | `現場MG` |
| `hq_manager` | `本社MG` |
| `department_manager` | `部長` |
| `executive_officer` | `執行役員` |
| `director` | `取締役` |
| `dx_user` | `DX` |
| `dx_manager` | `DX管理者` |
| `am_sm` | `現場MG` |

> User không có role khớp sẽ bị **skip** (không gửi sang VPL, tránh crash batch).

### Department ID Conversion (Courses)

```
departments.id = 1  → "LOC001"
departments.id = 22 → "LOC022"
```

Công thức: `'LOC' . str_pad($id, 3, '0', STR_PAD_LEFT)`

### Course Name Generation

IC không có cột `name` trên bảng `courses`. Tên được ghép từ:

1. `course_type` + `bin_type` + `address` (nếu có) → `"ＣＶＳ - 東京都港区"`
2. Fallback: `department.name` + `course_code` → `"横浜第一 001-001"`

---

## Logging

Log file: `storage/logs/vpl-sync/vpl-sync-YYYY-MM-DD.log`

Các log message chính:
- `[VplClient] VPL login successful`
- `[UserSync] Sending users/sync` (count, skipped)
- `[CourseSync] Sending courses/sync`
- `[VplSync] --- VPL Sync Start/End ---`
- Error/Warning khi retry hoặc skip records

---

## Xử lý lỗi

| HTTP Status | Hành vi |
|---|---|
| `200` | Thành công, log response |
| `400` | Client error → log chi tiết, **không retry** |
| `401` | Token expired → xóa cache, login lại, retry 1 lần |
| `403` | Permission denied → log error, dừng entity đó |
| `5xx` | Server error → retry tối đa 3 lần (exponential back-off) |
| Network error | Retry tối đa 3 lần |

---

## Kiểm thử

### Dry-run test (không cần VPL chạy)

```bash
php artisan vpl:sync --dry-run -v
```

Kiểm tra output có:
- Số lượng users/courses sẽ sync
- Danh sách skipped records (với `-v`)
- Log file trong `storage/logs/vpl-sync/`

### Integration test (cần VPL chạy trên localhost:4000)

```bash
# 1. Start VPL backend
cd ../backend && npm run dev

# 2. Chạy sync
cd ../izumi-cloud
php artisan vpl:sync --entity=users
php artisan vpl:sync --entity=courses

# 3. Kiểm tra kết quả trên VPL
# GET http://localhost:4000/api/users → sẽ thấy users mới
# GET http://localhost:4000/api/courses → sẽ thấy courses mới
```

---

## Timestamp Sync

- `createdAt` và `updatedAt` từ IC được đồng bộ sang VPL (ISO 8601).
- VPL sẽ dùng timestamp gốc từ IC thay vì auto-generate.
- Cột `deletedAt` đã được thêm vào VPL schema (nullable) — **chưa sync**, dành cho tương lai.

---

## Bugfixes (phát hiện khi integration test)

| Bug | Nguyên nhân | Sửa |
|-----|------------|-----|
| IC báo "557 synced" nhưng VPL chỉ có 3 user | IC có 581 user nhưng chỉ 9 email duy nhất → VPL `@unique` email constraint reject | `UserSyncService` tạo dummy email `user_{id}@izumi-dummy.local` cho email trùng |
| `UserSyncService` báo thành công khi VPL trả 500 | Không kiểm tra `_error` trong response array | Thêm check `_error` → throw `RuntimeException` |
| `CourseSyncService` tương tự | `courses/sync` không check `_error` | `CourseSyncService::sync()` cũng check `_error` + dùng `synced` từ response nếu có |
| cURL timeout 30s khi sync 500+ users | bcrypt hash 500+ passwords > 30s | Tăng timeout lên 120s |
| Login VPL thất bại khi dùng email | `VplClient` gửi email dưới key `userId` → VPL tìm `externalId` | Tự nhận diện `@` → gửi key `email` |
| Tên biến `VPL_AUTH_USER_ID` gây nhầm lẫn | Giá trị có thể là email | Đổi thành `VPL_AUTH_IDENTIFIER` (backward-compatible) |

---

## Known Limitations

- **ATMTC data:** Chưa tích hợp — courses không có driver linkage từ ATMTC.
- **Course name:** Logic nối chuỗi có thể cần product duyệt; điều chỉnh trong `CourseSyncService::generateCourseName()`.
- **Role `am_sm`:** Hiện map sang `現場MG` (closest match). Cần xác nhận với BA nếu cần role riêng trên VPL.
- **deletedAt:** Cột đã có trên VPL schema, nhưng chưa được sync từ IC.
- **Schedule:** Chưa đăng ký cron. Khi cần, thêm vào `app/Console/Kernel.php`:
  ```php
  $schedule->command('vpl:sync')->dailyAt('02:00');
  ```
