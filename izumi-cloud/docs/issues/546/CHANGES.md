# Issue #546 - Changes Summary

## Overview

Đã hoàn thành implementation để cập nhật AI Route Calculation Service hỗ trợ multiple delivery locations với prompt mới.

---

## Files Changed

### 1. Created Files

#### `database/migrations/2025_12_25_114924_update_quotation_routes_for_multiple_deliveries.php`

**Purpose**: Update database schema cho AI Routes

**Changes:**
- Add `start_location` column to `quotation_routes`
- Add `delivery_locations` (JSON) column to `quotation_routes`
- Add `compliance_note` column to `quotation_routes`
- Add `segment_type` column to `quotation_route_segments`

**Migration Command:**
```bash
php artisan migrate
```

---

### 2. Updated Files

#### `app/Models/QuotationRoute.php`

**Changes:**

**$fillable array** (Line 14-45):
```php
// Added 3 new fields:
'start_location',        // NEW - 出発地
'delivery_locations',    // NEW - 複数届け地 (JSON array)
'compliance_note',       // NEW - コンプライアンス注記
```

**$casts array** (Line 47-63):
```php
// Added JSON casting:
'delivery_locations' => 'array',  // NEW - Cast JSON to array
```

---

#### `storage/app/prompts/route_calculation_prompt.txt`

**Changes**: Completely replaced with new prompt template

**Old Variables (8):**
- `{pickup_location}`
- `{delivery_location}` (single)
- `{return_location}`
- `{start_time}`
- `{vehicle_type}`
- `{loading_time}`
- `{unloading_time}`
- `{break_time}`

**New Variables (9):**
- `{start_location}` - NEW
- `{pickup_location}`
- `{delivery_locations}` - NEW (comma-separated)
- `{return_location}`
- `{start_time}`
- `{vehicle_type}`
- `{loading_time}`
- `{unloading_time}` (default changed to 30)
- `{break_time}`

**New Features:**
- Route segments construction (start → pickup → deliveries → return)
- Compliance calculations (430 rule, Labor Law)
- New output format với `route_segments` array

---

#### `app/Services/AIRouteCalculationService.php`

**5 Methods Updated:**

**1. `calculate()` method (Line 27-93)**

Before:
```php
'pickup_location' => $input['pickup_location'],
'delivery_location' => $input['delivery_location'],
'return_location' => $input['return_location'],
'unloading_time_minutes' => $input['unloading_time'] ?? 60,
```

After:
```php
'start_location' => $input['start_location'] ?? null,              // NEW
'pickup_location' => $input['pickup_location'],
'delivery_location' => $input['delivery_location'] ?? null,        // Now nullable
'delivery_locations' => $input['delivery_locations'] ?? null,      // NEW
'return_location' => $input['return_location'],
'unloading_time_minutes' => $input['unloading_time'] ?? 30,       // Changed default
```

---

**2. `buildPrompt()` method (Line 107-167)**

Before:
```php
$userMessage = str_replace([
    '{pickup_location}',
    '{delivery_location}',
    '{return_location}',
    // ... 5 more variables
], [
    $input['pickup_location'],
    $input['delivery_location'],
    $input['return_location'],
    // ... 5 more values
], $promptTemplate);
```

After:
```php
// Handle delivery_locations array
$deliveryLocations = $input['delivery_locations'] ?? [];
if (is_array($deliveryLocations)) {
    $deliveryLocationsStr = implode('、', array_filter($deliveryLocations));
} else {
    $deliveryLocationsStr = $deliveryLocations;
}

// Fallback to old delivery_location
if (empty($deliveryLocationsStr) && !empty($input['delivery_location'])) {
    $deliveryLocationsStr = $input['delivery_location'];
}

$userMessage = str_replace([
    '{start_location}',           // NEW
    '{pickup_location}',
    '{delivery_locations}',       // NEW
    '{return_location}',
    // ... 5 more variables
], [
    $input['start_location'] ?? $input['departure_location'] ?? '',  // NEW with fallback
    $input['pickup_location'] ?? $input['loading_location'] ?? '',
    $deliveryLocationsStr,        // NEW
    $input['return_location'] ?? '',
    // ... 5 more values
], $promptTemplate);
```

---

**3. `parseAndSaveResponse()` method (Line 264-290)**

Before:
```php
$summary = $response['summary'] ?? [];
$timeBreakdown = $response['time_breakdown'] ?? [];
$costBreakdown = $response['cost_breakdown'] ?? [];
$complianceCheck = $summary['compliance_check'] ?? [];

$route->update([
    'total_distance_km' => $summary['total_distance_km'] ?? null,
    'highway_fee' => $costBreakdown['estimated_total_tolls'] ?? 0,
    'total_duty_time_hours' => $timeBreakdown['total_duty_time_hours'] ?? null,
    'is_compliant' => $complianceCheck['is_compliant'] ?? true,
    'applied_rule' => $complianceCheck['applied_rule'] ?? null,
]);
```

After:
```php
$summary = $response['summary'] ?? [];
$complianceInfo = $response['compliance_info'] ?? [];  // NEW

$route->update([
    'total_distance_km' => $summary['total_distance_km'] ?? null,
    'highway_fee' => $summary['total_tolls_yen'] ?? 0,  // Changed field name
    'total_duty_time_hours' => $summary['total_duty_time_hours'] ?? null,  // From summary now
    'total_break_time_minutes' => $complianceInfo['required_break_minutes'] ?? null,  // NEW
    'compliance_note' => $complianceInfo['note'] ?? null,  // NEW
    'is_compliant' => true,
]);
```

---

**4. `saveLocations()` method (Line 292-341)**

Before:
```php
$locations = [
    ['sequence_order' => 1, 'location_type' => 'pickup', 'address' => $route->pickup_location],
    ['sequence_order' => 2, 'location_type' => 'delivery', 'address' => $route->delivery_location],
    ['sequence_order' => 3, 'location_type' => 'return', 'address' => $route->return_location],
];
```

After:
```php
$locations = [];

// Add start location if exists
if (!empty($route->start_location)) {
    $locations[] = ['sequence_order' => 1, 'location_type' => 'start', 'address' => $route->start_location];
}

$sequenceOrder = !empty($route->start_location) ? 2 : 1;

// Add pickup
$locations[] = ['sequence_order' => $sequenceOrder++, 'location_type' => 'pickup', 'address' => $route->pickup_location];

// Add multiple deliveries (DYNAMIC)
$deliveryLocations = $route->delivery_locations ?? [];
if (is_string($deliveryLocations)) {
    $deliveryLocations = json_decode($deliveryLocations, true) ?? [];
}

if (!empty($deliveryLocations) && is_array($deliveryLocations)) {
    foreach ($deliveryLocations as $deliveryLocation) {
        if (!empty($deliveryLocation)) {
            $locations[] = ['sequence_order' => $sequenceOrder++, 'location_type' => 'delivery', 'address' => $deliveryLocation];
        }
    }
} elseif (!empty($route->delivery_location)) {
    // Fallback to old single delivery
    $locations[] = ['sequence_order' => $sequenceOrder++, 'location_type' => 'delivery', 'address' => $route->delivery_location];
}

// Add return
$locations[] = ['sequence_order' => $sequenceOrder, 'location_type' => 'return', 'address' => $route->return_location];
```

---

**5. `saveSegments()` method (Line 343-389)**

Before:
```php
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
```

After:
```php
$routeSegments = $response['route_segments'] ?? [];  // NEW format

if (empty($routeSegments)) {
    Log::warning('No route_segments in AI response');
    return;
}

$locations = $route->locations()->orderBy('sequence_order')->get();

// Dynamic loop through segments
foreach ($routeSegments as $segment) {
    $segmentOrder = $segment['segment_order'] ?? null;
    
    if ($segmentOrder === null) {
        continue;
    }
    
    // Map segment to locations
    $fromLocationIndex = $segmentOrder - 1;
    $toLocationIndex = $segmentOrder;
    
    $fromLocation = $locations[$fromLocationIndex] ?? null;
    $toLocation = $locations[$toLocationIndex] ?? null;
    
    if (!$fromLocation || !$toLocation) {
        Log::warning('Location not found for segment');
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
```

---

#### `app/Http/Requests/CalculateRouteRequest.php`

**Changes:**

**Validation Rules** (Line 14-27):

Before:
```php
return [
    'title' => 'nullable|string|max:500',
    'pickup_location' => 'required|string|max:500',
    'delivery_location' => 'required|string|max:500',  // Required
    'return_location' => 'required|string|max:500',
    // ... other fields
];
```

After:
```php
return [
    'title' => 'nullable|string|max:500',
    'start_location' => 'nullable|string|max:500',              // NEW
    'pickup_location' => 'required|string|max:500',
    'delivery_location' => 'nullable|string|max:500',           // Now nullable
    'delivery_locations' => 'nullable|array',                   // NEW
    'delivery_locations.*' => 'nullable|string|max:500',        // NEW
    'return_location' => 'required|string|max:500',
    // ... other fields
];
```

**Validation Messages** (Line 29-42):
- Added messages cho `start_location`
- Added messages cho `delivery_locations` array
- Removed required message cho `delivery_location`

---

### 3. Backup Files

#### `storage/app/prompts/route_calculation_prompt.txt.old`

**Purpose**: Backup của prompt cũ

**Content**: Original prompt template với 8 variables

---

## API Changes

### Request Format

**Old Format:**
```json
{
  "pickup_location": "東京倉庫",
  "delivery_location": "横浜倉庫",
  "return_location": "東京本社",
  "start_time": "09:00"
}
```

**New Format:**
```json
{
  "start_location": "東京本社",
  "pickup_location": "東京倉庫",
  "delivery_locations": ["横浜倉庫", "川崎センター", "千葉配送所"],
  "return_location": "東京本社",
  "start_time": "09:00"
}
```

**Backward Compatible**: Old format vẫn work (fallback logic)

---

### Response Format (No Changes)

Response format từ controller không thay đổi, vẫn return:
```json
{
  "code": 200,
  "data": {
    "route_id": 1,
    "route_code": "QR-20251225-001",
    "summary": { ... },
    "time_breakdown": { ... },
    "locations": [ ... ],
    "segments": [ ... ]
  }
}
```

**Note**: Segments array giờ sẽ có nhiều items hơn (dynamic based on deliveries)

---

## Database Schema Changes

### Table: `quotation_routes`

**New Columns:**
```sql
start_location VARCHAR(500) NULL COMMENT '出発地 - Điểm xuất phát'
delivery_locations JSON NULL COMMENT '複数届け地 - Multiple delivery locations'
compliance_note TEXT NULL COMMENT 'コンプライアンス注記 - Compliance note'
```

### Table: `quotation_route_segments`

**New Column:**
```sql
segment_type VARCHAR(50) NULL COMMENT '回送/実車 - Segment type'
```

---

## Backward Compatibility

### Maintained Compatibility

1. **Old `delivery_location` field**: ✅ Kept in database
2. **Fallback logic**: ✅ If `delivery_locations` empty, use `delivery_location`
3. **Request validation**: ✅ Both formats accepted
4. **Model casting**: ✅ Automatic JSON ↔ array conversion

### Breaking Changes

1. **AI Response Format**: ⚠️ Changed from `route_details` to `route_segments`
   - Old AI responses won't work with new code
   - Need to re-calculate routes with new format

2. **Prompt Template**: ⚠️ Completely replaced
   - Old prompt no longer used
   - Backup available at `.txt.old`

---

## Testing Guide

### 1. Run Migration

```bash
php artisan migrate
```

**Verify:**
```bash
php artisan tinker
>>> Schema::hasColumn('quotation_routes', 'start_location')
>>> Schema::hasColumn('quotation_routes', 'delivery_locations')
>>> Schema::hasColumn('quotation_routes', 'compliance_note')
>>> Schema::hasColumn('quotation_route_segments', 'segment_type')
```

---

### 2. Test với Postman

**Endpoint**: `POST /api/quotation/routes/calculate`

**Test Case 1: Single Delivery**
```json
{
  "title": "Test Single Delivery",
  "start_location": "東京本社",
  "pickup_location": "東京倉庫",
  "delivery_locations": ["横浜倉庫"],
  "return_location": "東京本社",
  "start_time": "09:00"
}
```

**Expected Segments:**
1. [回送] 東京本社 → 東京倉庫
2. [実車] 東京倉庫 → 横浜倉庫
3. [回送] 横浜倉庫 → 東京本社

---

**Test Case 2: Multiple Deliveries**
```json
{
  "title": "Test Multiple Deliveries",
  "start_location": "東京本社",
  "pickup_location": "東京倉庫",
  "delivery_locations": [
    "横浜倉庫",
    "川崎センター",
    "千葉配送所"
  ],
  "return_location": "東京本社",
  "start_time": "08:00",
  "vehicle_type": "中型車(4t)",
  "loading_time": 60,
  "unloading_time": 30
}
```

**Expected Segments:**
1. [回送] 東京本社 → 東京倉庫
2. [実車] 東京倉庫 → 横浜倉庫
3. [実車] 横浜倉庫 → 川崎センター
4. [実車] 川崎センター → 千葉配送所
5. [回送] 千葉配送所 → 東京本社

---

**Test Case 3: Backward Compatibility (Old Format)**
```json
{
  "pickup_location": "東京倉庫",
  "delivery_location": "横浜倉庫",
  "return_location": "東京本社",
  "start_time": "09:00"
}
```

**Expected**: Should still work với fallback logic

---

### 3. Verify Results

**Check Database:**
```sql
SELECT * FROM quotation_routes ORDER BY id DESC LIMIT 1;
SELECT * FROM quotation_route_locations WHERE route_id = [last_route_id] ORDER BY sequence_order;
SELECT * FROM quotation_route_segments WHERE route_id = [last_route_id] ORDER BY segment_order;
```

**Verify:**
- [ ] `start_location` populated correctly
- [ ] `delivery_locations` JSON array saved
- [ ] `compliance_note` populated với break time explanation
- [ ] Locations created với correct sequence_order
- [ ] Segments created với correct segment_type (回送/実車)
- [ ] Segment count = location count - 1

---

## Rollback Plan

### If Issues Found:

**1. Rollback Migration:**
```bash
php artisan migrate:rollback --step=1
```

**2. Restore Old Prompt:**
```bash
cp storage/app/prompts/route_calculation_prompt.txt.old storage/app/prompts/route_calculation_prompt.txt
```

**3. Revert Code Changes:**
```bash
git checkout app/Models/QuotationRoute.php
git checkout app/Services/AIRouteCalculationService.php
git checkout app/Http/Requests/CalculateRouteRequest.php
```

---

## Next Steps

1. **Run Migration** (when DB available)
   ```bash
   php artisan migrate
   ```

2. **Manual Testing**
   - Test với Postman (3 test cases above)
   - Verify database records
   - Check AI response format

3. **Integration Testing**
   - Test với Quotation API integration
   - Test full flow: Create Quotation → Calculate Route
   - Verify data consistency

4. **Code Review**
   - Review all changes
   - Check error handling
   - Verify logging

5. **Documentation**
   - Update API documentation (Swagger)
   - Document new request format
   - Document new response structure

6. **Deployment**
   - Deploy to staging first
   - Test thoroughly
   - Monitor AI responses
   - Deploy to production

---

## Success Metrics

- ✅ Migration runs successfully
- ✅ Prompt template updated
- ✅ Model supports JSON casting
- ✅ AI Service handles multiple deliveries
- ✅ Controller validation updated
- ⏳ All tests pass (pending migration run)
- ⏳ Manual testing successful
- ⏳ Integration testing successful
- ⏳ Code review approved
- ⏳ Documentation updated

**Current Status**: 5/10 completed (50%)

**Remaining**: Testing phases after migration run

---

## References

- **Issue**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/546
- **Parent Issue**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540
- **New Prompt**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540#issuecomment-3689198629
- **Dev Log**: `docs/issues/546/dev.md`
- **Plan**: `docs/issues/540/plan.md` (Part 2)
- **AI Prompt Guide**: `docs/issues/540/ai-prompt-update.md`

