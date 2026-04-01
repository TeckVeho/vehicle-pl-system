# Issue #752: AQ_Update thinking_process display

## Metadata
- **Title:** AQ_Update thinking_process display
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/752
- **State:** OPEN
- **Created:** 2026-02-05
- **Updated:** 2026-02-05
- **Assignees:** @tungnt183855
- **Labels:** None
- **Branch:** `752-aq_update-thinking_process-display`

---

## Description

Đã update prompt phần **thinking_process**. Cần cập nhật BE và FE cho đồng bộ.

### Yêu cầu

1. **Prompt (đã cập nhật)**  
   - Output format: `thinking_process` là **một chuỗi (string)** — báo cáo một câu bằng tiếng Nhật, tổng hợp: ルート、IC名、法令（430ルール・労基法）による休憩根拠、総運転時間 等.

2. **Backend (BE)**  
   - Cập nhật để nhận/lưu/trả về `thinking_process` dạng **string** (một câu) thay vì object (route_strategy, calculation_basis, …).

3. **Frontend (FE)**  
   - Hiển thị `thinking_process` thành **một câu** trên giao diện (không còn dạng nhiều field con).

### Định dạng mới của thinking_process (trong prompt)

- Trong JSON output của AI, `thinking_process` là **string**, ví dụ:
  - `"出発地の[出発地名]から[積地名]への回送を含めた全N区間のルートで計算しました。主要経路として[道路名]（[入口IC名]～[出口IC名]）などを選択し、[車種区分]料金を適用しています。総運転時間がX時間を超えるため、改善基準告示（430ルール）に基づきX分、および労基法に基づき追加X分、合計X分の休憩時間を確保したスケジュールを作成しました。"`

---

## Scope

- **Backend (BE):** Cập nhật prompt file, parse/lưu/trả về `thinking_process` dạng string.
- **Frontend (FE):** Hiển thị `thinking_process` thành một câu trên UI (FE AQ có thể nằm repo khác).

---

## Implementation Checklist

### Prompt
- [ ] Cập nhật file prompt `route_calculation_prompt.txt` theo nội dung mới (Output Format có `thinking_process` là string).

### Backend
- [ ] Cập nhật lưu `thinking_process`: chấp nhận string từ AI response, lưu vào DB (cột phù hợp: text hoặc json chứa string).
- [ ] Cập nhật model/cast nếu đổi kiểu dữ liệu (array → string).
- [ ] Cập nhật `getThinkingProcessFromResponse`: trả về `?string`, bỏ validate keys của object cũ.
- [ ] Cập nhật API response (calculate xong + get route detail): trả về `thinking_process` là string.
- [ ] Cập nhật test (unit/feature) cho thinking_process dạng string.

### Frontend
- [ ] Hiển thị `thinking_process` thành một câu trên giao diện (màn hình kết quả route/AI).

---

## Prompt mới (tham khảo)

_(Nội dung đầy đủ nằm trong `plan.md` hoặc file prompt sau khi cập nhật.)_

- **Role / Context / Input Data:** Giữ logic hiện tại, bổ sung mô tả rõ bước “Thinking Process” và yêu cầu output.
- **Output Format:**  
  - `time_breakdown` có cấu trúc chi tiết (total_duty_time_hours, actual_working_hours, total_break_time_minutes, details).  
  - `cost_breakdown` (total_tolls_yen).  
  - `compliance_info` (is_compliant, break_time_source, note).  
  - **`thinking_process`:** string — một câu báo cáo (自然な日本語で、IC名・法令ルール・総運転時間などを含む).  
  - `route_segments`: mảng các segment (segment_order, type, from, to, distance_km, driving_time_minutes, toll_yen, route_description).

---

## Notes

- Hiện tại BE đang coi `thinking_process` là **array/object** (route_strategy, calculation_basis, workload_analysis, compliance_reasoning, schedule_summary); sau issue này chuyển sang **string**.
- Cần đảm bảo backward compatibility hoặc migration dữ liệu cũ (nếu có) khi đổi format.
- FE AQ có thể ở repo khác (vd: izumi-ai-quotation); tài liệu API cần ghi rõ `thinking_process` là string.
