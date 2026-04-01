# Issue #820: Breakdown Summary

## 概要 (Overview)

Issue #820 "Send password initial setting email" đã được breakdown theo đúng chiến lược mặc định:

- 1 Backend issue
- 1 Frontend issue

Mục tiêu là tách ownership theo layer để BE/FE có thể triển khai song song, đồng thời giữ dependency rõ ràng cho giai đoạn integration.

---

## Created Issues

### 1) Backend Issue

**Issue:** [#821](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/821)  
**Title:** `[BE] パスワード初期設定メール送信履歴機能 / BE: Chuc nang luu lich su gui email thiet lap mat khau`  
**Labels:** `backend`, `enhancement`  
**Story Points:** 5 SP  
**Status:** Backlog  
**Dependencies:** None

**Scope chính:**
- Lưu lịch sử gửi mail thiết lập mật khẩu khi gửi thành công
- Bổ sung model + migration cho lịch sử gửi mail
- Mở rộng API user detail để trả lịch sử (newest-first)
- Cập nhật unit/feature tests backend

**Files dự kiến:**
- `app/Http/Controllers/Api/UserController.php`
- `app/Repositories/UserRepository.php`
- `app/Models/User.php`
- `database/migrations/*_create_password_setup_mail_histories_table.php` (new)
- `app/Models/PasswordSetupMailHistory.php` (new)
- `tests/Unit/UserManagementTest.php` (hoặc feature test tương ứng)

---

### 2) Frontend Issue

**Issue:** [#822](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/822)  
**Title:** `[FE] パスワード初期設定メール送信履歴表示対応 / FE: Hien thi lich su gui email thiet lap mat khau`  
**Labels:** `frontend`, `enhancement`  
**Story Points:** 4 SP  
**Status:** Backlog  
**Dependencies:** #821 (BE hoàn thành API/history payload để integration test)

**Scope chính:**
- Thêm khu vực hiển thị lịch sử gửi mail trên màn edit user
- Hiển thị 2 thông tin: ngày gửi, người gửi
- Reload lịch sử ngay sau khi gửi mail thành công
- Cập nhật unit tests frontend

**Files dự kiến:**
- `resources/js/pages/UserManagement/Edit.vue`
- `resources/js/lang/subs/ja.js`
- `resources/js/lang/subs/en.js` (nếu cần)
- `resources/js/tests/UserManagement/Edit.spec.js`

---

## Story Points Summary

| Layer | Issue | SP |
|---|---|---|
| Backend | #821 | 5 |
| Frontend | #822 | 4 |
| **Total** | **2 issues** | **9 SP** |

**SP rationale (1 SP = 1 giờ):**
- BE có migration + model + API + test cập nhật => complexity trung bình-khá -> 5 SP
- FE có UI + mapping + refresh flow + test cập nhật -> complexity trung bình -> 4 SP

---

## Dependency Graph

```text
Parent Issue #820
  ├─ Issue #821 [BE] (5 SP)  -- independent
  └─ Issue #822 [FE] (4 SP)  -- depends on #821 for integration payload
```

---

## GitHub Projects SP Registration

**Current status:** ⚠️ Pending

Lý do: token `gh` hiện tại thiếu scope `read:project/project`, nên script set SP chưa thể ghi trực tiếp vào GitHub Projects.

**Tình trạng thực tế:**
- Issues đã tạo thành công: #821, #822
- SP đã chốt trong breakdown: BE=5, FE=4
- Cần hoàn tất authorize scope project để tự động set SP

---

## Next Steps

1. Hoàn tất cấp quyền project scope cho `gh` account hiện tại.
2. Chạy script set SP cho:
   - `#821` -> `5`
   - `#822` -> `4`
3. Verify trên GitHub Projects rằng field Story Points đã được cập nhật đúng.

---

**Created:** 2026-03-04  
**Parent Issue:** #820  
**Breakdown Strategy:** 1 FE issue + 1 BE issue  
**Total Issues:** 2  
**Total SP:** 9
