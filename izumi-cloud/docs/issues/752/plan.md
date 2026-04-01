# Issue #752: AQ_Update thinking_process display - Implementation Plan

## 概要 (Overview)

- **Mục tiêu:** Prompt đã đổi `thinking_process` từ object (route_strategy, calculation_basis, …) sang **một chuỗi (string)** báo cáo một câu. BE cập nhật lưu/trả về string; FE hiển thị thành một câu.
- **Scope:** Prompt file, Backend (API + DB), Frontend (hiển thị).

---

## 1. Prompt

### 1.1. File: `storage/app/prompts/route_calculation_prompt.txt`

**Thay toàn bộ nội dung** bằng prompt mới (đã cung cấp trong issue). Điểm thay đổi chính:

- **Output Format – `thinking_process`:**
  - **Cũ:** object với các key `route_strategy`, `calculation_basis`, `workload_analysis`, `compliance_reasoning`, `schedule_summary`.
  - **Mới:** **string** – một câu tiếng Nhật, ví dụ:
    - `"出発地の[出発地名]から[積地名]への回送を含めた全N区間のルートで計算しました。主要経路として[道路名]（[入口IC名]～[出口IC名]）などを選択し、[車種区分]料金を適用しています。総運転時間がX時間を超えるため、改善基準告示（430ルール）に基づきX分、および労基法に基づき追加X分、合計X分の休憩時間を確保したスケジュールを作成しました。"`

- Các phần khác (Role, Context, Input Data, Steps/Thinking Process, Constraints) và cấu trúc JSON (`summary`, `time_breakdown`, `cost_breakdown`, `compliance_info`, `route_segments`) giữ tương thích với code hiện tại; chỉ đổi phần mô tả và ví dụ của `thinking_process` sang string.

**Lưu ý:** Placeholder vẫn dùng `{start_location}`, `{pickup_location}`, `{delivery_locations}`, `{return_location}`, `{start_time}`, `{vehicle_type}`, `{loading_time}`, `{unloading_time}`, `{break_time}` để `AIRouteCalculationService::buildPrompt()` không cần đổi.

---

## 2. Backend (BE)

### 2.1. Database: lưu `thinking_process` dạng string

**Hiện trạng:** Cột `quotation_routes.thinking_process` kiểu `json`, cast `array` – đang lưu object.

**Cách làm:** Đổi cột sang `text` để lưu một câu (string).

- **Tạo migration mới** (ví dụ: `xxxx_change_thinking_process_to_text_in_quotation_routes_table.php`):
  - `Schema::table('quotation_routes', function (Blueprint $table) { $table->text('thinking_process')->nullable()->change(); });`
  - Down: đổi lại về `json` nếu cần rollback (có thể bỏ qua nếu không cần giữ dữ liệu cũ).

- **Dữ liệu cũ:** Nếu có bản ghi đang lưu object, có thể:
  - Migration convert: đọc json, nếu là array thì `implode(' ', array_filter($v))` hoặc chỉ lấy một field (vd: `schedule_summary`) rồi ghi vào cột text; nếu đã là string thì giữ nguyên.
  - Hoặc chấp nhận để null/empty cho bản ghi cũ, chỉ route mới có thinking_process string.

### 2.2. Model: `app/Models/QuotationRoute.php`

- **Cast:** Xóa `'thinking_process' => 'array'`. Có thể thêm `'thinking_process' => 'string'` nếu muốn (Laravel không bắt buộc cho cột text).
- **$fillable:** Giữ `'thinking_process'` như hiện tại.

### 2.3. Service: `app/Services/AIRouteCalculationService.php`

- Trong method cập nhật route từ AI response (chỗ gán `thinking_process`):
  - **Hiện tại:** `$thinkingProcess = $response['thinking_process'] ?? [];` và `'thinking_process' => !empty($thinkingProcess) ? $thinkingProcess : null`.
  - **Đổi thành:**
    - `$thinkingProcess = $response['thinking_process'] ?? null;`
    - Nếu giá trị là **array** (response cũ/backward compat): có thể convert thành một câu, ví dụ `is_array($thinkingProcess) ? implode(' ', array_filter($thinkingProcess)) : $thinkingProcess`, hoặc chỉ lưu `null` cho response cũ.
    - Nếu là **string**: lưu trực tiếp.
    - Gán: `'thinking_process' => is_string($thinkingProcess) && $thinkingProcess !== '' ? $thinkingProcess : null`.

### 2.4. Controller: `app/Http/Controllers/Api/QuotationRouteController.php`

- **getThinkingProcessFromResponse(QuotationRoute $route):**
  - Đổi return type từ `?array` sang `?string` (hoặc `string|array|null` nếu tạm hỗ trợ cả hai).
  - Lấy `$thinkingProcess = $response['thinking_process'] ?? null;`
  - **Bỏ** kiểm tra `is_array($thinkingProcess)` và `requiredKeys` (route_strategy, calculation_basis, …).
  - Nếu `$thinkingProcess` là **string**: return nguyên.
  - Nếu là **array** (file response cũ): có thể `implode` hoặc return null tùy product.
  - Return `null` khi không có file / không có key.

- **Response khi calculate xong (store/calculate):**
  - Hiện tại trả về `'thinking_process' => $route->thinking_process ?? [ route_strategy => null, ... ]`.
  - **Đổi thành:** `'thinking_process' => $route->thinking_process ?? null` (string hoặc null).

- **Response show/detail (get route by id):**
  - Đảm bảo `$routeData['thinking_process']` là **string hoặc null**, không còn object với các key cũ.

### 2.5. API contract

- Tài liệu OpenAPI (nếu có): Sửa mô tả `thinking_process` từ object sang string (một câu báo cáo tiếng Nhật).

---

## 3. Frontend (FE)

- Ứng dụng AQ có thể nằm repo khác (vd: izumi-ai-quotation). Trong repo izumi-cloud chưa thấy component gọi API route detail.
- **Yêu cầu:** Trên màn hình kết quả route / chi tiết route, hiển thị `thinking_process` thành **một câu** (một đoạn text), không còn render từng field (route_strategy, calculation_basis, …).
- **Cách làm:** Binding một text block với `route.thinking_process` (string). Nếu giá trị null/empty thì ẩn block hoặc hiển thị placeholder tùy thiết kế.

---

## 4. Testing

- **Unit:** `QuotationRouteController::getThinkingProcessFromResponse` – test với response file chứa `thinking_process` là string; có thể thêm test với file cũ (object) trả về string hoặc null.
- **Unit:** `AIRouteCalculationService` – test parse response có `thinking_process` string và lưu vào `QuotationRoute`.
- **Feature:** `QuotationRouteApiTest` – test GET route detail trả về `thinking_process` là string; test calculate trả về `thinking_process` string.
- Cập nhật test hiện tại đang expect `thinking_process` là array (vd: `test_get_route_detail_returns_thinking_process_when_available`) sang expect string.

---

## 5. Thứ tự thực hiện đề xuất

1. Cập nhật **prompt** (`route_calculation_prompt.txt`).
2. **Migration** đổi cột `thinking_process` sang text (+ xử lý dữ liệu cũ nếu cần).
3. **Model** bỏ cast array cho `thinking_process`.
4. **AIRouteCalculationService** nhận và lưu `thinking_process` dạng string.
5. **QuotationRouteController** đọc/trả về `thinking_process` dạng string, bỏ validate keys object.
6. **Tests** cập nhật và chạy đầy đủ.
7. **FE** (repo AQ): hiển thị một câu theo API mới.

---

## 6. Ghi chú kỹ thuật

- **Backward compatibility:** Trong giai đoạn chuyển đổi, BE có thể tạm chấp nhận cả object và string từ AI/file; object thì convert hoặc bỏ qua, string thì lưu. Sau khi ổn định có thể chỉ nhận string.
- **Prompt:** File mới đã có sẵn tại `storage/app/prompts/route_calculation_prompt_new.txt` (theo git status); có thể đối chiếu với nội dung issue rồi dùng thay cho `route_calculation_prompt.txt` hoặc copy nội dung vào `route_calculation_prompt.txt`.
