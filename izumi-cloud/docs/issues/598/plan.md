# Issue #598: Department master export function - Implementation Plan

## 概要 (Overview)

**Current State:**
Department master màn hình hiện tại chỉ hỗ trợ hiển thị danh sách departments và các chức năng CRUD cơ bản. Không có chức năng export dữ liệu ra file CSV.

**Improved State:**
Thêm chức năng export CSV cho màn hình department master, cho phép người dùng tải xuống danh sách departments với đầy đủ thông tin theo format CSV với encoding phù hợp.

**Issue:** #598  
**Scope:** Backend Only  
**Type:** Feature Enhancement

---

## Backend Implementation

### 1. Files cần tạo/chỉnh sửa:

#### 1.1. File: `app/Exports/DepartmentExport.php` (Tạo mới)

Tạo class Export để xử lý việc export department data ra CSV format.

**Implementation:**

```php
<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class DepartmentExport implements FromView, WithCustomCsvSettings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('export.department', ["data" => $this->data]);
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => false,
            'output_encoding' => 'SJIS-win',
        ];
    }
}
```

**Chi tiết:**
- Implement `FromView`: Sử dụng Blade view để format CSV
- Implement `WithCustomCsvSettings`: Cấu hình encoding cho CSV (SJIS-win để hiển thị tiếng Nhật đúng)
- Constructor nhận `$data` là danh sách departments
- Method `view()` trả về view blade template
- Method `getCsvSettings()` cấu hình CSV encoding

#### 1.2. File: `resources/views/export/department.blade.php` (Tạo mới)

Tạo Blade template để format dữ liệu department thành CSV format.

**Implementation:**

```php
<?php
?>
<style type="text/css">.ritz .waffle a {
    color: inherit;
}

.ritz .waffle .s0 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #073763;
    text-align: left;
    color: #ffffff;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s2 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: left;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}

.ritz .waffle .s3 {
    border-bottom: 1px SOLID #000000;
    border-right: 1px SOLID #000000;
    background-color: #ffffff;
    text-align: right;
    color: #000000;
    font-family: 'Arial';
    font-size: 10pt;
    vertical-align: bottom;
    white-space: nowrap;
    direction: ltr;
    padding: 2px 3px 2px 3px;
}
</style>
<div class="ritz grid-container" dir="ltr">
    <table class="waffle" cellspacing="0" cellpadding="0">
        <thead>
        <tr style="height: 20px">
            <th class="s0">ID</th>
            <th class="s0">部署名</th>
            <th class="s0">位置</th>
            <th class="s0">住所</th>
            <th class="s0">都道府県名</th>
            <th class="s0">面接住所</th>
            <th class="s0">面接住所URL</th>
            <th class="s0">面接担当者</th>
            <th class="s0">郵便番号</th>
            <th class="s0">電話番号</th>
            <th class="s0">事務所名</th>
            <th class="s0">事務所場所</th>
            <th class="s0">事務所面積</th>
            <th class="s0">休憩室面積</th>
            <th class="s0">ガレージ場所1</th>
            <th class="s0">ガレージ面積1</th>
            <th class="s0">ガレージ場所2</th>
            <th class="s0">ガレージ面積2</th>
            <th class="s0">運営管理者</th>
            <th class="s0">運営管理補助</th>
            <th class="s0">保守管理者</th>
            <th class="s0">保守管理補助</th>
            <th class="s0">保守管理電話番号</th>
            <th class="s0">保守管理FAX番号</th>
            <th class="s0">トラック協会会員番号</th>
            <th class="s0">Gマーク番号</th>
            <th class="s0">Gマーク有効期限</th>
            <th class="s0">IT点呼</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $department)
            <tr style="height: 20px">
                <td class="s3">{{ $department->id ?? '' }}</td>
                <td class="s2">{{ $department->name ?? '' }}</td>
                <td class="s3">{{ $department->position ?? '' }}</td>
                <td class="s2">{{ $department->address ?? '' }}</td>
                <td class="s2">{{ $department->province_name ?? '' }}</td>
                <td class="s2">{{ $department->interview_address ?? '' }}</td>
                <td class="s2">{{ $department->interview_address_url ?? '' }}</td>
                <td class="s2">{{ $department->interview_pic ?? '' }}</td>
                <td class="s3">{{ $department->post_code ?? '' }}</td>
                <td class="s3">{{ $department->tel ?? '' }}</td>
                <td class="s2">{{ $department->office_name ?? '' }}</td>
                <td class="s2">{{ $department->office_location ?? '' }}</td>
                <td class="s2">{{ $department->office_area ?? '' }}</td>
                <td class="s2">{{ $department->rest_room_area ?? '' }}</td>
                <td class="s2">{{ $department->garage_location_1 ?? '' }}</td>
                <td class="s2">{{ $department->garage_area_1 ?? '' }}</td>
                <td class="s2">{{ $department->garage_location_2 ?? '' }}</td>
                <td class="s2">{{ $department->garage_area_2 ?? '' }}</td>
                <td class="s2">{{ $department->operations_manager_appointment ?? '' }}</td>
                <td class="s2">{{ $department->operations_manager_assistant ?? '' }}</td>
                <td class="s2">{{ $department->maintenance_manager_appointment ?? '' }}</td>
                <td class="s2">{{ $department->maintenance_manager_assistant ?? '' }}</td>
                <td class="s3">{{ $department->maintenance_manager_phone_number ?? '' }}</td>
                <td class="s3">{{ $department->maintenance_manager_fax_number ?? '' }}</td>
                <td class="s2">{{ $department->truck_association_membership_number ?? '' }}</td>
                <td class="s2">{{ $department->g_mark_number ?? '' }}</td>
                <td class="s2">{{ $department->g_mark_expiration_date ?? '' }}</td>
                <td class="s3">{{ $department->it_roll_call ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
```

**Chi tiết:**
- Header row với các cột tiếng Nhật theo thứ tự fields trong Department model
- Data rows: Lặp qua `$data` và hiển thị từng field
- Sử dụng `?? ''` để xử lý null values
- Format tương tự các export view khác (vehicle, course) để đảm bảo consistency
- CSS styling giống các export view khác

**Các field cần export:**
- id, name, position, address, province_name
- interview_address, interview_address_url, interview_pic
- post_code, tel
- office_name, office_location, office_area, rest_room_area
- garage_location_1, garage_area_1, garage_location_2, garage_area_2
- operations_manager_appointment, operations_manager_assistant
- maintenance_manager_appointment, maintenance_manager_assistant
- maintenance_manager_phone_number, maintenance_manager_fax_number
- truck_association_membership_number, g_mark_number, g_mark_expiration_date
- it_roll_call

#### 1.3. File: `app/Http/Controllers/Api/DepartmentController.php` (Chỉnh sửa)

Thêm method `exportCsv` để xử lý request export CSV.

**Changes:**

##### 1.3.1. Thêm use statements

**Vị trí:** Sau dòng 17 (sau các use statements hiện tại)

```php
use App\Exports\DepartmentExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
```

##### 1.3.2. Thêm method exportCsv

**Vị trí:** Sau method `listAll()` (sau dòng 308)

```php
    /**
     * @OA\Get(
     *   path="/api/department/export",
     *   tags={"Department"},
     *   summary="Export Department CSV",
     *   operationId="department_export_csv",
     *   @OA\Response(
     *     response=200,
     *     description="Export CSV success",
     *     @OA\MediaType(
     *      mediaType="application/octet-stream",
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Login false",
     *     @OA\MediaType(
     *      mediaType="application/json",
     *      example={"code":401,"message":"Username or password invalid"}
     *     )
     *   ),
     *   security={{"auth": {}}},
     * )
     * Export departments to CSV file.
     *
     * @param DepartmentRequest $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportCsv(DepartmentRequest $request)
    {
        $data = $this->repository->index();
        $fileName = 'department_' . Carbon::now()->format('Y_m_d') . '.csv';
        return Excel::download(
            new DepartmentExport($data),
            $fileName,
            null,
            [
                'Content-Type' => 'application/octet-stream; charset=SJIS-win',
                'Content-Transfer-Encoding' => 'Binary',
                'Charset' => 'SJIS-win'
            ]
        );
    }
```

**Chi tiết:**
- Method name: `exportCsv`
- Request validation: Sử dụng `DepartmentRequest` (có thể cần thêm rules nếu cần filter)
- Lấy data: Gọi `$this->repository->index()` để lấy danh sách departments đã sắp xếp theo position
- File name: Format `department_YYYY_MM_DD.csv` (ví dụ: `department_2026_01_08.csv`)
- Response: Sử dụng `Excel::download()` với encoding SJIS-win
- OpenAPI documentation: Thêm @OA annotations để document API

#### 1.4. File: `routes/api.php` (Chỉnh sửa)

Thêm route cho export CSV endpoint.

**Changes:**

**Vị trí:** Trong group middleware `auth:api`, sau dòng 95 (sau route `apiResource('department', 'DepartmentController')`) hoặc cùng vị trí với các route department khác

```php
        Route::get('department/export', 'DepartmentController@exportCsv');
```

**Chi tiết:**
- Route method: GET (giống với vehicle export)
- Path: `/api/department/export`
- Middleware: `auth:api` (yêu cầu authentication)
- Controller method: `DepartmentController@exportCsv`

#### 1.5. File: `app/Http/Requests/DepartmentRequest.php` (Optional - Chỉnh sửa nếu cần)

Nếu cần hỗ trợ filter khi export (ví dụ: export theo department cụ thể), có thể thêm validation rules.

**Tạm thời không cần thay đổi** vì yêu cầu là export toàn bộ danh sách departments.

Nếu sau này cần filter, có thể thêm vào `rules()` method:

```php
case 'exportCsv':
    return [
        'department_ids' => 'nullable|array',
        'department_ids.*' => 'exists:departments,id',
    ];
```

---

## 実装順序 (Implementation Order)

### Backend Implementation (1-2 giờ)

**Phase 1: Tạo Export class và View (30 phút)**
1. Task 1.1: Tạo `app/Exports/DepartmentExport.php`
2. Task 1.2: Tạo `resources/views/export/department.blade.php`

**Phase 2: Controller và Route (20 phút)**
3. Task 1.3.1: Thêm use statements vào DepartmentController
4. Task 1.3.2: Thêm method `exportCsv` vào DepartmentController
5. Task 1.4: Thêm route vào `routes/api.php`

**Phase 3: Testing và Validation (30 phút)**
6. Test API endpoint với Postman/curl
7. Verify CSV file được download đúng format
8. Verify encoding SJIS-win hiển thị đúng tiếng Nhật
9. Verify tất cả fields được export đầy đủ
10. Verify authentication middleware hoạt động đúng

---

## 見積もり工数 (Estimated Effort)

### Backend: 1-2 giờ

- **Export Class và View**: 30 phút
  - Tạo DepartmentExport class: 10 phút
  - Tạo Blade view template: 20 phút
  
- **Controller và Route**: 20 phút
  - Thêm use statements: 5 phút
  - Implement exportCsv method: 10 phút
  - Thêm route: 5 phút

- **Testing và Validation**: 30 phút
  - API endpoint testing: 15 phút
  - CSV format verification: 10 phút
  - Encoding verification: 5 phút

**合計**: 1-2 giờ

---

## 技術的な注意事項 (Technical Notes)

### 1. Encoding và CSV Format:

**SJIS-win Encoding:**
- Sử dụng `SJIS-win` encoding để đảm bảo tiếng Nhật hiển thị đúng trong Excel
- Pattern này đã được sử dụng trong các export khác (VehicleExport, CourseExport)
- `use_bom => false`: Không sử dụng BOM (Byte Order Mark)

**CSV Structure:**
- Header row với tiếng Nhật để dễ đọc
- Data rows: Tất cả các fields từ Department model
- Null values: Sử dụng `?? ''` để tránh lỗi khi field null

### 2. Performance Considerations:

**Data Loading:**
- Sử dụng `repository->index()` đã có sẵn để lấy data
- Method này đã có `orderBy('position', 'ASC')` để sắp xếp
- Không cần pagination vì export toàn bộ danh sách

**Memory Usage:**
- Với số lượng departments không quá lớn, việc load toàn bộ vào memory là acceptable
- Nếu sau này cần optimize cho số lượng lớn, có thể sử dụng chunk query

### 3. Consistency với các Export khác:

**Pattern Matching:**
- Export class structure giống `VehicleExport`, `CourseExport`
- View template format giống `course.blade.php`
- Controller method pattern giống `VehicleController::downloadVehicle()`
- Route pattern: GET method, path `/api/{resource}/export`

**Naming Convention:**
- File name: `{resource}_{date}.csv`
- Date format: `Y_m_d` (ví dụ: `2026_01_08`)

### 4. Authentication và Authorization:

**Middleware:**
- Sử dụng `auth:api` middleware giống các API khác
- Đảm bảo chỉ authenticated users mới có thể export

**Permissions:**
- Hiện tại không có permission check riêng
- Nếu cần, có thể thêm permission middleware sau

### 5. Error Handling:

**Exception Handling:**
- Maatwebsite\Excel sẽ tự động handle các exception
- Nếu cần custom error handling, có thể wrap trong try-catch

**Validation:**
- Hiện tại không có request validation riêng cho export
- Nếu cần filter/search, có thể thêm vào DepartmentRequest

---

## Testing Checklist

### Unit Testing
- [ ] DepartmentExport class được tạo đúng với FromView và WithCustomCsvSettings
- [ ] View template render đúng với data sample
- [ ] CSV settings (encoding) được cấu hình đúng

### Integration Testing
- [ ] API endpoint `/api/department/export` trả về file CSV
- [ ] File CSV có tên đúng format `department_YYYY_MM_DD.csv`
- [ ] File CSV có encoding SJIS-win
- [ ] Tất cả fields được export đầy đủ
- [ ] Data trong CSV đúng với database
- [ ] Authentication middleware hoạt động đúng (401 nếu chưa login)

### Manual Testing
- [ ] Download CSV file và mở bằng Excel
- [ ] Verify tiếng Nhật hiển thị đúng (không bị mojibake)
- [ ] Verify tất cả columns có header đúng
- [ ] Verify data trong mỗi row đúng với database
- [ ] Verify null values được xử lý đúng (hiển thị empty string)

### Regression Testing
- [ ] Các chức năng department khác vẫn hoạt động bình thường
- [ ] Không ảnh hưởng đến các export khác
- [ ] Không ảnh hưởng đến repository methods

---

## Dependencies

### Internal Dependencies:
- **DepartmentRepository**: Method `index()` để lấy danh sách departments
- **Department Model**: Các fields trong `$fillable` array
- **Maatwebsite\Excel**: Package đã được cài đặt (dùng cho các export khác)

### External Libraries:
- **Laravel Excel (Maatwebsite\Excel)**: Đã có sẵn trong project
- **Carbon**: Đã có sẵn trong Laravel

---

## Acceptance Criteria Review

✅ **Requirements from Issue #598:**

1. ✅ Export CSV functionality
   - Method `exportCsv` trong DepartmentController
   - Route `/api/department/export`
   
2. ✅ CSV Format
   - Header row với tiếng Nhật
   - Tất cả fields từ Department model
   - Encoding SJIS-win để hiển thị đúng tiếng Nhật
   
3. ✅ File naming
   - Format: `department_YYYY_MM_DD.csv`
   
4. ✅ Authentication
   - Sử dụng `auth:api` middleware
   - Chỉ authenticated users mới có thể export
   
5. ✅ Consistency
   - Follow pattern của các export khác (Vehicle, Course)
   - Code structure và naming convention nhất quán

---

## Risk Analysis

### Low Risk:
- Pattern đã được sử dụng rộng rãi trong project (Vehicle, Course export)
- Không có breaking changes
- Không ảnh hưởng đến existing functionality

### Potential Issues:
1. **Encoding Issues**: Tiếng Nhật có thể bị mojibake nếu encoding không đúng
   - **Mitigation**: Sử dụng SJIS-win encoding đã được verify trong các export khác

2. **Large Dataset**: Nếu số lượng departments rất lớn, có thể gặp vấn đề memory/timeout
   - **Mitigation**: Hiện tại số lượng không lớn. Nếu cần, có thể optimize bằng chunk query sau

3. **Null Values**: Một số fields có thể null
   - **Mitigation**: Sử dụng `?? ''` để xử lý null values

---

## Review Checklist

Before marking as complete, verify:

- [ ] DepartmentExport class được tạo và implement đúng interfaces
- [ ] View template `department.blade.php` được tạo với đầy đủ fields
- [ ] Controller method `exportCsv` được thêm vào DepartmentController
- [ ] Route được thêm vào `routes/api.php`
- [ ] OpenAPI documentation được thêm (@OA annotations)
- [ ] CSV file được download đúng format
- [ ] Encoding SJIS-win hoạt động đúng
- [ ] Tất cả tests pass
- [ ] Code follow project conventions
- [ ] No breaking changes to existing functionality

---

**Plan Created:** 2026-01-08  
**Issue:** #598  
**Type:** Backend Feature Enhancement  
**Estimated Effort:** 1-2 hours  
**Status:** Ready for Development
