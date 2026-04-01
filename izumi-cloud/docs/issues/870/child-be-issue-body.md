## 日本語 / Japanese

### 親Issue
Parent: #870

### 説明
従業員マスタDB詳細機能の一環として、健康診断結果通知書のファイル履歴を1件削除するAPIを追加する。健康診断タブで誤アップロードしたPDFを履歴から削除できるようにするためのバックエンド実装である。

### 要件
- DELETE エンドポイント `DELETE /api/employee/health-examination-results/file-history/{id}` を新規追加する。
- `health_examination_results_file_history` の id でレコードを取得し、削除する。
- 詳細APIで返す fileHistory に `id` を含め、フロントで削除対象を指定できるようにする。
- 実装・単体テストまでをスコープとする。

### 技術詳細
- **EmployeeRepository.php**: (1) `healthExaminationResults.fileHistory` の eager load で select に `id` を追加（306-307行付近）。(2) `deleteHealthExaminationFileHistory($id)` を追加。`HealthExaminationResultsFileHistory::findOrFail($id)` で取得し `delete()`。
- **EmployeeController.php**: `deleteHealthExaminationFileHistory($id)` を追加。Repository を呼び、成功時 200、失敗時 400。
- **routes/api.php**: `Route::delete('employee/health-examination-results/file-history/{id}', "EmployeeController@deleteHealthExaminationFileHistory");` を employee グループ内に追加。

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
なし（単体で開発可能）

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #870

### Mô tả
Trong khuôn khổ tính năng màn hình chi tiết nhân viên master DB, thêm API xóa một bản ghi lịch sử file kết quả khám sức khỏe. Đây là phần backend để cho phép xóa PDF tải nhầm khỏi lịch sử tại tab khám sức khỏe.

### Yêu cầu
- Thêm endpoint DELETE: `DELETE /api/employee/health-examination-results/file-history/{id}`.
- Lấy bản ghi theo id của `health_examination_results_file_history` và xóa.
- API chi tiết trả về fileHistory phải có trường `id` để frontend chỉ định bản ghi cần xóa.
- Phạm vi: triển khai và unit test.

### Chi tiết kỹ thuật
- **EmployeeRepository.php**: (1) Trong eager load `healthExaminationResults.fileHistory` thêm `id` vào select (khoảng dòng 306-307). (2) Thêm `deleteHealthExaminationFileHistory($id)`: `HealthExaminationResultsFileHistory::findOrFail($id)` rồi `delete()`.
- **EmployeeController.php**: Thêm `deleteHealthExaminationFileHistory($id)`, gọi Repository, trả 200 khi thành công, 400 khi thất bại.
- **routes/api.php**: Thêm `Route::delete('employee/health-examination-results/file-history/{id}', "EmployeeController@deleteHealthExaminationFileHistory");` trong nhóm employee.

### Tiêu chí chấp nhận
- [ ] Hoàn thành triển khai
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Không (có thể phát triển độc lập)
