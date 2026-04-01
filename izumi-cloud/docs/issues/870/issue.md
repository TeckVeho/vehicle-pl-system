# Issue #870 - Employee master DB_Display pdf on the detail screen

## Metadata

| Field | Value |
|-------|--------|
| **Title** | Employee master DB_Display pdf on the detail screen |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/870 |
| **State** | OPEN |
| **Created** | 2026-03-12T09:40:04Z |
| **Updated** | 2026-03-13T10:24:16Z |
| **Assignees** | maitue, DongVietLong |
| **Labels** | _(none)_ |

---

## Body (Overview)

### 従業員マスタ_DB詳細画面にPDF表示機能追加 / Thêm chức năng hiển thị PDF trên màn hình chi tiết nhân viên master_DB

#### 1. Tổng quan (Overview)

**Bối cảnh (Background)**

* **Vấn đề hiện tại:** Có phản hồi về phần thông tin cơ bản của nhân viên master trên Izumi Cloud.
* **Yêu cầu kinh doanh:** Cần thêm chức năng để người dùng có thể dễ dàng kiểm tra lịch sử khám sức khỏe của nhân viên và xóa các PDF đã tải lên nhầm.
* **Câu chuyện người dùng:** Là người dùng, tôi muốn nhấp vào ngày khám sức khỏe để hiển thị dữ liệu PDF. Ngoài ra, tôi muốn xóa các PDF đã tải lên nhầm.

**Mục tiêu đạt được (Goal)**

* **Hình ảnh lý tưởng:** Nhân viên master thông tin cơ bản FB
  - Khi nhấp vào cột ngày khám sức khỏe, danh sách ngày của lịch sử khám sẽ hiển thị, và khi chọn một ngày bất kỳ, dữ liệu PDF tương ứng sẽ được hiển thị. ※ Thời gian lưu trữ là 5 năm, với 2 lần khám mỗi năm, do đó có tối đa 10 lần lịch sử.
  - Thêm chức năng xóa liên quan đến PDF, cho phép xóa các PDF đã tải lên nhầm từ lịch sử.

#### 2. Thông số kỹ thuật (Specification)

**Yêu cầu chức năng (Functional Requirements)**

* Khi nhấp vào cột ngày khám sức khỏe, hệ thống sẽ hiển thị danh sách ngày của lịch sử khám.
* Khi người dùng chọn một ngày bất kỳ, hệ thống sẽ hiển thị dữ liệu PDF liên quan đến ngày đó.
* Dữ liệu PDF tối đa là 10 lần, thời gian lưu trữ là 5 năm.
* Khi người dùng sử dụng chức năng xóa PDF, hệ thống sẽ xóa PDF đã chọn khỏi lịch sử.

**Loại tác vụ:** Yêu cầu  
**Người khởi tạo:** Đào Thị Thư

---

## Implementation Checklist (Definition of Done)

- [ ] Khi nhấp vào ngày khám sức khỏe, danh sách ngày của lịch sử khám sẽ được hiển thị.
- [ ] Khi chọn một ngày bất kỳ, dữ liệu PDF tương ứng sẽ được hiển thị.
- [ ] Xác nhận thời gian lưu trữ PDF là 5 năm.
- [ ] Xác nhận chức năng xóa PDF hoạt động bình thường.
- [ ] Xác nhận người dùng có thể xóa PDF đã tải lên nhầm từ lịch sử.

---

## Implementation Tasks (Child issues – total SP: 6)

- [ ] [BE #876](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/876) – API xóa lịch sử PDF khám sức khỏe (SP: 2)
- [ ] [FE #877](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/877) – Hiển thị PDF khi click ngày khám, UI xóa lịch sử (SP: 4)

---

## Notes / Review

* Branch hiện tại: `870-be-employee-master-db_display-pdf-on-the-detail-screen`
* PDF: tối đa 10 bản ghi (5 năm × 2 lần/năm), cần API/UI cho danh sách ngày khám và xem/xóa PDF.
