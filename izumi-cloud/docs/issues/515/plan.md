# Issue #515: 運転者台帳の追加 - Implementation Plan

## 概要 (Overview)

### 現状 (Current State)
- Employee Master có cơ chế quản lý thông tin nhân viên cơ bản
- Đã có EmployeeContent model để lưu lịch sử thay đổi một số trường (company_car, etc_card, fuel_card, other)
- Đã có File model để lưu trữ file PDF (được sử dụng trong Vehicle Master với file_pdf_id)
- Đã có cơ chế CSV import/export cho Employee (EmployeeImport, ImportDataToTableJob)
- Vehicle Master đã có cơ chế lưu trữ PDF (vehicle_inspection_cert, file_pdf_id)

### 改善後 (Improved State)
- Employee Master có trang quản lý sổ tay lái xe (運転者台帳) cho từng nhân viên
- Có thể xuất/nhập CSV hàng loạt cho sổ tay lái xe
- Có thể lưu trữ PDF cho các loại giấy tờ: giấy khám sức khỏe, giấy phép lái xe, các giấy tờ khác
- Có lịch sử chỉnh sửa (edit history) cho sổ tay lái xe
- Vehicle Master có trang quản lý lịch sử kiểm tra định kỳ (定期点検整備記録簿) với PDF lưu trữ 3 tháng/lần

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: resources/js/pages/EmployeeMaster/detail.vue

##### 1.1.1. Thêm tab "運転者台帳" (Driver Ledger) vào trang chi tiết Employee

**既存コード** (line 1-100):

- Trang detail hiện tại có các tab thông tin nhân viên
- Cần thêm tab mới cho sổ tay lái xe

**変更内容:**

- Thêm tab "運転者台帳" vào component
- Tạo route con hoặc modal để hiển thị trang sổ tay lái xe
- Thêm button "運転者台帳" trong navigation của trang detail

##### 1.1.2. Tạo component hiển thị form sổ tay lái xe

**変更内容:**

- Tạo form với các trường: thông tin giấy phép lái xe, ngày cấp, ngày hết hạn
- Thêm section upload PDF cho: giấy khám sức khỏe, giấy phép lái xe, các giấy tờ khác
- Hiển thị danh sách PDF đã upload với preview và download
- Thêm button "印刷" (Print) để in sổ tay lái xe theo format quy định

##### 1.1.3. Tích hợp lịch sử chỉnh sửa (Edit History)

**変更内容:**

- Thêm section hiển thị lịch sử thay đổi
- Hiển thị bảng lịch sử với: ngày giờ, người thay đổi, trường thay đổi, giá trị cũ/mới
- Tương tự như modal "modal-welfare-expense-history" đã có (line 870-908)

#### 1.2. File: resources/js/pages/EmployeeMaster/index.vue

##### 1.2.1. Thêm button CSV Export cho sổ tay lái xe

**既存コード** (line 60-82):

- Trang index hiện có bảng danh sách employee với các action buttons

**変更内容:**

- Thêm button "CSV出力" (CSV Export) trong toolbar hoặc action menu
- Button này sẽ export tất cả dữ liệu sổ tay lái xe ra CSV
- Sử dụng API endpoint mới: `/api/employee/driver-ledger/export`

##### 1.2.2. Thêm button CSV Import cho sổ tay lái xe

**変更内容:**

- Thêm button "CSV一括入力" (CSV Bulk Import) 
- Tạo modal upload file CSV
- Validate file CSV trước khi upload
- Hiển thị preview và confirm trước khi import
- Sử dụng API endpoint: `/api/employee/driver-ledger/import`

#### 1.3. File: resources/js/pages/EmployeeMaster/DriverLedger.vue (新規作成)

##### 1.3.1. Tạo trang mới cho sổ tay lái xe

**変更内容:**

- Tạo component mới `DriverLedger.vue`
- Layout tương tự trang detail nhưng tập trung vào thông tin sổ tay lái xe
- Form nhập liệu với validation
- Section upload PDF với drag & drop
- Section hiển thị lịch sử PDF đã upload
- Button in ấn với format chuẩn

##### 1.3.2. Tích hợp PDF viewer

**変更内容:**

- Sử dụng PDF.js hoặc iframe để preview PDF
- Button download PDF
- Button xóa PDF (với confirm)

#### 1.4. File: resources/js/pages/VehicleMaster/detail.vue

##### 1.4.1. Thêm tab "定期点検整備記録簿" (Inspection Record)

**変更内容:**

- Thêm tab mới trong trang detail Vehicle
- Hiển thị danh sách lịch sử kiểm tra định kỳ (3 tháng/lần)
- Form thêm mới bản ghi kiểm tra với upload PDF
- Hiển thị timeline hoặc bảng lịch sử theo thời gian

##### 1.4.2. Form thêm bản ghi kiểm tra

**変更内容:**

- Form với các trường: ngày kiểm tra, nội dung kiểm tra, upload PDF
- Validation: đảm bảo không trùng lặp trong vòng 3 tháng
- Hiển thị cảnh báo nếu quá hạn 3 tháng

#### 1.5. File: resources/js/router/modules/masterManager.js

##### 1.5.1. Thêm route cho Driver Ledger

**既存コード** (line 2-655):

- File này quản lý routing cho các trang master

**変更内容:**

- Thêm route: `/employee/:id/driver-ledger` → `EmployeeMaster/DriverLedger.vue`
- Thêm route: `/vehicle/:id/inspection-record` → `VehicleMaster/InspectionRecord.vue` (nếu tạo component riêng)

#### 1.6. File: resources/js/api/modules/employeeMaster.js

##### 1.6.1. Thêm API functions cho Driver Ledger

**変更内容:**

- `getDriverLedger(employeeId)` - Lấy thông tin sổ tay lái xe
- `updateDriverLedger(employeeId, data)` - Cập nhật sổ tay lái xe
- `uploadDriverLedgerDocument(employeeId, file, documentType)` - Upload PDF
- `deleteDriverLedgerDocument(documentId)` - Xóa PDF
- `exportDriverLedgerCSV()` - Export CSV
- `importDriverLedgerCSV(file)` - Import CSV
- `getDriverLedgerHistory(employeeId)` - Lấy lịch sử chỉnh sửa

#### 1.7. File: resources/js/api/modules/vehicleMaster.js

##### 1.7.1. Thêm API functions cho Inspection Record

**変更内容:**

- `getInspectionRecords(vehicleId)` - Lấy danh sách lịch sử kiểm tra
- `createInspectionRecord(vehicleId, data, file)` - Tạo bản ghi mới
- `updateInspectionRecord(recordId, data, file)` - Cập nhật bản ghi
- `deleteInspectionRecord(recordId)` - Xóa bản ghi
- `downloadInspectionRecordPDF(recordId)` - Download PDF

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: database/migrations/XXXX_XX_XX_create_driver_ledgers_table.php (新規作成)

##### 1.1.1. Tạo migration cho bảng driver_ledgers

**変更内容:**

- Tạo bảng `driver_ledgers` với các cột:
  - `id` (bigIncrements)
  - `employee_id` (bigInteger, foreign key to employees)
  - `license_number` (string, nullable) - Số giấy phép lái xe
  - `license_type` (string, nullable) - Loại giấy phép
  - `license_issue_date` (date, nullable) - Ngày cấp
  - `license_expiry_date` (date, nullable) - Ngày hết hạn
  - `health_check_date` (date, nullable) - Ngày khám sức khỏe
  - `health_check_expiry_date` (date, nullable) - Ngày hết hạn khám sức khỏe
  - `aptitude_test_date` (date, nullable) - Ngày kiểm tra năng lực
  - `aptitude_test_expiry_date` (date, nullable) - Ngày hết hạn kiểm tra năng lực
  - `notes` (text, nullable) - Ghi chú
  - `created_at`, `updated_at` (timestamps)
- Thêm index cho `employee_id`

##### 1.1.2. Tạo migration cho bảng driver_ledger_documents

**変更内容:**

- Tạo bảng `driver_ledger_documents` với các cột:
  - `id` (bigIncrements)
  - `driver_ledger_id` (bigInteger, foreign key to driver_ledgers)
  - `file_id` (bigInteger, foreign key to files)
  - `document_type` (string) - Loại giấy tờ: 'health_check', 'license', 'aptitude_test', 'other'
  - `document_name` (string, nullable) - Tên tài liệu
  - `issue_date` (date, nullable) - Ngày cấp
  - `expiry_date` (date, nullable) - Ngày hết hạn
  - `notes` (text, nullable)
  - `created_at`, `updated_at` (timestamps)
- Thêm index cho `driver_ledger_id`, `file_id`

##### 1.1.3. Tạo migration cho bảng driver_ledger_histories

**変更内容:**

- Tạo bảng `driver_ledger_histories` để lưu lịch sử chỉnh sửa:
  - `id` (bigIncrements)
  - `driver_ledger_id` (bigInteger, foreign key to driver_ledgers)
  - `user_id` (bigInteger, foreign key to users)
  - `field_name` (string) - Tên trường thay đổi
  - `old_value` (text, nullable) - Giá trị cũ
  - `new_value` (text, nullable) - Giá trị mới
  - `created_at` (timestamp)
- Thêm index cho `driver_ledger_id`, `user_id`

##### 1.1.4. Tạo migration cho bảng vehicle_inspection_records

**変更内容:**

- Tạo bảng `vehicle_inspection_records`:
  - `id` (bigIncrements)
  - `vehicle_id` (bigInteger, foreign key to vehicles)
  - `inspection_date` (date) - Ngày kiểm tra
  - `inspection_type` (string) - Loại kiểm tra
  - `inspector_name` (string, nullable) - Tên người kiểm tra
  - `result` (text, nullable) - Kết quả kiểm tra
  - `file_id` (bigInteger, foreign key to files, nullable) - PDF bản ghi
  - `notes` (text, nullable)
  - `created_at`, `updated_at` (timestamps)
- Thêm index cho `vehicle_id`, `inspection_date`
- Thêm unique constraint: `vehicle_id` + `inspection_date` (để tránh trùng lặp)

#### 1.2. File: app/Models/DriverLedger.php (新規作成)

##### 1.2.1. Tạo model DriverLedger

**変更内容:**

- Tạo model với relationships:
  - `belongsTo(Employee::class)`
  - `hasMany(DriverLedgerDocument::class)`
  - `hasMany(DriverLedgerHistory::class)`
- Thêm fillable fields
- Thêm casts cho dates

##### 1.2.2. Thêm methods helper

**変更内容:**

- `getLatestHealthCheck()` - Lấy giấy khám sức khỏe mới nhất
- `getLatestLicense()` - Lấy giấy phép lái xe mới nhất
- `isExpiringSoon($days = 30)` - Kiểm tra sắp hết hạn

#### 1.3. File: app/Models/DriverLedgerDocument.php (新規作成)

##### 1.3.1. Tạo model DriverLedgerDocument

**変更内容:**

- Relationships:
  - `belongsTo(DriverLedger::class)`
  - `belongsTo(File::class)`
- Fillable fields
- Constants cho document_type

#### 1.4. File: app/Models/DriverLedgerHistory.php (新規作成)

##### 1.4.1. Tạo model DriverLedgerHistory

**変更内容:**

- Relationships:
  - `belongsTo(DriverLedger::class)`
  - `belongsTo(User::class)`
- Fillable fields

#### 1.5. File: app/Models/VehicleInspectionRecord.php (新規作成)

##### 1.5.1. Tạo model VehicleInspectionRecord

**変更内容:**

- Relationships:
  - `belongsTo(Vehicle::class)`
  - `belongsTo(File::class)`
- Fillable fields
- Validation rules

#### 1.6. File: app/Models/Employee.php

##### 1.6.1. Thêm relationship với DriverLedger

**既存コード** (line 74-97):

- Model đã có các relationships với departments, courses, employeeMobileInfo

**変更内容:**

- Thêm method: `hasOne(DriverLedger::class)`

#### 1.7. File: app/Models/Vehicle.php

##### 1.7.1. Thêm relationship với VehicleInspectionRecord

**既存コード** (line 120-128):

- Model đã có relationship `vehicle_inspection_cert()`

**変更内容:**

- Thêm method: `hasMany(VehicleInspectionRecord::class)`

#### 1.8. File: app/Repositories/DriverLedgerRepository.php (新規作成)

##### 1.8.1. Tạo repository cho DriverLedger

**変更内容:**

- Extend `BaseRepository`
- Implement `DriverLedgerRepositoryInterface`
- Methods:
  - `getByEmployeeId($employeeId)` - Lấy sổ tay lái xe theo employee
  - `createOrUpdate($employeeId, $attributes)` - Tạo hoặc cập nhật
  - `addDocument($driverLedgerId, $fileId, $documentType, $attributes)` - Thêm document
  - `deleteDocument($documentId)` - Xóa document
  - `getHistory($driverLedgerId)` - Lấy lịch sử
  - `logHistory($driverLedgerId, $userId, $fieldName, $oldValue, $newValue)` - Ghi lịch sử

##### 1.8.2. Implement CSV export/import

**変更内容:**

- `exportToCSV($filters = [])` - Export tất cả driver ledgers ra CSV
- `importFromCSV($filePath)` - Import từ CSV file
- Validation và error handling cho CSV import
- Sử dụng `Maatwebsite\Excel` tương tự `EmployeeImport`

#### 1.9. File: app/Repositories/VehicleInspectionRecordRepository.php (新規作成)

##### 1.9.1. Tạo repository cho VehicleInspectionRecord

**変更内容:**

- Methods:
  - `getByVehicleId($vehicleId)` - Lấy danh sách theo vehicle
  - `create($vehicleId, $attributes, $fileId = null)` - Tạo bản ghi mới
  - `update($recordId, $attributes, $fileId = null)` - Cập nhật
  - `delete($recordId)` - Xóa
  - `checkDuplicate($vehicleId, $inspectionDate)` - Kiểm tra trùng lặp
  - `getUpcomingInspections($days = 90)` - Lấy các bản ghi sắp đến hạn

#### 1.10. File: app/Http/Controllers/Api/DriverLedgerController.php (新規作成)

##### 1.10.1. Tạo controller cho Driver Ledger

**変更内容:**

- `show($employeeId)` - GET `/api/employee/{employeeId}/driver-ledger`
- `update(Request $request, $employeeId)` - PUT `/api/employee/{employeeId}/driver-ledger`
- `uploadDocument(Request $request, $employeeId)` - POST `/api/employee/{employeeId}/driver-ledger/documents`
- `deleteDocument($documentId)` - DELETE `/api/employee/driver-ledger/documents/{documentId}`
- `getHistory($employeeId)` - GET `/api/employee/{employeeId}/driver-ledger/history`
- `exportCSV(Request $request)` - GET `/api/employee/driver-ledger/export`
- `importCSV(Request $request)` - POST `/api/employee/driver-ledger/import`

##### 1.10.2. Implement file upload handling

**変更内容:**

- Sử dụng `UploadedFile` để xử lý PDF upload
- Validate file type (chỉ PDF)
- Validate file size (max 10MB)
- Lưu file sử dụng `File` model (tương tự `UploadDataRepository`)
- Trả về file URL và metadata

#### 1.11. File: app/Http/Controllers/Api/VehicleInspectionRecordController.php (新規作成)

##### 1.11.1. Tạo controller cho Inspection Record

**変更内容:**

- `index($vehicleId)` - GET `/api/vehicle/{vehicleId}/inspection-records`
- `store(Request $request, $vehicleId)` - POST `/api/vehicle/{vehicleId}/inspection-records`
- `update(Request $request, $recordId)` - PUT `/api/vehicle/inspection-records/{recordId}`
- `destroy($recordId)` - DELETE `/api/vehicle/inspection-records/{recordId}`
- `downloadPDF($recordId)` - GET `/api/vehicle/inspection-records/{recordId}/download`

#### 1.12. File: app/Http/Requests/DriverLedgerRequest.php (新規作成)

##### 1.12.1. Tạo Request validation

**変更内容:**

- Validation rules cho create/update driver ledger
- Validation cho CSV import
- Custom validation messages (Japanese/Vietnamese)

#### 1.13. File: app/Http/Requests/VehicleInspectionRecordRequest.php (新規作成)

##### 1.13.1. Tạo Request validation

**変更内容:**

- Validation rules cho inspection record
- Validate inspection_date không trùng trong vòng 3 tháng
- Validate file PDF nếu có

#### 1.14. File: app/Imports/DriverLedgerImport.php (新規作成)

##### 1.14.1. Tạo Import class cho CSV

**変更内容:**

- Implement `ToModel`, `WithBatchInserts`, `WithChunkReading`
- Map CSV columns to database fields
- Validation và error handling
- Tương tự `EmployeeImport` (line 1-68)

#### 1.15. File: app/Exports/DriverLedgerExport.php (新規作成)

##### 1.15.1. Tạo Export class cho CSV

**変更内容:**

- Implement `FromView`, `WithCustomCsvSettings`
- Tạo view template cho CSV export
- Encoding: SJIS-win (tương tự `VehicleExport`, `CourseExport`)
- Tương tự `VehicleExport` (line 1-30)

#### 1.16. File: app/Repositories/EmployeeRepository.php

##### 1.16.1. Cập nhật method detail để include DriverLedger

**既存コード** (line 48-420):

- Method `detail()` hiện tại trả về thông tin employee

**変更内容:**

- Thêm `with('driverLedger.documents', 'driverLedger.histories')` trong query
- Đảm bảo trả về đầy đủ thông tin sổ tay lái xe khi get detail

#### 1.17. File: app/Repositories/VehicleRepository.php

##### 1.17.1. Cập nhật method detail để include InspectionRecords

**既存コード** (line 1-39):

- Repository hiện có các methods cho vehicle

**変更内容:**

- Thêm `with('vehicleInspectionRecords.file')` trong query detail
- Hoặc tạo method riêng `getInspectionRecords($vehicleId)`

#### 1.18. File: routes/api.php

##### 1.18.1. Thêm routes cho Driver Ledger

**既存コード** (line 98-101):

- Đã có routes cho employee

**変更内容:**

- Thêm routes:
  ```php
  Route::get('employee/{employeeId}/driver-ledger', 'DriverLedgerController@show');
  Route::put('employee/{employeeId}/driver-ledger', 'DriverLedgerController@update');
  Route::post('employee/{employeeId}/driver-ledger/documents', 'DriverLedgerController@uploadDocument');
  Route::delete('employee/driver-ledger/documents/{documentId}', 'DriverLedgerController@deleteDocument');
  Route::get('employee/{employeeId}/driver-ledger/history', 'DriverLedgerController@getHistory');
  Route::get('employee/driver-ledger/export', 'DriverLedgerController@exportCSV');
  Route::post('employee/driver-ledger/import', 'DriverLedgerController@importCSV');
  ```

##### 1.18.2. Thêm routes cho Vehicle Inspection Record

**変更内容:**

- Thêm routes:
  ```php
  Route::get('vehicle/{vehicleId}/inspection-records', 'VehicleInspectionRecordController@index');
  Route::post('vehicle/{vehicleId}/inspection-records', 'VehicleInspectionRecordController@store');
  Route::put('vehicle/inspection-records/{recordId}', 'VehicleInspectionRecordController@update');
  Route::delete('vehicle/inspection-records/{recordId}', 'VehicleInspectionRecordController@destroy');
  Route::get('vehicle/inspection-records/{recordId}/download', 'VehicleInspectionRecordController@downloadPDF');
  ```

#### 1.19. File: app/Repositories/Contracts/DriverLedgerRepositoryInterface.php (新規作成)

##### 1.19.1. Tạo Interface

**変更内容:**

- Định nghĩa các methods cần thiết cho repository
- Tương tự các interface khác trong project

#### 1.20. File: app/Repositories/Contracts/VehicleInspectionRecordRepositoryInterface.php (新規作成)

##### 1.20.1. Tạo Interface

**変更内容:**

- Định nghĩa các methods cần thiết

#### 1.21. File: app/Providers/RepositoryServiceProvider.php

##### 1.21.1. Đăng ký repositories

**変更内容:**

- Bind `DriverLedgerRepositoryInterface` với `DriverLedgerRepository`
- Bind `VehicleInspectionRecordRepositoryInterface` với `VehicleInspectionRecordRepository`

#### 1.22. File: resources/views/export/driver_ledger.blade.php (新規作成)

##### 1.22.1. Tạo view template cho CSV export

**変更内容:**

- Tạo blade template với bảng dữ liệu driver ledger
- Format tương tự các export view khác
- Encoding UTF-8 với BOM nếu cần

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (必須 - Frontend phụ thuộc)

    - Task 1.1: Tạo database migrations (driver_ledgers, driver_ledger_documents, driver_ledger_histories, vehicle_inspection_records)
    - Task 1.2-1.5: Tạo Models (DriverLedger, DriverLedgerDocument, DriverLedgerHistory, VehicleInspectionRecord)
    - Task 1.6-1.7: Cập nhật Employee và Vehicle models với relationships
    - Task 1.8-1.9: Tạo Repositories (DriverLedgerRepository, VehicleInspectionRecordRepository)
    - Task 1.10-1.11: Tạo Controllers (DriverLedgerController, VehicleInspectionRecordController)
    - Task 1.12-1.13: Tạo Request validations
    - Task 1.14-1.15: Tạo Import/Export classes
    - Task 1.16-1.17: Cập nhật existing repositories
    - Task 1.18: Thêm routes
    - Task 1.19-1.21: Đăng ký interfaces và providers
    - Task 1.22: Tạo export view template

2. **Frontend 実装** (phụ thuộc Backend)

    - Task 1.1-1.3: Cập nhật EmployeeMaster detail và tạo DriverLedger component
    - Task 1.4: Cập nhật VehicleMaster detail với Inspection Record
    - Task 1.5: Thêm routes
    - Task 1.6-1.7: Thêm API functions
    - Task 1.2: Thêm CSV export/import buttons vào EmployeeMaster index

3. **統合テスト**
    - Test CSV export/import với dữ liệu lớn
    - Test PDF upload/download
    - Test edit history tracking
    - Test validation và error handling
    - Test duplicate prevention cho inspection records

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 25-35 時間

    - Database migrations: 2-3 時間
    - Models và relationships: 2-3 時間
    - Repositories: 4-6 時間
    - Controllers và validation: 4-5 時間
    - CSV import/export: 3-4 時間
    - PDF upload handling: 2-3 時間
    - Edit history tracking: 2-3 時間
    - Routes và service providers: 1-2 時間
    - Testing và debugging: 5-6 時間

- **Frontend**: 20-28 時間
    - Driver Ledger component: 6-8 時間
    - Vehicle Inspection Record component: 4-6 時間
    - CSV export/import UI: 3-4 時間
    - PDF upload/preview: 3-4 時間
    - Edit history display: 2-3 時間
    - Routing và navigation: 1-2 時間
    - Testing và UI polish: 1-3 時間

**合計**: 45-63 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

    - CSV export có thể chậm với dữ liệu lớn → cân nhắc sử dụng Queue Job
    - PDF storage cần optimize để tránh tốn dung lượng → implement file cleanup cho expired files
    - Index database cho các foreign keys và search fields
    - Cache driver ledger data nếu cần thiết

2. **UX 考慮:**

    - CSV import cần hiển thị progress bar và error summary
    - PDF preview nên có loading state
    - Validation messages cần rõ ràng (Japanese/Vietnamese)
    - Confirmation dialogs cho các thao tác xóa

3. **データ整合性:**

    - Foreign key constraints trong database
    - Transaction cho CSV import để đảm bảo atomicity
    - Soft delete cho documents để có thể recover
    - Validation duplicate inspection records (3 tháng/lần)

4. **既存機能との互換性:**
    - Đảm bảo không ảnh hưởng đến Employee Master hiện tại
    - Tái sử dụng File model và storage mechanism đã có
    - Follow pattern của EmployeeContent cho edit history
    - Sử dụng cùng encoding (SJIS-win) cho CSV như các export khác

5. **セキュリティ考慮:**
    - Validate file type và size cho PDF upload
    - Sanitize CSV input để tránh injection
    - Permission check cho các operations
    - Audit log cho các thay đổi quan trọng











