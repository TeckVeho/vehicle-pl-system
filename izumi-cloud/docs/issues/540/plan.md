# Issue #540: BE_Add functionality for time calculation - Implementation Plan

## 概要 (Overview)

**Mục tiêu:** Thêm chức năng quản lý địa điểm cho hệ thống quotation, bao gồm thêm trường "出発地" (Departure Location) và hỗ trợ multiple "届け地" (Delivery Locations) bằng cách tách bảng riêng.

**Hiện trạng:**
- Bảng `quotations` chỉ có `loading_location`, `delivery_location` (single value), `return_location`
- API không hỗ trợ multiple delivery locations
- Không có trường departure_location

**Sau khi cải tiến:**
- Thêm trường `departure_location` vào bảng `quotations`
- Tạo bảng `quotation_delivery_locations` để lưu multiple delivery locations
- API hỗ trợ nhận và trả về array của delivery locations
- Maintain backward compatibility với field cũ

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `database/migrations/YYYY_MM_DD_HHMMSS_add_departure_location_to_quotations_table.php`

##### 1.1.1. Tạo migration thêm cột departure_location

Tạo migration mới để thêm cột `departure_location` vào bảng `quotations`.

**Migration cần tạo:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('departure_location', 255)->nullable()->after('tonnage_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('departure_location');
        });
    }
};
```

**Command tạo migration:**
```bash
php artisan make:migration add_departure_location_to_quotations_table --table=quotations
```

---

#### 1.2. File: `database/migrations/YYYY_MM_DD_HHMMSS_create_quotation_delivery_locations_table.php`

##### 1.2.1. Tạo bảng quotation_delivery_locations

Tạo migration mới để tạo bảng `quotation_delivery_locations` với foreign key constraint và cascade delete.

**Migration cần tạo:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_delivery_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->string('location_name', 255);
            $table->integer('sequence_order');
            $table->timestamps();
            
            $table->index('quotation_id');
            $table->foreign('quotation_id')
                  ->references('id')
                  ->on('quotations')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_delivery_locations');
    }
};
```

**Command tạo migration:**
```bash
php artisan make:migration create_quotation_delivery_locations_table
```

---

#### 1.3. File: `app/Models/Quotation.php`

##### 1.3.1. Thêm departure_location vào $fillable array

**現在の実装** (line 14-72):
```php
protected $fillable = [
    'title',
    'author_id',
    'tonnage_id',
    'basic_hours',
    // ... các fields khác
    'loading_location',
    'delivery_location',
    'return_location',
    // ... các fields khác
];
```

**変更内容:**

Thêm `'departure_location'` vào array `$fillable` sau `'tonnage_id'`:

```php
protected $fillable = [
    'title',
    'author_id',
    'tonnage_id',
    'departure_location',  // NEW FIELD
    'basic_hours',
    // ... các fields khác
];
```

##### 1.3.2. Thêm relationship với QuotationDeliveryLocation

**変更内容:**

Thêm method relationship vào cuối class (sau line 83):

```php
public function deliveryLocations()
{
    return $this->hasMany(QuotationDeliveryLocation::class, 'quotation_id')->orderBy('sequence_order');
}
```

---

#### 1.4. File: `app/Models/QuotationDeliveryLocation.php` (NEW FILE)

##### 1.4.1. Tạo model mới cho quotation_delivery_locations

Tạo model mới hoàn toàn để quản lý delivery locations.

**File mới cần tạo:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationDeliveryLocation extends Model
{
    use HasFactory;

    protected $table = 'quotation_delivery_locations';

    protected $fillable = [
        'quotation_id',
        'location_name',
        'sequence_order',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }
}
```

**Command tạo model:**
```bash
php artisan make:model QuotationDeliveryLocation
```

---

#### 1.5. File: `app/Repositories/QuotationRepository.php`

##### 1.5.1. Override create() method để xử lý delivery_locations

**現在の実装:**

Repository hiện tại chỉ extend BaseRepository và không có custom create() method.

**変更内容:**

Thêm method `create()` để xử lý việc tạo quotation với delivery locations:

```php
public function create(array $attributes)
{
    return \DB::transaction(function () use ($attributes) {
        $deliveryLocations = $attributes['delivery_locations'] ?? [];
        unset($attributes['delivery_locations']);
        
        $quotation = $this->model->create($attributes);
        
        if (!empty($deliveryLocations)) {
            foreach ($deliveryLocations as $index => $location) {
                if (!empty($location)) {
                    $quotation->deliveryLocations()->create([
                        'location_name' => $location,
                        'sequence_order' => $index + 1,
                    ]);
                }
            }
        }
        
        return $quotation->load('deliveryLocations', 'author', 'quotationMasterData');
    });
}
```

##### 1.5.2. Override update() method để xử lý delivery_locations

**変更内容:**

Thêm method `update()` để xử lý việc update quotation và sync delivery locations:

```php
public function update(array $attributes, $id)
{
    return \DB::transaction(function () use ($attributes, $id) {
        $quotation = $this->model->findOrFail($id);
        
        $deliveryLocations = $attributes['delivery_locations'] ?? null;
        unset($attributes['delivery_locations']);
        
        $quotation->update($attributes);
        
        if ($deliveryLocations !== null) {
            $quotation->deliveryLocations()->delete();
            
            if (!empty($deliveryLocations)) {
                foreach ($deliveryLocations as $index => $location) {
                    if (!empty($location)) {
                        $quotation->deliveryLocations()->create([
                            'location_name' => $location,
                            'sequence_order' => $index + 1,
                        ]);
                    }
                }
            }
        }
        
        return $quotation->load('deliveryLocations', 'author', 'quotationMasterData');
    });
}
```

##### 1.5.3. Update search() và searchWithPagination() để eager load deliveryLocations

**現在の実装** (line 30, 57):
```php
$query->with(['author', 'quotationMasterData']);
```

**変更内容:**

Thêm `'deliveryLocations'` vào eager loading:

```php
$query->with(['author', 'quotationMasterData', 'deliveryLocations']);
```

Cần thay đổi tại 2 vị trí:
- Line 30 trong method `search()`
- Line 57 trong method `searchWithPagination()`

---

#### 1.6. File: `app/Http/Requests/CreateQuotationRequest.php`

##### 1.6.1. Thêm validation rules cho departure_location và delivery_locations

**現在の実装** (line 14-24):
```php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'author_id' => 'required|integer|exists:quotation_staff,id',
        'tonnage_id' => 'required|integer|exists:quotation_master_data,id',
        'total_delivery_cost' => 'required|numeric',
        'gross_profit' => 'required|numeric',
        'monthly_total' => 'required|numeric',
    ];
}
```

**変更内容:**

Thêm validation rules cho các trường mới:

```php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'author_id' => 'required|integer|exists:quotation_staff,id',
        'tonnage_id' => 'required|integer|exists:quotation_master_data,id',
        'departure_location' => 'nullable|string|max:255',
        'delivery_locations' => 'nullable|array',
        'delivery_locations.*' => 'nullable|string|max:255',
        'total_delivery_cost' => 'required|numeric',
        'gross_profit' => 'required|numeric',
        'monthly_total' => 'required|numeric',
    ];
}
```

---

#### 1.7. File: `app/Http/Requests/UpdateQuotationRequest.php`

##### 1.7.1. Thêm validation rules cho departure_location và delivery_locations

**現在の実装** (line 14-24):
```php
public function rules()
{
    return [
        'title' => 'sometimes|string|max:255',
        'author_id' => 'sometimes|integer|exists:quotation_staff,id',
        'tonnage_id' => 'sometimes|integer|exists:quotation_master_data,id',
        'total_delivery_cost' => 'sometimes|numeric',
        'gross_profit' => 'sometimes|numeric',
        'monthly_total' => 'sometimes|numeric',
    ];
}
```

**変更内容:**

Thêm validation rules cho các trường mới:

```php
public function rules()
{
    return [
        'title' => 'sometimes|string|max:255',
        'author_id' => 'sometimes|integer|exists:quotation_staff,id',
        'tonnage_id' => 'sometimes|integer|exists:quotation_master_data,id',
        'departure_location' => 'sometimes|nullable|string|max:255',
        'delivery_locations' => 'sometimes|nullable|array',
        'delivery_locations.*' => 'nullable|string|max:255',
        'total_delivery_cost' => 'sometimes|numeric',
        'gross_profit' => 'sometimes|numeric',
        'monthly_total' => 'sometimes|numeric',
    ];
}
```

---

#### 1.8. File: `app/Http/Resources/QuotationResource.php`

##### 1.8.1. Override toArray() để format delivery_locations

**現在の実装** (line 7-10):
```php
public function toArray($request)
{
    return parent::toArray($request);
}
```

**変更内容:**

Override method `toArray()` để format delivery_locations array:

```php
public function toArray($request)
{
    $array = parent::toArray($request);
    
    if ($this->relationLoaded('deliveryLocations')) {
        $array['delivery_locations'] = $this->deliveryLocations->map(function($dl) {
            return [
                'id' => $dl->id,
                'location_name' => $dl->location_name,
                'sequence_order' => $dl->sequence_order,
            ];
        })->toArray();
    }
    
    return $array;
}
```

---

#### 1.9. File: `app/Http/Controllers/Api/QuotationController.php`

##### 1.9.1. Update show() method để eager load deliveryLocations

**現在の実装** (line 206):
```php
$quotation = $this->repository->with(['author', 'quotationMasterData'])->find($id);
```

**変更内容:**

Thêm `'deliveryLocations'` vào eager loading:

```php
$quotation = $this->repository->with(['author', 'quotationMasterData', 'deliveryLocations'])->find($id);
```

##### 1.9.2. Update store() method để sử dụng CreateQuotationRequest

**現在の実装** (line 324-333):

Controller đã sử dụng Request validation và repository pattern, không cần thay đổi logic vì logic đã được handle trong Repository.

**変更内容:**

Không cần thay đổi gì, vì:
- Validation đã được handle trong `CreateQuotationRequest`
- Logic tạo quotation với delivery_locations đã được handle trong `QuotationRepository::create()`

##### 1.9.3. Update update() method để sử dụng UpdateQuotationRequest

**現在の実装** (line 445-454):

Controller đã sử dụng Request validation và repository pattern, không cần thay đổi logic.

**変更内容:**

Không cần thay đổi gì, vì:
- Validation đã được handle trong `UpdateQuotationRequest`
- Logic update quotation với delivery_locations đã được handle trong `QuotationRepository::update()`

---

#### 1.10. File: `app/Repositories/Contracts/BaseRepositoryInterface.php`

##### 1.10.1. Kiểm tra xem BaseRepository có method create() và update() chưa

**変更内容:**

Kiểm tra interface và base repository để đảm bảo có methods `create()` và `update()`. Nếu chưa có, cần implement trong BaseRepository hoặc override trong QuotationRepository.

Nếu BaseRepository đã có sẵn `create()` và `update()` methods, chỉ cần override trong `QuotationRepository` là đủ (đã làm ở bước 1.5).

---

## 実装順序 (Implementation Order)

### Phase 1: Database Setup (Phụ thuộc: Không)

1. **Tạo migration thêm cột departure_location** (1.1)
2. **Tạo migration tạo bảng quotation_delivery_locations** (1.2)
3. **Chạy migrations:**
   ```bash
   php artisan migrate
   ```

### Phase 2: Model Layer (Phụ thuộc: Phase 1)

4. **Tạo model QuotationDeliveryLocation** (1.4)
5. **Update model Quotation** (1.3)
   - Thêm departure_location vào $fillable
   - Thêm relationship deliveryLocations()

### Phase 3: Repository Layer (Phụ thuộc: Phase 2)

6. **Update QuotationRepository** (1.5)
   - Override create() method
   - Override update() method
   - Update search() và searchWithPagination()

### Phase 4: Request Validation (Phụ thuộc: Không, có thể song song với Phase 2-3)

7. **Update CreateQuotationRequest** (1.6)
8. **Update UpdateQuotationRequest** (1.7)

### Phase 5: Response Formatting (Phụ thuộc: Phase 2)

9. **Update QuotationResource** (1.8)

### Phase 6: Controller Layer (Phụ thuộc: Phase 2-5)

10. **Update QuotationController** (1.9)
    - Update show() method
    - Verify store() và update() methods

### Phase 7: Testing (Phụ thuộc: Phase 1-6)

11. **Test migrations**
12. **Test API endpoints với Postman/Thunder Client**
13. **Verify cascade delete**
14. **Test với multiple delivery locations**

---

## 見積もり工数 (Estimated Effort)

### Backend: 4-6 giờ

**Phase 1: Database Setup** - 0.5 giờ
- Tạo 2 migrations: 0.3 giờ
- Chạy và test migrations: 0.2 giờ

**Phase 2: Model Layer** - 0.5 giờ
- Tạo QuotationDeliveryLocation model: 0.2 giờ
- Update Quotation model: 0.3 giờ

**Phase 3: Repository Layer** - 1.5 giờ
- Override create() method với transaction: 0.5 giờ
- Override update() method với sync logic: 0.5 giờ
- Update search methods: 0.3 giờ
- Testing repository logic: 0.2 giờ

**Phase 4: Request Validation** - 0.3 giờ
- Update CreateQuotationRequest: 0.15 giờ
- Update UpdateQuotationRequest: 0.15 giờ

**Phase 5: Response Formatting** - 0.5 giờ
- Override toArray() trong QuotationResource: 0.3 giờ
- Test response format: 0.2 giờ

**Phase 6: Controller Updates** - 0.2 giờ
- Update eager loading: 0.2 giờ

**Phase 7: Testing & QA** - 1.5-3 giờ
- Unit tests: 0.5 giờ
- API testing với Postman: 0.5-1 giờ
- Integration testing: 0.5-1 giờ
- Bug fixes và adjustments: 0-0.5 giờ

**Frontend:** 0 giờ (Backend API only)

**合計: 4-6 giờ**

---

## 技術的な注意事項 (Technical Notes)

### 1. データ整合性 (Data Integrity)

**Transaction Usage:**
- Sử dụng DB transaction trong create() và update() để đảm bảo:
  - Nếu tạo quotation thành công nhưng lỗi khi tạo delivery_locations → rollback toàn bộ
  - Nếu update quotation thành công nhưng lỗi khi sync delivery_locations → rollback toàn bộ

**Cascade Delete:**
- Foreign key với `onDelete('cascade')` đảm bảo khi xóa quotation, tất cả delivery_locations liên quan cũng bị xóa tự động
- Không cần xử lý manual delete trong code

**Validation:**
- `delivery_locations` array có thể empty (user không nhập delivery location nào)
- Mỗi item trong array có thể empty string → cần filter ra khi lưu database
- Không yêu cầu validation nghiêm ngặt theo requirement

### 2. パフォーマンス考慮 (Performance)

**Eager Loading:**
- Luôn eager load `deliveryLocations` khi query quotations để tránh N+1 query problem
- Trong list API, có thể cân nhắc lazy loading nếu không cần hiển thị delivery_locations

**Indexing:**
- Index trên `quotation_id` trong bảng `quotation_delivery_locations` đã được thêm
- Giúp query nhanh khi JOIN hoặc WHERE theo quotation_id

**Sequence Order:**
- `sequence_order` giúp maintain thứ tự delivery locations
- Sort by sequence_order khi query để đảm bảo order đúng

### 3. 既存機能との互換性 (Backward Compatibility)

**Old Field Retention:**
- Giữ nguyên field cũ `delivery_location` trong database
- Frontend cũ (nếu có) vẫn có thể dùng field này
- Frontend mới sẽ dùng `delivery_locations` array

**API Response:**
- Response sẽ chứa cả `delivery_location` (old field) và `delivery_locations` (new array)
- Client có thể chọn dùng field nào phù hợp

**Migration Path:**
- Không cần migrate data từ `delivery_location` sang `quotation_delivery_locations`
- Các quotation cũ có thể vẫn dùng field cũ
- Quotation mới sẽ dùng bảng mới

### 4. Testing Strategy

**Unit Tests:**
- Test QuotationRepository::create() với empty, 1, nhiều delivery locations
- Test QuotationRepository::update() với các scenarios: add, remove, update locations
- Test transaction rollback khi có lỗi

**Integration Tests:**
- Test API POST `/api/quotations` với delivery_locations array
- Test API PUT `/api/quotations/{id}` update delivery_locations
- Test API GET trả về đúng format
- Test cascade delete

**Manual Testing:**
- Dùng Postman/Thunder Client test toàn bộ flow
- Test với edge cases: empty array, large array (10+ items), special characters

### 5. Code Quality

**Naming Conventions:**
- Model: `QuotationDeliveryLocation` (singular, PascalCase)
- Table: `quotation_delivery_locations` (plural, snake_case)
- Relationship: `deliveryLocations()` (camelCase, plural)

**Error Handling:**
- DB transaction sẽ tự động rollback khi có exception
- Catch exception ở controller level đã có sẵn (responseJsonEx)

**Documentation:**
- Cần update API documentation (Swagger) nếu có
- Comment trong code cho các logic phức tạp (transaction, sync)

---

## Risk Assessment

### Low Risk:
- ✅ Database migration đơn giản
- ✅ Không ảnh hưởng existing data
- ✅ Repository pattern dễ test

### Medium Risk:
- ⚠️ Transaction rollback cần test kỹ
- ⚠️ Frontend integration cần coordinate

### Mitigation:
- Test kỹ transaction rollback scenarios
- Provide clear API documentation cho frontend team
- Maintain backward compatibility với old field

---

## Success Criteria - Part 1: Quotation API

- [ ] Migrations chạy thành công không lỗi
- [ ] API POST tạo quotation với multiple delivery locations
- [ ] API PUT update quotation sync delivery locations đúng
- [ ] API GET trả về delivery_locations array đúng format
- [ ] Cascade delete hoạt động khi xóa quotation
- [ ] Transaction rollback đúng khi có lỗi
- [ ] Tất cả tests pass
- [ ] API documentation updated (nếu có)

---

## Part 2: AI Route Calculation Service Update

### 概要 (Overview - AI Service)

**Yêu cầu bổ sung (2025-12-24):** Cập nhật AI Prompt để hỗ trợ tính toán route phức tạp với multiple delivery locations.

**Hiện trạng:**
- AI Service chỉ hỗ trợ single delivery location
- Prompt không có `start_location` (出発地)
- Response format cũ với hardcoded sections

**Sau khi cải tiến:**
- AI Service hỗ trợ multiple delivery locations
- Thêm `start_location` vào prompt
- Response format mới với dynamic `route_segments` array
- Compliance calculations (430 rule, Labor Law)

---

### 1. Files need to edit:

#### 1.11. File: `database/migrations/YYYY_MM_DD_HHMMSS_update_quotation_routes_for_multiple_deliveries.php`

##### 1.11.1. Tạo migration cho AI Routes table

Cập nhật bảng `quotation_routes` để hỗ trợ multiple delivery locations.

**Migration cần tạo:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_routes', function (Blueprint $table) {
            $table->string('start_location', 255)->nullable()->after('user_id');
            $table->json('delivery_locations')->nullable()->after('delivery_location');
            $table->text('compliance_note')->nullable();
        });
        
        // Add segment_type column to quotation_route_segments if not exists
        if (Schema::hasTable('quotation_route_segments')) {
            Schema::table('quotation_route_segments', function (Blueprint $table) {
                if (!Schema::hasColumn('quotation_route_segments', 'segment_type')) {
                    $table->string('segment_type', 50)->nullable()->after('segment_order');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('quotation_routes', function (Blueprint $table) {
            $table->dropColumn(['start_location', 'delivery_locations', 'compliance_note']);
        });
        
        if (Schema::hasTable('quotation_route_segments')) {
            Schema::table('quotation_route_segments', function (Blueprint $table) {
                if (Schema::hasColumn('quotation_route_segments', 'segment_type')) {
                    $table->dropColumn('segment_type');
                }
            });
        }
    }
};
```

**Command tạo migration:**
```bash
php artisan make:migration update_quotation_routes_for_multiple_deliveries --table=quotation_routes
```

---

#### 1.12. File: `app/Models/QuotationRoute.php`

##### 1.12.1. Update model để support new fields

**變更内容:**

Thêm các fields mới vào `$fillable` và `$casts`:

```php
protected $fillable = [
    'route_code',
    'user_id',
    'title',
    'start_location',              // NEW
    'pickup_location',
    'delivery_location',           // Keep for backward compatibility
    'delivery_locations',          // NEW (JSON array)
    'return_location',
    'start_time',
    'vehicle_type',
    'loading_time_minutes',
    'unloading_time_minutes',
    'user_break_time_minutes',
    'status',
    'ai_model_used',
    'total_distance_km',
    'estimated_end_time',
    'date_change',
    'total_duty_time_hours',
    'highway_fee',
    'compliance_note',             // NEW
    'calculation_duration_seconds',
    'error_message',
];

protected $casts = [
    'date_change' => 'boolean',
    'delivery_locations' => 'array',  // NEW - cast JSON to array
];
```

---

#### 1.13. File: `storage/app/prompts/route_calculation_prompt.txt`

##### 1.13.1. Backup và tạo prompt mới

**變更内容:**

1. Backup prompt cũ:
```bash
cp storage/app/prompts/route_calculation_prompt.txt storage/app/prompts/route_calculation_prompt.txt.old
```

2. Tạo prompt mới với full content từ GitHub comment:

```
# Role
あなたは日本の物流地理、道路交通法（特に2024年改善基準告示）、および有料道路料金体系に精通した「高度運行管理AI」です。
提供された情報に基づき、トラック輸送における複雑なルート（回送・複数配送含む）の計算、コスト試算、および法令を遵守した勤務計画を作成してください。

# Context
ユーザーは運送会社の運行管理者です。
「出発地（車庫）」から「積地」へ向かい、荷物を積んで「複数の届け地」を回り、「帰社地」に戻るまでの詳細な運行データを求めています。

# Input Data
以下の変数が入力されます。

* **出発地 (Start Location):** {start_location}
* **積地 (Pickup Location):** {pickup_location}
* **届け地リスト (Delivery Locations):** {delivery_locations}
* **帰社地 (Return Location):** {return_location}
* **運行開始時間 (Start Time):** {start_time}
* **車両区分 (Vehicle Type):** {vehicle_type}
* **積み込み作業時間 (Loading Time):** {loading_time}
* **荷下ろし作業時間 (Unloading Time per stop):** {unloading_time}
* **休憩時間指定 (User Break Time):** {break_time}

# Steps / Thinking Process
以下の手順でルートを構築し、計算を行ってください。

1.  **ルートセグメントの構築:**
    * [Segment 0: 回送] 出発地 → 積地
    * [Segment 1: 実車] 積地 → 最初の届け地
    * [Segment 2...N: 実車] 届け地(i) → 届け地(i+1)
    * [Segment Final: 回送] 最後の届け地 → 帰社地

2.  **距離・時間・料金の算出:**
    * 各セグメントについて、最適なルート（原則、高速道路利用）を特定。
    * トラックの平均速度（高速:70-80km/h, 下道:30-40km/h）で運転時間を算出。
    * 車両区分に基づき、各区間の高速道路料金（ETC概算）を算出。

3.  **作業時間の積算:**
    * [積み込み] 積地で1回発生。
    * [荷下ろし] **届け地の数 × 荷下ろし作業時間** で算出。

4.  **法令に基づく休憩と拘束時間の計算:**
    * [総運転時間] + [総作業時間] = [実労働時間（休憩除く）]
    * **法令チェック:**
        * 430ルール（4時間運転ごとの30分休憩）
        * 労基法休憩（6時間超で45分、8時間超で60分）

5.  **終了時間の導出:**
    * [運行開始時間] + [総運転時間] + [総作業時間] + [休憩時間] = [終了時刻]

# Constraints
* **ルート最適化:** 複数の届け地がある場合、入力された順序に従って回るものとします（並べ替えはしない）。
* **コンプライアンス:** 必ず法令（改善基準告示）を満たす休憩時間を確保したスケジュールにしてください。
* **精度:** 距離は小数点第一位、料金は100円単位。

# Output Format
アプリケーションでリスト表示できるよう、`segments`配列を用いた以下のJSON形式のみを出力してください。

{
  "summary": {
    "total_distance_km": float,
    "total_tolls_yen": int,
    "total_duty_time_hours": float,
    "start_time": "HH:MM",
    "estimated_end_time": "HH:MM",
    "date_change": boolean
  },
  "compliance_info": {
    "required_break_minutes": int,
    "note": "休憩時間の算出根拠"
  },
  "route_segments": [
    {
      "segment_order": 1,
      "type": "回送(積地へ)" Or "実車配送" Or "回送(帰庫)",
      "from": "地点名",
      "to": "地点名",
      "distance_km": float,
      "driving_time_minutes": int,
      "toll_yen": int,
      "route_description": "ルート概要"
    }
  ]
}
```

---

#### 1.14. File: `app/Services/AIRouteCalculationService.php`

##### 1.14.1. Update buildPrompt() method

**現在の実装** (line 107-138):
```php
protected function buildPrompt(array $input): string
{
    $promptPath = storage_path('app/prompts/route_calculation_prompt.txt');
    
    if (!file_exists($promptPath)) {
        throw new \Exception('Prompt template not found: ' . $promptPath);
    }
    
    $promptTemplate = file_get_contents($promptPath);
    
    $userMessage = str_replace([
        '{pickup_location}',
        '{delivery_location}',      // Single delivery
        '{return_location}',
        '{start_time}',
        '{vehicle_type}',
        '{loading_time}',
        '{unloading_time}',
        '{break_time}',
    ], [
        $input['pickup_location'],
        $input['delivery_location'],
        $input['return_location'],
        $input['start_time'],
        $input['vehicle_type'] ?? '中型車(4t)',
        $input['loading_time'] ?? 60,
        $input['unloading_time'] ?? 60,
        $input['break_time'] ?? 'Auto',
    ], $promptTemplate);

    return $userMessage;
}
```

**変更内容:**

```php
protected function buildPrompt(array $input): string
{
    $promptPath = storage_path('app/prompts/route_calculation_prompt.txt');
    
    if (!file_exists($promptPath)) {
        throw new \Exception('Prompt template not found: ' . $promptPath);
    }
    
    $promptTemplate = file_get_contents($promptPath);
    
    // Handle delivery_locations array
    $deliveryLocations = $input['delivery_locations'] ?? [];
    if (is_array($deliveryLocations)) {
        $deliveryLocationsStr = implode('、', $deliveryLocations);
    } else {
        $deliveryLocationsStr = $deliveryLocations;
    }
    
    // Fallback to old single delivery_location if empty
    if (empty($deliveryLocationsStr) && !empty($input['delivery_location'])) {
        $deliveryLocationsStr = $input['delivery_location'];
    }
    
    $userMessage = str_replace([
        '{start_location}',           // NEW
        '{pickup_location}',
        '{delivery_locations}',       // NEW (comma-separated)
        '{return_location}',
        '{start_time}',
        '{vehicle_type}',
        '{loading_time}',
        '{unloading_time}',
        '{break_time}',
    ], [
        $input['start_location'] ?? $input['departure_location'] ?? '',  // NEW
        $input['pickup_location'] ?? $input['loading_location'] ?? '',
        $deliveryLocationsStr,        // NEW
        $input['return_location'] ?? '',
        $input['start_time'] ?? '09:00',
        $input['vehicle_type'] ?? '中型車(4t)',
        $input['loading_time'] ?? 60,
        $input['unloading_time'] ?? 30,  // Changed default to 30
        $input['break_time'] ?? 'Auto',
    ], $promptTemplate);

    return $userMessage;
}
```

##### 1.14.2. Update calculate() method

**現在の実装** (line 27-47):
```php
$route = QuotationRoute::create([
    'route_code' => $routeCode,
    'user_id' => $userId,
    'title' => $input['title'] ?? null,
    'pickup_location' => $input['pickup_location'],
    'delivery_location' => $input['delivery_location'],  // Single
    'return_location' => $input['return_location'],
    // ...
]);
```

**変更内容:**

```php
$route = QuotationRoute::create([
    'route_code' => $routeCode,
    'user_id' => $userId,
    'title' => $input['title'] ?? null,
    'start_location' => $input['start_location'] ?? null,              // NEW
    'pickup_location' => $input['pickup_location'],
    'delivery_location' => $input['delivery_location'] ?? null,        // Keep for backward compatibility
    'delivery_locations' => $input['delivery_locations'] ?? null,      // NEW (JSON array)
    'return_location' => $input['return_location'],
    // ...
]);
```

##### 1.14.3. Update saveLocations() method

**現在の実装** (line 277-302):
```php
protected function saveLocations(QuotationRoute $route, array $response): void
{
    $locations = [
        [
            'sequence_order' => 1,
            'location_type' => 'pickup',
            'address' => $route->pickup_location,
        ],
        [
            'sequence_order' => 2,
            'location_type' => 'delivery',
            'address' => $route->delivery_location,  // Single
        ],
        [
            'sequence_order' => 3,
            'location_type' => 'return',
            'address' => $route->return_location,
        ],
    ];
    
    foreach ($locations as $locationData) {
        QuotationRouteLocation::create(array_merge([
            'route_id' => $route->id,
        ], $locationData));
    }
}
```

**変更内容:**

```php
protected function saveLocations(QuotationRoute $route, array $response): void
{
    $locations = [
        [
            'sequence_order' => 1,
            'location_type' => 'start',           // NEW
            'address' => $route->start_location,  // NEW
        ],
        [
            'sequence_order' => 2,
            'location_type' => 'pickup',
            'address' => $route->pickup_location,
        ],
    ];
    
    // Add multiple delivery locations
    $deliveryLocations = $route->delivery_locations ?? [];
    if (is_string($deliveryLocations)) {
        $deliveryLocations = json_decode($deliveryLocations, true) ?? [];
    }
    
    $sequenceOrder = 3;
    foreach ($deliveryLocations as $deliveryLocation) {
        if (!empty($deliveryLocation)) {
            $locations[] = [
                'sequence_order' => $sequenceOrder++,
                'location_type' => 'delivery',
                'address' => $deliveryLocation,
            ];
        }
    }
    
    // Add return location
    $locations[] = [
        'sequence_order' => $sequenceOrder,
        'location_type' => 'return',
        'address' => $route->return_location,
    ];
    
    foreach ($locations as $locationData) {
        QuotationRouteLocation::create(array_merge([
            'route_id' => $route->id,
        ], $locationData));
    }
}
```

##### 1.14.4. Update saveSegments() method

**現在の実装** (line 304-336):
```php
protected function saveSegments(QuotationRoute $route, array $response): void
{
    $routeDetails = $response['route_details'] ?? [];
    $locations = $route->locations;
    
    // Hardcoded section_1 and section_2
    if (isset($routeDetails['section_1_pickup_to_delivery'])) {
        $section1 = $routeDetails['section_1_pickup_to_delivery'];
        QuotationRouteSegment::create([...]);
    }
    
    if (isset($routeDetails['section_2_delivery_to_return'])) {
        $section2 = $routeDetails['section_2_delivery_to_return'];
        QuotationRouteSegment::create([...]);
    }
}
```

**変更内容:**

```php
protected function saveSegments(QuotationRoute $route, array $response): void
{
    $routeSegments = $response['route_segments'] ?? [];
    
    if (empty($routeSegments)) {
        Log::warning('No route_segments in AI response', ['route_id' => $route->id]);
        return;
    }
    
    $locations = $route->locations()->orderBy('sequence_order')->get();
    
    foreach ($routeSegments as $segment) {
        $segmentOrder = $segment['segment_order'] ?? null;
        
        if ($segmentOrder === null) {
            continue;
        }
        
        // Map segment to locations
        // segment_order 1: from location[0] to location[1]
        // segment_order 2: from location[1] to location[2]
        $fromLocationIndex = $segmentOrder - 1;
        $toLocationIndex = $segmentOrder;
        
        $fromLocation = $locations[$fromLocationIndex] ?? null;
        $toLocation = $locations[$toLocationIndex] ?? null;
        
        if (!$fromLocation || !$toLocation) {
            Log::warning('Location not found for segment', [
                'segment_order' => $segmentOrder,
                'from_index' => $fromLocationIndex,
                'to_index' => $toLocationIndex,
            ]);
            continue;
        }
        
        QuotationRouteSegment::create([
            'route_id' => $route->id,
            'from_location_id' => $fromLocation->id,
            'to_location_id' => $toLocation->id,
            'segment_order' => $segmentOrder,
            'segment_type' => $segment['type'] ?? null,  // NEW
            'distance_km' => $segment['distance_km'] ?? 0,
            'driving_time_minutes' => $segment['driving_time_minutes'] ?? 0,
            'highway_fee' => $segment['toll_yen'] ?? 0,
            'route_description' => $segment['route_description'] ?? null,
        ]);
    }
}
```

##### 1.14.5. Update parseAndSaveResponse() method

**現在の実装** (line 249-275):
```php
protected function parseAndSaveResponse(QuotationRoute $route, array $response): void
{
    DB::transaction(function () use ($route, $response) {
        $summary = $response['summary'] ?? [];
        $timeBreakdown = $response['time_breakdown'] ?? [];
        $costBreakdown = $response['cost_breakdown'] ?? [];
        $complianceCheck = $summary['compliance_check'] ?? [];
        
        $route->update([
            'total_distance_km' => $summary['total_distance_km'] ?? null,
            'estimated_end_time' => $summary['estimated_end_time'] ?? null,
            'date_change' => $summary['date_change'] ?? false,
            'total_duty_time_hours' => $timeBreakdown['total_duty_time_hours'] ?? null,
            'highway_fee' => $costBreakdown['estimated_total_tolls'] ?? 0,
            // ...
        ]);
        
        $this->saveLocations($route, $response);
        $this->saveSegments($route, $response);
    });
}
```

**変更内容:**

```php
protected function parseAndSaveResponse(QuotationRoute $route, array $response): void
{
    DB::transaction(function () use ($route, $response) {
        $summary = $response['summary'] ?? [];
        $complianceInfo = $response['compliance_info'] ?? [];  // NEW
        
        $route->update([
            'total_distance_km' => $summary['total_distance_km'] ?? null,
            'estimated_end_time' => $summary['estimated_end_time'] ?? null,
            'date_change' => $summary['date_change'] ?? false,
            'total_duty_time_hours' => $summary['total_duty_time_hours'] ?? null,
            'highway_fee' => $summary['total_tolls_yen'] ?? 0,  // Updated field name
            'total_break_time_minutes' => $complianceInfo['required_break_minutes'] ?? null,  // NEW
            'compliance_note' => $complianceInfo['note'] ?? null,  // NEW
        ]);
        
        $this->saveLocations($route, $response);
        $this->saveSegments($route, $response);
    });
}
```

---

#### 1.15. File: API Controller (Find controller using AIRouteCalculationService)

##### 1.15.1. Update validation rules

**変更内容:**

Tìm controller sử dụng `AIRouteCalculationService`:
```bash
grep -r "AIRouteCalculationService" app/Http/Controllers/
```

Update validation rules:

```php
public function calculate(Request $request)
{
    $validated = $request->validate([
        'title' => 'nullable|string',
        'start_location' => 'nullable|string|max:255',           // NEW
        'pickup_location' => 'required|string|max:255',
        'delivery_locations' => 'nullable|array',                // NEW
        'delivery_locations.*' => 'nullable|string|max:255',     // NEW
        'delivery_location' => 'nullable|string',                // Fallback
        'return_location' => 'required|string|max:255',
        'start_time' => 'required|string',
        'vehicle_type' => 'nullable|string',
        'loading_time' => 'nullable|integer',
        'unloading_time' => 'nullable|integer',
        'break_time' => 'nullable|string',
    ]);
    
    $aiService = new AIRouteCalculationService();
    $result = $aiService->calculate($validated, auth()->id());
    
    return response()->json([
        'code' => 200,
        'message' => 'Route calculated successfully',
        'data' => $result,
    ]);
}
```

---

## 実装順序 (Implementation Order) - Part 2: AI Service

### Phase 8: Database Schema for AI Routes (Phụ thuộc: Phase 1-7 completed)

15. **Tạo migration cho quotation_routes** (1.11)
16. **Run migration:** `php artisan migrate`
17. **Update QuotationRoute model** (1.12)

### Phase 9: Prompt Template (Phụ thuộc: Không, có thể song song)

18. **Backup old prompt**
19. **Tạo prompt mới** (1.13)
20. **Verify variables trong prompt**

### Phase 10: AI Service Updates (Phụ thuộc: Phase 8, 9)

21. **Update buildPrompt() method** (1.14.1)
22. **Update calculate() method** (1.14.2)
23. **Update saveLocations() method** (1.14.3)
24. **Update saveSegments() method** (1.14.4)
25. **Update parseAndSaveResponse() method** (1.14.5)

### Phase 11: Controller Updates (Phụ thuộc: Phase 10)

26. **Find và update controller** (1.15)
27. **Update validation rules**

### Phase 12: Testing AI Service (Phụ thuộc: Phase 8-11)

28. **Unit tests cho buildPrompt()**
29. **Integration tests với 1, 3, 5+ deliveries**
30. **Manual testing với Postman**
31. **Verify compliance calculations**

---

## 見積もり工数 (Estimated Effort) - Part 2: AI Service

### AI Service Update: 6-8 giờ

**Phase 8: Database (1 giờ)**
- Migration: 0.5 giờ
- Model update: 0.3 giờ
- Testing: 0.2 giờ

**Phase 9: Prompt Template (0.5 giờ)**
- Backup và tạo mới: 0.3 giờ
- Verify variables: 0.2 giờ

**Phase 10: AI Service (3-4 giờ)**
- buildPrompt(): 0.5 giờ
- calculate(): 0.5 giờ
- saveLocations(): 1 giờ
- saveSegments(): 1 giờ
- parseAndSaveResponse(): 0.5 giờ
- Code review: 0.5 giờ

**Phase 11: Controller (0.5 giờ)**
- Find controller: 0.2 giờ
- Update validation: 0.3 giờ

**Phase 12: Testing (2-3 giờ)**
- Unit tests: 1 giờ
- Integration tests: 1-1.5 giờ
- Manual testing: 0.5 giờ

**合計 Part 2: 6-8 giờ**

**総合計 (Part 1 + Part 2): 10-14 giờ**

---

## 技術的な注意事項 (Technical Notes) - Part 2: AI Service

### 1. Response Format Changes

**Old Format:**
```json
{
  "summary": { ... },
  "time_breakdown": { ... },
  "cost_breakdown": { ... },
  "route_details": {
    "section_1_pickup_to_delivery": { ... },
    "section_2_delivery_to_return": { ... }
  }
}
```

**New Format:**
```json
{
  "summary": {
    "total_distance_km": 156.7,
    "total_tolls_yen": 5800,
    "total_duty_time_hours": 9.5,
    "start_time": "08:00",
    "estimated_end_time": "17:30",
    "date_change": false
  },
  "compliance_info": {
    "required_break_minutes": 90,
    "note": "430ルール適用および労基法休憩"
  },
  "route_segments": [
    {
      "segment_order": 1,
      "type": "回送(積地へ)",
      "from": "東京本社",
      "to": "東京倉庫",
      "distance_km": 5.2,
      "driving_time_minutes": 15,
      "toll_yen": 0,
      "route_description": "一般道経由"
    }
  ]
}
```

### 2. Route Segments Logic

**Example với 3 Delivery Locations:**

```
Input:
- start_location: "東京本社"
- pickup_location: "東京倉庫"
- delivery_locations: ["横浜倉庫", "川崎センター", "千葉配送所"]
- return_location: "東京本社"

Expected Segments:
1. [回送] 東京本社 → 東京倉庫
2. [実車] 東京倉庫 → 横浜倉庫
3. [実車] 横浜倉庫 → 川崎センター
4. [実車] 川崎センター → 千葉配送所
5. [回送] 千葉配送所 → 東京本社

Total: 5 segments (dynamic based on deliveries)
```

### 3. Compliance Rules

**430 Rule:**
- 4時間運転ごとに30分休憩
- Example: 5時間 → 30分休憩

**Labor Standards Act:**
- 6時間超 → 45分休憩
- 8時間超 → 60分休憩

**Combined:**
```
required_break = max(430_rule_break, labor_law_break)
```

### 4. Backward Compatibility

**Strategy:**
- Giữ nguyên field cũ `delivery_location`
- Thêm field mới `delivery_locations` (JSON array)
- Fallback logic trong `buildPrompt()`
- API hỗ trợ cả 2 formats

---

## Risk Assessment - Part 2: AI Service

### High Risk ⚠️

**1. Breaking Changes in AI Response Format**
- **Risk:** Old code expects `route_details.section_1` format
- **Impact:** AI service fails to parse response
- **Mitigation:**
  - Update all parsing methods
  - Add strict validation
  - Test thoroughly with various scenarios

**2. Database Schema Changes**
- **Risk:** Migration fails on production
- **Impact:** Service downtime
- **Mitigation:**
  - Test on staging first
  - Backup database
  - Prepare rollback plan

### Medium Risk ⚠️

**3. Multiple Segments Logic**
- **Risk:** Complex logic với dynamic segments
- **Impact:** Wrong segment order, incorrect mapping
- **Mitigation:**
  - Comprehensive unit tests
  - Test với 1, 3, 5, 10 deliveries
  - Add detailed logging

**4. AI Response Validation**
- **Risk:** AI returns invalid format
- **Impact:** Service crash
- **Mitigation:**
  - Strict validation
  - Error handling
  - Fallback values

---

## Success Criteria - Part 2: AI Service

- [ ] Migration chạy thành công
- [ ] Prompt template updated và verified
- [ ] buildPrompt() handles multiple deliveries
- [ ] saveLocations() tạo đúng số lượng locations
- [ ] saveSegments() tạo dynamic segments
- [ ] parseAndSaveResponse() parse new format
- [ ] Controller validation updated
- [ ] Unit tests pass (buildPrompt, array conversion)
- [ ] Integration tests pass (1, 3, 5+ deliveries)
- [ ] Manual testing với real addresses
- [ ] Compliance calculations correct (430 rule)
- [ ] date_change flag works
- [ ] Error handling robust
- [ ] API documentation updated

