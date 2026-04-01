# Issue #477: Change the reference destination of the item 記録年月日

## Metadata

- **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/477
- **State**: OPEN
- **Created**: 2025-12-02T10:15:21Z
- **Updated**: 2025-12-02T10:15:21Z
- **Assignees**: @hathaiviet411 (Hà Thái Việt)
- **Labels**: (none)

## Description

記録年月日 ở page trong ảnh đang lấy giá trị các mục dưới đây trong file JSON được liên kết qua shakensho:
- ElectCertPublishdateE
- ElectCertPublishdateY
- ElectCertPublishdateM
- ElectCertPublishdateD

=> **Sửa lại cho mục này lấy theo các trường này trong file JSON liên kết:**
- GrantdateE
- GrantdateY
- GrantdateM
- GrantdateD

## Screenshots

![Image 1](https://github.com/user-attachments/assets/9063b430-b1fa-49d9-a36e-07e76d43661a)

![Image 2](https://github.com/user-attachments/assets/918591e8-e119-4fa4-b4a2-a39a2035e2ef)

## Implementation Checklist

- [ ] Xác định file/component hiện tại đang sử dụng ElectCertPublishdate*
- [ ] Tìm các vị trí trong code cần thay đổi
- [ ] Thay đổi từ ElectCertPublishdate* sang Grantdate* cho trường 記録年月日
- [ ] Kiểm tra format và hiển thị của trường sau khi thay đổi
- [ ] Test với dữ liệu thực tế từ file JSON shakensho
- [ ] Verify không có regression ở các trường khác

## Analysis Notes

### Current State
- Trường 記録年月日 (Kiroku Nengappi - Ngày tháng ghi nhận) đang tham chiếu đến:
  - ElectCertPublishdateE (Era)
  - ElectCertPublishdateY (Year)
  - ElectCertPublishdateM (Month)
  - ElectCertPublishdateD (Day)

### Target State
- Cần thay đổi sang tham chiếu:
  - GrantdateE (Era)
  - GrantdateY (Year)
  - GrantdateM (Month)
  - GrantdateD (Day)

### Files to Investigate
- Backend: Controllers/Services xử lý shakensho data
- Frontend: Components hiển thị 記録年月日
- Models: Mapping giữa JSON fields và database
- Resources: API resources transform data

## Technical Context

- **Type**: Bug Fix / Data Mapping Change
- **Scope**: Shakensho (車検証) data handling
- **Impact**: Display của trường 記録年月日 trong page

## Review Checklist

- [ ] Code changes reviewed
- [ ] Test cases added/updated
- [ ] UI/Display verified
- [ ] Data mapping validated
- [ ] No breaking changes to other fields
