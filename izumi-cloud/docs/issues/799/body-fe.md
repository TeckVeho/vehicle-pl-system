## 日本語 / Japanese

### 親Issue
Parent: #799

### 説明
従業員台帳詳細（ドキュメント）ページに「PDF保管データ」セクションを追加するフロントエンド実装。アップロード・一覧表示・表示/振り分け/削除のUI、振り分けモーダル（分類先・種別・受診日）、APIクライアント、i18nを含む。実装完了後は単体テストを作成する。

### 要件
- document.vue のタブ上に PDF保管セクションを追加し、PdfUploadSection コンポーネントを組み込む
- PdfUploadSection: 一覧（ファイル名・アップロード日時・表示/振り分け/削除）、アップロードボタン、振り分けモーダル（運転免許証・運転記録証明書・適性診断票・健康診断結果通知書。適性は種別・受診日、健康診断は受診日必須）、削除確認モーダル
- employeeMaster API モジュールに getEmployeePdfUploads / addEmployeePdfUpload / classifyEmployeePdfUpload / deleteEmployeePdfUpload を追加
- ja.js / en.js 等に「PDF保管データ」「振り分け」「分類先」等のキーを追加
- 振り分け成功時はタブデータを再取得（handleRefreshData 相当）
- 単体テストの作成・合格

### 技術詳細
- resources/js/pages/EmployeeMaster/document.vue: タブ直前にブロック追加、PdfUploadSection に employee-id 渡す、@update-success で再取得
- resources/js/pages/EmployeeMaster/components/PdfUploadSection.vue: 新規。既存 AptitudeTest.vue / HealthExam.vue のアップロード・日付・PDF表示を参考
- resources/js/api/modules/employeeMaster.js: 4 API メソッド追加。GET /employee/{id}/pdf-uploads, POST /employee/{id}/pdf-uploads, POST classify, DELETE
- resources/js/lang/subs/ja.js, en.js: ラベル追加

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
BE issue 完了後が統合テストに望ましい（API 利用のため）。UI 実装は並行可能。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #799

### Mô tả
Triển khai frontend: thêm section 「PDF保管データ」vào trang chi tiết employee master (document): UI upload, danh sách, xem / phân loại / xóa, modal phân loại (loại, 種別, 受診日), API client, i18n. Sau khi triển khai xong thì viết unit test.

### Yêu cầu
- Trên document.vue, thêm section PDF phía trên tabs và nhúng component PdfUploadSection
- PdfUploadSection: bảng (tên file, thời gian upload, nút xem / 振り分け / xóa), nút upload, modal 振り分け (4 loại; 適性診断票 nhập 種別 + 受診日, 健康診断結果通知書 nhập 受診日), modal xác nhận xóa
- Trong module API employeeMaster thêm getEmployeePdfUploads, addEmployeePdfUpload, classifyEmployeePdfUpload, deleteEmployeePdfUpload
- Thêm key i18n trong ja.js, en.js: 「PDF保管データ」「振り分け」「分類先」...
- Khi phân loại thành công, gọi lại dữ liệu tab (tương đương handleRefreshData)
- Tạo và đạt unit test

### Chi tiết kỹ thuật
- resources/js/pages/EmployeeMaster/document.vue: thêm block trước tabs, truyền employee-id vào PdfUploadSection, @update-success để refresh
- resources/js/pages/EmployeeMaster/components/PdfUploadSection.vue: tạo mới. Tham khảo AptitudeTest.vue, HealthExam.vue (upload, ngày, xem PDF)
- resources/js/api/modules/employeeMaster.js: thêm 4 method API
- resources/js/lang/subs/ja.js, en.js: thêm label

### Tiêu chí chấp nhận
- [ ] Hoàn thành việc triển khai
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Nên có BE issue hoàn thành để test tích hợp; có thể làm song song phần UI.
