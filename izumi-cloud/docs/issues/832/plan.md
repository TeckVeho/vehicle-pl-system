# Issue #832: Disaster confirmation noti function feedback - Implementation Plan

## 概要 (Overview)

**Yêu cầu:** Cải thiện chức năng thông báo an toàn (安否確認 / disaster confirmation) theo feedback từ issue #328.

**Hiện trạng vs mong muốn:**

| Phía | Hiện tại | Mong muốn |
|------|----------|-----------|
| **App (IA)** | Thông báo chỉ đến khi user mở app; title 「絵文字+災害通知+絵文字」 | Gửi push ngay khi hệ thống gửi (kể cả chưa mở app); đổi title thành 「安否確認」 |
| **Web Admin (WA)** | Nội dung thông báo tạo mới trống; sau khi gửi, bấm 「一覧に戻る」 thì list không có bản ghi mới (phải F5) | Có nội dung mặc định 「皆様の安全を確認するため、安否報告をお願いします。」; khi quay lại list thì tự load lại và hiển thị bản ghi vừa gửi |

**Phạm vi codebase:** Trong repo **izumi-cloud** chỉ tìm thấy DriverRecorder Create/Edit gọi **API ngoài** `https://izumi-web-app.*/api/notices` để tạo notice (loại 原因対策). Màn tạo/xem thông báo an toàn (安否確認) và logic push notification nhiều khả năng nằm ở repo **izumi-web-app**. Plan dưới đây mô tả theo hướng thay đổi ở izumi-web-app; nếu có thêm module trong izumi-cloud sẽ bổ sung sau.

---

## FE (Frontend)

### 1. Files need to edit (izumi-web-app – dự kiến)

#### 1.1. File: [izumi-web-app] Màn tạo thông báo an toàn (form create notice)

##### 1.1.1. Set nội dung mặc định cho message (content)

- Form tạo thông báo an toàn cần có **default value** cho trường nội dung.
- Giá trị mặc định: `「皆様の安全を確認するため、安否報告をお願いします。」`
- User vẫn có thể sửa nội dung; nếu không sửa thì có thể gửi nhanh không cần nhập.

**変更内容:**

- Trong component form tạo notice (disaster/safety confirmation), khởi tạo `content` hoặc `message` với chuỗi mặc định trên (data default hoặc placeholder tùy UX).
- Đảm bảo khi submit, giá trị này được gửi lên API nếu user không thay đổi.

##### 1.1.2. Sau khi gửi thành công – quay lại list và load lại dữ liệu

- Hiện tại: Bấm 「一覧に戻る」 quay về màn list nhưng list không cập nhật (phải reload/F5).
- Yêu cầu: Khi từ màn tạo/send quay lại màn list, **tự động gọi lại API lấy list** (hoặc refetch) để bản ghi vừa gửi hiển thị ngay.

**変更内容:**

- Trong flow sau khi gửi thông báo thành công (ví dụ sau khi POST create/send notice):
  - Nếu navigate bằng `router.push` tới route list: trước khi push có thể emit event hoặc set flag để list page **fetch lại data** khi được mount/activated.
  - Hoặc: List page luôn **refetch khi enter route** (ví dụ `activated` hook hoặc `beforeRouteEnter`), đảm bảo lần vào từ 「一覧に戻る」 luôn lấy dữ liệu mới.
- Đảm bảo nút 「一覧に戻る」 thực hiện navigation về đúng route list và kích hoạt logic refetch trên.

#### 1.2. File: [izumi-web-app] Màn list thông báo (notice list)

##### 1.2.1. Refetch khi vào màn hình (từ màn tạo/quay lại)

- Màn list cần load lại dữ liệu mỗi khi user vào màn hình (đặc biệt khi quay lại từ màn tạo).

**変更内容:**

- Trong component list (ví dụ `NoticeList.vue` hoặc tương đương):
  - Gọi API lấy list trong `activated()` (Vue 2) hoặc `onActivated` (Vue 3) nếu dùng keep-alive; hoặc gọi trong `mounted()` nếu không dùng keep-alive.
  - Đảm bảo sau khi tạo xong và navigate về đây, lần mount/activate này sẽ chạy lại API và cập nhật bảng danh sách.

---

## BE (Backend)

### 1. Files need to edit (izumi-web-app – dự kiến)

#### 1.1. File: [izumi-web-app] Logic gửi push notification (FCM / push service)

##### 1.1.1. Gửi push ngay khi hệ thống gửi thông báo (không chờ app mở)

- Hiện tại: Notification đến thiết bị khi user mở app (có thể do polling hoặc chỉ đồng bộ khi mở app).
- Yêu cầu: Gửi **push notification (FCM/APNs)** ngay tại thời điểm backend gửi thông báo an toàn, để user nhận được ngay cả khi chưa mở app.

**変更内容:**

- Xác định endpoint/service tạo hoặc gửi “disaster confirmation” notice (ví dụ sau khi POST tạo notice hoặc action “gửi ngay”).
- Tại thời điểm đó gọi FCM (và/hoặc APNs) để gửi push tới các device token của user nhận thông báo.
- Đảm bảo không phụ thuộc vào việc app đang mở; push được gửi từ server ngay khi có sự kiện “gửi thông báo”.

##### 1.1.2. Đổi title push từ 「災害通知」 sang 「安否確認」

- Payload push hiện có title dạng 「絵文字+災害通知+絵文字」.
- Yêu cầu: Phần text title hiển thị là **「安否確認」** (có thể giữ hoặc bỏ emoji tùy spec).

**変更内容:**

- Trong code build payload gửi FCM/APNs cho loại thông báo an toàn (disaster confirmation):
  - Đổi field dùng làm title (ví dụ `title`, `notification.title`) thành `「安否確認」`.
  - Kiểm tra mọi nơi gửi push cho loại notice này (cron, queue, API handler) để thống nhất một giá trị title.

#### 1.2. File: [izumi-web-app] API tạo/gửi notice (nếu cần)

##### 1.2.1. Chấp nhận nội dung mặc định từ FE

- API tạo notice (ví dụ `POST /api/notices` hoặc tương đương) không bắt buộc thay đổi logic nghiệp vụ nếu FE đã gửi đúng nội dung mặc định.
- Nếu backend từng set default cho `content` khi trống: đảm bảo default trùng với 「皆様の安全を確認するため、安否報告をお願いします。」 cho loại thông báo an toàn.

**変更内容:**

- (Tùy chọn) Nếu request body `content` rỗng và loại notice là “disaster confirmation”, set default `content` phía BE giống chuỗi trên.
- Đảm bảo API trả về thành công và dữ liệu list (GET list notices) bao gồm bản ghi mới ngay sau khi tạo, để FE refetch list hoạt động đúng.

---

## 実装順序 (Implementation Order)

1. **Backend (izumi-web-app)** – Push notification
   - Implement gửi push ngay khi hệ thống gửi thông báo (task 1.1.1).
   - Đổi title push sang 「安否確認」(task 1.1.2).
   - (Tùy chọn) Default content phía BE cho disaster confirmation (task 1.2.1).

2. **Frontend (izumi-web-app)** – Form & List
   - Set default content form tạo thông báo (task 1.1.1).
   - Refetch list khi quay lại từ màn tạo (task 1.1.2, 1.2.1).

3. **統合テスト**
   - Tạo và gửi thông báo an toàn từ WA → kiểm tra push tới app (chưa mở app) và title 「安否確認」.
   - Kiểm tra nội dung mặc định và gửi nhanh không sửa nội dung.
   - Gửi xong bấm 「一覧に戻る」 → list hiển thị bản ghi mới không cần F5.

---

## 見積もり工数 (Estimated Effort)

- **Backend (izumi-web-app):** 2–4 時間
  - Push gửi ngay khi gửi thông báo: 1–2h
  - Đổi title push: 0.5h
  - Default content (nếu làm ở BE): 0.5h

- **Frontend (izumi-web-app):** 1–2 時間
  - Default content form: 0.5h
  - Refetch list khi quay lại: 0.5–1h

**合計:** 約 3–6 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮**
   - Refetch list mỗi lần vào màn list có thể tăng số lần gọi API; có thể giới hạn refetch chỉ khi từ màn tạo (query param hoặc route meta) nếu cần tối ưu.

2. **UX 考慮**
   - Default content nên rõ ràng là “có thể sửa”; tránh user nghĩ không sửa được.
   - 「一覧に戻る」 nên giữ feedback (loading) khi refetch list để user thấy dữ liệu đang cập nhật.

3. **データ整合性**
   - Đảm bảo API list notices trả về bản ghi mới ngay sau khi create (không bị delay do cache/replica).

4. **既存機能との互換性**
   - Thay đổi title push chỉ áp dụng cho loại thông báo an toàn (安否確認); không ảnh hưởng loại notice khác (ví dụ 原因対策 từ DriverRecorder trong izumi-cloud).
   - Repo **izumi-cloud** hiện không có thay đổi trực tiếp cho issue này (chỉ gọi izumi-web-app API từ DriverRecorder cho notice khác). Nếu sau này bổ sung màn thông báo an toàn trong cloud thì áp dụng cùng spec (default content, refetch list).

---

**Issue:** 832  
**Output:** docs/issues/832/plan.md  
**Repository note:** Implementation is expected in **izumi-web-app**. izumi-cloud is referenced only for context (external API calls to `/api/notices`).
