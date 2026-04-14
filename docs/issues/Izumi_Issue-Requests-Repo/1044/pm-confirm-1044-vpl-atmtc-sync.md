# Phiếu xác nhận PM — Đồng bộ ATMTC → VPL (Issue #1044)

**Dành cho:** PM / PO / BA  
**English (bản PM đã chốt):** [`pm-confirm-1044-vpl-atmtc-sync.en.md`](./pm-confirm-1044-vpl-atmtc-sync.en.md)  
**Mục đích:** chốt **quy tắc nghiệp vụ** còn lại trước khi đội dev hoàn thiện đồng bộ từ **Izumi Cloud** sang **hệ thống P&L xe (VPL)**. Thuật ngữ IT chỉ gom ở **phụ lục** (dev đối chiếu).

**Trạng thái:** Các lựa chọn mục **B** dưới đây đã được **đồng bộ theo phiên bản tiếng Anh** do PM trả lời; **B5.1** đã **chốt bổ sung** (mọi hệ thống gọi API dùng tài khoản quản trị cao). **B5.2 / B5.3** vẫn mở nếu sau này cần ngoại lệ / rà soát caller.

**Issue:** [#1044](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044)  
**Issue cha:** [#1010](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010)

**Bối cảnh (ngôn ngữ nghiệp vụ):** Sau khi nhận file / dữ liệu giao hàng từ ATMTC, Izumi Cloud lưu **từng lần giao** và cố gắng **ghép** với **nhân viên**, **xe**, **đơn vị (phòng ban)** đang quản lý trên Cloud. Khi gửi sang VPL, hệ thống dùng **cùng cách định danh** đã dùng khi đồng bộ danh mục tài xế / xe lên VPL (dev: mã ngoài VPL trùng với mã nội bộ Cloud).

**Plan tham chiếu:** `docs/issues/Izumi_Issue-Requests-Repo/1044/plan.md`

---

## A. Điểm đã rõ (ghi nhận)

| # | Nội dung |
|---|----------|
| A1 | Luồng tổng: **ATMTC → Izumi Cloud (lưu từng lần giao) → (bước mới) → VPL**. |
| A2 | Kênh gửi sang VPL do dev thiết kế; **chỉ tài khoản quản trị cao** được phép gọi (cùng mức với các đồng bộ nhạy cảm khác). |
| A3 | **Tài xế / xe** trên VPL được lấy theo bản ghi đã **ghép được** trên Cloud — PM **không** cần chốt lại map từng mã tài xế / biển số thô trên file ATMTC trong phiếu này. |
| A4 | Đổi quy tắc **ghép master** trên Cloud (ai là ai, xe nào là xe nào) là hạng mục **nhập liệu / master** riêng; phiếu này chỉ hỏi hành vi **khi đã có dữ liệu trên Cloud**. |

---

## B. Cần PM xác nhận (đánh dấu [ ] → [x] hoặc ghi “Ghi chú PM”)

### B1. Cách tính **số chạy trong ngày** trên VPL (theo **mỗi xe × mỗi ngày**)

Trong một ngày có thể có **nhiều lần giao** cho cùng một xe.

- [x] **B1.1** **Mỗi lần giao (mỗi dòng nghiệp vụ riêng trên Cloud) = 1 lần chạy** — cộng dồn theo xe + ngày.
- [ ] **B1.2** Dùng **số lượng** ghi trên phiếu giao (nếu có): **số chạy = tổng số lượng** theo xe + ngày (mô tả nếu khác: _________________________).
- [ ] **B1.3** **Khác** (mô tả ngắn): _________________________

**Ghi chú PM:** Một dòng chuyến = một lần chạy; gộp thành **tổng theo ngày cho từng xe**.

---

### B2. Lần giao đã có trên Cloud nhưng **chưa ghép được** nhân viên hoặc xe

- [x] **B2.1** **Bỏ qua** lần giao đó khi gửi VPL; có báo cáo / log kiểu “một phần thành công, một phần bỏ qua”.
- [ ] **B2.2** **Coi là lỗi**: không gửi (hoặc coi cả đợt là thất bại) nếu còn lần giao chưa ghép được.
- [ ] **B2.3** **Khác:** _________________________

**Ghi chú PM:** Dòng chưa ghép được **bỏ qua**, ghi nhận **thành công một phần**. **Thông báo người phụ trách** trước **ngày làm việc tiếp theo** kèm **số lượng và chi tiết** các dòng đã bỏ qua.

---

### B3. Cùng **xe + ngày**, nhiều **tài xế** khác nhau trong dữ liệu

- [x] **B3.1** **Chấp nhận:** nhiều **phân công** (tài xế – xe – ngày) trên VPL; **số chạy trong ngày** vẫn là **một con số gộp theo xe–ngày** (theo B1).
- [ ] **B3.2** **Khác** (mô tả): _________________________

**Ghi chú PM:** Chấp nhận **nhiều tài xế** cùng xe cùng ngày. **Giữ** phân bổ theo **từng dòng** (ai lái); **tổng số chạy** gộp theo xe–ngày **theo B1**.

---

### B4. Sau khi cập nhật phân công và số chạy từ ATMTC, có cần **chạy thêm bước phân bổ theo tài xế** giống luồng timesheet?

- [ ] **B4.1** **Có** — chạy **đủ** các bước phân bổ liên quan tài xế **và** phần **lương / số chạy** như thiết kế hiện tại của VPL.
- [x] **B4.2** **Chỉ** phần **lương / số chạy** (không chạy đủ phân bổ tài xế như timesheet).
- [ ] **B4.3** **Khác / cần họp thêm với kế toán–vận hành:** _________________________

**Ghi chú PM:** Dòng ATMTC là **chuyến giao hàng**, nguồn và **độ chi tiết khác timesheet**. Chỉ chạy bước **lương / số chạy**, **không** chạy **đủ** phân bổ tài xế giống timesheet để **tránh cộng trùng**; mở rộng phạm vi **sau khi tài chính rà soát** nếu cần.

---

### B5. **Đồng bộ “ngày vận hành / số chạy”** trên VPL chỉ cho phép tài khoản **quản trị cao (MASTER)**

(Nếu còn hệ thống cũ gọi bằng quyền thấp hơn, việc siết quyền có thể cần điều chỉnh tích hợp.)

- [x] **B5.1** **Đồng ý** — mọi hệ thống gọi API này dùng tài khoản quản trị cao.
- [ ] **B5.2** **Cần giữ** kênh riêng cho tích hợp cũ (PM ghi tên hệ thống / đối tác): _________________________
- [ ] **B5.3** **Chưa rõ** — nhờ IT rà soát trước khi áp dụng.

**Ghi chú PM:** **B5.1 đã chốt:** mọi caller (IC, tích hợp khác) phải dùng **MASTER / quản trị cao** trên VPL khi gọi các API đồng bộ “ngày vận hành / số chạy” (gồm `daily-operating/sync`, `atmtc-transactions/sync`, và các API cùng nhóm theo issue). **B5.2** chỉ áp dụng nếu sau này PM ghi nhận **ngoại lệ** có kênh riêng.

---

### B6. Màn hình **tổng hợp theo ngày** trên VPL có cần **hiển thị số chạy** trong phạm vi sprint này?

- [ ] **B6.1** **Có** (tối thiểu: thêm trên API hoặc cột phụ màn hiện có).
- [x] **B6.2** **Không** — giai đoạn sau; tạm dùng **nhật ký đồng bộ** để đối soát.
- [ ] **B6.3** **Khác:** _________________________

**Ghi chú PM:** **Không** bắt buộc hiển thị số chạy trên màn tổng hợp theo ngày trong **release này**; đối soát qua **nhật ký đồng bộ** là đủ.

---

## C. Chữ ký / ngày

| Vai trò | Họ tên | Ngày |
|---------|--------|------|
| PM / PO | *(theo bản EN đã chốt)* | |
| BA (nếu có) | | |

**Ghi nhận nội bộ:** Quyết định mục B1–B4, **B5.1**, B6 đồng bộ với [`pm-confirm-1044-vpl-atmtc-sync.en.md`](./pm-confirm-1044-vpl-atmtc-sync.en.md).

---

## Phụ lục — Thuật ngữ cho PM

| Thuật ngữ | Giải thích ngắn |
|-----------|-----------------|
| **VPL** | Hệ thống tính toán P&L theo xe / ngày (vehicle-pl-system). |
| **Izumi Cloud** | Hệ thống cloud trung gian — nơi lưu dữ liệu giao hàng sau ATMTC. |
| **Số chạy trong ngày** | Con số dùng phân bổ chi phí lương liên quan số chuyến (trên VPL). |
| **MASTER** | Quyền quản trị cao trên VPL, dùng cho các API đồng bộ nhạy cảm. |

---

## Phụ lục — Bảng đối chiếu kỹ thuật (chỉ dev / IT)

| Cách gọi trong phiếu PM | Gợi ý tên trong hệ thống |
|-------------------------|---------------------------|
| Bảng lưu từng lần giao ATMTC trên Cloud | bảng kết quả giao hàng ATMTC |
| Mã một lần giao từ phía ATMTC | khóa dòng / id nguồn (dev map sang cột id nguồn ATMTC) |
| Nhân viên đã ghép trên Cloud | khóa nội bộ nhân viên trên Cloud |
| Xe đã ghép trên Cloud | khóa nội bộ xe trên Cloud |
| Đơn vị đã ghép trên Cloud | khóa nội bộ phòng ban trên Cloud |
| API đồng bộ ATMTC → VPL (tên có thể đổi khi dev triển khai) | endpoint đồng bộ giao dịch ATMTC |
| Đồng bộ “ngày vận hành / số chạy” (B5) | API đồng bộ nhật ký vận hành theo ngày trên VPL |
