# Issue #968 — [BE] VPL同期: vehicles・drivers / Đồng bộ vehicles & drivers

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/968
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: ./izumi-cloud
migrations_path: ./izumi-cloud/database/migrations
api_docs_path:
tests_path: ./izumi-cloud/tests
workspace_root: .
```

**Note:** Implementation targets **izumi-cloud** (Laravel). The repo also contains `./backend` (Node/Prisma) and a Next.js app at the root; this issue's APIs and PHPUnit live under `izumi-cloud`. Paths are relative to this workspace.

---

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [BE] VPL同期: vehicles・drivers / Đồng bộ vehicles & drivers |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/968 |
| **Created** | 2026-03-27T03:39:44Z |
| **Updated** | 2026-03-30T03:50:43Z |
| **Assignees** | tungnt183855 |
| **Labels** | backend, enhancement, Child issue |

**Parent:** #956

---

## Description (summary)

Implement **`POST /api/vehicles/sync`** and **`POST /api/drivers/sync`** in **izumi-cloud**, aligned with `ic-sync-field-mapping.md` §3–4 and `external-integration-spec.md` §5.3, §5.6.

### Vehicles

| VPL Field | IC Source | Transform |
|-----------|----------|-----------|
| `departmentId` | `vehicles.department_id` | `toDepartmentCode(id)` → `"LOCxxx"` |
| `vehicleNo` | `latestNumberPlateHistory()` → `PlateHistory.no_number_plate` | Eager load `latestNumberPlateHistory` để tránh N+1 (500+ xe) |
| `serviceType` | `vehicles.truck_classification` | Map trực tiếp |
| `tonnage` | `vehicles.tonnage` | Map trực tiếp (dùng tính bảo hiểm tải trọng) |
| `externalId` | `vehicles.id` | Cast string |
| `courseExternalId` | Không có trực tiếp | Nullable — IC thiếu `course_id` trên bảng `vehicles` |

### Drivers

| VPL Field | IC Source | Transform |
|-----------|----------|-----------|
| `departmentId` | `employees.final_department_id` hoặc `departments()` M-N relation | `toDepartmentCode(id)` → `"LOCxxx"`. **Ưu tiên `final_department_id` nếu có**, fallback query pivot `employee_department` |
| `code` | `employees.employee_code` | Cast `(string)` (DB type = integer) |
| `name` | `employees.name` | Map trực tiếp |
| `externalId` | `employees.id` | Cast string |

### Filtering

- **Vehicles:** Filter `whereNull('deleted_at')` (SoftDeletes)
- **Drivers:** Filter `retirement_date IS NULL OR retirement_date > today` → bỏ qua nhân viên đã nghỉ việc

### Non-functional

- Avoid N+1: Eager load `Vehicle::with('latestNumberPlateHistory')` (500+ vehicles expected)
- Eager load `Employee::with('departments')` nếu dùng M-N relation cho primary department
- PHPUnit coverage
- Recommended order: after **courses** sync, run **vehicles** sync for data consistency

### VPL Response format khác nhau

- `POST /api/vehicles/sync` → `{ synced: N, results: [...] }`
- `POST /api/drivers/sync` → `{ success: true, results: [...] }`
- Service cần handle cả hai format khi kiểm tra thành công

---

## Implementation checklist

- [ ] `VehicleSyncService.php` — `POST /api/vehicles/sync`
    - [ ] `vehicleNo` từ `PlateHistory.no_number_plate` (eager load `latestNumberPlateHistory`)
    - [ ] `departmentId` = `toDepartmentCode(department_id)` (tái sử dụng từ `CourseSyncService::toDepartmentCode()`)
    - [ ] `serviceType` từ `truck_classification`
    - [ ] `tonnage` map trực tiếp
    - [ ] `externalId` = `(string) vehicles.id`
    - [ ] `courseExternalId` nullable (IC thiếu `course_id` column)
    - [ ] Filter SoftDeletes (`whereNull('deleted_at')`)
    - [ ] Skip vehicles không có biển số (no `latestNumberPlateHistory`)
- [ ] `DriverSyncService.php` — `POST /api/drivers/sync`
    - [ ] `departmentId`: ưu tiên `final_department_id`, fallback `departments()` relation → `toDepartmentCode()`
    - [ ] `code` = `(string) employee_code`
    - [ ] `name` = `employees.name`
    - [ ] `externalId` = `(string) employees.id`
    - [ ] Filter nhân viên đã nghỉ (`retirement_date`)
    - [ ] Handle response format `{ success: true }` (khác với vehicles `{ synced: N }`)
- [ ] Đăng ký `vehicles`, `drivers` vào `VplSyncCommand.php`
    - [ ] Thêm vào `resolveEntities()` → mảng `$all`
    - [ ] Thêm vào `syncEntity()` → switch case
    - [ ] Cập nhật `$signature` help text
- [ ] PHPUnit unit tests
    - [ ] `VehicleSyncServiceTest.php`
    - [ ] `DriverSyncServiceTest.php`
    - [ ] Existing suite still passes
- [ ] Project conventions; no breaking changes to unrelated features
- [ ] Cross-check `ic-sync-field-mapping.md` §3–4

---

## Notes / review

- Dependency: VPL foundation; **courses** sync should precede **vehicles** where data alignment matters.
- Use `/breakdown` with `github_project_v2_id` above when adding child issues to **Izumi_Issue**.
- `toDepartmentCode()` đã implement ở `CourseSyncService` (static method) — tái sử dụng trực tiếp.
- **`final_department_id` trên Employee:** Cần xác nhận với team IC đây có phải department chính không. Nếu có → dùng trực tiếp, tránh phức tạp hóa.
- **Employee có 3 relation departments:** `departments()` (employee_department), `departmentWorkings()` (employee_working_department), và cột `final_department_id`. Cần chốt dùng cái nào.
- **`employee_course` pivot table tồn tại** — có thể dùng cho driver-course mapping trong tương lai (ngoài scope issue này vì VPL drivers/sync không nhận `courseExternalId`).

---

## Acceptance criteria (from issue)

- [ ] Implementation complete
- [ ] Unit tests created and passing
- [ ] Conforms to project conventions
- [ ] No destructive changes to existing behavior
