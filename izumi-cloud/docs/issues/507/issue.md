# Issue #507: [FE] 所定労働時間の設定: UI表示・入力・統合 / Thiết lập thời gian làm việc quy định: Hiển thị UI, Nhập liệu, Tích hợp

## 📋 Metadata

- **Issue Number**: 507
- **Title**: [FE] 所定労働時間の設定: UI表示・入力・統合 / Thiết lập thời gian làm việc quy định: Hiển thị UI, Nhập liệu, Tích hợp
- **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/507
- **Status**: OPEN
- **Labels**: `enhancement`, `frontend`
- **Assignees**: @hathaiviet411 (Hà Thái Việt)
- **Created**: 2025-12-15T08:29:33Z
- **Updated**: 2025-12-18T04:00:09Z
- **Branch**: `507-feat-scheduled-work-time-ui`

---

## 📝 Mô tả / Description

### 日本語 / Japanese

#### 親Issue
#504 に関連

#### 説明
Crew（従業員）の所定労働時間の開始時刻と終了時刻を表示・編集するためのフロントエンド機能を実装します。勤務情報モーダルに新しいフィールドを追加し、分単位（1分単位）で時刻を選択できるtime pickerを実装します。

### Tiếng Việt / Vietnamese

#### Issue cha
Liên quan đến #504

#### Mô tả
Triển khai chức năng frontend để hiển thị và chỉnh sửa thời gian bắt đầu và kết thúc làm việc quy định của Crew (nhân viên). Thêm các trường mới vào modal thông tin làm việc và triển khai time picker cho phép chọn thời gian theo từng phút (1分単位).

---

## 🎯 Yêu cầu / Requirements

### 1. Detail View (`detail.vue`)
- Thêm trường hiển thị thời gian làm việc quy định (bắt đầu) và (kết thúc) vào Detail Modal
- Cập nhật method `handleGetDepartmentWorkingDetail()` để lấy dữ liệu mới từ API
- Khởi tạo các trường mới trong `detailModalData`

### 2. Edit View (`edit.vue`) - Detail Modal
- Thêm trường hiển thị thời gian làm việc quy định (bắt đầu) và (kết thúc) vào Detail Modal (chỉ đọc)
- Cập nhật method `handleGetDepartmentWorkingDetail()`

### 3. Edit View (`edit.vue`) - Edit Modal
- Thêm trường nhập liệu sử dụng `b-form-timepicker` vào Edit Modal
- Cho phép chọn thời gian theo từng phút (1分単位)
- Định dạng 24 giờ (`:hour12="false"`)
- Hỗ trợ locale tiếng Nhật
- Cập nhật method `handleGetDepartmentWorkingEdit()` để load dữ liệu
- Cập nhật method `handleSubmitEditModal()` để gửi dữ liệu mới lên API
- Khởi tạo các trường mới trong `editModalData`

### 4. Data Mapping
- Lấy `scheduled_work_start_time` và `scheduled_work_end_time` từ API response
- Bao gồm các trường mới trong API request
- Xử lý giá trị rỗng (NULL) một cách thích hợp (placeholder: "--:--")

---

## 🛠️ Chi tiết kỹ thuật / Technical Details

### Files cần sửa đổi

#### 1. `resources/js/pages/EmployeeMaster/detail.vue`
- **Dòng 404-419**: Thêm trường hiển thị Detail Modal
- **Dòng 1404-1435**: Cập nhật `handleGetDepartmentWorkingDetail()`
- **data()**: Khởi tạo `detailModalData`

#### 2. `resources/js/pages/EmployeeMaster/edit.vue`
- **Dòng 396-419**: Thêm trường hiển thị Detail Modal
- **Dòng 667-697**: Thêm trường nhập liệu Edit Modal (time picker)
- **Dòng 1338-1368**: Cập nhật `handleGetDepartmentWorkingDetail()`
- **Dòng 1401-1431**: Cập nhật `handleGetDepartmentWorkingEdit()`
- **handleSubmitEditModal()**: Cập nhật API request
- **data()**: Khởi tạo `detailModalData` và `editModalData`

### UI Components
- **Component**: Bootstrap Vue's `b-form-timepicker`
- **Props**: 
  - `:locale="lang"`
  - `:hour12="false"`
  - `show-seconds`
  - `:seconds="0"`
- **Placeholder**: 
  - Edit Modal: "選択してください"
  - Detail Modal: "--:--"

### API Integration
- **GET** `/api/employee/dp-working`: Nhận các trường mới
- **PUT** `/api/employee/{id}`: Gửi các trường mới

### Data Structure
```javascript
detailModalData: {
  scheduled_work_start_time: '',
  scheduled_work_end_time: '',
  // ... other fields
}

editModalData: {
  scheduled_work_start_time: '',
  scheduled_work_end_time: '',
  // ... other fields
}
```

---

## ✅ Tiêu chí chấp nhận / Acceptance Criteria

- [ ] Detail Modal hiển thị chính xác thời gian làm việc quy định (bắt đầu) và (kết thúc)
- [ ] Time picker trong Edit Modal cho phép chọn thời gian theo từng phút
- [ ] Hiển thị định dạng 24 giờ
- [ ] Áp dụng locale tiếng Nhật
- [ ] Dữ liệu được lấy chính xác từ API
- [ ] Dữ liệu được gửi chính xác lên API
- [ ] Giá trị rỗng (NULL) được xử lý thích hợp (hiển thị placeholder)
- [ ] Chức năng hiển thị và chỉnh sửa dữ liệu hiện có hoạt động bình thường
- [ ] Responsive design (hỗ trợ mobile và tablet)
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Frontend unit tests được tạo và vượt qua
- [ ] Không có thay đổi phá vỡ chức năng hiện có

---

## 🔗 Phụ thuộc / Dependencies

- **Backend issue #506** phải hoàn thành (để integration testing)
- Backend API có thể trả về và nhận các trường mới:
  - `scheduled_work_start_time`
  - `scheduled_work_end_time`

---

## 📝 Implementation Checklist

### Phase 1: Thiết lập cơ bản
- [ ] Đọc và phân tích code hiện tại trong `detail.vue`
- [ ] Đọc và phân tích code hiện tại trong `edit.vue`
- [ ] Xác định vị trí chính xác cần thêm code
- [ ] Kiểm tra Bootstrap Vue `b-form-timepicker` component

### Phase 2: Implement Detail View
- [ ] Thêm trường hiển thị trong Detail Modal của `detail.vue`
- [ ] Cập nhật `handleGetDepartmentWorkingDetail()` trong `detail.vue`
- [ ] Khởi tạo fields mới trong `detailModalData` của `detail.vue`
- [ ] Test hiển thị Detail Modal

### Phase 3: Implement Edit View - Detail Modal
- [ ] Thêm trường hiển thị trong Detail Modal của `edit.vue` (read-only)
- [ ] Cập nhật `handleGetDepartmentWorkingDetail()` trong `edit.vue`
- [ ] Test hiển thị Detail Modal trong edit view

### Phase 4: Implement Edit View - Edit Modal
- [ ] Thêm `b-form-timepicker` vào Edit Modal
- [ ] Cấu hình time picker với các props phù hợp
- [ ] Cập nhật `handleGetDepartmentWorkingEdit()` để load dữ liệu
- [ ] Cập nhật `handleSubmitEditModal()` để submit dữ liệu
- [ ] Khởi tạo fields mới trong `editModalData`
- [ ] Test chức năng chỉnh sửa

### Phase 5: Data Handling
- [ ] Implement mapping từ API response
- [ ] Implement mapping đến API request
- [ ] Xử lý NULL values với placeholder phù hợp
- [ ] Validate dữ liệu input

### Phase 6: Testing
- [ ] Unit testing cho các components
- [ ] Integration testing với backend API
- [ ] Responsive testing (mobile, tablet, desktop)
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Regression testing cho chức năng cũ

### Phase 7: Polish & Documentation
- [ ] Kiểm tra UI/UX
- [ ] Kiểm tra accessibility
- [ ] Cập nhật documentation nếu cần
- [ ] Code review
- [ ] Final testing

---

## 📌 Notes / Ghi chú

### Technical Notes
- Sử dụng Bootstrap Vue's `b-form-timepicker` component
- Time picker phải cho phép chọn từng phút (1分単位)
- Định dạng 24 giờ, không có AM/PM
- Hỗ trợ locale tiếng Nhật

### Important Considerations
- Backend issue #506 phải hoàn thành trước khi integration testing
- Đảm bảo không phá vỡ chức năng hiện có
- Xử lý edge cases: NULL values, invalid times
- Responsive design là bắt buộc

### Related Issues
- Parent issue: #504
- Backend dependency: #506

---

## 🔍 Review Section

### Code Review Checklist
- [ ] Code follows project conventions
- [ ] No console.log or debug code left
- [ ] Error handling is proper
- [ ] Code is well-commented
- [ ] No hardcoded values
- [ ] Responsive design implemented
- [ ] Accessibility standards met

### Testing Review
- [ ] All unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed
- [ ] No regression bugs found

### Documentation Review
- [ ] Code comments are clear
- [ ] API documentation updated if needed
- [ ] User guide updated if needed

---

**Generated**: 2025-12-18
**Branch**: 507-feat-scheduled-work-time-ui
