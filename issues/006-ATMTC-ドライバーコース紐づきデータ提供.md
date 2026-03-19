# ATMTC: ドライバー・コース紐づきデータの提供（イズミクラウド経由）

## 1. 概要 (Overview)

### 背景 (Background)

* **現状の課題:** 日次乗務記録のためのドライバーとコース（または車両）の紐づきは ATMTC で取得したものをイズミクラウド経由で IZUMI に連携するが、ATMTC 側でイズミクラウドが取得できる形式でのデータ提供が未整備の可能性がある。
* **ビジネス要求:** ATMTC からドライバー・コース紐づきをイズミクラウドが取得し、ドライバー sync やコース sync に反映して IZUMI に連携できるようにすること。
* **ユーザーストーリー:** システム管理者として、私は ATMTC のドライバー・コース紐づきが IZUMI の日次乗務記録・ドライバー配賦に正確に反映されるよう、データ連携の基盤を整えたい。

### 達成目標 (Goal)

* **あるべき姿:** ATMTC がドライバーとコース（または車両）の紐づきをイズミクラウドが取得できる形式で提供し、イズミクラウド経由で IZUMI の drivers sync や courses sync に反映されること。
* **完了条件 (Definition of Done):**
* [ ] 紐づきデータの提供: ドライバーとコース（または車両）の紐づきをイズミクラウドが取得できる形式で提供されていること。
* [ ] 識別子の整合: イズミクラウドのドライバー・コースの externalId と整合する識別子を使用していること。

---
## 2. 仕様 (Specification)

### 機能要件 (Functional Requirements)

**紐づきデータの提供**
* ドライバーとコース（または車両）の紐づきをイズミクラウドが取得できる形式で提供すること。
* 提供方式: API、ファイル、DB 連携などのいずれか。
* 紐づき情報には、どのドライバーがどのコース（または車両）に紐づくかが含まれること。

**識別子の整合**
* イズミクラウドのドライバー・コースの externalId と整合する識別子を使用すること。
* IZUMI の drivers sync や courses sync で使用される externalId と一貫性が保たれること。

### タスクタイプ
データ連携準備

### 添付ファイル

### 参考資料
- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) 5. ATMTC
- [external-integration-spec.md](../docs/external-integration-spec.md) 5.6 ドライバー一括同期

### メモ
- 実際の `drivers/sync` や `courses/sync` の呼び出しはイズミクラウドが担当。ATMTC は紐づきデータの提供のみ。ATMTC から IZUMI へ直接連携はしない。

### UI/UX (あれば)
* **デザイン:** なし
* **コンポーネント:** なし

### 起票者
-

---
# ATMTC: Cung cấp dữ liệu liên kết tài xế-khóa học (qua Izumi Cloud)

## 1. Tổng quan

### Bối cảnh

* **Vấn đề hiện tại:** Liên kết tài xế-khóa học (hoặc xe) cho ghi chép phục vụ hàng ngày được lấy từ ATMTC và liên kết sang IZUMI qua Izumi Cloud, nhưng phía ATMTC có thể chưa chuẩn bị cung cấp dữ liệu dưới dạng Izumi Cloud có thể lấy được.
* **Yêu cầu kinh doanh:** Izumi Cloud lấy liên kết tài xế-khóa học từ ATMTC và phản ánh vào drivers sync hoặc courses sync để liên kết sang IZUMI.
* **Câu chuyện người dùng:** Là quản trị viên hệ thống, tôi muốn chuẩn bị nền tảng liên kết dữ liệu để liên kết tài xế-khóa học từ ATMTC được phản ánh chính xác vào ghi chép phục vụ hàng ngày và phân bổ tài xế của IZUMI.

### Mục tiêu đạt được

* **Hình ảnh lý tưởng:** ATMTC cung cấp liên kết tài xế-khóa học (hoặc xe) dưới dạng Izumi Cloud có thể lấy được, và được phản ánh vào drivers sync hoặc courses sync của IZUMI qua Izumi Cloud.
* **Điều kiện hoàn thành (Definition of Done):**
* [ ] Cung cấp dữ liệu liên kết: Liên kết tài xế-khóa học (hoặc xe) được cung cấp dưới dạng Izumi Cloud có thể lấy được.
* [ ] Thống nhất định danh: Sử dụng định danh thống nhất với externalId tài xế・khóa học của Izumi Cloud.

---
## 2. Thông số kỹ thuật (Specification)

### Yêu cầu chức năng (Functional Requirements)

**Cung cấp dữ liệu liên kết**
* Cung cấp liên kết tài xế-khóa học (hoặc xe) dưới dạng Izumi Cloud có thể lấy được.
* Phương thức cung cấp: API, file, liên kết DB, v.v.
* Thông tin liên kết bao gồm tài xế nào liên kết với khóa học (hoặc xe) nào.

**Thống nhất định danh**
* Sử dụng định danh thống nhất với externalId tài xế・khóa học của Izumi Cloud.
* Đảm bảo tính nhất quán với externalId được sử dụng trong drivers sync và courses sync của IZUMI.

### Loại nhiệm vụ
Chuẩn bị liên kết dữ liệu

### Tài liệu đính kèm

### Tài liệu tham khảo
- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) 5. ATMTC
- [external-integration-spec.md](../docs/external-integration-spec.md) 5.6 Đồng bộ hàng loạt tài xế

### Ghi chú
- Việc gọi thực tế `drivers/sync` và `courses/sync` do Izumi Cloud đảm nhận. ATMTC chỉ cung cấp dữ liệu liên kết. Không liên kết trực tiếp từ ATMTC sang IZUMI.

### UI/UX (nếu có)
* **Thiết kế:** Không
* **Thành phần:** Không

### Người khởi tạo
-

## Implementation Tasks
- [ ] 紐づきデータの提供方式の決定・実装（API/ファイル/DB連携）
- [ ] イズミクラウドの externalId との識別子整合
- [ ] イズミクラウドとの連携仕様のすり合わせ
