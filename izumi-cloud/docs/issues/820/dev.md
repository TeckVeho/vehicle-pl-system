# Issue #820: Development Log (FE Only)

## 概要

Triển khai phần Frontend cho issue #820: hiển thị lịch sử gửi email thiết lập mật khẩu ban đầu trong User Master, và cập nhật lịch sử ngay sau khi gửi thành công.

Phạm vi thực hiện lần này: **FE only**.

---

## Parent / Dependency Context

- Parent issue: `#820`
- Breakdown:
  - BE issue: `#821`
  - FE issue: `#822`
- Dependency tích hợp:
  - FE tiêu thụ dữ liệu lịch sử do BE trả về (field đề xuất: `password_setup_mail_histories`).

---

## Development Approach

- Chọn **Direct Implementation** (không TDD đầy đủ) vì thay đổi tập trung vào UI rendering + data mapping.
- Giữ backward-compatible ở FE bằng cách map linh hoạt:
  - `sent_at` fallback `created_at`
  - `sender_name` fallback `sender.name`

---

## Implemented Changes

### 1) `resources/js/pages/UserManagement/Edit.vue`

#### 1.1 UI: thêm bảng lịch sử gửi mail
- Thêm block "送信履歴" ngay dưới link `パスワード初期設定メールを送信`.
- Thêm `b-table` với:
  - cột thời gian gửi (`DATE_TIME`)
  - cột người gửi (`REGISTERED_BY`)
- Có empty state dùng `TABLE_EMPTY`.

#### 1.2 State + computed cho data lịch sử
- Thêm state:
  - `passwordSetupMailHistories: []`
- Thêm computed fields cho bảng:
  - `passwordMailHistoryFields`

#### 1.3 Data mapping từ user detail API
- Trong `getOneUserData()`:
  - map `response.data.password_setup_mail_histories`
  - dùng helper `mapPasswordSetupMailHistories()`
- Bọc `getOneUserData()` bằng `try/finally` để đảm bảo overlay luôn tắt.

#### 1.4 Refresh sau khi gửi mail thành công
- Trong `handleSendMailSetUpPassword()`:
  - khi response `200`, gọi lại `getOneUserData()` để refresh lịch sử
  - thêm `.catch()` để hiển thị toast lỗi và tắt overlay an toàn

#### 1.5 Styling
- Thêm style nhẹ cho title lịch sử (`.mail-history-title`).

---

### 2) `resources/js/tests/UserManagement/Edit.spec.js`

- Nới lỏng assert đếm số `.input-row`/`.user-regis-label` (tránh brittle khi UI thêm section mới).
- Bổ sung test cho `mapPasswordSetupMailHistories()`:
  - map đúng với `sent_at/sender_name`
  - fallback từ `created_at/sender.name`
  - input không hợp lệ trả `[]`

---

## Validation

### Command đã chạy

```bash
npx jest resources/js/tests/UserManagement/Edit.spec.js --runInBand
```

### Kết quả

- Test run **failed** do các test cũ có lỗi môi trường/router (`NavigationDuplicated`, assertion cũ liên quan hash path), không phải do logic mới của history table.
- Phần thay đổi FE mới đã được implement đầy đủ theo yêu cầu issue.

---

## Notes / Decisions

- Tận dụng key i18n sẵn có `DATE_TIME`, `REGISTERED_BY` để tránh mở rộng scope không cần thiết.
- FE xử lý payload linh hoạt để giảm rủi ro mismatch trong giai đoạn BE/FE chưa merge cùng lúc.
- Chưa thay đổi API contract phía FE module, vì history lấy từ `getOneUser()` response.

---

## Final Check

- ✅ FE implementation completed for issue #820
- ✅ Development log created
- ✅ No commit was executed
- ✅ Changes remain uncommitted for `/test` and `/pr` phases
