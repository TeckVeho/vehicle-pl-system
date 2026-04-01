## 日本語 / Japanese

### 親Issue
Parent: #799

### 説明
従業員台帳詳細ページ用の「PDF保管データ」機能のバックエンド実装。未分類PDFを一時保存するテーブル・API（一覧・登録・振り分け・削除）および既存の運転免許証・運転記録証明書・適性診断票・健康診断結果への振り分け処理を実装する。

### 要件
- employee_pdf_uploads テーブルのマイグレーション作成（employee_id, file_id, user_id）
- EmployeePdfUpload モデルの作成とリレーション定義
- EmployeeRepository に getEmployeePdfUploads / addEmployeePdfUpload / deleteEmployeePdfUpload / classifyEmployeePdfUpload を追加
- EmployeeController に GET/POST pdf-uploads、POST classify、DELETE エンドポイントを追加
- routes/api.php に上記ルートを追加
- 振り分け時は既存の addDrivingRecordCertificate / addAptitudeAssessmentForm / addHealthExaminationResults 等を利用
- 単体テストの作成・合格

### 技術詳細
- Migration: database/migrations/ に employee_pdf_uploads 作成
- Model: app/Models/EmployeePdfUpload.php（employee, file リレーション）
- Repository: app/Repositories/EmployeeRepository.php に4メソッド追加。classify は category に応じて既存 addXxx を呼び、成功後に employee_pdf_uploads から削除
- Controller: app/Http/Controllers/Api/EmployeeController.php。Request で file_id, category, type, date_of_visit をバリデーション
- 既存の /employee/upload-file はそのまま利用し、返却された file_id を POST /employee/{id}/pdf-uploads で紐付ける

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
なし（先行して開発可能）

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #799

### Mô tả
Triển khai backend cho tính năng 「PDF保管データ」trên trang chi tiết employee master: bảng lưu PDF chưa phân loại, API (danh sách, thêm, phân loại, xóa) và xử lý phân loại vào 運転免許証 / 運転記録証明書 / 適性診断票 / 健康診断結果通知書.

### Yêu cầu
- Tạo migration bảng employee_pdf_uploads (employee_id, file_id, user_id)
- Tạo model EmployeePdfUpload và quan hệ employee, file
- Thêm vào EmployeeRepository: getEmployeePdfUploads, addEmployeePdfUpload, deleteEmployeePdfUpload, classifyEmployeePdfUpload
- Thêm vào EmployeeController: endpoint GET/POST pdf-uploads, POST classify, DELETE
- Thêm route tương ứng vào routes/api.php
- Khi phân loại, tái sử dụng addDrivingRecordCertificate / addAptitudeAssessmentForm / addHealthExaminationResults
- Tạo và đạt unit test

### Chi tiết kỹ thuật
- Migration: database/migrations/ tạo employee_pdf_uploads
- Model: app/Models/EmployeePdfUpload.php (quan hệ employee, file)
- Repository: app/Repositories/EmployeeRepository.php thêm 4 method. classify gọi addXxx theo category, sau đó xóa bản ghi trong employee_pdf_uploads
- Controller: app/Http/Controllers/Api/EmployeeController.php. Request validate file_id, category, type, date_of_visit
- Giữ nguyên /employee/upload-file, dùng file_id trả về gửi qua POST /employee/{id}/pdf-uploads

### Tiêu chí chấp nhận
- [ ] Hoàn thành việc triển khai
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Không (có thể làm trước)
