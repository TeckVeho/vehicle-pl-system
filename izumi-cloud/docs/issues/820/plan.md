# Issue #820: Send password initial setting email - Implementation Plan

## 概要 (Overview)

Hiện tại màn hình User Master đã có hành động gửi mail tại mục **「パスワード初期設定メールを送信」**, nhưng chưa lưu và chưa hiển thị lịch sử gửi.
Mục tiêu của issue là bổ sung luồng lưu lịch sử (ngày gửi, người gửi) ở BE và hiển thị rõ ràng ở FE để tránh gửi trùng hoặc hiểu nhầm chưa gửi.

Trạng thái hiện tại:
- FE chỉ gọi API gửi mail, không có khu vực hiển thị lịch sử.
- BE chỉ gửi mail và trả message thành công/thất bại, không persist lịch sử.

Trạng thái sau cải tiến:
- Mỗi lần gửi thành công sẽ có log lịch sử gửi.
- API user detail trả thêm danh sách lịch sử gửi.
- User Master (Edit) hiển thị bảng lịch sử: Ngày gửi / Người gửi.

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/UserManagement/Edit.vue`

##### 1.1.1. Add UI block for password setup email history

Hiện file đang có link gửi mail nhưng chưa có lịch sử hiển thị.

**既存コード** (line 53-57):
- Có clickable text `パスワード初期設定メールを送信`.

**変更内容:**
- Thêm section ngay dưới link gửi mail để hiển thị bảng lịch sử.
- Dùng `b-table` (hoặc block table HTML) với 2 cột:
  - `DATE_TIME` (hoặc key i18n mới cho "送信日時")
  - `REGISTERED_BY` (hoặc key i18n mới cho "送信者")
- Có empty state khi chưa có dữ liệu (`TABLE_EMPTY`).

##### 1.1.2. Bind history data from user detail response

Hiện `getOneUserData()` chỉ map role/name/id/email và payload gửi mail.

**既存コード** (line 212-224):
- `getOneUser()` response đang được set vào `user_role`, `employee_name`, `user_id`, `email`, `dataSendMail`.

**変更内容:**
- Thêm state mới, ví dụ: `passwordSetupMailHistories: []`.
- Map dữ liệu từ API:
  - `response.data.password_setup_mail_histories` (đề xuất field).
- Chuẩn hóa format hiển thị ngày giờ tại FE nếu cần (hoặc dùng format từ BE).

##### 1.1.3. Refresh history immediately after send success

Hiện `handleSendMailSetUpPassword()` chỉ toast success/error.

**既存コード** (line 394-410):
- Sau khi gửi thành công chỉ đóng overlay.

**変更内容:**
- Sau success:
  - gọi lại `getOneUserData()` để reload lịch sử mới nhất, hoặc
  - append item mới từ response nếu BE trả ngay record vừa tạo.
- Giữ UX không bị đơ (overlay đúng vòng đời, đảm bảo tắt trong cả nhánh lỗi).

##### 1.1.4. Update unit tests for new UI rows/behavior

Test hiện tại đang assert số lượng `.input-row` cố định.

**既存コード** (line 21-29: `resources/js/tests/UserManagement/Edit.spec.js`):
- `expect(ListInputRow.length).toEqual(6);`
- `expect(ListInputLabel.length).toEqual(5);`

**変更内容:**
- Điều chỉnh assertion để không brittle theo số lượng row cứng.
- Bổ sung test:
  - render history table khi có dữ liệu.
  - hiển thị empty state khi rỗng.
  - sau send mail success thì trigger reload data.

#### 1.2. File: `resources/js/lang/subs/ja.js` (và `resources/js/lang/subs/en.js` nếu cần)

##### 1.2.1. Add translation keys for history table labels

**既存コード** (line 430-432):
- Đã có key dùng chung `DATE_TIME`, `REGISTERED_BY`.

**変更内容:**
- Nếu cần phân biệt ngữ nghĩa gửi mail, thêm key riêng trong `USER_MANAGEMENT`, ví dụ:
  - `PASSWORD_SETUP_EMAIL_HISTORY`
  - `PASSWORD_SETUP_EMAIL_SENT_AT`
  - `PASSWORD_SETUP_EMAIL_SENT_BY`
- Nếu dùng key chung hiện có thì không bắt buộc thêm key mới.

#### 1.3. File: `resources/js/api/modules/user.js` (optional)

##### 1.3.1. Add dedicated API for history (only if BE exposes separate endpoint)

**既存コード** (line 27-29):
- Đã có `sendMailSetUpPassword(url, data)`.

**変更内容:**
- Chỉ thêm hàm API mới nếu chọn phương án endpoint riêng (vd: `/user/{id}/password-setup-mail-history`).
- Nếu dùng `GET /user/{id}` mở rộng payload thì không cần đổi file này.

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `app/Http/Controllers/Api/UserController.php`

##### 1.1.1. Persist history when sending initial password setup email

**現在の実装** (line 490-503):
- `sendMailSetUpPassword()` chỉ:
  - validate `email !== null`,
  - tạo payload + gửi `RegisterMail`,
  - trả message thành công.
- Chưa ghi lịch sử.

**変更内容:**
- Sau khi gửi mail thành công, tạo record lịch sử gồm tối thiểu:
  - target user code/id,
  - recipient email,
  - sender user id (người thao tác),
  - sent_at (hoặc dùng created_at).
- Bọc trong try/catch để handle lỗi gửi mail và lỗi ghi DB rõ ràng.
- Đảm bảo chỉ ghi log khi `Mail::send` thành công.

##### 1.1.2. Return history in user detail API

**現在の実装** (line 160-165):
- `show($id)` trả `new BaseResource($user)` từ `getUserByEmpCode()`.

**変更内容:**
- Nạp thêm quan hệ lịch sử gửi mail cho user detail:
  - `passwordSetupMailHistories` (đề xuất relation).
- Sắp xếp DESC theo thời gian gửi.
- Chuẩn hóa response field cho FE dễ dùng:
  - `sent_at`, `sender_name`.

#### 1.2. File: `app/Repositories/UserRepository.php`

##### 1.2.1. Load relation for user detail and optimize query

**現在の実装** (line 121-126):
- `getUserByEmpCode($id)` chỉ query user và role.

**変更内容:**
- Eager load relation lịch sử gửi mail + người gửi để tránh N+1.
- Ví dụ:
  - `User::with(['passwordSetupMailHistories.sender'])->where('id', $id)->first();`
- Đảm bảo thứ tự newest-first ngay tại query relation hoặc scope.

#### 1.3. File: `app/Models/User.php`

##### 1.3.1. Define relation to password setup mail history

**現在の実装** (line 121-130):
- Có một số relation (`employee`, `user_contacts`) nhưng chưa có relation cho mail setup history.

**変更内容:**
- Thêm relation:
  - `passwordSetupMailHistories()`.
- Nếu cần relation người gửi theo `sender_user_id` thì map đúng key (lưu ý hệ `users.id` trong project là employee code).

#### 1.4. File: `database/migrations/{timestamp}_create_password_setup_mail_histories_table.php` (new)

##### 1.4.1. Create dedicated history table

**変更内容:**
- Tạo bảng mới để lưu lịch sử gửi mail setup password, đề xuất:
  - `id`
  - `target_user_id` (index)
  - `recipient_email`
  - `sender_user_id` (nullable/index)
  - `created_at`, `updated_at`
- Thêm index phục vụ query theo user và sort theo thời gian.
- Ưu tiên kiểu dữ liệu tương thích với `users.id` hiện tại.

#### 1.5. File: `app/Models/PasswordSetupMailHistory.php` (new)

##### 1.5.1. Add Eloquent model for history records

**変更内容:**
- Khai báo `$table`, `$fillable`, `$casts`.
- Thêm relation:
  - `targetUser()`
  - `sender()`

#### 1.6. File: `tests/Unit/UserManagementTest.php` (or feature test file)

##### 1.6.1. Add test coverage for send-mail history flow

**現在の実装**:
- Có test CRUD user cơ bản, chưa cover `sendMailSetUpPassword()` + history.

**変更内容:**
- Bổ sung test:
  - gửi mail thành công -> có record lịch sử.
  - `show(user_id)` trả về danh sách lịch sử đúng thứ tự.
  - email null -> 422 và không tạo lịch sử.

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (must be completed first)
   - [BE-1.4] Tạo migration + [BE-1.5] model history.
   - [BE-1.3] Thêm relation trong `User`.
   - [BE-1.1] Ghi lịch sử trong `sendMailSetUpPassword`.
   - [BE-1.2], [BE-1.2] + [BE-1.2] cập nhật `show/getUserByEmpCode` trả lịch sử.
   - [BE-1.6] Viết test BE.

2. **Frontend 実装** (depends on BE response shape)
   - [FE-1.1] Thêm bảng lịch sử tại `Edit.vue`.
   - [FE-1.1.2] map dữ liệu history từ API.
   - [FE-1.1.3] refresh sau gửi mail.
   - [FE-1.2], [FE-1.4] update i18n + unit test.

3. **統合テスト**
   - Verify luồng end-to-end từ click gửi mail -> record DB -> hiển thị FE.
   - Verify order mới nhất trước.
   - Verify hiển thị đúng người gửi theo account login.

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 3.5 - 5.0 時間
  - Migration + model + relation: 1.0h
  - Controller/repository cập nhật logic lưu + trả history: 1.5 - 2.0h
  - Unit/feature test + fix edge cases: 1.0 - 2.0h

- **Frontend**: 2.5 - 4.0 時間
  - UI table + bind data + refresh flow: 1.5 - 2.0h
  - i18n + formatting datetime + empty state: 0.5 - 1.0h
  - Unit test cập nhật: 0.5 - 1.0h

**合計**: 6.0 - 9.0 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**
   - Eager load relation history + sender để tránh N+1 ở `GET /user/{id}`.
   - Giới hạn số bản ghi trả về (vd latest 20) nếu history dài.

2. **UX 考慮:**
   - Sau khi gửi mail thành công phải thấy lịch sử cập nhật ngay.
   - Empty state rõ ràng để user biết chưa từng gửi.

3. **データ整合性:**
   - Chỉ ghi history sau khi gửi mail thành công.
   - Lưu `sender_user_id` từ user đăng nhập hiện tại để audit chính xác.
   - Chuẩn hóa timezone hiển thị FE/BE.

4. **既存機能との互換性:**
   - Giữ nguyên API gửi mail hiện có (`/send-mail-set-up-password`), chỉ bổ sung side-effect ghi history.
   - Mở rộng `GET /user/{id}` theo hướng backward-compatible (thêm field mới, không đổi field cũ).
