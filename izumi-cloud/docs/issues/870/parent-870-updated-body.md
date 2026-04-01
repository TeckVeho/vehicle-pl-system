# 従業員マスタ_DB詳細画面にPDF表示機能追加

## 1. 概要 (Overview)

### 背景 (Background)

* **現状の課題:** イズミクラウドの従業員マスタ・基本情報部分についてのフィードバックがある

* **ビジネス要求:** 従業員の健康診断受診履歴を簡単に確認できるようにし、誤ってアップロードしたPDFを削除できる機能を追加する。

* **ユーザーストーリー:** ユーザーとして、私は健康診断受診日をクリックしてPDFデータを表示したい。また、誤ってアップロードしたPDFを削除したい。

### 達成目標 (Goal)
* **あるべき姿:** 従業員マスタ　基本情報FB
  - 健康診断受診日欄をクリックすることで受診履歴の日付一覧が出てきて、その中から任意の日付を選択すると対象のPDFデータを表示させたい。※保存期間５年、年2回受診が基本なので10回分の履歴が対象。
  - PDF関連全般の削除機能を追加し、誤ってアップロードしたPDFを履歴から削除できるようにしたい。

* **完了条件 (Definition of Done):**
  * [ ] 健康診断受診日をクリックした際に受診履歴の日付一覧が表示されること。
  * [ ] 任意の日付を選択すると、対象のPDFデータが表示されること。
  * [ ] PDFの保存期間が5年であることを確認すること。
  * [ ] PDF削除機能が正常に動作することを確認すること。
  * [ ] ユーザーが誤ってアップロードしたPDFを履歴から削除できることを確認すること。

---

## 2. 仕様 (Specification)

### 機能要件 (Functional Requirements)
* 健康診断受診日欄をクリックした際、システムは受診履歴の日付一覧を表示する。
* ユーザーが任意の日付を選択すると、システムはその日付に関連するPDFデータを表示する。
* PDFデータは最大10回分、保存期間は5年とする。
* ユーザーがPDF削除機能を使用した場合、システムは選択されたPDFを履歴から削除する。

### タスクタイプ
要件

### 添付ファイル


### 参考資料
-

### メモ
-

### UI/UX (あれば)
* **デザイン:**
* **コンポーネント:**

### 起票者
Đào Thị Thư

---

# Thêm chức năng hiển thị PDF trên màn hình chi tiết nhân viên master_DB

## 1. Tổng quan (Overview)

### Bối cảnh (Background)

* **Vấn đề hiện tại:** Có phản hồi về phần thông tin cơ bản của nhân viên master trên Izumi Cloud.

* **Yêu cầu kinh doanh:** Cần thêm chức năng để người dùng có thể dễ dàng kiểm tra lịch sử khám sức khỏe của nhân viên và xóa các PDF đã tải lên nhầm.

* **Câu chuyện người dùng:** Là người dùng, tôi muốn nhấp vào ngày khám sức khỏe để hiển thị dữ liệu PDF. Ngoài ra, tôi muốn xóa các PDF đã tải lên nhầm.

### Mục tiêu đạt được (Goal)
* **Hình ảnh lý tưởng:** Nhân viên master thông tin cơ bản FB
  - Khi nhấp vào cột ngày khám sức khỏe, danh sách ngày của lịch sử khám sẽ hiển thị, và khi chọn một ngày bất kỳ, dữ liệu PDF tương ứng sẽ được hiển thị. ※ Thời gian lưu trữ là 5 năm, với 2 lần khám mỗi năm, do đó có tối đa 10 lần lịch sử.
  - Thêm chức năng xóa liên quan đến PDF, cho phép xóa các PDF đã tải lên nhầm từ lịch sử.

* **Điều kiện hoàn thành (Definition of Done):**
  * [ ] Khi nhấp vào ngày khám sức khỏe, danh sách ngày của lịch sử khám sẽ được hiển thị.
  * [ ] Khi chọn một ngày bất kỳ, dữ liệu PDF tương ứng sẽ được hiển thị.
  * [ ] Xác nhận thời gian lưu trữ PDF là 5 năm.
  * [ ] Xác nhận chức năng xóa PDF hoạt động bình thường.
  * [ ] Xác nhận người dùng có thể xóa PDF đã tải lên nhầm từ lịch sử.

---

## 2. Thông số kỹ thuật (Specification)

### Yêu cầu chức năng (Functional Requirements)
* Khi nhấp vào cột ngày khám sức khỏe, hệ thống sẽ hiển thị danh sách ngày của lịch sử khám.
* Khi người dùng chọn một ngày bất kỳ, hệ thống sẽ hiển thị dữ liệu PDF liên quan đến ngày đó.
* Dữ liệu PDF tối đa là 10 lần, thời gian lưu trữ là 5 năm.
* Khi người dùng sử dụng chức năng xóa PDF, hệ thống sẽ xóa PDF đã chọn khỏi lịch sử.

### Loại tác vụ
Yêu cầu

### Tài liệu đính kèm


### Tài liệu tham khảo
-

### Ghi chú
-

### UI/UX (nếu có)
* **Thiết kế:**
* **Thành phần:**

### Người khởi tạo
Đào Thị Thư

---

## Implementation Tasks
- [ ] https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/876 (SP: 2)
- [ ] https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/877 (SP: 4)
