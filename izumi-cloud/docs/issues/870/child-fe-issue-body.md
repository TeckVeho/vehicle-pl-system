## 日本語 / Japanese

### 親Issue
Parent: #870

### 説明
従業員マスタDB詳細画面で、(1) 健康診断受診日欄をクリックすると受診履歴の日付一覧をモーダル表示し、日付選択で該当PDFを表示する。(2) 健康診断結果通知書タブの履歴テーブルに削除ボタンを追加し、誤アップロードしたPDFを削除できるようにする。実装・単体テストまでをスコープとする。

### 要件
- 詳細画面（detail.vue）: 健康診断受診日をクリック可能にし、モーダルで日付一覧（最大10件）を表示。日付選択でPDF表示（iframe または ModalViewPDF）。
- 健康診断タブ（HealthExam.vue）: 履歴テーブルの操作列に削除ボタン追加。確認ダイアログ後、DELETE API を呼び、成功時に一覧を再取得。
- API クライアント（employeeMaster.js）: 健康診断ファイル履歴削除用の DELETE 関数を追加。

### 技術詳細
- **detail.vue**: data に `healthExaminationFileHistory`, `showModalHealthExamDates`。getEmployeeDetailData で `health_examination_results[0].file_history` を保存。141-153行付近の受診日欄をクリック可能にし、b-modal で日付一覧＋PDF表示。
- **HealthExam.vue**: updateItemsFromData で items に `id` を含める。#cell(operation) に削除ボタン。handleDeleteFileHistory で確認→DELETE `/employee/health-examination-results/file-history/{id}`→emit('update-success')。
- **employeeMaster.js**: `deleteHealthExaminationFileHistory(url)` を追加（RequestApi.deleteOne）。

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
BE issue（健康診断ファイル履歴削除API）の完了後、統合テスト可能。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #870

### Mô tả
Trên màn hình chi tiết nhân viên master DB: (1) Khi click vào ô ngày khám sức khỏe thì hiển thị modal danh sách ngày khám, chọn ngày thì hiển thị PDF tương ứng. (2) Tab kết quả khám sức khỏe: thêm nút xóa trong bảng lịch sử để xóa PDF tải nhầm. Phạm vi: triển khai và unit test.

### Yêu cầu
- Màn hình chi tiết (detail.vue): Ô ngày khám sức khỏe có thể click, mở modal danh sách ngày (tối đa 10). Chọn ngày thì hiển thị PDF (iframe hoặc ModalViewPDF).
- Tab khám sức khỏe (HealthExam.vue): Thêm nút xóa ở cột thao tác trong bảng lịch sử. Sau khi xác nhận, gọi DELETE API, thành công thì tải lại danh sách.
- API client (employeeMaster.js): Thêm hàm DELETE cho xóa lịch sử file khám sức khỏe.

### Chi tiết kỹ thuật
- **detail.vue**: data thêm `healthExaminationFileHistory`, `showModalHealthExamDates`. Trong getEmployeeDetailData lưu `health_examination_results[0].file_history`. Ô ngày khám (khoảng dòng 141-153) cho phép click, b-modal hiển thị danh sách ngày và PDF.
- **HealthExam.vue**: updateItemsFromData map items có `id`. #cell(operation) thêm nút xóa. handleDeleteFileHistory: xác nhận → DELETE `/employee/health-examination-results/file-history/{id}` → emit('update-success').
- **employeeMaster.js**: Thêm `deleteHealthExaminationFileHistory(url)` (RequestApi.deleteOne).

### Tiêu chí chấp nhận
- [ ] Hoàn thành triển khai
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Sau khi hoàn thành BE issue (API xóa lịch sử file khám sức khỏe) có thể kiểm tra tích hợp.
