# Issue #540 - Implementation Summary & Required Updates

## Overview

Issue #540 yêu cầu cập nhật hệ thống để hỗ trợ:
1. ✅ **Database**: Thêm `departure_location` và multiple `delivery_locations` 
2. ✅ **API**: Cập nhật endpoints để xử lý multiple delivery locations
3. 🔄 **AI Service**: Cập nhật prompt để tính toán route phức tạp hơn

---

## Part 1: Database & API (ĐÃ HOÀN THÀNH ✅)

### Database Changes

**Đã implement:**
- ✅ Migration thêm cột `departure_location` vào bảng `quotations`
- ✅ Migration tạo bảng `quotation_delivery_locations`
- ✅ Foreign key constraints và cascade delete

**Schema hiện tại:**

```sql
-- quotations table
ALTER TABLE quotations ADD COLUMN departure_location VARCHAR(255) NULL;

-- quotation_delivery_locations table (NEW)
CREATE TABLE quotation_delivery_locations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  quotation_id BIGINT UNSIGNED NOT NULL,
  location_name VARCHAR(255) NOT NULL,
  sequence_order INT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_quotation_id (quotation_id),
  FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
);
```

### API Changes

**Đã implement:**
- ✅ Model `Quotation` - thêm `departure_location` vào `$fillable`
- ✅ Model `Quotation` - thêm relationship `deliveryLocations()`
- ✅ Model `QuotationDeliveryLocation` - model mới
- ✅ Repository `QuotationRepository` - override `create()` và `update()`
- ✅ Request validation - thêm rules cho `departure_location` và `delivery_locations`
- ✅ Resource `QuotationResource` - format response với `delivery_locations` array
- ✅ Controller - eager load `deliveryLocations`

**Current Quotation Model Fields:**

```php
protected $fillable = [
    'title',
    'author_id',
    'tonnage_id',
    'departure_location',        // ✅ NEW
    'loading_location',           // ✅ Existing (積地)
    'delivery_location',          // ⚠️ Old single value (kept for backward compatibility)
    'return_location',            // ✅ Existing (帰社地)
    'start_time',
    'loading_time',               // ✅ Existing
    'unloading_time',             // ✅ Existing
    // ... other fields
];
```

---

## Part 2: AI Service Update (CẦN CẬP NHẬT 🔄)

### Current AI Service Analysis

**File:** `app/Services/AIRouteCalculationService.php`

**Current Implementation:**
- ✅ Có sẵn AI route calculation service
- ✅ Sử dụng OpenAI API
- ✅ Đọc prompt từ file: `storage/app/prompts/route_calculation_prompt.txt`
- ⚠️ **Chỉ hỗ trợ single delivery location**
- ⚠️ **Chưa có `departure_location` (出発地)**

**Current Variables Mapping:**

| Current Variable | Maps To | Status |
|-----------------|---------|--------|
| `{pickup_location}` | 積地 (Loading) | ✅ OK |
| `{delivery_location}` | 届け地 (Delivery) - Single | ⚠️ Need Update |
| `{return_location}` | 帰社地 (Return) | ✅ OK |
| `{start_time}` | 運行開始時間 | ✅ OK |
| `{vehicle_type}` | 車両区分 | ✅ OK |
| `{loading_time}` | 積み込み作業時間 | ✅ OK |
| `{unloading_time}` | 荷下ろし作業時間 | ✅ OK |
| `{break_time}` | 休憩時間 | ✅ OK |
| - | **出発地 (Departure)** | ❌ MISSING |
| - | **届け地リスト (Multiple Deliveries)** | ❌ MISSING |

### Required Changes

#### 1. Update Prompt Template File

**File:** `storage/app/prompts/route_calculation_prompt.txt`

**Action:** Thay thế toàn bộ nội dung bằng prompt mới từ GitHub comment

**New Variables:**
```
{start_location}        - 出発地 (NEW)
{pickup_location}       - 積地 (existing)
{delivery_locations}    - 届け地リスト (NEW - comma separated)
{return_location}       - 帰社地 (existing)
{start_time}           - 運行開始時間 (existing)
{vehicle_type}         - 車両区分 (existing)
{loading_time}         - 積み込み作業時間 (existing)
{unloading_time}       - 荷下ろし作業時間 (existing)
{break_time}           - 休憩時間 (existing)
```

#### 2. Update `buildPrompt()` Method

**Current Code (Line 107-138):**

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
        '{delivery_location}',      // ⚠️ Single delivery
        '{return_location}',
        '{start_time}',
        '{vehicle_type}',
        '{loading_time}',
        '{unloading_time}',
        '{break_time}',
    ], [
        $input['pickup_location'],
        $input['delivery_location'],  // ⚠️ Single value
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

**New Code (Required):**

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
    
    // Fallback to old single delivery_location if delivery_locations is empty
    if (empty($deliveryLocationsStr) && !empty($input['delivery_location'])) {
        $deliveryLocationsStr = $input['delivery_location'];
    }
    
    $userMessage = str_replace([
        '{start_location}',           // NEW
        '{pickup_location}',
        '{delivery_locations}',       // NEW (array → comma separated)
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
        $input['unloading_time'] ?? 30,  // Changed default to 30 per spec
        $input['break_time'] ?? 'Auto',
    ], $promptTemplate);

    return $userMessage;
}
```

#### 3. Update `calculate()` Method

**Current Code (Line 27-47):**

```php
public function calculate(array $input, int $userId)
{
    // ...
    $route = QuotationRoute::create([
        'route_code' => $routeCode,
        'user_id' => $userId,
        'title' => $input['title'] ?? null,
        'pickup_location' => $input['pickup_location'],
        'delivery_location' => $input['delivery_location'],  // ⚠️ Single
        'return_location' => $input['return_location'],
        'start_time' => $input['start_time'],
        'vehicle_type' => $input['vehicle_type'] ?? '4t',
        'loading_time_minutes' => $input['loading_time'] ?? 60,
        'unloading_time_minutes' => $input['unloading_time'] ?? 60,
        'user_break_time_minutes' => $input['break_time'] ?? null,
        'status' => 'pending',
        'ai_model_used' => $this->aiModel,
    ]);
    // ...
}
```

**Required Changes:**

Cần kiểm tra schema của bảng `quotation_routes`:
- Có cột `start_location` (departure) chưa?
- Có hỗ trợ multiple delivery locations chưa?

**Có 2 options:**

**Option A: Cập nhật DB schema cho `quotation_routes`**
- Thêm cột `start_location`
- Tạo bảng `quotation_route_delivery_locations` tương tự như `quotation_delivery_locations`

**Option B: Lưu delivery_locations dưới dạng JSON** (Quick fix)
- Thêm cột `delivery_locations` kiểu JSON
- Giữ nguyên `delivery_location` cũ

#### 4. Update `saveLocations()` Method

**Current Code (Line 277-302):**

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
            'address' => $route->delivery_location,  // ⚠️ Single
        ],
        [
            'sequence_order' => 3,
            'location_type' => 'return',
            'address' => $route->return_location,
        ],
    ];
    // ...
}
```

**New Code (Required):**

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
        $locations[] = [
            'sequence_order' => $sequenceOrder++,
            'location_type' => 'delivery',
            'address' => $deliveryLocation,
        ];
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

#### 5. Update `saveSegments()` Method

**Current Code (Line 304-336):**

```php
protected function saveSegments(QuotationRoute $route, array $response): void
{
    $routeDetails = $response['route_details'] ?? [];
    $locations = $route->locations;
    
    // Only handles 2 segments: pickup→delivery, delivery→return
    if (isset($routeDetails['section_1_pickup_to_delivery'])) {
        // ...
    }
    
    if (isset($routeDetails['section_2_delivery_to_return'])) {
        // ...
    }
}
```

**New Code (Required):**

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
        // etc.
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
            'segment_type' => $segment['type'] ?? null,
            'distance_km' => $segment['distance_km'] ?? 0,
            'driving_time_minutes' => $segment['driving_time_minutes'] ?? 0,
            'highway_fee' => $segment['toll_yen'] ?? 0,
            'route_description' => $segment['route_description'] ?? null,
        ]);
    }
}
```

#### 6. Update Response Parsing

**Current Response Structure:**

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

**New Response Structure:**

```json
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
    "note": "string"
  },
  "route_segments": [
    {
      "segment_order": 1,
      "type": "回送(積地へ)" | "実車配送" | "回送(帰庫)",
      "from": "地点名",
      "to": "地点名",
      "distance_km": float,
      "driving_time_minutes": int,
      "toll_yen": int,
      "route_description": "string"
    }
  ]
}
```

**Update `parseAndSaveResponse()` method:**

```php
protected function parseAndSaveResponse(QuotationRoute $route, array $response): void
{
    DB::transaction(function () use ($route, $response) {
        $summary = $response['summary'] ?? [];
        $complianceInfo = $response['compliance_info'] ?? [];
        
        $route->update([
            'total_distance_km' => $summary['total_distance_km'] ?? null,
            'estimated_end_time' => $summary['estimated_end_time'] ?? null,
            'date_change' => $summary['date_change'] ?? false,
            'total_duty_time_hours' => $summary['total_duty_time_hours'] ?? null,
            'highway_fee' => $summary['total_tolls_yen'] ?? 0,
            'total_break_time_minutes' => $complianceInfo['required_break_minutes'] ?? null,
            'compliance_note' => $complianceInfo['note'] ?? null,
        ]);
        
        $this->saveLocations($route, $response);
        
        $this->saveSegments($route, $response);
    });
}
```

---

## Part 3: Database Schema for AI Route (CẦN KIỂM TRA)

### Check Current Schema

Cần kiểm tra bảng `quotation_routes`:

```bash
php artisan tinker
>>> Schema::getColumnListing('quotation_routes')
```

### Required Columns

Bảng `quotation_routes` cần có:

```sql
-- Existing columns (expected)
id
route_code
user_id
title
pickup_location
delivery_location          -- ⚠️ Old single value
return_location
start_time
vehicle_type
loading_time_minutes
unloading_time_minutes
user_break_time_minutes
status
ai_model_used
total_distance_km
estimated_end_time
date_change
highway_fee
created_at
updated_at

-- NEW columns needed
start_location             -- ❌ Need to add (出発地)
delivery_locations         -- ❌ Need to add (JSON or separate table)
total_duty_time_hours      -- ❌ May need to add
compliance_note            -- ❌ May need to add
```

### Migration Required

**Option A: Add JSON column (Quick)**

```php
Schema::table('quotation_routes', function (Blueprint $table) {
    $table->string('start_location', 255)->nullable()->after('user_id');
    $table->json('delivery_locations')->nullable()->after('delivery_location');
    $table->text('compliance_note')->nullable();
});
```

**Option B: Separate table (Better for querying)**

```php
// Migration 1: Add start_location
Schema::table('quotation_routes', function (Blueprint $table) {
    $table->string('start_location', 255)->nullable()->after('user_id');
    $table->text('compliance_note')->nullable();
});

// Migration 2: Create quotation_route_delivery_locations table
Schema::create('quotation_route_delivery_locations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('route_id');
    $table->string('location_name', 255);
    $table->integer('sequence_order');
    $table->timestamps();
    
    $table->index('route_id');
    $table->foreign('route_id')
          ->references('id')
          ->on('quotation_routes')
          ->onDelete('cascade');
});
```

### Update QuotationRoute Model

```php
// app/Models/QuotationRoute.php

protected $fillable = [
    'route_code',
    'user_id',
    'title',
    'start_location',              // NEW
    'pickup_location',
    'delivery_location',           // Keep for backward compatibility
    'delivery_locations',          // NEW (if using JSON)
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
    'delivery_locations' => 'array',  // NEW (if using JSON)
];

// NEW relationship (if using separate table)
public function deliveryLocationsList()
{
    return $this->hasMany(QuotationRouteDeliveryLocation::class, 'route_id')
                ->orderBy('sequence_order');
}
```

---

## Part 4: API Controller Update

### Check Current Controller

Cần tìm controller xử lý AI route calculation:

```bash
grep -r "AIRouteCalculationService" app/Http/Controllers/
```

### Expected Controller Usage

```php
// app/Http/Controllers/Api/QuotationRouteController.php (or similar)

public function calculate(Request $request)
{
    $validated = $request->validate([
        'title' => 'nullable|string',
        'start_location' => 'nullable|string',           // NEW
        'pickup_location' => 'required|string',
        'delivery_locations' => 'nullable|array',        // NEW
        'delivery_locations.*' => 'nullable|string',     // NEW
        'delivery_location' => 'nullable|string',        // Fallback
        'return_location' => 'required|string',
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

## Implementation Checklist

### Phase 1: Database Schema (AI Routes)

- [ ] Check current schema của `quotation_routes`
- [ ] Tạo migration thêm `start_location` column
- [ ] Quyết định: JSON column hay separate table cho delivery_locations
- [ ] Tạo migration cho delivery_locations (nếu dùng separate table)
- [ ] Thêm `compliance_note` column
- [ ] Run migrations
- [ ] Update `QuotationRoute` model
- [ ] Test migrations

### Phase 2: Prompt Template Update

- [ ] Backup prompt cũ: `storage/app/prompts/route_calculation_prompt.txt`
- [ ] Tạo prompt mới với full template từ GitHub comment
- [ ] Test prompt template có đầy đủ variables không
- [ ] Verify format output JSON

### Phase 3: AI Service Update

- [ ] Update `buildPrompt()` method
  - [ ] Thêm `{start_location}` variable
  - [ ] Thêm `{delivery_locations}` variable (array → comma separated)
  - [ ] Update default values
  - [ ] Add fallback logic
- [ ] Update `calculate()` method
  - [ ] Accept `start_location` input
  - [ ] Accept `delivery_locations` array input
  - [ ] Save to `QuotationRoute` model
- [ ] Update `saveLocations()` method
  - [ ] Handle start location
  - [ ] Handle multiple delivery locations
  - [ ] Update sequence_order logic
- [ ] Update `saveSegments()` method
  - [ ] Parse new `route_segments` array format
  - [ ] Handle dynamic number of segments
  - [ ] Map segment_order to locations correctly
- [ ] Update `parseAndSaveResponse()` method
  - [ ] Parse new response structure
  - [ ] Handle `compliance_info` section
  - [ ] Update field mappings

### Phase 4: API Controller Update

- [ ] Find controller sử dụng `AIRouteCalculationService`
- [ ] Update validation rules
  - [ ] Add `start_location` validation
  - [ ] Add `delivery_locations` array validation
- [ ] Update request handling
- [ ] Update response format

### Phase 5: Testing

- [ ] Unit test: `buildPrompt()` với single delivery
- [ ] Unit test: `buildPrompt()` với multiple deliveries (2-5)
- [ ] Unit test: `buildPrompt()` với empty deliveries
- [ ] Unit test: Response parsing
- [ ] Integration test: Full flow với 1 delivery
- [ ] Integration test: Full flow với 3 deliveries
- [ ] Integration test: Full flow với 5+ deliveries
- [ ] Test compliance calculations (430 rule)
- [ ] Test date_change flag
- [ ] Test error handling
- [ ] Manual test với Postman

### Phase 6: Documentation

- [ ] Update API documentation
- [ ] Update README nếu cần
- [ ] Document new prompt format
- [ ] Document new response structure

---

## Risk Assessment

### High Risk ⚠️

1. **Breaking Changes:**
   - Prompt format thay đổi hoàn toàn
   - Response structure khác
   - **Mitigation:** Keep backward compatibility, test thoroughly

2. **AI Response Validation:**
   - AI có thể trả về format không đúng
   - **Mitigation:** Strict validation, error handling, retry logic

### Medium Risk ⚠️

3. **Database Schema:**
   - Cần migration cho `quotation_routes`
   - **Mitigation:** Test migrations trên staging first

4. **Multiple Segments:**
   - Logic phức tạp hơn với nhiều delivery locations
   - **Mitigation:** Comprehensive testing với nhiều scenarios

### Low Risk ✅

5. **Quotation API:**
   - Phần này đã hoàn thành và stable
   - Không ảnh hưởng đến AI service

---

## Estimated Effort

### Backend Development: 6-8 giờ

**Phase 1: Database (1 giờ)**
- Migrations: 0.5 giờ
- Model updates: 0.3 giờ
- Testing: 0.2 giờ

**Phase 2: Prompt Update (0.5 giờ)**
- Create new prompt template: 0.3 giờ
- Verify variables: 0.2 giờ

**Phase 3: AI Service (3-4 giờ)**
- Update buildPrompt(): 0.5 giờ
- Update calculate(): 0.5 giờ
- Update saveLocations(): 1 giờ
- Update saveSegments(): 1 giờ
- Update parseAndSaveResponse(): 0.5 giờ
- Code review: 0.5 giờ

**Phase 4: Controller (0.5 giờ)**
- Update validation: 0.2 giờ
- Update handling: 0.3 giờ

**Phase 5: Testing (2-3 giờ)**
- Unit tests: 1 giờ
- Integration tests: 1-1.5 giờ
- Manual testing: 0.5 giờ

**Phase 6: Documentation (0.5 giờ)**

**Total: 6-8 giờ**

---

## Next Steps

1. **Xác nhận với team:**
   - Schema design cho `quotation_routes` (JSON vs separate table)
   - Breaking changes có acceptable không
   - Timeline deployment

2. **Bắt đầu implementation:**
   - Phase 1: Database migrations
   - Phase 2: Prompt template
   - Phase 3: AI Service updates
   - Phase 4: Testing

3. **Deployment plan:**
   - Deploy to staging
   - Test thoroughly
   - Deploy to production
   - Monitor AI responses

---

## References

- **GitHub Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540
- **GitHub Comment (New Prompt):** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540#issuecomment-3689198629
- **Documentation:**
  - `docs/issues/540/issue.md`
  - `docs/issues/540/ai-prompt-update.md`
  - `docs/issues/540/plan.md`
  - `docs/issues/540/dev.md`

---

## Conclusion

Issue #540 bao gồm 2 phần chính:

1. **✅ Quotation API (Completed):**
   - Database schema updated
   - API endpoints updated
   - Multiple delivery locations supported

2. **🔄 AI Route Calculation (In Progress):**
   - Cần update database schema cho `quotation_routes`
   - Cần update prompt template
   - Cần update AI service logic
   - Cần testing thoroughly

**Total Effort:** ~6-8 giờ development + testing

**Status:** Ready to implement Phase 1 (Database migrations)

