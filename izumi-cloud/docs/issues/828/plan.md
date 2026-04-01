# Issue #828: Add new role - Implementation Plan

## 概要 (Overview)

- **Requirement:** Thêm vai trò mới **事業部事務員 (nhân viên văn phòng bộ phận / Department Office Staff)**. Quyền hạn đối chiếu từ **TL (チームリーダー)** theo [権限詳細](https://docs.google.com/spreadsheets/d/17Y6WtbU54kAHTddTZt--rN_04-xFereUuup8v1sN0bY/edit?gid=196370208#gid=196370208).
- **Current state:** Hệ thống dùng Spatie Permission, role lưu trong bảng `roles` (name, display_name, position). Danh sách role lấy từ API `GET /api/role` (RoleRepository::getAll()). Frontend load options từ API và map tên role sang i18n. Các chỗ check quyền theo role (Vehicle, User, Department) đang dùng mảng `[ROLE_CREW, ROLE_CLERKS, ROLE_TL]` hoặc `[ROLE_TL]`.
- **Target state:** Role mới xuất hiện trong dropdown User Management, có thể gán cho user; phân quyền đúng theo bảng quyền (đối chiếu từ TL, chỉ một ngoại lệ ở employee list).

### Quy tắc đối chiếu từ bảng quyền (Google Sheets)

| Phạm vi | Cách xử lý |
|--------|-------------|
| **Mọi màn khác** | 事業部事務員 = TL, **không thay đổi gì**. Ở đâu đang dùng TL thì thêm 事業部事務員 vào cùng điều kiện. |
| **Chỉ 1 ngoại lệ: 従業員一覧 (Employee list)** | **TL:** `O` ※自拠点のみ → filter theo department/tự拠点 (chỉ thấy nhân viên thuộc chi nhánh của mình). **事業部事務員:** `O` (full) → **không** filter theo department, xem **toàn bộ** danh sách nhân viên. |

- **Tóm tắt:** Gán quyền cho role mới dựa trên TL, chỉ chỉnh khác biệt duy nhất tại màn Employee list (事業部事務員 xem full list, TL xem theo tự拠点).

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/const/role.js`

##### 1.1.1. Thêm constant và export cho role 事業部事務員

Thêm constant `DEPARTMENT_OFFICE_STAFF = 'department_office_staff'` (name trùng backend) và đưa vào object export và mảng `ROLES` để các nơi dùng `CONST_ROLE` có thể so sánh role (router, permission, filter, v.v.).

**既存コード** (line 1-55):

- Hiện tại: CREW, CLERKS, TL, ... DX_MANAGER; export object và ROLES array.

**変更内容:**

- Thêm: `const DEPARTMENT_OFFICE_STAFF = 'department_office_staff';`
- Thêm `DEPARTMENT_OFFICE_STAFF` vào mảng `ROLES` (thứ tự có thể đặt sau TL).
- Thêm `DEPARTMENT_OFFICE_STAFF` vào object export.

##### 1.1.2. Thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF vào tất cả chỗ có CONST_ROLE.TL (mọi màn = TL)

Theo bảng quyền, **mọi màn khác không có thay đổi** — 事業部事務員 đối chiếu từ TL. Thêm `CONST_ROLE.DEPARTMENT_OFFICE_STAFF` vào **cùng mảng** với `CONST_ROLE.TL` tại mọi file: router (masterManager, driverRecorder, playRecorder, index), permission/index.js, VehicleMaster, CourseMaster, RouteMaster, StoreMaster, DriverRecorder, EmployeeMaster, FilterEmployeeMaster, TableCourse, store/modules/filter.js. **Lưu ý màn 従業員一覧 (Employee list):** logic “chỉ TL bị giới hạn theo tự拠点” xử lý ở **Backend** (BE 1.8); FE chỉ cần quyền vào màn và gọi API, BE trả full list hay filter theo department tùy role.

#### 1.2. File: `resources/js/pages/UserManagement/Create.vue`

##### 1.2.1. Map tên role mới sang i18n trong toI18nKey()

Danh sách role lấy từ API (getListRole), option hiển thị dùng `this.$t(this.toI18nKey(response.data[i].name))`. Cần map `'department_office_staff'` sang key i18n.

**既存コード** (line 245-274):

- switch (string) với các case 'crew', 'clerks', 'tl', ... 'dx_manager'; default trả về `[${string}]`.

**変更内容:**

- Thêm case: `case 'department_office_staff': return 'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF';`

#### 1.3. File: `resources/js/pages/UserManagement/Edit.vue`

##### 1.3.1. Map tên role mới sang i18n trong toI18nKey()

Giống Create.vue: Edit cũng dùng getListRole và toI18nKey cho dropdown. Thêm case `'department_office_staff'` → `'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF'`.

**変更内容:**

- Trong method toI18nKey (khu vực switch): thêm `case 'department_office_staff': return 'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF';`

#### 1.4. File: `resources/js/components/organisms/TableUserManagement.vue`

##### 1.4.1. Hiển thị tên role trong bảng (toI18nKey / role name mapping)

Bảng user cần hiển thị tên role; nếu dùng cùng cơ chế map name → i18n thì cần thêm case cho `department_office_staff`.

**変更内容:**

- Trong method map role name sang i18n (tương tự toI18nKey): thêm case cho `'department_office_staff'` → `'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF'`.

#### 1.5. File: `resources/js/components/organisms/UserManagementFilter.vue`

##### 1.5.1. Thêm option filter theo role 事業部事務員

ROLE_LIST hiện hardcode theo value (id) và text i18n. Role mới sẽ có id do DB assign (ví dụ 17 nếu thêm sau các role hiện có). Có 2 hướng: (A) Load danh sách role từ API (giống Create/Edit) để luôn đúng id và thứ tự; (B) Thêm 1 option mới với value = id của role 事業部事務員 (sau khi chạy seeder) và text = `this.$t('USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF')`. Nên ưu tiên (A) để tránh lệch id giữa môi trường.

**変更内容:**

- Nếu giữ hardcode: thêm 1 item vào ROLE_LIST với value = id role 事業部事務員 (xác định sau khi seeder chạy), text: `this.$t('USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF')`.
- Hoặc refactor ROLE_LIST lấy từ API getRoleList (tái sử dụng logic Create/Edit) và dùng chung toI18nKey.

#### 1.6. File: `resources/js/lang/subs/ja.js`

##### 1.6.1. Thêm key i18n cho tên role 事業部事務員 (JA)

Trong `USER_MANAGEMENT.ROLE` thêm key mới.

**既存コード** (line 134-150):

- ROLE: { CREW, CLERKS, TL, ... DX_MANAGER }

**変更内容:**

- Thêm: `DEPARTMENT_OFFICE_STAFF: '事業部事務員',`

#### 1.7. File: `resources/js/lang/subs/en.js`

##### 1.7.1. Thêm key i18n cho tên role (EN)

Trong `USER_MANAGEMENT.ROLE` thêm key tương ứng.

**変更内容:**

- Thêm: `DEPARTMENT_OFFICE_STAFF: 'Department Office Staff',` (hoặc bản dịch thống nhất với BA).

#### 1.8. Các file có hasRole([..., CONST_ROLE.TL]) hoặc array [CONST_ROLE.CLERKS, CONST_ROLE.TL]

##### 1.8.1. Bổ sung CONST_ROLE.DEPARTMENT_OFFICE_STAFF vào tất cả chỗ có TL (mọi màn = TL)

Theo bảng quyền, **các màn khác không có thay đổi** — đối chiếu từ TL. Thêm `CONST_ROLE.DEPARTMENT_OFFICE_STAFF` vào **cùng mảng** với `CONST_ROLE.TL` tại tất cả các file có so sánh role (router, permission, VehicleMaster, CourseMaster, RouteMaster, StoreMaster, DriverRecorder, EmployeeMaster, FilterEmployeeMaster, TableCourse, store/modules/filter.js). Không cần xử lý riêng cho 事業部事務員 ở FE về “full list vs 自拠点のみ” — Backend trả dữ liệu đã filter hay full tùy role (xem BE 1.8).

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `app/constants.php`

##### 1.1.1. Định nghĩa constant role mới

Thêm constant PHP để dùng thống nhất trong backend (role name trùng Spatie/DB).

**現在の実装** (line 180-196):

- define("ROLE_CREW", "crew"); ... define("ROLE_EXECUTIVE_OFFICER", "executive_officer");

**変更内容:**

- Thêm: `define("ROLE_DEPARTMENT_OFFICE_STAFF", "department_office_staff"); // 事業部事務員`
- Đặt gần nhóm role hiện có (sau ROLE_EXECUTIVE_OFFICER hoặc sau ROLE_TL tùy convention).

#### 1.2. File: `database/seeders/RoleSeeder.php`

##### 1.2.1. Thêm role 事業部事務員 vào danh sách seed

Role được tạo/cập nhật theo name; display_name dùng cho API/FE. Position quyết định thứ tự (TL = 3). Có thể đặt position = 16 (sau DX_MANAGER 15) để không đụng thứ tự hiện tại, hoặc đặt giữa TL và các role khác nếu product yêu cầu.

**現在の実装** (line 19-35):

- $lisRoles = [ ['name' => ROLE_CREW, ...], ..., ['name' => ROLE_EXECUTIVE_OFFICER, ...] ];
- Loop: nếu tồn tại role theo name thì update position/display_name; không thì create với id = $key + 1.

**変更内容:**

- Thêm 1 phần tử vào $lisRoles: `['name' => ROLE_DEPARTMENT_OFFICE_STAFF, 'position' => 16, 'display_name' => '事業部事務員']` (hoặc position khác theo yêu cầu).
- Không set 'id' khi create cho role mới (để Laravel auto-increment), hoặc set id cố định (ví dụ 17) nếu muốn đồng bộ với FE hardcode; tốt hơn là không set id và dùng API cho FE filter.

#### 1.3. File: `app/Repositories/VehicleRepository.php`

##### 1.3.1. Phân quyền filter theo department (mọi màn = TL)

User có role CREW, CLERKS, TL hoặc 事業部事務員 thì $department = [$user->department->id]. Thêm ROLE_DEPARTMENT_OFFICE_STAFF vào mảng.

**現在の実装** (line 512-516):

- if (in_array($role->name, [ROLE_CREW, ROLE_CLERKS, ROLE_TL])) { $department = [$user->department->id]; }

**変更内容:**

- Thêm ROLE_DEPARTMENT_OFFICE_STAFF: `[ROLE_CREW, ROLE_CLERKS, ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF]`.

##### 1.3.2. Phân quyền filter vehicle theo department (mọi màn = TL)

Chỉ role TL và 事業部事務員 thì filter `vehicles.department_id = $user->department->id`. Thêm ROLE_DEPARTMENT_OFFICE_STAFF.

**現在の実装** (line 688-691):

- if (in_array($role->name, [ROLE_TL])) { ... }

**変更内容:**

- Đổi thành: `in_array($role->name, [ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF])`.

#### 1.4. File: `app/Http/Controllers/Api/VehicleController.php`

##### 1.4.1. Gán department theo role khi list vehicle (mọi màn = TL)

Nếu role là CREW, CLERKS, TL hoặc 事業部事務員 thì $department = $user->department->id. Thêm ROLE_DEPARTMENT_OFFICE_STAFF.

**変更内容:**

- Thêm ROLE_DEPARTMENT_OFFICE_STAFF vào mảng: `[ROLE_CREW, ROLE_CLERKS, ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF]`.

#### 1.5. File: `app/Repositories/UserRepository.php`

##### 1.5.1. getInterviewPic – danh sách user theo role (mọi màn = TL)

Query user có role ROLE_AM_SM, ROLE_QUALITY_CONTROL, ROLE_SITE_MANAGER, ROLE_HQ_MANAGER, ROLE_TL. Thêm ROLE_DEPARTMENT_OFFICE_STAFF.

**変更内容:**

- Thêm ROLE_DEPARTMENT_OFFICE_STAFF vào array trong ->role([...]).

#### 1.6. File: `app/Repositories/DepartmentRepository.php`

##### 1.6.1. getInterviewPic – user theo role (mọi màn = TL)

Giống UserRepository, ->role([..., ROLE_TL]). Thêm ROLE_DEPARTMENT_OFFICE_STAFF.

**変更内容:**

- Thêm ROLE_DEPARTMENT_OFFICE_STAFF vào array trong ->role([...]).

#### 1.7. Permissions (Spatie) – role_has_permissions

##### 1.7.1. Gán permission cho role 事業部事務員 theo bảng quyền

Hệ thống dùng Spatie (roles + permissions). Copy permission từ role TL sang role 事業部事務員 (mọi màn = TL). Nếu có middleware `role:tl` trên route: thêm alias cho `department_office_staff` ở những route 事業部事務員 được truy cập giống TL.

**変更内容:**

- Gán permission cho ROLE_DEPARTMENT_OFFICE_STAFF dựa trên TL (seeder/command tùy cấu trúc hiện tại).

#### 1.8. 従業員一覧 (Employee list) – **ngoại lệ duy nhất**

Theo bảng quyền: **TL** = `O` ※自拠点のみ (chỉ xem nhân viên thuộc chi nhánh của mình); **事業部事務員** = `O` (full, xem toàn bộ). Cần xử lý khác với các màn khác: **không** áp dụng filter theo department cho 事業部事務員.

**File:** `app/Repositories/EmployeeRepository.php`

**現在の実装** (line 70-76):

- `$f_department_base_id = $request->get('department_base_id');`
- `if (in_array('tl', $listRoleName) || in_array('clerks', $listRoleName)) { $user = auth()->user()->load(['department']); if ($user->department) { $f_department_base_id = $user->department->id; } }`
- → TL và Clerk bị ép filter theo department (自拠点のみ).

**変更内容:**

- **Không thêm** `'department_office_staff'` (hoặc ROLE_DEPARTMENT_OFFICE_STAFF) vào điều kiện trên. Chỉ giữ `'tl'` và `'clerks'`.
- Kết quả: user 事業部事務員 **không** bị gán `$f_department_base_id` từ `$user->department->id` → API trả **toàn bộ** danh sách nhân viên (có thể filter theo param từ request nếu FE gửi). TL và Clerk vẫn chỉ thấy nhân viên thuộc tự拠点.

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (FE có thể làm song song sau khi BE đã có role trong DB)
   - constants.php → RoleSeeder (chạy seeder để có role trong DB).
   - VehicleRepository, VehicleController, UserRepository, DepartmentRepository: thêm ROLE_DEPARTMENT_OFFICE_STAFF vào tất cả chỗ đang dùng TL (mọi màn = TL).
   - **EmployeeRepository:** không thêm 事業部事務員 vào điều kiện filter theo department (line 71) — đây là ngoại lệ duy nhất: 事業部事務員 xem full employee list.
   - Cấu hình permission (Spatie) cho role mới dựa trên TL.
2. **Frontend 実装**
   - const/role.js → lang ja.js, en.js → Create.vue, Edit.vue, TableUserManagement.vue, UserManagementFilter.vue (option + i18n).
   - Các file router/permission/hasRole: thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF vào tất cả chỗ có CONST_ROLE.TL (mọi màn = TL).
3. **統合テスト**
   - Tạo user với role 事業部事務員, kiểm tra dropdown và lưu.
   - Đăng nhập user 事業部事務員: kiểm tra list vehicle (theo department), interview PIC, menu/router giống TL.
   - **Kiểm tra ngoại lệ:** 従業員一覧 (Employee list) — 事業部事務員 thấy **toàn bộ** danh sách nhân viên; TL chỉ thấy nhân viên thuộc tự拠点.

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 2–3 時間
  - constants + RoleSeeder: 0.5h
  - VehicleRepository, VehicleController, UserRepository, DepartmentRepository: 0.5–1h
  - Permissions (Spatie) + đối chiếu sheet: 1–1.5h
- **Frontend**: 2–3 時間
  - const/role, i18n, Create/Edit/TableUserManagement/UserManagementFilter: 1h
  - Các file router/permission/hasRole (nhiều file): 1–2h
- **Testing & 仕様確認**: 1–2 時間

**合計**: 5–8 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:** Không ảnh hưởng; chỉ thêm role và điều kiện in_array.
2. **UX 考慮:** Dropdown role và filter user phải hiển thị đúng tên 事業部事務員 (i18n). Nếu UserManagementFilter tiếp tục hardcode id, cần đồng bộ id giữa môi trường hoặc chuyển sang load từ API.
3. **データ整合性:** Role mới chỉ thêm bản ghi; không đổi cấu trúc bảng. User gán role qua model_has_roles (Spatie) như hiện tại.
4. **既存機能との互換性:** Các role hiện tại (CREW, CLERKS, TL, ...) không đổi. 事業部事務員 = TL ở mọi màn, **trừ 従業員一覧:** TL giữ filter 自拠点のみ, 事業部事務員 xem full list (không thêm 事業部事務員 vào điều kiện filter trong EmployeeRepository).
