# Issue #459

## Thông tin Issue

**Tiêu đề:** Transportation list không hiển thị đầy đủ trên màn hình nhỏ (MacBook)

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/459

**Trạng thái:** Open

**Branch:** issue_441_459

---

## Mô tả vấn đề

Kích thước của mỗi item trong danh sách Transportation quá lớn, khiến không thể xem đầy đủ danh sách trên màn hình nhỏ như MacBook.

---

## Files cần xử lý

- `/Users/thaiviet/Downloads/Veho/izumi-cloud/resources/js/pages/Transportation/index.vue`

---

## Kết quả mong đợi

- Xem được đầy đủ danh sách transportation trên màn hình nhỏ
- Các item hiển thị gọn gàng và phù hợp với kích thước màn hình

---

## Giải pháp đề xuất

Chỉnh sửa style của item và icon bên trong để:
- Giảm kích thước các item
- Giảm kích thước icon
- Đảm bảo hiển thị đầy đủ các hàng (rows) của items

---

## Checklist thực hiện

- [x] Đọc và phân tích file `Transportation/index.vue`
- [x] Xác định các element cần điều chỉnh kích thước
- [x] Tạo plan với chi tiết thay đổi cần thực hiện
- [x] Tạo breakdown và GitHub issue #460
- [ ] Chạy `/dev` để bắt đầu implementation
- [ ] Giảm kích thước của item container (200px → 150px)
- [ ] Giảm kích thước icon (120px → 80px)
- [ ] Điều chỉnh padding/margin để tối ưu không gian (margin: 80px → 40px, padding: 50px → 30-40px)
- [ ] Test trên màn hình MacBook để đảm bảo hiển thị đầy đủ
- [ ] Test responsive trên các kích thước màn hình khác

---

## Ghi chú

- Cần chú ý giữ cân đối giữa kích thước nhỏ gọn và khả năng đọc/tương tác
- Đảm bảo không làm ảnh hưởng đến UX trên màn hình lớn hơn

---

**Ngày tạo:** 2025-11-21

---

## Breakdown Status

✅ **Breakdown hoàn thành**

**Child Issue:**
- [#460 [FE] Transportation: Tối ưu layout cho màn hình MacBook nhỏ](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/460) - **2 SP**

**Tổng Story Points:** 2 SP (~2 hours)

