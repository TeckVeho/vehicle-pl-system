# Issue #820: Send password initial setting email

## Metadata
- **Title:** Send password initial setting email
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/820
- **State:** OPEN
- **Created:** 2026-03-04T04:45:57Z
- **Updated:** 2026-03-04T04:50:38Z
- **Assignees:** @hathaiviet411 (Hà Thái Việt)
- **Labels:** backend, documentation, enhancement, frontend, feature
- **Branch:** `820-feat-send-password-initial-setting-email`

---

## Description

Thêm chức năng hiển thị **lịch sử gửi email thiết lập mật khẩu ban đầu** tại mục
**「パスワード初期設定メールを送信」** trong User Master.

### Thông tin cần hiển thị
- Ngày gửi
- Người gửi

### Ví dụ hiển thị

| Ngày gửi | Người gửi |
| --- | --- |
| 2026/03/04 12:51:20 | 加納 |
| 2026/03/03 12:30:42 | 鈴木 |

### Mục đích
- Tránh trường hợp tưởng đã gửi email thiết lập mật khẩu nhưng thực tế chưa gửi.
- Tránh gửi email thiết lập mật khẩu trùng lặp nhiều lần cho cùng một user.

---

## Scope

- **Backend (BE):** Ghi nhận và cung cấp lịch sử gửi email thiết lập mật khẩu.
- **Frontend (FE):** Hiển thị lịch sử gửi (ngày gửi, người gửi) tại User Master.
- **Documentation:** Cập nhật mô tả API/flow nếu thay đổi response.

---

## Implementation Checklist

### Backend
- [ ] Xác định API/service đang xử lý hành động 「パスワード初期設定メールを送信」.
- [ ] Bổ sung lưu lịch sử gửi email (thời điểm gửi, người gửi, user nhận).
- [ ] Nếu cần, thêm/cập nhật bảng hoặc cột DB + migration.
- [ ] Cập nhật API trả về danh sách lịch sử gửi theo user.
- [ ] Đảm bảo dữ liệu lịch sử được sắp xếp theo thời gian mới nhất trước.
- [ ] Thêm validation/tránh tạo bản ghi trùng không hợp lệ.

### Frontend
- [ ] Cập nhật màn hình User Master để hiển thị lịch sử gửi email.
- [ ] Hiển thị tối thiểu 2 cột: ngày gửi, người gửi.
- [ ] Format ngày giờ theo chuẩn đang dùng trong hệ thống.
- [ ] Xử lý trạng thái không có dữ liệu lịch sử.

### Testing
- [ ] Test gửi email lần đầu và verify có bản ghi lịch sử.
- [ ] Test gửi nhiều lần và verify hiển thị đúng thứ tự.
- [ ] Test phân quyền người thao tác gửi email (người gửi hiển thị chính xác).
- [ ] Cập nhật/viết unit test, integration test liên quan.

---

## Technical Notes

- Nên tách rõ:
  - **Event gửi email thành công** (nguồn để ghi log lịch sử)
  - **Dữ liệu hiển thị UI** (query theo user, có phân trang nếu số lượng lớn)
- Cân nhắc timezone nhất quán giữa DB, API và UI.
- Nếu đã có bảng audit/log chung, ưu tiên tái sử dụng thay vì tạo bảng mới.

---

## Review & Testing

### Acceptance Criteria
- [ ] Sau mỗi lần gửi email thiết lập mật khẩu, hệ thống ghi nhận được lịch sử.
- [ ] User Master hiển thị đúng ngày gửi và người gửi.
- [ ] Không còn tình trạng khó xác định đã gửi email hay chưa.
- [ ] Hạn chế việc gửi lặp không cần thiết nhờ có lịch sử trực quan.

### Notes
- Issue có phạm vi liên quan cả BE và FE.
- Cần đồng bộ naming field giữa API và UI trước khi triển khai.
