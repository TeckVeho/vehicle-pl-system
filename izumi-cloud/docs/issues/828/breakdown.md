# Issue #828: Breakdown Summary

## Overview

Task #828 đã được breakdown thành **2 GitHub issues** (1 FE + 1 BE), 1 SP = 1h.

- **Parent issue:** #828 Add new role (事業部事務員 / Department Office Staff)
- **Quy tắc:** Mọi màn = TL; chỉ 1 ngoại lệ: 従業員一覧 — 事業部事務員 xem full list, TL xem theo 自拠点のみ.

---

## Created Issues

### 1. Backend Issue #830

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/830

**Title:** `[BE] 新規ロール追加: 事業部事務員・API・権限・従業員一覧例外 / Thêm role mới: 事業部事務員, API, quyền, ngoại lệ employee list`

**Story Points:** 3 SP (3h)

**Labels:** `backend`, `enhancement`

**Scope:**
- constants.php: ROLE_DEPARTMENT_OFFICE_STAFF
- RoleSeeder: thêm role 事業部事務員
- VehicleRepository, VehicleController: thêm role vào filter department (2 chỗ)
- UserRepository, DepartmentRepository: getInterviewPic — thêm role
- Spatie: gán permission cho role mới dựa trên TL
- **EmployeeRepository:** không thêm 事業部事務員 vào điều kiện filter department (line 71) — ngoại lệ duy nhất
- Backend unit tests

**Dependencies:** Không (có thể triển khai trước)

---

### 2. Frontend Issue #831

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/831

**Title:** `[FE] 新規ロール追加: 事業部事務員・UI・i18n・ルーティング / Thêm role mới: 事業部事務員, UI, i18n, routing`

**Story Points:** 3 SP (3h)

**Labels:** `frontend`, `enhancement`

**Scope:**
- const/role.js: DEPARTMENT_OFFICE_STAFF + export
- Create.vue, Edit.vue, TableUserManagement.vue: toI18nKey / role mapping
- UserManagementFilter.vue: option filter role
- ja.js, en.js: USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF
- Tất cả file có CONST_ROLE.TL: thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF (router, permission, VehicleMaster, CourseMaster, RouteMaster, StoreMaster, DriverRecorder, EmployeeMaster, FilterEmployeeMaster, TableCourse, store/modules/filter.js)
- Frontend unit tests

**Dependencies:** BE issue (cho integration test); có thể dev song song sau khi BE có role trong DB.

---

## Total SP

- **BE:** 3 SP  
- **FE:** 3 SP  
- **合計:** 6 SP (6h)

---

## Implementation Order

1. **BE** — constants → RoleSeeder → Vehicle/User/Department repos & controller → EmployeeRepository (giữ nguyên điều kiện, không thêm 事業部事務員) → Spatie permissions.
2. **FE** — const/role → i18n → form/table/filter → router & hasRole.
3. **Integration test** — tạo user 事業部事務員, kiểm tra dropdown, list vehicle, **employee list (full)** vs TL (自拠点のみ).

---

## Story Points Registration (1 SP = 1h)

Nếu có script setsp (ví dụ `pwsh .cursor/script/setsp.ps1` hoặc `docs/AI_driven_dedelopment/cursor/script/setsp.ps1`), chạy:

```powershell
# Windows
pwsh .cursor/script/setsp.ps1 -IssueUrl "https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/830" -SpValue 3
pwsh .cursor/script/setsp.ps1 -IssueUrl "https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/831" -SpValue 3
```

**Đăng ký thủ công:** Vào GitHub Projects → tìm issue #830 và #831 → set field **Story Points** (hoặc **SP**) = **3** cho mỗi issue.
