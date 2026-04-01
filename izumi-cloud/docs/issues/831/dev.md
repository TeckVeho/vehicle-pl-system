# Issue #831: Development Log – [FE] Thêm role 事業部事務員 (Department Office Staff)

**Parent issue:** #828 Add new role  
**Issue:** #831 [FE] 新規ロール追加: 事業部事務員・UI・i18n・ルーティング  
**Tham chiếu:** docs/issues/828/plan.md (phần FE), docs/issues/831/issue.md

---

## 1. Phương pháp triển khai

- **Direct implementation** (theo checklist issue 831 và plan 828).
- Không TDD: thêm constant, i18n, và bổ sung role vào mọi chỗ đang dùng TL.

---

## 2. Các thay đổi đã thực hiện

### 2.1. Const & role

| File | Thay đổi |
|------|----------|
| `resources/js/const/role.js` | Thêm `DEPARTMENT_OFFICE_STAFF = 'department_office_staff'`; thêm vào mảng `ROLES` (sau TL); thêm vào export. |

### 2.2. i18n

| File | Thay đổi |
|------|----------|
| `resources/js/lang/subs/ja.js` | `USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF: '事業部事務員'` |
| `resources/js/lang/subs/en.js` | `USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF: 'Department Office Staff'` |

### 2.3. UserManagement UI

| File | Thay đổi |
|------|----------|
| `resources/js/pages/UserManagement/Create.vue` | `toI18nKey()`: thêm `case 'department_office_staff': return 'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF'` |
| `resources/js/pages/UserManagement/Edit.vue` | Giống Create.vue. |
| `resources/js/components/organisms/TableUserManagement.vue` | Giống Create.vue (map role name → i18n). |
| `resources/js/components/organisms/UserManagementFilter.vue` | ROLE_LIST: thêm `{ value: 17, text: this.$t('USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF') }` (id 17 theo RoleSeeder). |

### 2.4. Router & permission

| File | Thay đổi |
|------|----------|
| `resources/js/router/modules/masterManager.js` | Thêm `CONST_ROLE.DEPARTMENT_OFFICE_STAFF` vào mọi mảng `roles` có `CONST_ROLE.TL` (toàn bộ master routes). |
| `resources/js/router/modules/driverRecorder.js` | Thêm role vào 2 route (Create, Edit). |
| `resources/js/router/modules/playRecorder.js` | Thêm role vào meta.roles. |
| `resources/js/router/index.js` | Thêm role vào meta.roles (redirect). |
| `resources/js/permission/index.js` | Thêm role vào hasRole([...]) redirect course-master. |
| `resources/js/store/modules/filter.js` | `ROLE_CAN_EDIT`: thêm `CONST_ROLE.DEPARTMENT_OFFICE_STAFF`. |

### 2.5. Các màn Master & component

| File | Thay đổi |
|------|----------|
| `resources/js/pages/VehicleMaster/index.vue` | 3 mảng hasRole/hasAccess: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/VehicleMaster/detail.vue` | 1 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/CourseMaster/index.vue` | 1 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/CourseMaster/detail.vue` | 1 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/RouteMaster/index.vue` | 8 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/StoreMaster/index.vue` | hasRole template + hasAccessEdit + hasAccessDelete: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/DriverRecorder/List/index.vue` | hasAccess, hasAccessRegister, hasAccessDetail, haveAccessRegiserEditPlaylist: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/DriverRecorder/Detail/index.vue` | 2 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/DriverRecorder/Edit/index.vue` | 1 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/DriverRecorder/Create/index.vue` | 1 mảng: thêm DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/EmployeeMaster/detail.vue` | hasRole (2 chỗ) + `handleTransformEmployeeRole`: thêm DEPARTMENT_OFFICE_STAFF và `employee_role === 17` → DEPARTMENT_OFFICE_STAFF. |
| `resources/js/pages/EmployeeMaster/edit.vue` | Giống detail.vue. |
| `resources/js/pages/EmployeeMaster/tabs/DetailEmployee.vue` | Giống detail.vue. |
| `resources/js/components/organisms/FilterEmployeeMaster.vue` | `roleCanNotEdit`: thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF. |
| `resources/js/components/organisms/TableCourse.vue` | 3 mảng: thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF. |

### 2.6. Ghi chú kỹ thuật

- **UserManagementFilter ROLE_LIST:** Giữ hardcode; thêm option với `value: 17` (id role 事業部事務員 theo RoleSeeder – phần tử thứ 17, id = 17). Nếu môi trường khác có id khác, cần chỉnh hoặc chuyển sang load từ API.
- **Employee list (従業員一覧):** Không xử lý riêng ở FE; BE đã trả full list cho 事業部事務員 và filter theo 自拠点 cho TL.
- **EmployeeMaster handleTransformEmployeeRole:** Role id 17 tương ứng 事業部事務員 trong RoleSeeder (index 16 → id 17).

---

## 3. Chưa thực hiện (để phase sau)

- **Unit tests FE:** Issue 831 yêu cầu unit tests; có thể bổ sung trong phase `/test` hoặc task riêng.
- **Test thủ công:** Tạo/sửa user với role 事業部事務員, filter UserManagement, đăng nhập và kiểm tra quyền truy cập các Master, so sánh Employee list (full) với TL (自拠点のみ).

---

## 4. Trạng thái cuối

- Toàn bộ thay đổi **chưa commit** (đúng quy định /dev).
- Checklist issue 831: Const, i18n, UserManagement (Create/Edit/Table/Filter), router, permission, các Master và filter.js đã bổ sung 事業部事務員 cùng điều kiện với TL.
