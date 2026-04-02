# Issue #969 — [BE] VPL同期: vehicle-monthly-costs・ITP / Chi phí xe tháng & ITP

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/969
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: ./izumi-cloud
migrations_path: ./izumi-cloud/database/migrations
api_docs_path:
tests_path: ./izumi-cloud/tests
workspace_root: .
```

**Note:** Implementation targets **izumi-cloud** (Laravel). The repo also contains `./backend` (Node/Prisma) and a Next.js app at the root; this issue's API and PHPUnit live under `izumi-cloud`. Paths are relative to this workspace.

---

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [BE] VPL同期: vehicle-monthly-costs・ITP / Chi phí xe tháng & ITP |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/969 |
| **Created** | 2026-03-27T03:39:46Z |
| **Updated** | 2026-03-30T03:50:45Z |
| **Assignees** | tungnt183855 |
| **Labels** | backend, enhancement, Child issue |

**Parent:** #956

---

## Description (summary)

Implement **`POST /api/vehicle-monthly-costs/sync`** in izumi-cloud.

- Aggregate **5 cost fields** từ các bảng IC database
- Lấy **`fuelEfficiency`** và **`roadUsageFee`** từ bảng `vehicle_itp_data` (đã có sẵn trong IC DB — **không cần gọi ITP API**)
- Support **`yearMonth`** parameter (format: `YYYY-MM`)

> **Phát hiện quan trọng:** ITP trong izumi-cloud **không phải REST API client**. IC dùng ChromeDriver/Selenium scrape web portal ITP (`https://itpv3.transtron.fujitsu.com`), download CSV/ZIP rồi import vào bảng `vehicle_itp_data`. Khi sync VPL, chỉ cần **query bảng `vehicle_itp_data` đã có sẵn** — hoàn toàn không cần gọi HTTP ra ngoài.

### Field Mapping (ic-sync-field-mapping.md §5 + external-integration-spec.md §5.4)

| VPL Field | Account Code | IC Table thực tế | Column / Logic |
|-----------|-------------|------------------|----------------|
| `vehicleExternalId` | — | `vehicles.id` | `(string)` cast — nhất quán với #968 |
| `departmentId` | — | `vehicles.department_id` | `toDepartmentCode(id)` → `"LOCxxx"` (reuse `CourseSyncService`) |
| `leaseDepreciation` | 6191 | `mahoujins` | `WHERE type='lease' AND vehicle_id=? AND year=? AND month=?` → `SUM(cost)` |
| `vehicleDepreciation` | 6192 | `mahoujins` | `WHERE type='vehicle' AND vehicle_id=? AND year=? AND month=?` → `SUM(cost)` |
| `vehicleLease` | 6193 | `vehicle_costs` | `WHERE vehicle_id=? AND DATE_FORMAT(date,'%Y-%m')=yearMonth` → `maintenance_lease` |
| `insuranceCost` | 6194 | `vehicle_data_orc_ai` | Latest record per vehicle → `insurance_fee` (cast int, strip non-numeric) |
| `taxCost` | 6195 | `vehicle_costs` | Same record as vehicleLease → `car_tax` |
| `fuelEfficiency` | — | `vehicle_itp_data` | `WHERE type='km_l' AND vehicle_id=? AND year=? AND month=?` → `cost` (km/L) |
| `roadUsageFee` | — | `vehicle_itp_data` | `WHERE type='etc' AND vehicle_id=? AND year=? AND month=?` → `cost` (ETC費) |

> **Quan trọng:** `fuelEfficiency` và `roadUsageFee` là **dữ liệu thô** (raw). IC không tính ra số tiền — VPL sẽ tự tính 燃料費(6175) và 道路使用料(6176) bằng cách nhân với `LocationCalculationParameter` (fuel unit price, road usage discount rate) của kỳ trước.

### Vehicle Identifier Strategy

Spec VPL cho phép 3 cách identify xe: `vehicleId` (internal) / `vehicleExternalId` / `(vehicleNo + departmentId)`.

**Sử dụng `vehicleExternalId`** = `(string) vehicles.id` — nhất quán với #968 (VehicleSyncService).

### VPL Response Format

Xác nhận từ code thực tế (`backend/src/routes/vehicle-monthly-costs.ts` line 131):

```json
{
  "synced": 15,
  "yearMonth": "2026-03",
  "results": [
    { "vehicleNo": "001-001", "status": "created" },
    { "vehicleNo": "002-001", "status": "updated" }
  ]
}
```

Service dùng `$response['synced']` để count — **cùng pattern với `VehicleSyncService`** (không phải `success: true` như DriverSyncService).

**Hành vi VPL cần biết:**

| Trường hợp | Hành vi VPL |
|---|---|
| `fuelEfficiency` = `null` hoặc bị bỏ qua | VPL tự convert `null → 0` (`Number(c.fuelEfficiency ?? 0)`) — không cần gửi tường minh |
| `roadUsageFee` = `null` hoặc bị bỏ qua | Tương tự — VPL tự convert → `0` |
| Xe không tìm được theo `vehicleExternalId` | VPL **skip silently** (log warning, không trả lỗi) → IC cần tự log xe bị skip |
| 5 cost fields bị bỏ qua / `null` | VPL lưu `0`, không lỗi |

### Requirements

- Plan Phase 3, **`ic-sync-field-mapping.md` §5**
- **PHPUnit** — thuần DB query, **không cần mock HTTP client**

### Technical

- **izumi-cloud** (Laravel) — toàn bộ data từ DB nội bộ, không gọi external API
- ITP data đã có sẵn trong `vehicle_itp_data` (được scrape định kỳ bởi `VehicleServiceRepository`)


### Dependencies

- VPL foundation (#966)
- **Vehicles** sync (#968) — `vehicleExternalId` alignment
- `vehicle_itp_data` phải có data cho `yearMonth` tương ứng (được scrape bởi job ITP hiện có — ngoài scope issue này)
- **`LocationCalculationParameter`** phải được setup trên VPL trước khi kết quả fuel/road usage được tính đúng (`PUT /api/location-calculation-parameters` — thực hiện thủ công phía VPL)

---

## Full issue body (reference)

<details>
<summary>Japanese / Vietnamese (from GitHub)</summary>

### 日本語

- Parent: #956
- `POST /api/vehicle-monthly-costs/sync`: aggregate from IC (MaintenanceLease, InsuranceRate, etc.) + merge ITP `fuelEfficiency` / `roadUsageFee`; `yearMonth` parameter.
- Requirements: plan Phase 3, `ic-sync-field-mapping.md` §5; PHPUnit (mock ITP).
- Tech: izumi-cloud.

### Tiếng Việt

- Issue cha: #956
- `vehicle-monthly-costs/sync`: tổng hợp từ bảng IC + ITP cho `fuelEfficiency`, `roadUsageFee`; tham số `yearMonth`.
- Yêu cầu: plan Phase 3, `ic-sync-field-mapping.md` §5; PHPUnit (mock ITP).
- Chi tiết: izumi-cloud.

</details>

---

## Implementation checklist

- [ ] **`VehicleMonthlyCostSyncService.php`** — `POST /api/vehicle-monthly-costs/sync`
    - [ ] `yearMonth` parameter (`YYYY-MM` format) — bắt buộc, parse ra `$year` / `$month`
    - [ ] Query xe active: `Vehicle::whereNull('deleted_at')->with(['vehicleCost', 'itpData', 'latestDataOrcAi', 'mahoujins'])->get()`
    - [ ] **Avoid N+1:** eager load tất cả relations cần thiết cho 500+ xe
    - [ ] `vehicleExternalId` = `(string) $vehicle->id`
    - [ ] `departmentId` = `CourseSyncService::toDepartmentCode($vehicle->department_id)`
    - [ ] `leaseDepreciation` (6191) — `Mahoujin` `WHERE type='lease' AND vehicle_id AND year AND month` → `SUM(cost)` — default `0`
    - [ ] `vehicleDepreciation` (6192) — `Mahoujin` `WHERE type='vehicle' AND vehicle_id AND year AND month` → `SUM(cost)` — default `0`
    - [ ] `vehicleLease` (6193) — `VehicleCost` `WHERE vehicle_id AND DATE_FORMAT(date,'%Y-%m')=yearMonth` → `maintenance_lease` — default `0`
    - [ ] `insuranceCost` (6194) — `VehicleDataORCAI` latest record per vehicle → `(int) filter_var(insurance_fee, FILTER_SANITIZE_NUMBER_INT)` — default `0`
    - [ ] `taxCost` (6195) — `VehicleCost` same record → `car_tax` — default `0`
    - [ ] `fuelEfficiency` — `VehicleITPData` `WHERE type='km_l' AND vehicle_id AND year AND month` → `cost` — gửi `null` nếu không có data
    - [ ] `roadUsageFee` — `VehicleITPData` `WHERE type='etc' AND vehicle_id AND month` → `cost` — gửi `null` nếu không có data
    - [ ] Preload `Department::all()->keyBy('id')` cho `toDepartmentCode()` — tránh N+1
    - [ ] Log xe bị VPL skip: detect `$response['synced'] < count($costs)` → log warning
- [ ] **Đăng ký vào `VplSyncCommand`** — thêm `vehicle-monthly-costs` vào `resolveEntities()` và `syncEntity()` switch case; cập nhật `$signature` help text
    - [ ] Lưu ý: `vehicle-monthly-costs` cần `--year-month=` option; thêm option này vào command nếu chưa có
- [ ] **PHPUnit** — test thuần logic, **không cần mock HTTP** (ITP data từ DB)
    - [ ] `VehicleMonthlyCostSyncServiceTest.php`
    - [ ] Test case: xe có đủ `VehicleCost` + `Mahoujin` + `VehicleDataORCAI` + `VehicleITPData` → payload đúng tất cả 7 fields
    - [ ] Test case: xe có ITP data (`km_l` + `etc`) → `fuelEfficiency` và `roadUsageFee` không null
    - [ ] Test case: xe thiếu ITP data → `fuelEfficiency`/`roadUsageFee` = `null` (không skip xe)
    - [ ] Test case: xe thiếu `VehicleCost` → cost fields = `0` (không skip xe)
    - [ ] Test case: `departmentId` format `LOCxxx` đúng
- [ ] Project conventions; no breaking changes to unrelated features
- [ ] Cross-check **`ic-sync-field-mapping.md` §5** và **`external-integration-spec.md` §5.4**

---

## Notes / review

- **ITP KHÔNG phải REST API — data đã có sẵn trong DB:** ITP trong izumi-cloud là web scraping (ChromeDriver login `itpv3.transtron.fujitsu.com`, download CSV). Kết quả được import vào bảng `vehicle_itp_data` qua `VehicleServiceRepository::downloadDataITP()`. `VehicleMonthlyCostSyncService` **chỉ cần query `VehicleITPData` model** — không cần HTTP client, không cần mock.
- **`vehicle_itp_data` type values:** `'km_l'` = km/L (燃費 → `fuelEfficiency`) | `'etc'` = ETC費 (道路使用料 → `roadUsageFee`). Xác nhận từ `VehicleITPImport.php:73` và `VehicleServiceRepository:1134`.
- **IC tables đã xác nhận từ `PLServiceRepository`:** `mahoujins` (lease/vehicle depreciation), `vehicle_costs` (maintenance_lease, car_tax), `vehicle_data_orc_ai` (insurance_fee), `vehicle_itp_data` (km_l, etc).
- **VPL Response:** `{ synced: N, yearMonth, results: [{vehicleNo, status}] }` — dùng `$response['synced']` (giống VehicleSyncService, khác DriverSyncService).
- **`fuelEfficiency` / `roadUsageFee` là raw data:** IC chỉ gửi dữ liệu thô, VPL tự tính 燃料費(6175) và 道路使用料(6176) bằng `LocationCalculationParameter`. Không cần (và không nên) tính ra tiền tại IC.
- **`LocationCalculationParameter` dependency:** Cần setup `PUT /api/location-calculation-parameters` phía VPL trước kỳ báo cáo — việc thủ công của VPL side, cần confirm với team VPL.
- Use **`github_project_v2_id`** above when `/breakdown` adds child issues so they land on **Izumi_Issue**.

---

## Acceptance criteria (from issue)

- [ ] Implementation complete
- [ ] Unit tests created and passing
- [ ] Conforms to project conventions
- [ ] No destructive changes to existing behavior
