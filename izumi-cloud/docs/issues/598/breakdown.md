# Issue #598: Breakdown Report - Department master export function

## Breakdown Summary

**Parent Issue:** #598  
**Scope:** Backend Only  
**Strategy:** Single BE Issue (Backend-only feature)  
**Total Issues to Create:** 1 issue  
**Total Story Points:** 3 SP (~3 hours)

---

## 分解戦略 (Breakdown Strategy)

### ✅ Recommended: 1 Backend Issue (Backend-only Strategy)

**理由 / Rationale:**
- Issue #598 は Backend Only の機能追加
- すべてのタスクが密接に関連している（Export機能の実装）
- 既存のパターン（VehicleExport, CourseExport）をフォロー
- 1人のBE開発者が一括で実装可能
- 分割する必要性がない（Total SP: 3 SP < 8 SP threshold）

**Benefits:**
- ✅ 単一オーナーシップ（1人のBE開発者が担当）
- ✅ タスク間の調整不要
- ✅ 実装の一貫性を保証
- ✅ 進捗管理がシンプル
- ✅ コミュニケーションオーバーヘッドなし

---

## Issue Details (予定)

### Issue #601: [BE] Department master export function / Chức năng export CSV cho màn hình department master

**Type:** Backend Enhancement  
**Labels:** `backend`, `enhancement`  
**Story Points:** 3 SP (~3 hours) ✅ Registered to GitHub Projects  
**GitHub Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/601  
**Dependency:** None (can be developed independently)

---

## 日本語 / Japanese

### 親Issue

#598 に関連

### 説明

Department master 画面にCSVエクスポート機能を追加します。ユーザーが部門マスタデータをCSVファイルとしてダウンロードできるようにし、既存のVehicle exportやCourse exportと同じパターンで実装します。SJIS-winエンコーディングを使用して日本語が正しく表示されるようにします。

### 実装タスク

この issue には以下のすべてのタスクが含まれます：

#### 1. Export Classの実装 (10分)

**File:** `app/Exports/DepartmentExport.php` (新規作成)

- `FromView` インターフェースを実装
- `WithCustomCsvSettings` インターフェースを実装
- `getCsvSettings()` メソッドでSJIS-winエンコーディングを設定
- `view()` メソッドでBladeテンプレートを返す
- Constructorでデータを受け取る

#### 2. Blade View Templateの実装 (20分)

**File:** `resources/views/export/department.blade.php` (新規作成)

##### 2.1. CSV Header行の作成 (5分)
- 日本語ヘッダー行を追加
- 27個のカラム（Department modelの全fields）
- ID, 部署名, 位置, 住所, 都道府県名, 面接住所, 面接住所URL, 面接担当者
- 郵便番号, 電話番号, 事務所名, 事務所場所, 事務所面積, 休憩室面積
- ガレージ場所1, ガレージ面積1, ガレージ場所2, ガレージ面積2
- 運営管理者, 運営管理補助, 保守管理者, 保守管理補助
- 保守管理電話番号, 保守管理FAX番号
- トラック協会会員番号, Gマーク番号, Gマーク有効期限, IT点呼

##### 2.2. Data行の実装 (10分)
- `@foreach($data as $department)` ループで各departmentを処理
- 各fieldを `{{ $department->field ?? '' }}` で出力
- Null値の処理（`?? ''` を使用）
- CSS styling（既存のexport viewと同様）

##### 2.3. CSS Styling (5分)
- `.ritz .waffle` クラスのスタイル定義
- Header用の `.s0` クラス（背景色 #073763、白文字）
- Data用の `.s2`（左揃え）と `.s3`（右揃え）クラス
- 既存のexport view（vehicle, course）と一貫性を保つ

#### 3. Controller実装 (15分)

**File:** `app/Http/Controllers/Api/DepartmentController.php`

##### 3.1. Use statements追加 (2分)
- `use App\Exports\DepartmentExport;`
- `use Maatwebsite\Excel\Facades\Excel;`
- `use Carbon\Carbon;`

##### 3.2. exportCsvメソッド実装 (13分)
- OpenAPIドキュメント（@OA annotations）追加
- `DepartmentRequest` でバリデーション
- `$this->repository->index()` でデータ取得
- ファイル名を `department_YYYY_MM_DD.csv` 形式で生成
- `Excel::download()` でCSVファイルをダウンロード
- Content-Typeヘッダーに `charset=SJIS-win` を設定

#### 4. Route追加 (5分)

**File:** `routes/api.php`

- `auth:api` ミドルウェアグループ内に追加
- `Route::get('department/export', 'DepartmentController@exportCsv');`
- 既存のdepartment routesの近くに配置

#### 5. テスト・検証 (30分)

##### 5.1. API Endpoint Testing (15分)
- Postman/curlで `/api/department/export` エンドポイントをテスト
- 認証なしでアクセス（401エラー確認）
- 認証ありでアクセス（CSVファイルダウンロード確認）
- ファイル名が正しい形式かを確認

##### 5.2. CSV Format Verification (10分)
- ダウンロードしたCSVファイルをExcelで開く
- 日本語ヘッダーが正しく表示されるか確認（mojibakeがないか）
- 全27カラムが存在するか確認
- データが正しく表示されるか確認
- Null値が空文字として表示されるか確認

##### 5.3. Encoding Verification (5分)
- SJIS-winエンコーディングが適用されているか確認
- Excelで日本語が正しく表示されるか確認

### 技術詳細

**API Endpoint:**
```
GET /api/department/export
Headers:
  Authorization: Bearer {token}
  Accept-Language: ja
Response:
  Content-Type: application/octet-stream; charset=SJIS-win
  Content-Disposition: attachment; filename="department_YYYY_MM_DD.csv"
```

**Export Class Structure:**
```php
class DepartmentExport implements FromView, WithCustomCsvSettings
{
    protected $data;
    
    public function __construct($data) { ... }
    public function view(): View { ... }
    public function getCsvSettings(): array { ... }
}
```

**CSV Settings:**
- `use_bom`: false
- `output_encoding`: 'SJIS-win'

**使用ライブラリ:**
- Maatwebsite\Excel (既存パッケージ)
- Carbon (Laravel標準)

**実装パターン:**
- VehicleExport, CourseExportと同じパターンをフォロー
- Blade view templateを使用したCSV生成
- Repository patternを使用したデータ取得

### 受け入れ基準

- [ ] `DepartmentExport` classが作成され、`FromView`と`WithCustomCsvSettings`を実装していること
- [ ] `department.blade.php` view templateが作成され、全27カラムのヘッダーとデータ行が含まれていること
- [ ] `DepartmentController::exportCsv` メソッドが実装されていること
- [ ] Route `/api/department/export` が追加され、`auth:api` ミドルウェアで保護されていること
- [ ] API endpointがCSVファイルを正しく返すこと
- [ ] ファイル名が `department_YYYY_MM_DD.csv` 形式であること
- [ ] CSVファイルのエンコーディングがSJIS-winであること
- [ ] Excelで開いたときに日本語が正しく表示されること（mojibakeがないこと）
- [ ] 全27カラムのデータが正しくエクスポートされていること
- [ ] Null値が空文字として処理されていること
- [ ] 認証なしのアクセスが401エラーを返すこと
- [ ] 既存のdepartment機能に影響がないこと
- [ ] OpenAPIドキュメントが追加されていること
- [ ] プロジェクト規約に準拠していること

### 依存関係

なし（独立して開発可能）

- 既存のRepository（`DepartmentRepository::index()`）を使用
- 既存のExcel package（Maatwebsite\Excel）を使用
- 既存のパターン（VehicleExport, CourseExport）をフォロー

### 見積もり工数

**Total: 3 SP (~3 hours)**

- Export Class実装: 10分 (0.17 SP → 1 SP)
- Blade View Template実装: 20分 (0.33 SP → 1 SP)
- Controller実装: 15分 (0.25 SP → 1 SP)
  - Use statements追加: 2分
  - exportCsvメソッド実装: 13分
- Route追加: 5分 (0.08 SP → 含む)
- テスト・検証: 30分 (0.5 SP → 1 SP)
  - API endpoint testing: 15分
  - CSV format verification: 10分
  - Encoding verification: 5分
- Buffer: 30分 (0.5 SP)

---

## Tiếng Việt / Vietnamese

### Issue cha

Liên quan đến #598

### Mô tả

Thêm chức năng export CSV cho màn hình Department master. Cho phép người dùng tải xuống dữ liệu department master dưới dạng file CSV, được triển khai theo cùng pattern với Vehicle export và Course export hiện có. Sử dụng encoding SJIS-win để đảm bảo tiếng Nhật hiển thị đúng.

### Các task triển khai

Issue này bao gồm tất cả các task sau:

#### 1. Triển khai Export Class (10 phút)

**File:** `app/Exports/DepartmentExport.php` (tạo mới)

- Implement interface `FromView`
- Implement interface `WithCustomCsvSettings`
- Method `getCsvSettings()` để cấu hình encoding SJIS-win
- Method `view()` để trả về Blade template
- Constructor nhận data

#### 2. Triển khai Blade View Template (20 phút)

**File:** `resources/views/export/department.blade.php` (tạo mới)

##### 2.1. Tạo CSV Header row (5 phút)
- Thêm header row tiếng Nhật
- 27 cột (tất cả fields từ Department model)
- ID, 部署名, 位置, 住所, 都道府県名, 面接住所, 面接住所URL, 面接担当者
- 郵便番号, 電話番号, 事務所名, 事務所場所, 事務所面積, 休憩室面積
- ガレージ場所1, ガレージ面積1, ガレージ場所2, ガレージ面積2
- 運営管理者, 運営管理補助, 保守管理者, 保守管理補助
- 保守管理電話番号, 保守管理FAX番号
- トラック協会会員番号, Gマーク番号, Gマーク有効期限, IT点呼

##### 2.2. Triển khai Data rows (10 phút)
- Sử dụng `@foreach($data as $department)` để lặp qua các department
- Output mỗi field bằng `{{ $department->field ?? '' }}`
- Xử lý null values (sử dụng `?? ''`)
- CSS styling (giống các export view khác)

##### 2.3. CSS Styling (5 phút)
- Định nghĩa style cho class `.ritz .waffle`
- Class `.s0` cho header (background #073763, text trắng)
- Classes `.s2` (căn trái) và `.s3` (căn phải) cho data
- Đảm bảo consistency với các export view hiện có (vehicle, course)

#### 3. Triển khai Controller (15 phút)

**File:** `app/Http/Controllers/Api/DepartmentController.php`

##### 3.1. Thêm Use statements (2 phút)
- `use App\Exports\DepartmentExport;`
- `use Maatwebsite\Excel\Facades\Excel;`
- `use Carbon\Carbon;`

##### 3.2. Triển khai method exportCsv (13 phút)
- Thêm OpenAPI documentation (@OA annotations)
- Validation với `DepartmentRequest`
- Lấy data bằng `$this->repository->index()`
- Tạo tên file theo format `department_YYYY_MM_DD.csv`
- Download CSV file bằng `Excel::download()`
- Set header Content-Type với `charset=SJIS-win`

#### 4. Thêm Route (5 phút)

**File:** `routes/api.php`

- Thêm vào nhóm middleware `auth:api`
- `Route::get('department/export', 'DepartmentController@exportCsv');`
- Đặt gần các department routes hiện có

#### 5. Testing và Validation (30 phút)

##### 5.1. API Endpoint Testing (15 phút)
- Test endpoint `/api/department/export` với Postman/curl
- Kiểm tra truy cập không có authentication (401 error)
- Kiểm tra truy cập có authentication (download CSV file)
- Xác nhận tên file đúng format

##### 5.2. CSV Format Verification (10 phút)
- Mở CSV file đã download bằng Excel
- Xác nhận header tiếng Nhật hiển thị đúng (không bị mojibake)
- Xác nhận có đủ 27 cột
- Xác nhận data hiển thị đúng
- Xác nhận null values được hiển thị là empty string

##### 5.3. Encoding Verification (5 phút)
- Xác nhận encoding SJIS-win được áp dụng
- Xác nhận tiếng Nhật hiển thị đúng trong Excel

### Chi tiết kỹ thuật

**API Endpoint:**
```
GET /api/department/export
Headers:
  Authorization: Bearer {token}
  Accept-Language: ja
Response:
  Content-Type: application/octet-stream; charset=SJIS-win
  Content-Disposition: attachment; filename="department_YYYY_MM_DD.csv"
```

**Cấu trúc Export Class:**
```php
class DepartmentExport implements FromView, WithCustomCsvSettings
{
    protected $data;
    
    public function __construct($data) { ... }
    public function view(): View { ... }
    public function getCsvSettings(): array { ... }
}
```

**CSV Settings:**
- `use_bom`: false
- `output_encoding`: 'SJIS-win'

**Thư viện sử dụng:**
- Maatwebsite\Excel (package đã có sẵn)
- Carbon (Laravel standard)

**Pattern triển khai:**
- Follow pattern giống VehicleExport, CourseExport
- Sử dụng Blade view template để generate CSV
- Sử dụng Repository pattern để lấy data

### Tiêu chí chấp nhận

- [ ] `DepartmentExport` class được tạo và implement `FromView` và `WithCustomCsvSettings`
- [ ] View template `department.blade.php` được tạo với đầy đủ 27 cột header và data rows
- [ ] Method `DepartmentController::exportCsv` được triển khai
- [ ] Route `/api/department/export` được thêm và bảo vệ bởi middleware `auth:api`
- [ ] API endpoint trả về CSV file đúng
- [ ] Tên file theo format `department_YYYY_MM_DD.csv`
- [ ] CSV file có encoding SJIS-win
- [ ] Khi mở bằng Excel, tiếng Nhật hiển thị đúng (không bị mojibake)
- [ ] Tất cả 27 cột data được export đầy đủ
- [ ] Null values được xử lý là empty string
- [ ] Truy cập không có authentication trả về 401 error
- [ ] Không ảnh hưởng đến các chức năng department hiện có
- [ ] OpenAPI documentation được thêm
- [ ] Tuân thủ quy ước dự án

### Phụ thuộc

Không có (có thể phát triển độc lập)

- Sử dụng Repository hiện có (`DepartmentRepository::index()`)
- Sử dụng Excel package hiện có (Maatwebsite\Excel)
- Follow pattern hiện có (VehicleExport, CourseExport)

### Ước tính công sức

**Tổng: 3 SP (~3 giờ)**

- Triển khai Export Class: 10 phút (0.17 SP → 1 SP)
- Triển khai Blade View Template: 20 phút (0.33 SP → 1 SP)
- Triển khai Controller: 15 phút (0.25 SP → 1 SP)
  - Thêm Use statements: 2 phút
  - Triển khai method exportCsv: 13 phút
- Thêm Route: 5 phút (0.08 SP → bao gồm)
- Testing và Validation: 30 phút (0.5 SP → 1 SP)
  - API endpoint testing: 15 phút
  - CSV format verification: 10 phút
  - Encoding verification: 5 phút
- Buffer: 30 phút (0.5 SP)

---

## Story Points Calculation

### Factors Considered

1. **Code Volume:** Small-Medium (S-M)
   - 1 Export class (~30 lines)
   - 1 Blade view template (~190 lines)
   - 1 Controller method (~20 lines)
   - 1 Route addition (~1 line)
   - Total: ~240 lines of code

2. **Complexity:** Simple
   - Follow existing pattern (VehicleExport, CourseExport)
   - No complex business logic
   - Standard CSV export functionality
   - No algorithm complexity

3. **Testing:** Manual Testing Required
   - API endpoint testing (15 min)
   - CSV format verification (10 min)
   - Encoding verification (5 min)
   - No unit test creation required initially

4. **Architecture Impact:** None
   - No new patterns introduced
   - No migrations needed
   - No breaking changes
   - Uses existing Excel package infrastructure

5. **Integration Dependencies:** None
   - Can be developed independently
   - Uses existing Repository methods
   - No external service integration
   - No blocking dependencies

6. **Uncertainty:** Very Low
   - Well-defined requirements
   - Clear pattern already exists in codebase
   - No research needed
   - Straightforward implementation path

### SP Calculation Result

Based on the factors above:

**Total SP: 3 SP (~3 hours)**

Breakdown:
- Export Class implementation: 0.17 SP (10 min) → rounded to 1 SP (minimum)
- View Template implementation: 0.33 SP (20 min) → rounded to 1 SP
- Controller & Route implementation: 0.33 SP (20 min) → rounded to 1 SP
- Testing & verification: 0.5 SP (30 min) → rounded to 1 SP
- Buffer: 0.5 SP (30 min) - Unexpected issues, code review feedback, adjustments

**Complexity Level:** Simple  
**Risk Level:** Low  
**Confidence Level:** High

---

## Implementation Order

### Recommended Development Sequence

```
Phase 1: Export Class & View Template (30 min)
  ├─ Task 1: Create DepartmentExport class
  └─ Task 2: Create department.blade.php view template

Phase 2: Controller & Route (20 min)
  ├─ Task 3: Add use statements to DepartmentController
  ├─ Task 4: Implement exportCsv method
  └─ Task 5: Add route to api.php

Phase 3: Testing & Verification (30 min)
  ├─ API endpoint testing
  ├─ CSV format verification
  ├─ Encoding verification
  └─ Regression testing (existing features)
```

### Dependencies Flow

```
Issue #598 (Parent)
        ↓
   BE Issue (独立)
        ↓
  Manual Testing
        ↓
    Ready for PR
```

---

## Files to Modify

### 1. `app/Exports/DepartmentExport.php` (新規作成)
**Lines:** ~30 lines  
**Purpose:** Export class implementation

### 2. `resources/views/export/department.blade.php` (新規作成)
**Lines:** ~190 lines  
**Purpose:** CSV template with header and data rows

### 3. `app/Http/Controllers/Api/DepartmentController.php` (編集)
**Lines to add:** ~20 lines  
**Locations:**
- Line ~17: Add use statements (3 lines)
- Line ~309: Add exportCsv method (~17 lines)

### 4. `routes/api.php` (編集)
**Lines to add:** 1 line  
**Location:** Line ~95 (after department apiResource)

**Total files:** 4 files (2 new, 2 modified)  
**Total lines:** ~240 lines

---

## Risk Analysis

### Low Risk ✅
- Simple feature following existing patterns
- No database changes required
- No breaking changes to existing features
- Uses familiar Excel export infrastructure
- Well-tested pattern (Vehicle, Course exports)

### Potential Issues (Mitigation Planned)

1. **Encoding Issues** (Low probability)
   - Risk: Japanese characters might display incorrectly (mojibake)
   - Mitigation: Use SJIS-win encoding (same as VehicleExport, CourseExport)

2. **Large Dataset** (Very Low probability)
   - Risk: Memory/timeout issues if many departments
   - Mitigation: Current dataset is small. Can optimize with chunk query later if needed

3. **Null Values** (Very Low probability)
   - Risk: Some fields might be null
   - Mitigation: Use `?? ''` operator to handle null values

4. **View Template Consistency** (Very Low probability)
   - Risk: Template might not match other export views
   - Mitigation: Follow exact structure from course.blade.php

---

## Technical Notes

### Export Class Pattern
```php
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

### Controller Method Pattern
```php
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

### Consistency Guidelines
- Follow VehicleExport and CourseExport patterns exactly
- Use same CSS classes (.s0, .s2, .s3) for styling
- Use same file naming convention: `{resource}_{date}.csv`
- Use same encoding: SJIS-win
- Use same route pattern: GET `/api/{resource}/export`

---

## Verification Checklist

Before marking this breakdown as complete:

- [x] Plan.md analyzed and all tasks identified
- [x] Tasks grouped by layer (Backend only in this case)
- [x] Story Points calculated (3 SP)
- [x] Dependencies identified (None)
- [x] Risk analysis completed
- [x] Implementation order defined
- [x] Files to modify listed
- [x] Bilingual content prepared (Japanese/Vietnamese)
- [x] Acceptance criteria defined
- [x] Technical details documented

---

## Summary

**Issue #598 Breakdown Result:**

| Metric | Value |
|--------|-------|
| Total Issues | 1 (Backend only) |
| Total Story Points | 3 SP (~3 hours) |
| Files to Create | 2 files |
| Files to Modify | 2 files |
| Lines of Code | ~240 lines |
| Complexity | Simple |
| Risk Level | Low |
| Dependencies | None |

**Recommended Strategy:** Single Backend Issue  
**Ready for Development:** ✅ Yes  
**Backend Dependency:** ✅ None (can develop independently)

---

**Breakdown Created:** 2026-01-08  
**Parent Issue:** #598  
**Status:** ✅ Complete - GitHub Issue #601 created and SP registered  
**Next Step:** `/dev 601` when ready to implement
