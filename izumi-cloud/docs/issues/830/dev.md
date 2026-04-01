# Issue #830: Backend – 事業部事務員 role – Development Log

**Parent issue:** [#828](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/828) Add new role  
**This issue:** [#830](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/830) [BE] 新規ロール追加: 事業部事務員・API・権限・従業員一覧例外

---

## 1. Requirements (from plan.md / breakdown)

- Thêm constant `ROLE_DEPARTMENT_OFFICE_STAFF` và role 事業部事務員 vào hệ thống.
- Mọi màn = TL: thêm role mới vào tất cả chỗ đang check TL (Vehicle, User, Department).
- **Ngoại lệ duy nhất:** 従業員一覧 (Employee list) — **không** thêm 事業部事務員 vào điều kiện filter theo department (EmployeeRepository line 71 chỉ giữ `tl` và `clerks`).

---

## 2. Implementation Summary

### 2.1. app/constants.php

- Thêm: `define('ROLE_DEPARTMENT_OFFICE_STAFF', 'department_office_staff'); // 事業部事務員`

### 2.2. database/seeders/RoleSeeder.php

- Thêm vào `$lisRoles`:  
  `['name' => ROLE_DEPARTMENT_OFFICE_STAFF, 'position' => 16, 'display_name' => '事業部事務員']`

### 2.3. app/Repositories/VehicleRepository.php

- **Line ~515 (filter department):** Mảng role thêm `ROLE_DEPARTMENT_OFFICE_STAFF` (cùng CREW, CLERKS, TL).
- **Line ~692 (getDashboardVehicle):** Mảng role thêm `ROLE_DEPARTMENT_OFFICE_STAFF` (cùng TL).

### 2.4. app/Http/Controllers/Api/VehicleController.php

- **Line ~121 (index):** Mảng role thêm `ROLE_DEPARTMENT_OFFICE_STAFF` (cùng CREW, CLERKS, TL).

### 2.5. app/Repositories/UserRepository.php

- **getInterviewPic:** Trong `->role([...])` thêm `ROLE_DEPARTMENT_OFFICE_STAFF`.

### 2.6. app/Repositories/DepartmentRepository.php

- **getInterviewPic:** Trong `->role([...])` thêm `ROLE_DEPARTMENT_OFFICE_STAFF`.

### 2.7. app/Repositories/EmployeeRepository.php

- **Không thay đổi.** Điều kiện line 71 vẫn chỉ `'tl'` và `'clerks'` → 事業部事務員 không bị ép filter theo department (xem full employee list).

### 2.8. Spatie permissions

- Phân quyền theo role name trong code (in_array / ->role([...])), không dùng bảng permissions trong luồng hiện tại. Không thêm seeder/command permission; role mới đủ để áp dụng cùng logic TL.

---

## 3. Tests

- **tests/Unit/RoleDepartmentOfficeStaffTest.php**
  - `test_role_department_office_staff_constant_is_defined()`: Kiểm tra constant `ROLE_DEPARTMENT_OFFICE_STAFF` tồn tại và bằng `'department_office_staff'`.
- Chạy test: `php artisan test --compact --filter=RoleDepartmentOfficeStaffTest tests/Unit/RoleDepartmentOfficeStaffTest.php`  
  (Cần kết nối DB vì Laravel TestCase khởi tạo application.)

---

## 4. How to verify

1. Chạy seeder: `php artisan db:seed --class=RoleSeeder` (hoặc seed full). Kiểm tra bảng `roles` có bản ghi `name = 'department_office_staff'`, `display_name = '事業部事務員'`, `position = 16`.
2. Gán role 事業部事務員 cho user, đăng nhập:
   - Vehicle list: filter theo department giống TL.
   - Employee list (従業員一覧): thấy **toàn bộ** nhân viên (không bị ép department).
3. getInterviewPic: User có role 事業部事務員 xuất hiện trong danh sách chọn (cùng với TL, AM_SM, ...).

---

## 5. Files changed

| File | Change |
|------|--------|
| app/constants.php | +1 constant |
| database/seeders/RoleSeeder.php | +1 role trong $lisRoles |
| app/Repositories/VehicleRepository.php | +ROLE_DEPARTMENT_OFFICE_STAFF (2 chỗ) |
| app/Http/Controllers/Api/VehicleController.php | +ROLE_DEPARTMENT_OFFICE_STAFF (1 chỗ) |
| app/Repositories/UserRepository.php | +ROLE_DEPARTMENT_OFFICE_STAFF trong getInterviewPic |
| app/Repositories/DepartmentRepository.php | +ROLE_DEPARTMENT_OFFICE_STAFF trong getInterviewPic |
| app/Repositories/EmployeeRepository.php | Không đổi (giữ chỉ tl, clerks) |
| tests/Unit/RoleDepartmentOfficeStaffTest.php | Mới – test constant |

---

## 6. Notes

- Mọi thay đổi **chưa commit** (theo quy trình /dev).
- Pint đã chạy trên các file sửa.
