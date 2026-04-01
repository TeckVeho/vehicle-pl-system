# Issue #540: BE_Add functionality for time calculation

## Metadata

- **Title:** BE_Add functionality for time calculation
- **Issue Number:** 540
- **State:** OPEN
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540
- **Created At:** 2025-12-23T09:56:50Z
- **Updated At:** 2025-12-23T09:57:26Z
- **Assignees:** phuongcodeunited
- **Labels:** N/A

---

## Description

### Japanese Version (日本語)

時間計算について機能を追加する。

1. 「積地」の上に「出発地」という列を追加する。
2. 「届け地」の右側のスペースに「出発地」と同じ、というチェックボックスを追加する。チェックすると、自動的に「出発地」と同じ情報が入力される。
3. 「届け地」をプラスボタンで追加できるようにする。プラスボタンをクリックすると下に枠が追加されるようにする。バリデーションは不要。
4. 「帰社地」の右側のスペースに「出発地」と同じ、というチェックボックスを追加する。チェックすると、自動的に「出発地」と同じ情報が入力される。

### Vietnamese Version (Tiếng Việt)

Thêm chức năng cho phần tính toán thời gian.

1. Thêm cột "出発地" (Departure Location) phía trên "積地" (Loading Location).
2. Thêm checkbox giống với "出発地" vào bên phải mục "届け地" (Delivery Location). Khi check, thông tin giống với "出発地" sẽ được tự động nhập.
3. Cho phép thêm "届け地" bằng nút dấu cộng. Nhấp vào nút dấu cộng sẽ thêm một ô bên dưới. Không cần validation.
4. Thêm checkbox giống với "出発地" vào bên phải mục "帰社地" (Return Location). Khi check, thông tin giống với "出発地" sẽ được tự động nhập.

### Referenced Image

![Image](https://github.com/user-attachments/assets/44a2decc-fa48-4257-bfa6-3b31fe3c20c7)

---

## Implementation Checklist

### Backend (BE) Tasks

#### Database Schema Updates

**Migration 1: Update bảng `quotations`**
- [ ] Thêm cột `departure_location` (出発地) kiểu `string/text` vào bảng `quotations`
- [ ] GIỮ NGUYÊN cột `delivery_location` hiện tại (không đổi type)

**Migration 2: Tạo bảng mới `quotation_delivery_locations`**
- [ ] Tạo bảng `quotation_delivery_locations` với cấu trúc:
  - `id` - bigInteger, primary key
  - `quotation_id` - bigInteger, foreign key -> quotations.id
  - `location_name` - string/text - Tên địa điểm giao hàng
  - `sequence_order` - integer - Thứ tự của delivery location (1, 2, 3...)
  - `created_at`, `updated_at` - timestamps
- [ ] Thêm index cho `quotation_id`
- [ ] Thêm foreign key constraint với `onDelete('cascade')`
- [ ] Tạo migration file
- [ ] Chạy migration và test trên database

#### Model Updates

**Model `app/Models/Quotation.php`**
- [ ] Thêm `departure_location` vào `$fillable` array
- [ ] Thêm relationship `hasMany` với `QuotationDeliveryLocation`
```php
public function deliveryLocations()
{
    return $this->hasMany(QuotationDeliveryLocation::class)->orderBy('sequence_order');
}
```

**Tạo Model mới `app/Models/QuotationDeliveryLocation.php`**
- [ ] Tạo model mới với table `quotation_delivery_locations`
- [ ] Định nghĩa `$fillable`: quotation_id, location_name, sequence_order
- [ ] Thêm relationship `belongsTo` với `Quotation`
- [ ] Thêm scope để sort by sequence_order

#### API Endpoints (`app/Http/Controllers/Api/QuotationController.php`)

**API hiện có:**
- GET `/api/quotations` - List quotations
- GET `/api/quotations/{id}` - Get detail
- POST `/api/quotations` - Create quotation
- PUT `/api/quotations/{id}` - Update quotation
- DELETE `/api/quotations/{id}` - Delete quotation

**Cần cập nhật:**
- [ ] **store()** method: Xử lý lưu `departure_location` và tạo records trong `quotation_delivery_locations`
- [ ] **update()** method: Xử lý update `departure_location` và sync `quotation_delivery_locations` (xóa cũ, tạo mới)
- [ ] **show()** method: Eager load `deliveryLocations` relationship
- [ ] **index()** method: Eager load `deliveryLocations` nếu cần
- [ ] Đảm bảo `return_location` có thể nhận giá trị giống `departure_location`

#### Request Validation

**Cập nhật `app/Http/Requests/CreateQuotationRequest.php`**
- [ ] Thêm rule `departure_location` => 'required|string|max:255'
- [ ] Thêm rule `delivery_locations` => 'nullable|array' (array of delivery locations)
- [ ] Thêm rule `delivery_locations.*` => 'nullable|string|max:255' (không bắt buộc validation nghiêm ngặt)
- [ ] Rule cho `return_location` => 'nullable|string|max:255'

**Cập nhật `app/Http/Requests/UpdateQuotationRequest.php`**
- [ ] Tương tự như CreateQuotationRequest
- [ ] Tất cả fields đều nullable cho update

#### API Response Format

**Cập nhật `app/Http/Resources/QuotationResource.php`**
- [ ] Thêm `departure_location` vào response
- [ ] Thêm `delivery_locations` array vào response (map từ relationship)
```php
'departure_location' => $this->departure_location,
'delivery_locations' => $this->deliveryLocations->map(function($dl) {
    return [
        'id' => $dl->id,
        'location_name' => $dl->location_name,
        'sequence_order' => $dl->sequence_order,
    ];
}),
```

#### Repository Layer

**Cập nhật `app/Repositories/QuotationRepository.php`**
- [ ] **create()** method: 
  - Lưu quotation với `departure_location`
  - Loop qua `delivery_locations` array và tạo records trong `quotation_delivery_locations`
  - Sử dụng DB transaction để đảm bảo data consistency
- [ ] **update()** method:
  - Update `departure_location` trong quotation
  - Xóa tất cả `quotation_delivery_locations` cũ
  - Tạo lại records mới từ `delivery_locations` array
  - Sử dụng DB transaction
- [ ] **find/show()** method: Eager load `deliveryLocations` relationship
- [ ] **delete()** method: Cascade delete sẽ tự động xóa `quotation_delivery_locations` (do foreign key constraint)

### Testing

#### Database Testing
- [ ] Test migration chạy thành công
- [ ] Test foreign key constraint hoạt động
- [ ] Test cascade delete khi xóa quotation

#### API Testing - CREATE (POST)
- [ ] Test tạo quotation với `departure_location`
- [ ] Test tạo quotation với 1 delivery location
- [ ] Test tạo quotation với multiple delivery locations (2-5 locations)
- [ ] Test tạo quotation không có delivery locations (empty array)
- [ ] Test `return_location` = `departure_location`
- [ ] Verify data được lưu đúng vào cả 2 bảng
- [ ] Verify `sequence_order` được set đúng (1, 2, 3...)

#### API Testing - READ (GET)
- [ ] Test GET `/api/quotations/{id}` trả về đúng `departure_location`
- [ ] Test GET trả về đúng array `delivery_locations` với đúng thứ tự
- [ ] Test response format đúng với document
- [ ] Test GET list quotations có eager load deliveryLocations

#### API Testing - UPDATE (PUT)
- [ ] Test update `departure_location`
- [ ] Test update delivery locations từ 1 lên 3 locations
- [ ] Test update delivery locations từ 3 xuống 1 location
- [ ] Test update xóa hết delivery locations (empty array)
- [ ] Verify old delivery locations bị xóa và tạo mới đúng
- [ ] Test update `return_location`

#### API Testing - DELETE
- [ ] Test xóa quotation
- [ ] Verify cascade delete xóa luôn delivery locations trong bảng `quotation_delivery_locations`

#### Integration Testing
- [ ] Test DB transaction rollback khi có lỗi
- [ ] Test performance với nhiều delivery locations (10+ locations)
- [ ] Test với Postman/Thunder Client/Insomnia

---

## Technical Notes

### Database Schema Design

#### Bảng `quotations` (Existing - cần update)

**Hiện có:**
```sql
- loading_location: decimal(50) - Tích địa (積地)
- delivery_location: decimal(50) - Địa điểm giao hàng (届け地) - SINGLE VALUE
- return_location: decimal(50) - Địa điểm về công ty (帰社地)
```

**Thêm mới:**
```sql
+ departure_location: VARCHAR(255) - Điểm xuất phát (出発地) - NEW FIELD
```

**Giữ nguyên `delivery_location`** - field cũ có thể deprecated hoặc dùng cho backward compatibility

---

#### Bảng mới `quotation_delivery_locations` (NEW TABLE)

```sql
CREATE TABLE quotation_delivery_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quotation_id BIGINT UNSIGNED NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    sequence_order INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_quotation_id (quotation_id),
    FOREIGN KEY (quotation_id) 
        REFERENCES quotations(id) 
        ON DELETE CASCADE
);
```

**Lý do tách bảng:**
- ✅ Không lưu JSON vào DB, tránh nặng và khó query
- ✅ Dễ dàng thêm/xóa/sửa từng delivery location
- ✅ Có thể query, filter, sort theo delivery location
- ✅ Hỗ trợ unlimited số lượng delivery locations
- ✅ Maintain data integrity với foreign key
- ✅ Cascade delete tự động khi xóa quotation

### Current API Structure

**QuotationController** sử dụng:
- Repository Pattern: `QuotationRepositoryInterface`
- Resource: `QuotationResource` để format response
- Request: `CreateQuotationRequest` và `UpdateQuotationRequest`

**Request Body hiện tại (POST/PUT):**
```json
{
  "title": "見積もりタイトル",
  "author_id": 1,
  "tonnage_id": 1,
  "loading_location": "東京",
  "delivery_location": "横浜",
  "return_location": "東京",
  "start_time": "09:00",
  ...
}
```

**Request Body SAU KHI CẬP NHẬT (POST/PUT):**
```json
{
  "title": "見積もりタイトル",
  "author_id": 1,
  "tonnage_id": 1,
  "departure_location": "東京本社",     // 出発地 (NEW FIELD)
  "loading_location": "東京",           // 積地 (unchanged)
  "delivery_locations": [               // 届け地 ARRAY (NEW - lưu vào bảng riêng)
    "横浜倉庫",
    "川崎センター",
    "千葉配送所"
  ],
  "return_location": "東京本社",         // 帰社地 (có thể copy từ departure_location)
  "start_time": "09:00",
  ...
}
```

**Response Format SAU KHI CẬP NHẬT (GET):**
```json
{
  "code": 200,
  "data": {
    "id": 1,
    "title": "見積もりタイトル",
    "departure_location": "東京本社",
    "loading_location": "東京",
    "delivery_locations": [
      {
        "id": 1,
        "location_name": "横浜倉庫",
        "sequence_order": 1
      },
      {
        "id": 2,
        "location_name": "川崎センター",
        "sequence_order": 2
      },
      {
        "id": 3,
        "location_name": "千葉配送所",
        "sequence_order": 3
      }
    ],
    "return_location": "東京本社",
    ...
  }
}
```

### Logic Flow for Frontend

1. User nhập "出発地" (departure_location)
2. Khi check box "same as departure" ở "届け地":
   - Frontend tự động copy giá trị từ departure_location
   - Frontend có thể add multiple delivery locations
3. Khi check box "same as departure" ở "帰社地":
   - Frontend tự động copy giá trị từ departure_location vào return_location

**Backend chỉ nhận và lưu data, không xử lý logic auto-copy (do frontend xử lý)**

### Migration Strategy

**Step 1: Migration thêm cột `departure_location`**
```php
Schema::table('quotations', function (Blueprint $table) {
    $table->string('departure_location', 255)->nullable()->after('tonnage_id');
});
```

**Step 2: Migration tạo bảng `quotation_delivery_locations`**
```php
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
```

**Step 3: Data Migration (Optional)**
- Nếu có data cũ trong `delivery_location`, có thể migrate sang bảng mới
- Hoặc để field cũ giữ nguyên cho backward compatibility

### Implementation Flow trong Repository

**QuotationRepository::create()**
```php
public function create(array $attributes)
{
    return DB::transaction(function () use ($attributes) {
        // Tách delivery_locations ra khỏi attributes
        $deliveryLocations = $attributes['delivery_locations'] ?? [];
        unset($attributes['delivery_locations']);
        
        // Tạo quotation
        $quotation = Quotation::create($attributes);
        
        // Tạo delivery locations
        foreach ($deliveryLocations as $index => $location) {
            $quotation->deliveryLocations()->create([
                'location_name' => $location,
                'sequence_order' => $index + 1,
            ]);
        }
        
        return $quotation->load('deliveryLocations');
    });
}
```

**QuotationRepository::update()**
```php
public function update(array $attributes, $id)
{
    return DB::transaction(function () use ($attributes, $id) {
        $quotation = Quotation::findOrFail($id);
        
        // Tách delivery_locations
        $deliveryLocations = $attributes['delivery_locations'] ?? null;
        unset($attributes['delivery_locations']);
        
        // Update quotation
        $quotation->update($attributes);
        
        // Sync delivery locations
        if ($deliveryLocations !== null) {
            // Xóa hết locations cũ
            $quotation->deliveryLocations()->delete();
            
            // Tạo mới
            foreach ($deliveryLocations as $index => $location) {
                $quotation->deliveryLocations()->create([
                    'location_name' => $location,
                    'sequence_order' => $index + 1,
                ]);
            }
        }
        
        return $quotation->load('deliveryLocations');
    });
}
```

### API Backward Compatibility

**Chiến lược:**
- ✅ Giữ nguyên field cũ `delivery_location` trong DB (không xóa)
- ✅ Frontend mới sẽ dùng `delivery_locations` (array)
- ✅ Frontend cũ (nếu có) vẫn có thể dùng `delivery_location` (single value)
- ✅ API hỗ trợ cả 2 format trong transition period
- ⚠️ Sau khi tất cả frontend migrate xong, có thể deprecate field cũ

---

## Review Notes

- [ ] Code review completed
- [ ] All tests passed
- [ ] Documentation updated
- [ ] Ready for QA testing

---

## Related Issues/PRs

_To be updated during development_

---

## Development Notes

### Update Request (2025-12-24)

**Yêu cầu bổ sung:** Cập nhật AI Prompt cho tính toán route

**Chi tiết:**
- Cần cập nhật prompt gửi đến AI để tính toán route phức tạp hơn
- Prompt mới hỗ trợ multiple delivery locations (届け地)
- Tham khảo: [GitHub Comment #3689198629](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540#issuecomment-3689198629)

---

## AI Prompt Update Requirements

### Current Implementation
File cần cập nhật: `app/Services/AIRouteCalculationService.php` (hoặc service tương tự xử lý AI route calculation)

### New Prompt Template

Prompt mới cần hỗ trợ các biến sau:

**Input Variables:**
- `{start_location}` - 出発地 (Start Location/Departure)
- `{pickup_location}` - 積地 (Pickup Location/Loading)
- `{delivery_locations}` - 届け地リスト (Delivery Locations List - comma separated or array)
- `{return_location}` - 帰社地 (Return Location)
- `{start_time}` - 運行開始時間 (Start Time)
- `{vehicle_type}` - 車両区分 (Vehicle Type, default: "中型車(4t)")
- `{loading_time}` - 積み込み作業時間 (Loading Time, default: 60 minutes)
- `{unloading_time}` - 荷下ろし作業時間 (Unloading Time per stop, default: 30 minutes)
- `{break_time}` - 休憩時間指定 (Break Time, default: "Auto")

### Complete New AI Prompt

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
* **届け地リスト (Delivery Locations):** {delivery_locations} (※カンマ区切り、または配列形式で複数の住所が渡されます)
* **帰社地 (Return Location):** {return_location}
* **運行開始時間 (Start Time):** {start_time}
* **車両区分 (Vehicle Type):** {vehicle_type} (指定がなければ"中型車(4t)"とする)
* **積み込み作業時間 (Loading Time):** {loading_time} (指定がなければ60分とする)
* **荷下ろし作業時間 (Unloading Time per stop):** {unloading_time} (指定がなければ**1カ所につき**30分とする)
* **休憩時間指定 (User Break Time):** {break_time} (指定がない、または"Auto"の場合は法令に基づき自動算出する)

# Steps / Thinking Process
以下の手順でルートを構築し、計算を行ってください。

1.  **ルートセグメントの構築:**
    以下の順序で走行ルートを定義します。
    * [Segment 0: 回送] 出発地 → 積地 (※出発地と積地が同じ・極めて近い場合は距離0とする)
    * [Segment 1: 実車] 積地 → 最初の届け地
    * [Segment 2...N: 実車] 届け地(i) → 届け地(i+1) (※届け地が複数ある場合)
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
        * 上記に基づき、必要な休憩時間を自動算出し、拘束時間に加算してください。

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
    "note": "休憩時間の算出根拠（例: 運転時間が長いため430ルール適用）"
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
      "route_description": "ルート概要（例: 首都高湾岸線経由）"
    }
  ]
}
```

### Implementation Checklist for AI Prompt Update

#### Phase 1: Database Schema for AI Routes

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_update_quotation_routes_for_multiple_deliveries.php`

- [ ] Check current schema của bảng `quotation_routes`
- [ ] Tạo migration thêm cột `start_location` (VARCHAR 255, nullable)
- [ ] Quyết định approach: JSON column hoặc separate table cho `delivery_locations`
  - **Option A (Quick):** Thêm cột `delivery_locations` kiểu JSON
  - **Option B (Better):** Tạo bảng `quotation_route_delivery_locations` tương tự như `quotation_delivery_locations`
- [ ] Thêm cột `compliance_note` (TEXT, nullable)
- [ ] Run migrations: `php artisan migrate`
- [ ] Test migrations thành công

**Model Update:**
- [ ] Update `app/Models/QuotationRoute.php`
- [ ] Thêm `start_location`, `delivery_locations`, `compliance_note` vào `$fillable`
- [ ] Thêm cast cho `delivery_locations` => 'array' (nếu dùng JSON)
- [ ] Thêm relationship `deliveryLocationsList()` (nếu dùng separate table)

#### Phase 2: Prompt Template Update

**File:** `storage/app/prompts/route_calculation_prompt.txt`

- [ ] Backup prompt cũ (rename to `route_calculation_prompt.txt.old`)
- [ ] Tạo prompt mới với full template từ GitHub comment
- [ ] Verify các variables:
  - `{start_location}` - NEW
  - `{pickup_location}` - existing
  - `{delivery_locations}` - NEW (comma-separated)
  - `{return_location}` - existing
  - `{start_time}` - existing
  - `{vehicle_type}` - existing
  - `{loading_time}` - existing
  - `{unloading_time}` - existing (default changed to 30)
  - `{break_time}` - existing
- [ ] Test prompt format

#### Phase 3: AI Service Update

**File:** `app/Services/AIRouteCalculationService.php`

**Method: `buildPrompt()`** (Line 107-138)
- [ ] Thêm xử lý `start_location` variable
- [ ] Thêm xử lý `delivery_locations` array
  - Convert array to comma-separated string: `implode('、', $deliveryLocations)`
  - Fallback to old `delivery_location` nếu array empty
- [ ] Update variable mapping:
  ```php
  '{start_location}' => $input['start_location'] ?? $input['departure_location'] ?? '',
  '{pickup_location}' => $input['pickup_location'] ?? $input['loading_location'] ?? '',
  '{delivery_locations}' => $deliveryLocationsStr,  // NEW
  ```
- [ ] Update default value cho `unloading_time` từ 60 → 30 minutes

**Method: `calculate()`** (Line 27-47)
- [ ] Accept `start_location` input parameter
- [ ] Accept `delivery_locations` array input parameter
- [ ] Save to `QuotationRoute` model:
  ```php
  'start_location' => $input['start_location'] ?? null,
  'delivery_locations' => $input['delivery_locations'] ?? null,  // JSON or separate table
  ```

**Method: `saveLocations()`** (Line 277-302)
- [ ] Thêm location type `start` với sequence_order = 1
- [ ] Update location type `pickup` với sequence_order = 2
- [ ] Handle multiple delivery locations (loop qua array)
  - Sequence order: 3, 4, 5, ... (dynamic)
  - Location type: `delivery`
- [ ] Update location type `return` với sequence_order = N (last)
- [ ] Code example:
  ```php
  $locations = [
      ['sequence_order' => 1, 'location_type' => 'start', 'address' => $route->start_location],
      ['sequence_order' => 2, 'location_type' => 'pickup', 'address' => $route->pickup_location],
  ];
  
  $sequenceOrder = 3;
  foreach ($deliveryLocations as $deliveryLocation) {
      $locations[] = [
          'sequence_order' => $sequenceOrder++,
          'location_type' => 'delivery',
          'address' => $deliveryLocation,
      ];
  }
  
  $locations[] = [
      'sequence_order' => $sequenceOrder,
      'location_type' => 'return',
      'address' => $route->return_location,
  ];
  ```

**Method: `saveSegments()`** (Line 304-336)
- [ ] Update để parse new response format `route_segments` array
- [ ] Remove hardcoded section_1 và section_2
- [ ] Loop qua `route_segments` array từ AI response
- [ ] Map `segment_order` to locations dynamically
- [ ] Save `segment_type` field (回送/実車)
- [ ] Code example:
  ```php
  foreach ($routeSegments as $segment) {
      $segmentOrder = $segment['segment_order'];
      $fromLocationIndex = $segmentOrder - 1;
      $toLocationIndex = $segmentOrder;
      
      QuotationRouteSegment::create([
          'route_id' => $route->id,
          'from_location_id' => $locations[$fromLocationIndex]->id,
          'to_location_id' => $locations[$toLocationIndex]->id,
          'segment_order' => $segmentOrder,
          'segment_type' => $segment['type'] ?? null,  // NEW
          'distance_km' => $segment['distance_km'] ?? 0,
          'driving_time_minutes' => $segment['driving_time_minutes'] ?? 0,
          'highway_fee' => $segment['toll_yen'] ?? 0,
          'route_description' => $segment['route_description'] ?? null,
      ]);
  }
  ```

**Method: `parseAndSaveResponse()`** (Line 249-275)
- [ ] Update để parse new response structure
- [ ] Parse `compliance_info` section:
  ```php
  $complianceInfo = $response['compliance_info'] ?? [];
  $route->update([
      'total_break_time_minutes' => $complianceInfo['required_break_minutes'] ?? null,
      'compliance_note' => $complianceInfo['note'] ?? null,
  ]);
  ```
- [ ] Update field mapping cho `summary`:
  - `total_tolls_yen` → `highway_fee`
  - `total_duty_time_hours` → `total_duty_time_hours`

#### Phase 4: API Controller Update

**File:** Find controller using `AIRouteCalculationService`

- [ ] Locate controller (search: `grep -r "AIRouteCalculationService" app/Http/Controllers/`)
- [ ] Update validation rules:
  ```php
  'start_location' => 'nullable|string|max:255',
  'pickup_location' => 'required|string|max:255',
  'delivery_locations' => 'nullable|array',
  'delivery_locations.*' => 'nullable|string|max:255',
  'delivery_location' => 'nullable|string',  // Fallback
  'return_location' => 'required|string|max:255',
  ```
- [ ] Update request handling để accept new fields
- [ ] Update response format nếu cần

#### Phase 5: Testing

**Unit Tests:**
- [ ] Test `buildPrompt()` với single delivery location
- [ ] Test `buildPrompt()` với multiple delivery locations (2-5)
- [ ] Test `buildPrompt()` với empty delivery locations array
- [ ] Test `buildPrompt()` với fallback to old `delivery_location`
- [ ] Test delivery locations array → comma-separated conversion
- [ ] Test default values (vehicle_type, loading_time, unloading_time=30, break_time)

**Integration Tests:**
- [ ] Test full flow với 1 delivery location
- [ ] Test full flow với 3 delivery locations
- [ ] Test full flow với 5+ delivery locations
- [ ] Test với same start and return location
- [ ] Test response parsing với new format
- [ ] Test `saveLocations()` tạo đúng số lượng locations
- [ ] Test `saveSegments()` tạo đúng số lượng segments
- [ ] Test compliance calculations (430 rule, labor law)
- [ ] Test `date_change` flag
- [ ] Test error handling với invalid AI response

**Manual Testing:**
- [ ] Test với Postman/Thunder Client
- [ ] Verify route segments order
- [ ] Verify segment types (回送/実車)
- [ ] Verify distance calculations
- [ ] Verify toll calculations
- [ ] Verify break time calculations
- [ ] Verify end time calculations
- [ ] Test với real Japanese addresses

#### Phase 6: Documentation & Deployment

- [ ] Update API documentation (Swagger/Postman collection)
- [ ] Update README nếu cần
- [ ] Document new prompt format
- [ ] Document new response structure
- [ ] Code review
- [ ] Deploy to staging
- [ ] QA testing on staging
- [ ] Deploy to production
- [ ] Monitor AI responses

---

## Technical Implementation Details

### New Response Structure (AI)

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
    "note": "430ルール適用(4時間運転ごと30分)および労基法休憩(8時間超60分)"
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
    },
    {
      "segment_order": 2,
      "type": "実車配送",
      "from": "東京倉庫",
      "to": "横浜倉庫",
      "distance_km": 35.8,
      "driving_time_minutes": 45,
      "toll_yen": 1600,
      "route_description": "首都高速・横浜環状経由"
    }
  ]
}
```

### Route Segments Logic

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

Total: 5 segments (dynamic based on number of deliveries)
```

### Database Schema Changes Required

**Table: `quotation_routes`**

```sql
-- Add new columns
ALTER TABLE quotation_routes 
ADD COLUMN start_location VARCHAR(255) NULL AFTER user_id,
ADD COLUMN delivery_locations JSON NULL AFTER delivery_location,
ADD COLUMN compliance_note TEXT NULL;

-- Or if using separate table:
CREATE TABLE quotation_route_delivery_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    route_id BIGINT UNSIGNED NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    sequence_order INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_route_id (route_id),
    FOREIGN KEY (route_id) REFERENCES quotation_routes(id) ON DELETE CASCADE
);
```

**Table: `quotation_route_segments`**

```sql
-- Add segment_type column if not exists
ALTER TABLE quotation_route_segments 
ADD COLUMN segment_type VARCHAR(50) NULL AFTER segment_order;
```

---

## Risk Assessment & Mitigation

### High Risk ⚠️

**1. Breaking Changes in AI Response Format**
- **Risk:** Old code expects `route_details.section_1_pickup_to_delivery` format
- **Impact:** AI service sẽ fail khi parse response
- **Mitigation:** 
  - Update `parseAndSaveResponse()` và `saveSegments()` methods
  - Add validation cho new format
  - Keep backward compatibility nếu có thể

**2. Database Schema Changes**
- **Risk:** Migration có thể fail trên production
- **Impact:** Service downtime
- **Mitigation:**
  - Test migrations thoroughly trên staging
  - Backup database trước khi migrate
  - Prepare rollback plan

### Medium Risk ⚠️

**3. Multiple Segments Logic**
- **Risk:** Logic phức tạp với dynamic number of segments
- **Impact:** Sai thứ tự segments, mapping locations không đúng
- **Mitigation:**
  - Comprehensive unit tests
  - Test với nhiều scenarios (1, 3, 5, 10 deliveries)
  - Add logging để debug

**4. AI Response Validation**
- **Risk:** AI có thể trả về format không đúng hoặc thiếu fields
- **Impact:** Service crash hoặc data không đầy đủ
- **Mitigation:**
  - Strict validation cho AI response
  - Error handling và retry logic
  - Fallback values cho missing fields

### Low Risk ✅

**5. Prompt Template Update**
- **Risk:** Thấp, chỉ là text file
- **Mitigation:** Backup old prompt, test thoroughly

---

## Estimated Effort

**Total: 6-8 giờ**

- Phase 1 (Database): 1 giờ
- Phase 2 (Prompt): 0.5 giờ
- Phase 3 (AI Service): 3-4 giờ
- Phase 4 (Controller): 0.5 giờ
- Phase 5 (Testing): 2-3 giờ
- Phase 6 (Documentation): 0.5 giờ

---

## References

- **GitHub Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540
- **New Prompt Comment:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540#issuecomment-3689198629
- **Detailed Guide:** `docs/issues/540/ai-prompt-update.md`
- **Implementation Summary:** `docs/issues/540/implementation-summary.md`

---

_Add any notes, blockers, or important discoveries during development here_

