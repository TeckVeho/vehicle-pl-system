# Issue #603: AQ_Update calculation prompt

## Metadata

- **Issue Number:** 603
- **Title:** AQ_Update calculation prompt
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/603
- **State:** OPEN
- **Created At:** 2026-01-09T02:09:37Z
- **Updated At:** 2026-01-09T02:10:59Z
- **Assignees:** phuongcodeunited

## Description

Điểm thay đổi là có thể xuất được quy trình thực hiện

### Yêu cầu

Cập nhật lại prompt mới và trả thêm về response API các key sau:
- `route_strategy`
- `calculation_basis`
- `workload_analysis`
- `compliance_reasoning`
- `schedule_summary`

Nếu các key này đã có rồi thì báo cho tôi.

### Prompt hiện tại

Prompt hiện tại yêu cầu AI trả về JSON với format:
- `summary`
- `time_breakdown`
- `cost_breakdown`
- `compliance_info`
- `route_segments`

### Prompt mới (từ issue body)

Prompt mới yêu cầu thêm `thinking_process` object với các key:
- `route_strategy`: "出発地と積地の位置関係、および届け地リストの順序に基づき、効率的な回送・配送ルートをどのように構築したかの説明。"
- `calculation_basis`: "採用した車両区分、設定した平均速度（高速/下道）、および料金区分の前提条件の説明。"
- `workload_analysis`: "積み込み・荷下ろしの回数に基づき、運転以外の作業時間がトータルでどれくらい発生しているかの説明。"
- `compliance_reasoning`: "総拘束時間や連続運転時間を監視し、どの法令ルール（430、改善基準告示等）に基づいて休憩時間を割り当てたかの法的根拠の説明。"
- `schedule_summary`: "開始時間に各所要時間を加算し、最終的な終了時間を導き出したプロセスの要約。"

## Implementation Checklist

- [x] Kiểm tra các key API đã có trong codebase chưa
- [x] Cập nhật prompt file (`storage/app/prompts/route_calculation_prompt.txt`) để thêm `thinking_process` vào output format
- [x] Cập nhật `QuotationRouteController@calculate` để trả về `thinking_process` trong API response
- [x] Cập nhật `QuotationRouteController@show` để trả về `thinking_process` trong API response
- [ ] Test API response có chứa các key mới

## Notes / Review

### Kiểm tra các key đã có

Sau khi kiểm tra codebase:
- ❌ `route_strategy` - **CHƯA CÓ**
- ❌ `calculation_basis` - **CHƯA CÓ**
- ❌ `workload_analysis` - **CHƯA CÓ**
- ❌ `compliance_reasoning` - **CHƯA CÓ**
- ❌ `schedule_summary` - **CHƯA CÓ**
- ❌ `thinking_process` - **CHƯA CÓ**

**Kết luận:** Tất cả các key này chưa có trong codebase, cần implement mới.

### Files đã thay đổi

1. ✅ `storage/app/prompts/route_calculation_prompt.txt` - Đã cập nhật output format để thêm `thinking_process` object
2. ✅ `app/Http/Controllers/Api/QuotationRouteController.php` - Đã thêm method `getThinkingProcessFromResponse()` và cập nhật `calculate()` và `show()` để trả về `thinking_process` trong API response

### Chi tiết thay đổi

#### 1. Prompt File (`storage/app/prompts/route_calculation_prompt.txt`)
- Thêm `thinking_process` object vào output format với 5 key:
  - `route_strategy`
  - `calculation_basis`
  - `workload_analysis`
  - `compliance_reasoning`
  - `schedule_summary`

#### 2. Controller (`app/Http/Controllers/Api/QuotationRouteController.php`)
- Thêm method `getThinkingProcessFromResponse()` để đọc `thinking_process` từ AI response file
- Cập nhật `calculate()` để trả về `thinking_process` trong response
- Cập nhật `show()` để trả về `thinking_process` trong response

### Lưu ý

- `thinking_process` không cần lưu vào database, chỉ cần trả về trong API response
- Có thể lưu vào response file JSON để tham khảo sau này
- Các key này là thông tin giải thích quá trình tính toán, không ảnh hưởng đến logic tính toán
