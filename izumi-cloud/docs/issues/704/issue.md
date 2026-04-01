# Issue #704: AQ address/calculation bug

## Metadata
- **Title:** AQ address/calculation bug
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/704
- **State:** OPEN
- **Created:** 2026-01-29T04:44:48Z
- **Updated:** 2026-01-29T06:05:14Z
- **Assignees:** @DongVietLong
- **Labels:** None
- **Branch:** `704-fix-aq-address-calculation-bug`

---

## Description

Môi trường PROD - Các lỗi liên quan đến địa chỉ và tính toán trong module AQ:

### 1. Lỗi mất địa chỉ đã nhập
- **Hiện tượng:** Nhập địa chỉ xong lưu => địa chỉ đã nhập bị mất
- **Môi trường:** Production

### 2. Lỗi tính toán 高速料金 (Phí cao tốc)
- **Hiện tượng:** Hiện tại chỉ tính 1 chiều
- **Yêu cầu:** 
  - Thêm checkbox cho trường hợp 2 chiều (往復)
  - Khi chọn 往復 thì tính phí 2 chiều
  - Ở AI route cho hiển thị điểm đầu vào - đầu ra cao tốc

### 3. Lỗi tính toán 車両償却費 (Phí khấu hao xe) - 車両購入価格 (Giá mua xe)
- **Hiện tượng:** Lỗi trong tính toán các khoản phí liên quan đến xe

---

## Scope

**Backend Only (BE)** - Chỉ xử lý phần backend

---

## Implementation Checklist

### Bug #1: Địa chỉ bị mất sau khi lưu
- [ ] Xác định API endpoint xử lý lưu địa chỉ
- [ ] Kiểm tra logic lưu địa chỉ trong controller/service
- [ ] Kiểm tra validation và xử lý dữ liệu
- [ ] Kiểm tra database schema và migration
- [ ] Xác định nguyên nhân địa chỉ bị mất
- [ ] Fix logic lưu địa chỉ
- [ ] Test API lưu và lấy địa chỉ

### Bug #2: Tính toán 高速料金 (Phí cao tốc)
- [ ] Xác định API endpoint tính toán phí cao tốc
- [ ] Thêm field checkbox 往復 (2 chiều) vào database
- [ ] Cập nhật migration cho field mới
- [ ] Cập nhật logic tính toán phí cao tốc:
  - [ ] Logic tính 1 chiều (mặc định)
  - [ ] Logic tính 2 chiều khi checkbox được chọn
- [ ] Thêm logic lưu/trả về thông tin điểm đầu vào - đầu ra cao tốc
- [ ] Cập nhật API response để trả về thông tin cao tốc
- [ ] Validate input cho checkbox 往復
- [ ] Test API với các trường hợp:
  - [ ] Tính 1 chiều
  - [ ] Tính 2 chiều (往復)
  - [ ] Hiển thị điểm vào/ra cao tốc

### Bug #3: Lỗi tính toán 車両償却費 và 車両購入価格
- [ ] Xác định API endpoint tính toán phí khấu hao xe
- [ ] Kiểm tra công thức tính toán hiện tại
- [ ] Xác định nguyên nhân lỗi tính toán
- [ ] Fix logic tính toán 車両償却費
- [ ] Fix logic tính toán 車両購入価格
- [ ] Validate input cho các giá trị liên quan
- [ ] Test API với các trường hợp edge case

### Testing & Documentation
- [ ] Viết/cập nhật unit tests
- [ ] Test integration với các module liên quan
- [ ] Test trên môi trường staging
- [ ] Cập nhật API documentation nếu có thay đổi
- [ ] Chuẩn bị test cases cho QA

---

## Technical Notes

### Files to Check
- Controllers xử lý AQ address
- Services/Repositories liên quan đến địa chỉ
- Models và migrations cho AQ module
- Logic tính toán phí (高速料金, 車両償却費, 車両購入価格)
- AI route integration

### Database Changes
- Thêm field cho checkbox 往復 (2 chiều)
- Thêm fields cho điểm đầu vào/đầu ra cao tốc (nếu chưa có)
- Migration cho các thay đổi schema

### API Changes
- Cập nhật request/response format cho checkbox 往復
- Thêm thông tin điểm vào/ra cao tốc trong response
- Đảm bảo backward compatibility nếu cần

---

## Review & Testing

### Test Scenarios
1. **Địa chỉ:**
   - Nhập địa chỉ mới và lưu
   - Kiểm tra địa chỉ được lưu đúng
   - Load lại trang và verify địa chỉ vẫn còn

2. **Phí cao tốc:**
   - Tính phí 1 chiều (mặc định)
   - Tính phí 2 chiều với checkbox 往復
   - Verify điểm vào/ra cao tốc hiển thị đúng

3. **Phí khấu hao xe:**
   - Test với các giá trị khác nhau
   - Verify công thức tính toán
   - Test edge cases

### Acceptance Criteria
- [ ] Địa chỉ không bị mất sau khi lưu
- [ ] Có thể chọn tính phí cao tốc 1 chiều hoặc 2 chiều
- [ ] Hiển thị đúng điểm vào/ra cao tốc
- [ ] Tính toán đúng 車両償却費 và 車両購入価格
- [ ] Tất cả tests pass
- [ ] Code review approved

---

## Notes
- Môi trường: **PRODUCTION**
- Priority: **HIGH** (đã có bug trên production)
- Scope: **Backend Only**
- Cần test kỹ trước khi deploy lên production

