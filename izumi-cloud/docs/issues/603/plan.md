# Issue #603: AQ_Update calculation prompt - Implementation Plan

## 概要 (Overview)

Issue này yêu cầu cập nhật prompt tính toán route để AI trả về thêm thông tin `thinking_process` (quy trình thực hiện) với 5 key:
- `route_strategy`: Chiến lược xây dựng route
- `calculation_basis`: Cơ sở tính toán
- `workload_analysis`: Phân tích khối lượng công việc
- `compliance_reasoning`: Lý do tuân thủ pháp luật
- `schedule_summary`: Tóm tắt lịch trình

**Trạng thái hiện tại:**
- Prompt chỉ yêu cầu AI trả về `summary`, `time_breakdown`, `cost_breakdown`, `compliance_info`, `route_segments`
- API response không có `thinking_process`
- Frontend chưa có component hiển thị thông tin này

**Trạng thái sau khi cải thiện:**
- Prompt yêu cầu AI trả về thêm `thinking_process` với 5 key chi tiết
- API response bao gồm `thinking_process` để frontend có thể hiển thị quy trình tính toán
- Thông tin này giúp người dùng hiểu rõ hơn về cách AI tính toán route

---

## FE (Frontend)

### 1. Files need to edit:

**Lưu ý:** Hiện tại chưa có frontend component cho quotation route calculation. Nếu cần hiển thị `thinking_process` trong tương lai, sẽ cần tạo component mới.

#### 1.1. File: `resources/js/pages/QuotationRoute/Detail.vue` (Nếu cần tạo mới)

##### 1.1.1. Hiển thị thinking_process trong route detail page

**Mô tả:**
- Tạo component để hiển thị thông tin `thinking_process` từ API response
- Hiển thị 5 key: `route_strategy`, `calculation_basis`, `workload_analysis`, `compliance_reasoning`, `schedule_summary`
- Format hiển thị: Accordion hoặc Tab để dễ đọc

**変更内容:**
- Tạo component mới nếu cần
- Thêm section hiển thị `thinking_process` với format dễ đọc
- Sử dụng API endpoint `/api/quotation/routes/{id}` để lấy data

**Lưu ý:** Task này chỉ cần thực hiện nếu có yêu cầu hiển thị trên frontend. Hiện tại chỉ cần backend trả về data.

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `storage/app/prompts/route_calculation_prompt.txt`

##### 1.1.1. Thêm thinking_process vào output format

**現在の実装** (line 84-96):

- Prompt hiện tại chỉ yêu cầu AI trả về `route_segments` array
- Không có `thinking_process` trong output format

**変更内容:**

- Thêm `thinking_process` object vào JSON output format (sau `route_segments`)
- Định nghĩa 5 key với mô tả chi tiết:
  - `route_strategy`: Giải thích cách xây dựng route dựa trên vị trí và thứ tự delivery
  - `calculation_basis`: Giải thích vehicle type, tốc độ, và phí đường cao tốc được sử dụng
  - `workload_analysis`: Phân tích thời gian làm việc ngoài việc lái xe
  - `compliance_reasoning`: Giải thích các quy tắc pháp luật được áp dụng
  - `schedule_summary`: Tóm tắt quá trình tính toán thời gian kết thúc

**Code example:**

```json
"thinking_process": {
  "route_strategy": "出発地と積地の位置関係、および届け地リストの順序に基づき、効率的な回送・配送ルートをどのように構築したかの説明。",
  "calculation_basis": "採用した車両区分、設定した平均速度（高速/下道）、および料金区分の前提条件の説明。",
  "workload_analysis": "積み込み・荷下ろしの回数に基づき、運転以外の作業時間がトータルでどれくらい発生しているかの説明。",
  "compliance_reasoning": "総拘束時間や連続運転時間を監視し、どの法令ルール（430、改善基準告示等）に基づいて休憩時間を割り当てたかの法的根拠の説明。",
  "schedule_summary": "開始時間に各所要時間を加算し、最終的な終了時間を導き出したプロセスの要約。"
}
```

#### 1.2. File: `app/Http/Controllers/Api/QuotationRouteController.php`

##### 1.2.1. Thêm method getThinkingProcessFromResponse()

**現在の実装** (line 45-90):

- Method `calculate()` chỉ trả về data từ database
- Không có method để đọc `thinking_process` từ AI response file

**変更内容:**

- Thêm protected method `getThinkingProcessFromResponse(QuotationRoute $route): ?array`
- Method này sẽ:
  1. Lấy response file từ `quotation_route_files` table (file_type = 'response')
  2. Validate file path để tránh path traversal attack
  3. Đọc file JSON từ storage
  4. Parse JSON và validate structure
  5. Trả về `thinking_process` object nếu hợp lệ
  6. Return `null` nếu không tìm thấy file hoặc có lỗi
  7. Log warning nếu có exception (sử dụng `Log::warning` với use statement)

**Lưu ý quan trọng:**
- Cần đảm bảo `files` relationship được load trước khi gọi method này
- Validate file path để tránh security issues
- Validate structure của `thinking_process` (có đủ 5 key không)

**Code example:**

```php
use Illuminate\Support\Facades\Log;

protected function getThinkingProcessFromResponse(QuotationRoute $route): ?array
{
    try {
        // Ensure files relationship is loaded
        if (!$route->relationLoaded('files')) {
            $route->load('files');
        }
        
        $responseFile = $route->files()->where('file_type', 'response')->first();
        
        if (!$responseFile) {
            return null;
        }
        
        $filePath = storage_path('app/' . $responseFile->file_path);
        
        // Security: Validate file path to prevent path traversal
        $realPath = realpath($filePath);
        $storagePath = realpath(storage_path('app'));
        
        if (!$realPath || strpos($realPath, $storagePath) !== 0) {
            Log::warning('Invalid file path detected', [
                'route_id' => $route->id,
                'file_path' => $responseFile->file_path,
            ]);
            return null;
        }
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        $response = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Failed to decode JSON from response file', [
                'route_id' => $route->id,
                'json_error' => json_last_error_msg(),
            ]);
            return null;
        }
        
        $thinkingProcess = $response['thinking_process'] ?? null;
        
        // Optional: Validate structure of thinking_process
        if ($thinkingProcess && is_array($thinkingProcess)) {
            $requiredKeys = ['route_strategy', 'calculation_basis', 'workload_analysis', 'compliance_reasoning', 'schedule_summary'];
            $missingKeys = array_diff($requiredKeys, array_keys($thinkingProcess));
            
            if (!empty($missingKeys)) {
                Log::warning('thinking_process missing some keys', [
                    'route_id' => $route->id,
                    'missing_keys' => $missingKeys,
                ]);
                // Still return partial data if some keys are missing
            }
        }
        
        return $thinkingProcess;
        
    } catch (\Exception $e) {
        Log::warning('Failed to get thinking_process from response file', [
            'route_id' => $route->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return null;
    }
}
```

##### 1.2.2. Cập nhật method calculate() để trả về thinking_process

**現在の実装** (line 56-85):

- Method `calculate()` trả về response với các key: `summary`, `time_breakdown`, `cost_breakdown`, `compliance_info`, `locations`, `segments`
- Không có `thinking_process` trong response
- Route được return từ service đã có `locations` và `segments` loaded, nhưng chưa có `files`

**変更内容:**

- Load `files` relationship trước khi gọi `getThinkingProcessFromResponse()`
- Gọi `getThinkingProcessFromResponse($route)` sau khi tính toán xong
- Thêm `thinking_process` vào response array (line 82)
- Đảm bảo `thinking_process` có thể là `null` nếu không tìm thấy

**Code example:**

```php
$route = $this->aiService->calculate($request->validated(), $userId);

// Load files relationship to access response file
$route->load('files');

// Get thinking_process from AI response file
$thinkingProcess = $this->getThinkingProcessFromResponse($route);

return $this->responseJson(Response::HTTP_OK, [
    'route_id' => $route->id,
    'route_code' => $route->route_code,
    // ... existing fields ...
    'thinking_process' => $thinkingProcess,
    'locations' => $route->locations,
    'segments' => $route->segments,
], 'Route calculated successfully');
```

##### 1.2.3. Cập nhật method show() để trả về thinking_process

**現在の実装** (line 165-182):

- Method `show()` chỉ trả về route data từ database
- Không có `thinking_process` trong response
- Đã eager load `files` relationship nhưng có thể optimize

**変更内容:**

- Gọi `getThinkingProcessFromResponse($route)` để lấy `thinking_process`
- Convert route to array và thêm `thinking_process` nếu có
- Trả về route data với `thinking_process` included
- `files` relationship đã được eager load nên không cần load lại

**Code example:**

```php
$route = QuotationRoute::with(['locations', 'segments', 'files'])->find($id);

if (!$route) {
    return $this->responseJsonError(Response::HTTP_NOT_FOUND, 'Route not found');
}

// Get thinking_process from AI response file
// files relationship đã được eager load ở trên
$thinkingProcess = $this->getThinkingProcessFromResponse($route);

$routeData = $route->toArray();
if ($thinkingProcess) {
    $routeData['thinking_process'] = $thinkingProcess;
}

return $this->responseJson(Response::HTTP_OK, $routeData);
```

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (Không có dependency)

   - Task 1.1.1: Cập nhật prompt file để thêm `thinking_process` vào output format
   - Task 1.2.1: Thêm method `getThinkingProcessFromResponse()` vào controller
   - Task 1.2.2: Cập nhật method `calculate()` để trả về `thinking_process`
   - Task 1.2.3: Cập nhật method `show()` để trả về `thinking_process`

2. **Frontend 実装** (Optional, phụ thuộc vào yêu cầu)

   - Task 1.1.1: Tạo component hiển thị `thinking_process` (nếu cần)

3. **統合テスト**
   - Test API endpoint `/api/quotation/routes/calculate` trả về `thinking_process`
   - Test API endpoint `/api/quotation/routes/{id}` trả về `thinking_process`
   - Verify `thinking_process` có đầy đủ 5 key: `route_strategy`, `calculation_basis`, `workload_analysis`, `compliance_reasoning`, `schedule_summary`
   - Test trường hợp response file không tồn tại (should return `null`)

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 2-3 時間

  - Cập nhật prompt file: 30 phút
  - Thêm method `getThinkingProcessFromResponse()`: 1 giờ
  - Cập nhật method `calculate()`: 30 phút
  - Cập nhật method `show()`: 30 phút
  - Testing và fix bugs: 30 phút - 1 giờ

- **Frontend**: 0 時間 (Optional)

  - Chưa có yêu cầu hiển thị trên frontend
  - Nếu cần tạo component: 2-3 giờ

**合計**: 2-3 時間 (Backend only)

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

   - Method `getThinkingProcessFromResponse()` đọc file từ disk mỗi lần request
   - Có thể cache `thinking_process` trong database nếu cần optimize (nhưng không cần thiết cho issue này)
   - File size của AI response thường nhỏ (< 10KB), nên không ảnh hưởng performance đáng kể

2. **UX 考慮:**

   - `thinking_process` là optional field, có thể là `null` nếu không tìm thấy
   - Frontend cần handle trường hợp `thinking_process` là `null` hoặc empty
   - Nên hiển thị thông tin này dưới dạng expandable section để không làm UI quá dài

3. **データ整合性:**

   - `thinking_process` không được lưu vào database, chỉ lưu trong response file JSON
   - Nếu response file bị xóa, `thinking_process` sẽ không còn
   - Có thể xem xét lưu vào database trong tương lai nếu cần persistence

4. **既存機能との互換性:**

   - Thay đổi này không ảnh hưởng đến logic tính toán hiện tại
   - Chỉ thêm field mới vào API response, không thay đổi structure hiện có
   - Backward compatible: Frontend cũ vẫn hoạt động bình thường nếu không sử dụng `thinking_process`

5. **エラーハンドリング:**

   - Method `getThinkingProcessFromResponse()` có try-catch để handle exceptions
   - Log warning nếu có lỗi, nhưng không throw exception để không ảnh hưởng đến response chính
   - Return `null` nếu không tìm thấy file hoặc có lỗi parse JSON

6. **テスト考慮:**

   - Test với response file có `thinking_process` đầy đủ 5 key
   - Test với response file có `thinking_process` nhưng thiếu một số key
   - Test với response file không có `thinking_process` (old format)
   - Test với response file không tồn tại
   - Test với response file có JSON invalid
   - Test với file path bị path traversal attack (security test)
   - Test với route không có files relationship loaded

7. **セキュリティ考慮:**

   - Validate file path để tránh path traversal attack
   - Sử dụng `realpath()` để normalize path
   - Kiểm tra file path có nằm trong storage directory không
   - Log warning khi phát hiện invalid file path

8. **パフォーマンス最適化:**

   - Eager load `files` relationship trong `calculate()` và `show()` methods
   - Có thể cache `thinking_process` trong memory nếu cần (nhưng không cần thiết cho issue này)
   - File size nhỏ nên không ảnh hưởng performance đáng kể

9. **コード品質:**

   - Sử dụng `Log::warning` với use statement thay vì `\Log::warning`
   - Validate structure của `thinking_process` trước khi return
   - Handle edge cases: missing keys, invalid JSON, file not found
   - Add proper error logging với context information
