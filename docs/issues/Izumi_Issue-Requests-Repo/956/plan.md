# Plan — Issue #956: Izumi Cloud → VPL (vehicle-pl-system) sync

## Mục tiêu

Triển khai **client đồng bộ phía Izumi Cloud (IC)** gọi HTTP API của **VPL** (`vehicle-pl-system` backend), đẩy master + dữ liệu kế toán theo `docs/external-integration-spec.md`, với **ETL** (không đẩy raw DB). Chi tiết field-level: [`ic-sync-field-mapping.md`](ic-sync-field-mapping.md).

## Phạm vi & giả định đã chốt

| Hạng mục | Quyết định |
|----------|------------|
| Hướng dữ liệu | **IC → VPL** (Cloud chủ động gọi API VPL). |
| Code chính | Repo **izumi-cloud** (Laravel): HTTP client, ETL, Artisan command. |
| Contract API | Repo **vehicle-pl-system** (`backend/`): Express + Prisma; tham chiếu `external-integration-spec.md`. |
| `departmentId` | Chuỗi **`LOC` + str_pad(`departments.id`, 3, '0', STR_PAD_LEFT)`** (vd. `1` → `LOC001`), khớp `Location.code` trên VPL. |
| Kích hoạt | **Chỉ khi chạy command** (chưa bắt buộc schedule; có thể bổ sung sau). |
| Logging | Ghi log theo ngày vào **storage** (pattern Laravel `daily` / channel riêng `vpl-sync`). |
| Thuật ngữ | **VPL** = `vehicle-pl-system`; không nhầm với hệ **PL** legacy (`BASE_URL_PL` trong IC). |

## Tài liệu tham chiếu (VPL)

- `docs/external-integration-spec.md`
- `docs/external-system-implementation-checklist.md`
- `docs/department-id-standard.md`
- Issue: [`issue.md`](issue.md)

## Kiến trúc triển khai (IC)

1. **HTTP client VPL:** base URL theo môi trường (dev: thường `http://localhost:4000`); header `Authorization: Bearer <JWT>`.
2. **Auth:** `POST /api/auth/login` — user tích hợp có quyền **MASTER** (và **EDIT_PL** cho import/bulk nếu dùng cùng hoặc user riêng); cache token, refresh trước khi hết hạn (7 ngày).
3. **ETL layer:** class/service per domain (users, courses, vehicles, drivers, monthly costs, location expenses) — đọc DB IC + (ITP, PCA) → payload đúng spec.
4. **Entry point:** một hoặc nhiều Artisan command, tham số `--entity=`, `--year-month=`, `--dry-run` (nếu implement).

## Phân phase

### Phase 0 — Chuẩn bị

- [ ] Xác nhận base URL VPL dev/stg/prod (constants hoặc config; secret chỉ `.env`).
- [ ] Tạo user VPL dùng cho liên kết (MASTER / EDIT_PL theo endpoint).
- [ ] Rà soát seed **`Location.code`** trên VPL khớp quy tắc `LOC` + pad 3 với `departments.id` IC (~20 đơn vị).

### Phase 1 — Nền tảng (IC)

- [ ] Module client VPL + xử lý lỗi HTTP (400/401/403/500), retry tối thiểu (policy ghi trong code/README issue).
- [ ] Quản lý JWT (lưu cache tạm, login lại khi 401).
- [ ] Command shell: ví dụ `vpl:sync` với option tách entity.

### Phase 2 — Master sync (theo thứ tự phụ thuộc)

Thứ tự gợi ý: **users → courses → vehicles → drivers** (vehicles sau courses; drivers có thể cần ATMTC sau).

| API VPL | Nội dung chính (IC) | Ghi chú từ mapping |
|---------|---------------------|---------------------|
| `POST /api/users/sync` | `users` | Map Spatie role → `VALID_ROLES` VPL. |
| `POST /api/courses/sync` | `courses` + `departments` | Tạo `name` (IC không có cột tên đủ); `externalId` = `courses.id`. |
| `POST /api/vehicles/sync` | `vehicles` + biển số qua `latestNumberPlateHistory` | `courseExternalId` có thể null nếu chưa có quan hệ ổn định. |
| `POST /api/drivers/sync` | `employees` + department chính (many-to-many) | Có thể cần dữ liệu ATMTC cho gán course (sau). |

### Phase 3 — Chi phí theo tháng

- [ ] `POST /api/vehicle-monthly-costs/sync`: gộp bảng nội bộ (MaintenanceLease, InsuranceRate, …) + **ITP API** cho `fuelEfficiency`, `roadUsageFee`; tham số `yearMonth`.
- [ ] `POST /api/location-monthly-expenses/sync`: **PCA** → map `accountItemCode` sang 20 mã VPL (`6150`–`6189`).

### Phase 4 — Kế toán (chọn ưu tiên)

- [ ] `POST /api/import` (multipart) **hoặc** `POST /api/income-statement/records/bulk` (JSON) — chốt nguồn dữ liệu trên IC (file vs DB) và user có quyền **EDIT_PL**.

### Phase 5 — Kiểm thử & bàn giao

- [ ] Test trên local: từng entity + một kịch bản end-to-end tối thiểu.
- [ ] Cập nhật `dev.md` / hướng dẫn chạy command; ghi known limitations (ATMTC, course–vehicle nếu chưa xong).

## Tiêu chí hoàn thành (acceptance)

1. Có thể chạy command trên IC và thấy bản ghi tương ứng trên VPL (DB) cho các entity đã implement, với `departmentId` đúng `LOCxxx`.
2. Log sync theo ngày trong `storage`, đủ để debug (request id / số bản ghi / lỗi API).
3. Mapping field thống nhất với [`ic-sync-field-mapping.md`](ic-sync-field-mapping.md) hoặc có mục “deviation” ghi rõ nếu đổi so với bản phân tích.

## Rủi ro & hạng mục còn mở

- **Role IC → VPL:** cần bảng map cụ thể (test với từng role thực tế).
- **Course `name`:** công thức nối chuỗi cần product duyệt.
- **Vehicle ↔ course:** nếu thiếu `course_id` trên IC, chấp nhận `courseExternalId` null tạm thời hoặc bổ sung nguồn (playlist/ATMTC).
- **PCA account code:** bảng map PCA → mã VPL cần BA/ kế toán xác nhận.
- **Import vs bulk:** chọn một đường trước để giảm phạm vi.
- **Biển số xe (Vehicle Number):** Do IC lưu lịch sử biển số (`latestNumberPlateHistory`), cần đảm bảo query Eloquent khi lấy ra biển số không bị lỗi N+1 Query (do phải lặp qua 500+ chiếc xe).

## Không nằm trong plan này (trừ khi mở rộng issue)

- Cron/schedule tự động (có thể thêm sau bằng cách gọi cùng service từ `Kernel`).
- Sửa lớn API VPL (chỉ fix lệch spec nếu phát hiện khi tích hợp).

---

*Cập nhật lần cuối: tạo theo `/plan 956` — đồng bộ với thảo luận issue và `ic-sync-field-mapping.md`.*
