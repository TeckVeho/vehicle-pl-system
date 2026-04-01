# Issue #540 - AI Prompt Update Guide

## Metadata

- **Related Issue:** #540 - BE_Add functionality for time calculation
- **Update Date:** 2025-12-24
- **Reference:** [GitHub Comment #3689198629](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/540#issuecomment-3689198629)

---

## Overview

Cập nhật AI prompt để hỗ trợ tính toán route phức tạp với multiple delivery locations (届け地). Prompt mới sẽ xử lý route từ departure location → pickup location → multiple delivery locations → return location.

---

## Changes Summary

### Old Flow (Before)
```
出発地 → 積地 → 届け地 (single) → 帰社地
```

### New Flow (After)
```
出発地 → 積地 → 届け地1 → 届け地2 → ... → 届け地N → 帰社地
```

---

## New Prompt Template

### Role Definition

```
# Role
あなたは日本の物流地理、道路交通法（特に2024年改善基準告示）、および有料道路料金体系に精通した「高度運行管理AI」です。
提供された情報に基づき、トラック輸送における複雑なルート（回送・複数配送含む）の計算、コスト試算、および法令を遵守した勤務計画を作成してください。
```

### Context

```
# Context
ユーザーは運送会社の運行管理者です。
「出発地（車庫）」から「積地」へ向かい、荷物を積んで「複数の届け地」を回り、「帰社地」に戻るまでの詳細な運行データを求めています。
```

---

## Input Variables Mapping

### Backend → AI Prompt Variable Mapping

| Backend Field | AI Prompt Variable | Type | Default | Description |
|--------------|-------------------|------|---------|-------------|
| `departure_location` | `{start_location}` | string | - | 出発地 (Departure) |
| `loading_location` | `{pickup_location}` | string | - | 積地 (Pickup/Loading) |
| `delivery_locations` | `{delivery_locations}` | array/csv | [] | 届け地リスト (Delivery Locations) |
| `return_location` | `{return_location}` | string | - | 帰社地 (Return) |
| `start_time` | `{start_time}` | string | - | 運行開始時間 (Start Time) |
| - | `{vehicle_type}` | string | "中型車(4t)" | 車両区分 (Vehicle Type) |
| - | `{loading_time}` | int | 60 | 積み込み作業時間 (minutes) |
| - | `{unloading_time}` | int | 30 | 荷下ろし作業時間/1カ所 (minutes) |
| - | `{break_time}` | string | "Auto" | 休憩時間指定 (Break Time) |

### Input Data Section

```
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
```

---

## Route Calculation Logic

### Steps / Thinking Process

```
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
```

### Route Segments Example

**Example with 3 Delivery Locations:**

```
Segment 0: [回送] 東京本社 (出発地) → 東京倉庫 (積地)
Segment 1: [実車] 東京倉庫 (積地) → 横浜倉庫 (届け地1)
Segment 2: [実車] 横浜倉庫 (届け地1) → 川崎センター (届け地2)
Segment 3: [実車] 川崎センター (届け地2) → 千葉配送所 (届け地3)
Segment 4: [回送] 千葉配送所 (届け地3) → 東京本社 (帰社地)
```

---

## Constraints

```
# Constraints
* **ルート最適化:** 複数の届け地がある場合、入力された順序に従って回るものとします（並べ替えはしない）。
* **コンプライアンス:** 必ず法令（改善基準告示）を満たす休憩時間を確保したスケジュールにしてください。
* **精度:** 距離は小数点第一位、料金は100円単位。
```

**Key Points:**
- ✅ Giữ nguyên thứ tự delivery locations (không optimize/reorder)
- ✅ Tuân thủ 430 rule (4 giờ lái xe → 30 phút nghỉ)
- ✅ Tuân thủ Labor Standards Act (6h+ → 45min, 8h+ → 60min break)
- ✅ Độ chính xác: distance (1 decimal), toll (100 yen unit)

---

## Output Format

### JSON Response Structure

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
    "note": "休憩時間の算出根拠（例: 運転時間が長いため430ルール適用）"
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
      "route_description": "ルート概要（例: 首都高湾岸線経由）"
    }
  ]
}
```

### Output Format Prompt

```
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

---

## Example Scenarios

### Scenario 1: Single Delivery Location

**Input:**
```json
{
  "departure_location": "東京本社",
  "loading_location": "東京倉庫",
  "delivery_locations": ["横浜倉庫"],
  "return_location": "東京本社",
  "start_time": "09:00"
}
```

**Expected Segments:**
1. 回送: 東京本社 → 東京倉庫
2. 実車: 東京倉庫 → 横浜倉庫
3. 回送: 横浜倉庫 → 東京本社

### Scenario 2: Multiple Delivery Locations

**Input:**
```json
{
  "departure_location": "東京本社",
  "loading_location": "東京倉庫",
  "delivery_locations": ["横浜倉庫", "川崎センター", "千葉配送所"],
  "return_location": "東京本社",
  "start_time": "09:00"
}
```

**Expected Segments:**
1. 回送: 東京本社 → 東京倉庫
2. 実車: 東京倉庫 → 横浜倉庫
3. 実車: 横浜倉庫 → 川崎センター
4. 実車: 川崎センター → 千葉配送所
5. 回送: 千葉配送所 → 東京本社

### Scenario 3: Same Departure and Return

**Input:**
```json
{
  "departure_location": "大阪営業所",
  "loading_location": "大阪港",
  "delivery_locations": ["神戸港", "京都配送センター"],
  "return_location": "大阪営業所",
  "start_time": "08:00"
}
```

**Expected Segments:**
1. 回送: 大阪営業所 → 大阪港
2. 実車: 大阪港 → 神戸港
3. 実車: 神戸港 → 京都配送センター
4. 回送: 京都配送センター → 大阪営業所

---

## Implementation Guide

### File to Update

**Primary File:**
- `app/Services/AIRouteCalculationService.php` (or similar AI service)

### Implementation Steps

#### Step 1: Update Prompt Template

```php
class AIRouteCalculationService
{
    protected string $promptTemplate = <<<PROMPT
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

[... rest of prompt ...]
PROMPT;
}
```

#### Step 2: Format Delivery Locations

```php
public function calculateRoute(Quotation $quotation): array
{
    $deliveryLocations = $quotation->deliveryLocations()
        ->ordered()
        ->pluck('location_name')
        ->toArray();
    
    $deliveryLocationsStr = implode('、', $deliveryLocations);
    
    $variables = [
        'start_location' => $quotation->departure_location ?? '',
        'pickup_location' => $quotation->loading_location ?? '',
        'delivery_locations' => $deliveryLocationsStr,
        'return_location' => $quotation->return_location ?? '',
        'start_time' => $quotation->start_time ?? '09:00',
        'vehicle_type' => $this->getVehicleType($quotation),
        'loading_time' => $quotation->loading_time ?? 60,
        'unloading_time' => $quotation->unloading_time ?? 30,
        'break_time' => $quotation->break_time ?? 'Auto',
    ];
    
    $prompt = $this->buildPrompt($variables);
    
    return $this->sendToAI($prompt);
}
```

#### Step 3: Get Vehicle Type

```php
protected function getVehicleType(Quotation $quotation): string
{
    if ($quotation->quotationMasterData) {
        return $quotation->quotationMasterData->vehicle_type ?? '中型車(4t)';
    }
    
    return '中型車(4t)';
}
```

#### Step 4: Build Prompt

```php
protected function buildPrompt(array $variables): string
{
    $prompt = $this->promptTemplate;
    
    foreach ($variables as $key => $value) {
        $prompt = str_replace("{{$key}}", $value, $prompt);
    }
    
    return $prompt;
}
```

#### Step 5: Parse Response

```php
protected function parseAIResponse(string $response): array
{
    $json = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Invalid JSON response from AI');
    }
    
    $this->validateResponseStructure($json);
    
    return [
        'summary' => $json['summary'],
        'compliance_info' => $json['compliance_info'],
        'route_segments' => $json['route_segments'],
    ];
}

protected function validateResponseStructure(array $json): void
{
    $requiredKeys = ['summary', 'compliance_info', 'route_segments'];
    
    foreach ($requiredKeys as $key) {
        if (!isset($json[$key])) {
            throw new \Exception("Missing required key: {$key}");
        }
    }
    
    if (!is_array($json['route_segments']) || empty($json['route_segments'])) {
        throw new \Exception('route_segments must be a non-empty array');
    }
}
```

---

## Testing Checklist

### Unit Tests

- [ ] Test prompt building với single delivery location
- [ ] Test prompt building với multiple delivery locations (2-5)
- [ ] Test prompt building với empty delivery locations
- [ ] Test delivery locations formatting (array → comma-separated)
- [ ] Test default values (vehicle_type, loading_time, unloading_time, break_time)
- [ ] Test response parsing với valid JSON
- [ ] Test response parsing với invalid JSON
- [ ] Test response validation

### Integration Tests

- [ ] Test full flow với real quotation data
- [ ] Test với 1 delivery location
- [ ] Test với 3 delivery locations
- [ ] Test với 5+ delivery locations
- [ ] Test với same departure and return location
- [ ] Test compliance calculations (430 rule)
- [ ] Test date_change flag when route spans multiple days
- [ ] Test error handling khi AI response invalid

### Manual Testing

- [ ] Test với Postman/Thunder Client
- [ ] Verify route segments order
- [ ] Verify segment types (回送/実車)
- [ ] Verify distance calculations
- [ ] Verify toll calculations
- [ ] Verify break time calculations
- [ ] Verify end time calculations

---

## Expected Response Examples

### Example 1: Single Delivery

```json
{
  "summary": {
    "total_distance_km": 85.3,
    "total_tolls_yen": 3200,
    "total_duty_time_hours": 6.5,
    "start_time": "09:00",
    "estimated_end_time": "15:30",
    "date_change": false
  },
  "compliance_info": {
    "required_break_minutes": 45,
    "note": "労基法に基づき6時間超のため45分休憩"
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
    },
    {
      "segment_order": 3,
      "type": "回送(帰庫)",
      "from": "横浜倉庫",
      "to": "東京本社",
      "distance_km": 44.3,
      "driving_time_minutes": 55,
      "toll_yen": 1600,
      "route_description": "首都高速・横浜環状経由"
    }
  ]
}
```

### Example 2: Multiple Deliveries

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
    },
    {
      "segment_order": 3,
      "type": "実車配送",
      "from": "横浜倉庫",
      "to": "川崎センター",
      "distance_km": 18.5,
      "driving_time_minutes": 25,
      "toll_yen": 800,
      "route_description": "横浜環状・首都高速経由"
    },
    {
      "segment_order": 4,
      "type": "実車配送",
      "from": "川崎センター",
      "to": "千葉配送所",
      "distance_km": 52.9,
      "driving_time_minutes": 70,
      "toll_yen": 2200,
      "route_description": "首都高速・東京湾アクアライン経由"
    },
    {
      "segment_order": 5,
      "type": "回送(帰庫)",
      "from": "千葉配送所",
      "to": "東京本社",
      "distance_km": 44.3,
      "driving_time_minutes": 60,
      "toll_yen": 1200,
      "route_description": "京葉道路・首都高速経由"
    }
  ]
}
```

---

## Compliance Rules Reference

### 430 Rule (430ルール)

**Rule:** 4時間運転ごとに30分休憩

**Implementation:**
- Tính tổng driving time
- Mỗi 4 giờ (240 phút) → thêm 30 phút nghỉ
- Example: 5 giờ lái xe → 30 phút nghỉ

### Labor Standards Act (労基法)

**Rules:**
- 6時間超 → 45分休憩
- 8時間超 → 60分休憩

**Implementation:**
- Tính total work time (driving + loading + unloading)
- Nếu > 6h và ≤ 8h → 45 phút nghỉ
- Nếu > 8h → 60 phút nghỉ

### Combined Calculation

**Logic:**
```
required_break = max(430_rule_break, labor_law_break)
```

**Example:**
- Driving time: 5 hours → 430 rule: 30 min
- Total work: 7 hours → Labor law: 45 min
- **Required break: 45 min** (max of 30 and 45)

---

## Migration Notes

### Backward Compatibility

**Old System:**
- Single delivery_location field
- Simple route: start → pickup → delivery → return

**New System:**
- Multiple delivery_locations (array)
- Complex route: start → pickup → delivery1 → delivery2 → ... → return

**Compatibility Strategy:**
- ✅ Giữ nguyên old field `delivery_location`
- ✅ New field `delivery_locations` array
- ✅ AI prompt hỗ trợ cả single và multiple
- ✅ Nếu `delivery_locations` empty → fallback to `delivery_location`

### Fallback Logic

```php
protected function getDeliveryLocations(Quotation $quotation): array
{
    $locations = $quotation->deliveryLocations()
        ->ordered()
        ->pluck('location_name')
        ->toArray();
    
    if (empty($locations) && !empty($quotation->delivery_location)) {
        $locations = [$quotation->delivery_location];
    }
    
    return $locations;
}
```

---

## Performance Considerations

### Optimization Tips

1. **Eager Loading:**
   - Always eager load `deliveryLocations` relationship
   - Prevent N+1 queries

2. **Caching:**
   - Cache AI responses for identical routes
   - Cache key: hash of (departure, pickup, deliveries, return, start_time)

3. **Rate Limiting:**
   - Implement rate limiting for AI API calls
   - Queue long-running calculations

4. **Timeout:**
   - Set reasonable timeout for AI API (e.g., 30 seconds)
   - Implement retry logic with exponential backoff

---

## Error Handling

### Common Errors

1. **Invalid JSON Response:**
   - AI returns malformed JSON
   - Solution: Validate and retry with clearer prompt

2. **Missing Required Fields:**
   - Response missing `summary`, `compliance_info`, or `route_segments`
   - Solution: Validate structure and throw descriptive error

3. **Empty Route Segments:**
   - `route_segments` array is empty
   - Solution: Check input data completeness

4. **Timeout:**
   - AI API takes too long
   - Solution: Implement timeout and retry logic

### Error Response Format

```json
{
  "error": true,
  "message": "AI route calculation failed",
  "details": "Invalid JSON response from AI",
  "code": "AI_INVALID_RESPONSE"
}
```

---

## Conclusion

Prompt mới hỗ trợ đầy đủ multiple delivery locations với:
- ✅ Route segmentation phức tạp
- ✅ Compliance calculations (430 rule + Labor law)
- ✅ Detailed cost breakdown
- ✅ Structured JSON response
- ✅ Backward compatibility

**Next Steps:**
1. Implement prompt update trong service
2. Test với various scenarios
3. Validate compliance calculations
4. Deploy và monitor

