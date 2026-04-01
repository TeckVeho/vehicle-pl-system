# Issue #489: 車検証連携時の表示について

## Metadata

- **Title:** 車検証連携時の表示について
- **Issue URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/489
- **Status:** OPEN
- **Created At:** 2025-12-08T04:37:07Z
- **Updated At:** 2025-12-09T02:13:15Z
- **Assignees:** @phuongcodeunited
- **Labels:** None
- **Branch:** 489-fix-vehicle-inspection-date-display

---

## 1. Tổng quan (Overview)

### Bối cảnh (Background)

**Vấn đề hiện tại:**
- **Hệ thống:** イズミクラウド (Izumi Cloud)
- **Màn hình:** 車両マスタ (Master Vehicle - Quản lý xe)
- **Sự kiện:** Hiển thị tháng của 車検満了日 (Ngày hết hạn kiểm định xe) không đồng nhất giữa các mục:
  - Một số mục hiển thị: "08" (2 chữ số)
  - Một số mục khác hiển thị: "8" (1 chữ số)

**Yêu cầu kinh doanh:**
- Tất cả các mục ngày tháng cần hiển thị tháng dưới dạng 2 chữ số
- Đảm bảo tính nhất quán của dữ liệu
- Ngăn ngừa sự nhầm lẫn cho người dùng

**User Story:**
> Là một người dùng, tôi muốn tháng của 車検満了日 (Ngày hết hạn kiểm định xe) luôn được hiển thị dưới dạng 2 chữ số.

### Mục tiêu đạt được (Goal)

**Hình thức mong muốn:**
- Tất cả các mục ngày tháng cần hiển thị tháng dưới dạng 2 chữ số (ví dụ: 08)
- Điều tra và sửa chữa lý do gây ra sự không đồng nhất giữa các mục
- Sửa đổi dữ liệu hiện đang hiển thị 1 chữ số thành 2 chữ số

**Điều kiện hoàn thành (Definition of Done):**
- [ ] Xác nhận rằng tất cả các hiển thị tháng đều được thống nhất dưới dạng 2 chữ số
- [ ] Xác định nguyên nhân gây ra sự không đồng nhất và thực hiện sửa chữa
- [ ] Xác nhận rằng tất cả dữ liệu hiển thị 1 chữ số đã được sửa thành 2 chữ số
- [ ] Thực hiện kiểm tra và xác nhận rằng hiển thị đúng như mong đợi

---

## 2. Thông số kỹ thuật (Specification)

### Yêu cầu chức năng (Functional Requirements)

1. **Sửa đổi hiển thị:**
   - Hệ thống cần được sửa đổi để hiển thị tháng dưới dạng 2 chữ số cho tất cả các mục ngày tháng
   - Đảm bảo khi người dùng mở màn hình 車両マスタ (Master Vehicle), tất cả các 車検満了日 đều hiển thị 2 chữ số

2. **Điều tra nguyên nhân:**
   - Xác định nguyên nhân gây ra sự không đồng nhất
   - Kiểm tra logic hiển thị trong code
   - Kiểm tra format dữ liệu trong database

3. **Triển khai sửa chữa:**
   - Sửa đổi cơ sở dữ liệu hoặc logic hiển thị nếu cần thiết
   - Triển khai chức năng tự động format tháng thành 2 chữ số
   - Đảm bảo tính nhất quán trong toàn bộ hệ thống

### Loại nhiệm vụ
課題 (Vấn đề/Issue)

### Người khởi tạo
Đào Thị Thư

---

## 3. Implementation Checklist

### Phase 1: Điều tra (Investigation)
- [ ] Xác định các màn hình/component liên quan đến hiển thị 車検満了日
- [ ] Kiểm tra code backend xử lý dữ liệu ngày tháng
- [ ] Kiểm tra code frontend render ngày tháng
- [ ] Xác định nguồn gốc dữ liệu (database, API, file import)
- [ ] Tìm tất cả các nơi format ngày tháng trong hệ thống

### Phase 2: Phân tích (Analysis)
- [ ] Xác định nguyên nhân gây ra sự không đồng nhất
- [ ] Liệt kê tất cả các trường hợp hiển thị không đúng
- [ ] Đánh giá impact của việc thay đổi
- [ ] Xác định phương án sửa chữa tối ưu

### Phase 3: Triển khai (Implementation)
- [ ] Tạo/cập nhật helper function để format tháng thành 2 chữ số
- [ ] Sửa đổi logic hiển thị trong backend (nếu cần)
- [ ] Sửa đổi logic hiển thị trong frontend (nếu cần)
- [ ] Cập nhật tất cả các nơi sử dụng 車検満了日
- [ ] Đảm bảo tính backward compatibility

### Phase 4: Testing
- [ ] Test hiển thị với các trường hợp khác nhau (tháng 1-12)
- [ ] Test với dữ liệu cũ (1 chữ số)
- [ ] Test với dữ liệu mới (2 chữ số)
- [ ] Test trên các màn hình liên quan
- [ ] Test export/import dữ liệu
- [ ] Regression test các chức năng liên quan

### Phase 5: Documentation & Review
- [ ] Cập nhật documentation
- [ ] Code review
- [ ] QA review
- [ ] User acceptance testing

---

## 4. Technical Notes

### Các file có thể liên quan:
- Backend:
  - `app/Models/Vehicle.php` - Model xe
  - `app/Repositories/VehicleRepository.php` - Repository xử lý dữ liệu xe
  - `app/Http/Controllers/*` - Controllers liên quan đến xe
  - `app/Helpers/Common.php` - Helper functions
  
- Frontend:
  - `resources/js/components/*` - Components hiển thị thông tin xe
  - `resources/js/pages/*` - Pages liên quan đến quản lý xe

- Database:
  - Migration files liên quan đến bảng vehicles
  - Seeder files

### Phương án kỹ thuật đề xuất:
1. Tạo helper function để format ngày tháng nhất quán
2. Áp dụng format này ở cả backend và frontend
3. Sử dụng Carbon hoặc date format chuẩn của PHP/JavaScript

### Lưu ý:
- Cần kiểm tra xem dữ liệu trong DB đã đúng format chưa
- Nếu DB lưu đúng nhưng hiển thị sai → sửa ở layer hiển thị
- Nếu DB lưu sai → cần migration để chuẩn hóa dữ liệu
- Đảm bảo không ảnh hưởng đến các chức năng khác

---

## 5. Review & Notes

### Questions:
- Dữ liệu trong database được lưu dưới dạng gì? (DATE, VARCHAR, etc.)
- Có bao nhiêu màn hình hiển thị 車検満了日?
- Có chức năng export/import dữ liệu xe không?
- Format hiển thị có khác nhau giữa các màn hình không?

### Risks:
- Thay đổi format có thể ảnh hưởng đến logic so sánh ngày tháng
- Cần test kỹ các trường hợp edge case
- Cần đảm bảo tính nhất quán trong toàn bộ hệ thống

### Dependencies:
- None identified yet

---

## 6. Progress Log

### 2025-12-09
- Issue được tạo và phân tích
- Branch `489-fix-vehicle-inspection-date-display` được tạo
- File issue.md được tạo

