# Issue #831: [FE] Thêm role 事業部事務員 – UI, i18n, routing

## Thông tin Issue

- **Issue Number:** 831
- **Title:** [FE] IC: 新規ロール追加: 事業部事務員・UI・i18n・ルーティング / Thêm role mới: 事業部事務員, UI, i18n, routing
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/831
- **Status:** OPEN
- **Created At:** 2026-03-05T09:52:04Z
- **Updated At:** 2026-03-13T06:04:24Z
- **Labels:** enhancement, frontend, Child issue
- **Assignees:** hathaiviet411 (Hà Thái Việt), tungnt183855
- **Issue cha:** #828

---

## 1. Tổng quan (Overview)

### 日本語 / Japanese

#### 親 Issue
#828 に関連

#### 説明
新規ロール「事業部事務員」の Frontend 対応。一覧・フォーム・フィルタで選択可能にし、ルーティング・権限チェックでは TL と同様に扱う（従業員一覧の「全件 vs 自拠点のみ」は Backend で制御）。

### Tiếng Việt / Vietnamese

#### Issue cha
Liên quan đến #828

#### Mô tả
Triển khai Frontend cho role mới「事業部事務員」: hiển thị trong dropdown/filter, map i18n; tại routing và permission xử lý giống TL (logic full list vs 自拠点 chỉ ở Backend).

---

## 2. Yêu cầu (Requirements)

### 日本語

- const/role.js に DEPARTMENT_OFFICE_STAFF を追加し、全 CONST_ROLE.TL 使用箇所に CONST_ROLE.DEPARTMENT_OFFICE_STAFF を追加する。
- UserManagement Create/Edit、TableUserManagement、UserManagementFilter で role 名の i18n マッピングとオプションを追加する。
- ja.js / en.js に USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF を追加する。
- ルーター・権限・各マスタ画面（VehicleMaster, CourseMaster, RouteMaster, StoreMaster, DriverRecorder, EmployeeMaster, FilterEmployeeMaster, TableCourse, store/modules/filter.js）で TL と同列に事業部事務員を扱う。
- 実装と unit tests を完了する。

### Tiếng Việt

- Thêm DEPARTMENT_OFFICE_STAFF vào const/role.js và thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF vào mọi chỗ đang dùng CONST_ROLE.TL.
- UserManagement Create/Edit, TableUserManagement, UserManagementFilter: thêm map tên role sang i18n và option cho role mới.
- ja.js / en.js: thêm USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF.
- Router, permission, các màn Master (VehicleMaster, CourseMaster, RouteMaster, StoreMaster, DriverRecorder, EmployeeMaster, FilterEmployeeMaster, TableCourse, store/modules/filter.js): thêm 事業部事務員 cùng điều kiện với TL.
- Hoàn thành implementation và unit tests.

---

## 3. Chi tiết kỹ thuật (Technical Details)

- **Files cần sửa:**
  - `resources/js/const/role.js`
  - `resources/js/pages/UserManagement/Create.vue`, `Edit.vue`
  - `resources/js/components/organisms/TableUserManagement.vue`, `UserManagementFilter.vue`
  - `resources/js/lang/subs/ja.js`, `en.js`
  - Mọi file có CONST_ROLE.TL: router, permission, các trang Master (VehicleMaster, CourseMaster, RouteMaster, StoreMaster, DriverRecorder, EmployeeMaster, FilterEmployeeMaster, TableCourse), `store/modules/filter.js`
- **Tham chiếu:** docs/issues/828/plan.md (phần FE)

---

## 4. Tiêu chí chấp nhận (Acceptance Criteria)

- [ ] Hoàn thành triển khai (実装完了)
- [ ] Tạo và vượt qua unit tests (ユニットテスト作成・合格)
- [ ] Tuân thủ quy ước dự án (プロジェクト規約に準拠)
- [ ] Không có thay đổi phá vỡ chức năng hiện có (既存機能への破壊的変更なし)

---

## 5. Checklist triển khai (Implementation Checklist)

### Const & Role

- [ ] Thêm DEPARTMENT_OFFICE_STAFF vào `resources/js/const/role.js`
- [ ] Thêm CONST_ROLE.DEPARTMENT_OFFICE_STAFF vào tất cả chỗ đang dùng CONST_ROLE.TL (router, permission, các Master, filter)

### UserManagement UI

- [ ] Create.vue: i18n mapping + option cho role mới
- [ ] Edit.vue: i18n mapping + option cho role mới
- [ ] TableUserManagement.vue: hiển thị tên role (i18n)
- [ ] UserManagementFilter.vue: option filter theo role

### i18n

- [ ] ja.js: thêm USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF
- [ ] en.js: thêm USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF

### Router & Permission

- [ ] Cập nhật router: 事業部事務員 cùng điều kiện với TL
- [ ] Cập nhật permission check: 事業部事務員 cùng điều kiện với TL

### Các màn Master

- [ ] VehicleMaster
- [ ] CourseMaster
- [ ] RouteMaster
- [ ] StoreMaster
- [ ] DriverRecorder
- [ ] EmployeeMaster
- [ ] FilterEmployeeMaster
- [ ] TableCourse
- [ ] store/modules/filter.js

### Test

- [ ] Unit tests cho các thay đổi liên quan
- [ ] Test thủ công: tạo/sửa user với role 事業部事務員, filter, quyền truy cập các Master

---

## 6. Phụ thuộc (Dependencies)

- Backend issue (cho integration test). Có thể dev FE song song sau khi role đã có trong DB.

---

## 7. Ghi chú / Review (Notes)

- Xử lý 事業部事務員 giống TL ở FE; logic "全件 vs 自拠点のみ" do Backend điều khiển.
- Có thể tham chiếu docs/issues/828/plan.md phần FE để đảm bảo đồng bộ với plan cha.
