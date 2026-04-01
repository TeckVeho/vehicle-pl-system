# Issue #828: Add new role (Thêm vai trò mới)

## Thông tin Issue

- **Issue Number:** 828
- **Title:** Add new role
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/828
- **Status:** OPEN
- **Created At:** 2026-03-05T08:23:35Z
- **Updated At:** 2026-03-05T08:36:01Z
- **Labels:** (không có)
- **Assignees:** phuongcodeunited
- **Người khởi tạo:** Đào Thị Thư

---

## 1. Tổng quan (Overview)

### Bối cảnh (Background)

- **Vấn đề hiện tại:** Không có vai trò nhân viên văn phòng bộ phận (事業部事務員).
- **Yêu cầu kinh doanh:** Thêm vai trò mới **nhân viên văn phòng bộ phận** (事業部事務員), với quyền hạn cơ bản tương tự trưởng nhóm (チームリーダー), nhưng có một số điểm khác biệt.
- **Câu chuyện người dùng:** Là người dùng, tôi muốn có vai trò nhân viên văn phòng bộ phận.

### Mục tiêu (Goal)

- **Hình thức mong muốn:** Thêm vai trò mới "事業部事務員 (nhân viên văn phòng bộ phận)". Quyền hạn cơ bản tương tự trưởng nhóm, một số điểm khác biệt — chi tiết xem tại:
  - [権限詳細 (Chi tiết quyền hạn)](https://docs.google.com/spreadsheets/d/17Y6WtbU54kAHTddTZt--rN_04-xFereUuup8v1sN0bY/edit?gid=196370208#gid=196370208)

---

## 2. Yêu cầu chức năng (Functional Requirements)

- Vai trò **nhân viên văn phòng bộ phận** được thêm vào hệ thống.
- Quyền hạn của vai trò này tương ứng với trưởng nhóm, với một số điểm khác biệt (theo tài liệu quyền hạn).
- Người dùng có thể được gán / chọn vai trò nhân viên văn phòng bộ phận.
- Trong hệ thống có thể hiển thị / tham chiếu liên kết đến tài liệu mô tả chi tiết quyền hạn (nếu cần).

### Loại tác vụ

Yêu cầu (Requirement)

---

## 3. Điều kiện hoàn thành (Definition of Done)

- [ ] Vai trò nhân viên văn phòng bộ phận đã được thêm vào hệ thống.
- [ ] Quyền hạn đã được thiết lập đúng theo tài liệu.
- [ ] Tài liệu liên quan đã được cập nhật.
- [ ] Kiểm tra đã hoàn tất và không có vấn đề.

---

## 4. Checklist triển khai (Implementation Checklist)

### Backend

- [ ] Định nghĩa role mới (enum/constant/DB) cho 事業部事務員.
- [ ] Cấu hình quyền (permissions) theo bảng quyền chi tiết (Google Sheets), dựa trên quyền trưởng nhóm + điều chỉnh khác biệt.
- [ ] Migration/Seeder (nếu dùng bảng roles/permissions).
- [ ] Cập nhật policy/gate nếu có phân quyền theo role.

### Frontend

- [ ] Hiển thị vai trò mới tại màn hình quản lý user/role (dropdown, danh sách, v.v.).
- [ ] Cho phép gán vai trò nhân viên văn phòng bộ phận cho user.
- [ ] (Tùy chọn) Hiển thị link hoặc hint đến tài liệu quyền chi tiết.

### Tài liệu & QA

- [ ] Cập nhật tài liệu nội bộ (role list, quyền hạn).
- [ ] Test phân quyền: so sánh với trưởng nhóm và các điểm khác biệt theo spec.
- [ ] Test gán role và luồng sử dụng cơ bản.

---

## 5. Ghi chú / Review (Notes)

- So sánh trực tiếp với quyền **trưởng nhóm** để tái sử dụng và chỉ chỉnh những chỗ khác biệt.
- Cần đối chiếu chi tiết với Google Sheets quyền hạn trước khi implement để không thiếu/bớt quyền.
- Nếu hệ thống đang dùng enum role: thêm giá trị mới; nếu dùng bảng `roles`: thêm record và gán permissions tương ứng.

---

## 6. Tham chiếu (References)

- Issue: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/828
- [権限詳細 (Chi tiết quyền hạn)](https://docs.google.com/spreadsheets/d/17Y6WtbU54kAHTddTZt--rN_04-xFereUuup8v1sN0bY/edit?gid=196370208#gid=196370208)
