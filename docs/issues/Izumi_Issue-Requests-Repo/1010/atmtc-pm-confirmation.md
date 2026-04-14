# Xác nhận PM — Làm việc với ATMTC (hướng tích hợp + dữ liệu transaction)

| Mục | Nội dung |
|-----|----------|
| **Mục đích** | PM phê duyệt nội dung sẽ gửi / trao đổi với đội ATMTC trước khi Izumi liên hệ chính thức. |
| **Liên quan** | [#1010](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010), master IC → ATMTC (department, vehicle, driver, course) đã / đang triển khai. |
| **Phạm vi tài liệu này** | Chỉ **mục 1** (hướng tích hợp tổng thể) và **mục 3** (giao dịch / vận hành theo ngày). |

---

## Tóm tắt gửi PM

Phía Izumi đã có luồng **đồng bộ master Izumi Cloud → ATMTC**. Bước tiếp theo cần **làm rõ với ATMTC** cách cung cấp **dữ liệu giao dịch / vận hành theo ngày** (ATMTC → Izumi Cloud), để sau đó phản ánh trên PL theo thiết kế nội bộ.

Sau khi PM đồng ý nội dung các mục dưới đây, bản **“Câu hỏi / brief gửi ATMTC”** có thể copy từ mục **Phụ lục A**.

---




Kính gửi đội kỹ thuật ATMTC,

Phía Izumi Cloud hiện đã triển khai **đồng bộ master từ Izumi Cloud sang ATMTC** (ví dụ: department, vehicle, driver, course) theo luồng import đã thống nhất. Để triển khai bước tiếp theo — **dữ liệu giao dịch / vận hành theo ngày** phục vụ hệ thống PL — nhờ đội ATMTC cùng xác nhận các nội dung dưới đây.

### 1. Hướng tích hợp tổng thể

#### 1.1. Phạm vi đã có (bối cảnh)

- Master **Izumi Cloud → ATMTC**: đã / đang có cơ chế đẩy dữ liệu master (department, vehicle, driver, course) vào ATMTC.

#### 1.2. Phạm vi cần làm rõ (giao dịch / vận hành)

- **Dữ liệu giao dịch hoặc bản ghi vận hành theo ngày** (transaction): cần đưa từ **ATMTC** về phía **Izumi Cloud** (và sau đó phản ánh trên PL theo thiết kế nội bộ Izumi).

#### 1.3. Câu hỏi kỹ thuật

**Câu 1.** Với **dữ liệu giao dịch / vận hành theo ngày**, ATMTC dự kiến phương án nào (có thể chọn nhiều hoặc mô tả kết hợp)?

- [ ] **A.** Izumi Cloud **chủ động gọi** API phía ATMTC (pull, có thể theo lịch hoặc theo khoảng thời gian).
- [ ] **B.** ATMTC **chủ động gọi** API / webhook phía Izumi Cloud (push) khi có sự kiện hoặc theo batch.
- [ ] **C.** Trao đổi qua **tệp** (CSV / JSON) — SFTP, object storage, hoặc cổng tải lên; tần suất và quy ước đặt tên tệp.
- [ ] **D.** Khác: `[mô tả ngắn]`.

**Câu 2.** Môi trường triển khai: **development / staging / production** — URL, thông tin xác thực, quy tắc release từng môi trường? (Nếu có OpenAPI / tài liệu, xin đính kèm hoặc link.)

**Câu 3.** Luồng **xử lý lỗi và thử lại**: khi batch hoặc request thất bại, ATMTC khuyến nghị Izumi **retry** thế nào (ví dụ: chỉ với HTTP 5xx, giới hạn số lần), và cách gắn với **idempotency** (mục 3).

---

### 3. Dữ liệu giao dịch / vận hành theo ngày (transaction)

#### 3.1. Định nghĩa nghiệp vụ

**Câu 4.** Một **bản ghi** mà ATMTC coi là “giao dịch / vận hành” trong phạm vi tích hợp này là:

- [ ] Một **chuyến / lần chạy** cụ thể?
- [ ] Một **dòng tổng hợp theo ngày** (theo tài xế + xe + …)?
- [ ] Khác: `[mô tả]`.

**Câu 5.** Bản ghi có gắn với **master đã đồng bộ từ Izumi** (department / vehicle / driver / course) **bằng định danh nào**? Xin ATMTC liệt kê **tên trường** và **ví dụ giá trị**.

#### 3.2. Danh sách trường (key)

**Câu 6.** Với **mỗi** bản giao dịch / vận hành, nhờ ATMTC cung cấp bảng đầy đủ: **tên trường**, kiểu dữ liệu, bắt buộc / tùy chọn, mô tả ngắn. Gợi ý phía Izumi (ATMTC có thể đổi / bổ sung / đánh dấu không dùng):

| Trường (gợi ý) | Bắt buộc (ATMTC: Có/Không) | Mô tả / ví dụ (do ATMTC điền) |
|----------------|-----------------------------|--------------------------------|
| ID giao dịch duy nhất trên ATMTC (idempotency) | | |
| Mã / ID tài xế (khớp master Izumi hoặc quy ước mapping) | | |
| Mã / ID xe | | |
| Mã / ID lộ trình / course (nếu có) | | |
| Ngày (và giờ nếu có), timezone | | |
| Số chuyến / increment trong ngày (nếu có) | | |
| Trạng thái: tạo mới / điều chỉnh / hủy (nếu có) | | |
| Thời điểm tạo/cập nhật trên ATMTC | | |
| `[trường khác]` | | |

**Câu 7.** **Cập nhật / hủy:** Bản ghi đã tạo có **sửa** hoặc **hủy** không? Thể hiện thế nào (bản ghi mới, cờ trạng thái, phiên bản, …)?

**Câu 8.** **Trùng lặp / gửi lại:** Idempotency theo trường nào (`transaction_id`, hash, …)?

**Câu 9.** **Tần suất và độ trễ:** Real-time / theo giờ / T+1? Độ trễ chấp nhận được?

#### 3.3. Ví dụ minh họa

**Câu 10.** Xin 1–2 **ví dụ** (JSON hoặc dòng CSV đúng định dạng tích hợp), kèm giải thích ngắn.

---

**Đầu mối Izumi Cloud:** `[Họ tên, email]`  
**Đề xuất họp (30–60 phút):** `[lịch]`

Trân trọng,  
`[Izumi — chữ ký]`

---

## Ghi chú nội bộ (không gửi ATMTC)

- Sau khi có trả lời ATMTC: IC triển khai ingest → map → gọi PL (`POST /api/daily-operating/sync`, `POST /api/driver-assignments/sync`, hoặc API gộp nếu có). Chi tiết kỹ thuật PL nằm tại `vehicle`: `docs/external-integration-spec.md` §6.2–6.3.
- Xung đột nguồn với **timesheet** cần quyết định nghiệp vụ sau khi biết tần suất và semantics bản ghi ATMTC.
