# Issue #499: AQ_AI calculation

## Metadata

- **Title:** AQ_AI calculation
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/499
- **Status:** OPEN
- **Created:** 2025-12-11T10:00:23Z
- **Updated:** 2025-12-11T10:02:08Z
- **Assignees:** @phuongcodeunited
- **Labels:** None

## Description

- Hệ thống AQ
- Chức năng tính toán AI: user nhập các địa điểm giao nhận hàng, thời gian
- AI sẽ tính toán khoảng cách quãng đường, thời gian di chuyển, thời gian làm việc, nghỉ ngơi, phí cao tốc
- Task: sử dụng promt để thực hiện và lưu dữ liệu vào DB, hiển thị dữ liệu tính toán lên màn hình

## Implementation Checklist

### Phase 1: Database Design ✅

- [ ] Tạo migration cho bảng `quotation_routes` (Bảng chính lưu kết quả tính toán)
- [ ] Tạo migration cho bảng `quotation_route_locations` (Các điểm dừng: pickup, delivery, return)
- [ ] Tạo migration cho bảng `quotation_route_segments` (Chi tiết từng đoạn đường)
- [ ] Tạo migration cho bảng `quotation_route_files` (Lưu path đến file JSON response từ AI)

### Phase 2: AI Integration

- [ ] Tạo service `AIRouteCalculationService` để xử lý logic gọi AI
- [ ] Implement prompt template cho AI (đã có sẵn prompt tiếng Nhật)
- [ ] Xử lý parse JSON response từ AI
- [ ] Implement logic lưu AI response thành file JSON (storage/app/ai_responses/)
- [ ] Xử lý timeout và error từ AI service
- [ ] Implement retry mechanism nếu AI call fail

### Phase 3: API Development

- [ ] Tạo Controller `QuotationRouteController`
- [ ] POST `/api/quotation/routes/calculate` - Nhận input và trigger AI calculation
  - Validate input (pickup_location, delivery_location, return_location, start_time, etc)
  - Call AI service
  - Lưu kết quả vào DB
  - Lưu AI response thành file JSON
  - Return response cho FE
- [ ] GET `/api/quotation/routes/{id}` - Lấy chi tiết 1 route calculation
- [ ] GET `/api/quotation/routes` - Lấy danh sách lịch sử tính toán (có pagination)
- [ ] GET `/api/quotation/routes/{id}/ai-response` - Lấy file JSON gốc từ AI

### Phase 4: Validation & Error Handling

- [ ] Tạo FormRequest cho validate input
- [ ] Validate địa chỉ (không được rỗng, format hợp lệ)
- [ ] Validate thời gian (start_time, loading_time, unloading_time)
- [ ] Validate vehicle_type
- [ ] Xử lý error khi AI không trả về đúng format
- [ ] Xử lý error khi không thể lưu file
- [ ] Return error response chuẩn cho FE

### Phase 5: Testing

- [ ] Unit test cho AIRouteCalculationService
- [ ] Unit test cho parse AI response
- [ ] Feature test cho API endpoints
- [ ] Test case: AI trả về success
- [ ] Test case: AI trả về error
- [ ] Test case: Timeout
- [ ] Test case: Invalid input
- [ ] Test case: Lưu và đọc file JSON

## Technical Notes

### Database Design

**Nguyên tắc:** TUYỆT ĐỐI KHÔNG lưu JSON vào database

#### Bảng 1: `quotation_routes` (Bảng chính)
Lưu thông tin tổng quan và kết quả tính toán (các giá trị scalar)
- Input: pickup_location, delivery_location, return_location, start_time, loading_time, unloading_time, vehicle_type
- Output từ AI: total_distance_km, estimated_end_time, total_duty_time_hours, highway_fee, is_compliant, applied_rule
- Metadata: user_id, route_code, status, ai_model_used, created_at, updated_at

#### Bảng 2: `quotation_route_locations` (Các điểm dừng)
Lưu chi tiết từng điểm (pickup, delivery, return) - NORMALIZED
- route_id (FK)
- sequence_order, location_type, address, prefecture, city, lat, lng
- arrival_time, departure_time, stay_duration_minutes
- distance_from_previous_km, travel_time_from_previous_min

#### Bảng 3: `quotation_route_segments` (Chi tiết đoạn đường)
Lưu chi tiết từng đoạn (pickup→delivery, delivery→return) - NORMALIZED
- route_id (FK), from_location_id (FK), to_location_id (FK)
- segment_order, distance_km, driving_time_minutes
- highway_fee, fuel_cost, road_type, highway_name, route_description

#### Bảng 4: `quotation_route_files` (Lưu path file JSON)
Lưu đường dẫn đến file JSON response từ AI (để audit/debug)
- route_id (FK)
- file_type (request/response)
- file_path (VD: ai_responses/2025/12/QR-20251212-001-response.json)
- file_size, mime_type
- created_at

**Lý do tách file JSON:**
- ✅ Database nhẹ, query nhanh
- ✅ Dễ archive/xóa file cũ (sau 30-90 ngày)
- ✅ Có thể move sang S3/cloud storage
- ✅ Không ảnh hưởng performance khi query data thường xuyên
- ✅ Vẫn có thể audit/debug khi cần

### AI Integration

**Prompt:** Đã có sẵn prompt tiếng Nhật tuân thủ luật lao động 2024

**AI Service:** Cần xác định sử dụng:
- OpenAI GPT-4 / GPT-4 Turbo
- Anthropic Claude 3.5 Sonnet
- Google Gemini Pro

**Flow:**
1. Nhận input từ FE
2. Build prompt từ template + input variables
3. Call AI API
4. Lưu request thành file: `storage/app/ai_responses/{year}/{month}/QR-{code}-request.json`
5. Lưu response thành file: `storage/app/ai_responses/{year}/{month}/QR-{code}-response.json`
6. Parse response JSON
7. Lưu vào DB (3 bảng: routes, locations, segments)
8. Lưu file paths vào bảng `quotation_route_files`
9. Return response cho FE

### API Design

**Endpoint chính:**
```
POST /api/quotation/routes/calculate
Request:
{
  "pickup_location": "東京都千代田区...",
  "delivery_location": "神奈川県横浜市...",
  "return_location": "埼玉県さいたま市...",
  "start_time": "01:00",
  "vehicle_type": "4t",
  "loading_time": 60,
  "unloading_time": 60,
  "break_time": "Auto"
}

Response:
{
  "success": true,
  "data": {
    "route_id": 123,
    "route_code": "QR-20251212-001",
    "summary": {
      "total_distance_km": 150.5,
      "estimated_end_time": "18:30",
      "highway_fee": 5000,
      "is_compliant": true
    },
    "time_breakdown": {...},
    "locations": [...],
    "segments": [...]
  }
}
```

**Endpoint lấy lịch sử:**
```
GET /api/quotation/routes?page=1&per_page=20
GET /api/quotation/routes/{id}
GET /api/quotation/routes/{id}/ai-response (download file JSON gốc)
```

### File Storage Structure

```
storage/app/
└── ai_responses/
    └── quotation_routes/
        └── 2025/
            └── 12/
                ├── QR-20251212-001-request.json
                ├── QR-20251212-001-response.json
                ├── QR-20251212-002-request.json
                └── QR-20251212-002-response.json
```

**Cleanup Policy:**
- Giữ file 30 ngày cho debug
- Archive file > 30 ngày sang S3 (nếu cần)
- Xóa file > 90 ngày (nếu không cần audit lâu dài)

## Review & Notes

### Đã làm rõ:
✅ Prompt AI đã có sẵn (tuân thủ luật Nhật Bản 2024)
✅ Input: 3 địa điểm (pickup, delivery, return) + thời gian + loại xe
✅ Output: Khoảng cách, thời gian, phí cao tốc, tuân thủ luật lao động
✅ UI: Màn hình 運行スケジュール với nút "AI計算"
✅ Lưu DB: 3 bảng normalized + 1 bảng lưu file paths
✅ Không lưu JSON vào DB, lưu thành file riêng

### Cần confirm với team:
- AI service nào sẽ dùng? (OpenAI/Claude/Gemini)
- API key và budget cho AI calls
- Có cần cache kết quả cho route giống nhau không?
- Policy xóa file JSON cũ (30 ngày? 90 ngày?)
- Có cần notification khi AI calculation fail không?

## Branch Information

- **Branch Name:** 499-feat-aq-ai-calculation
- **Base Branch:** 472-create-database-and-api-list-cursor-command-test

