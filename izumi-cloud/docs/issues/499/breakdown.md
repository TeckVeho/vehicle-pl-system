# Issue #499: AQ_AI calculation - Breakdown Summary

## 概要 (Overview)

Issue #499 đã được breakdown thành **2 issues** (1 Backend + 1 Frontend) theo chiến lược mặc định.

---

## Created Issues

### ✅ Issue #501: [BE] AI経路計算: データベース・API・ユニットテスト / Tính toán lộ trình AI: Database, API với Unit Tests

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/501

**Labels:** `backend`, `enhancement`

**Story Points:** **15 SP** (15 giờ)

**Scope:**
- 4 Database migrations (quotation_routes, quotation_route_locations, quotation_route_segments, quotation_route_files)
- 4 Eloquent Models với relationships
- AIRouteCalculationService (AI API integration, JSON file storage, DB operations)
- 4 API endpoints (calculate, index, show, downloadAIResponse)
- Request validation (CalculateRouteRequest)
- Cleanup command (xóa file JSON cũ)
- OpenAI configuration
- Backend unit tests

**Dependencies:** Không có (có thể phát triển độc lập)

**Estimated Effort Breakdown:**
- Database & Models: 2-3 giờ
- Service Layer: 4-6 giờ
- API Layer: 2-3 giờ
- Cleanup & Utilities: 1-2 giờ
- Testing & Bug Fixes: 3-4 giờ
- **Total:** 12-18 giờ → **15 SP**

---

### ✅ Issue #502: [FE] AI経路計算: UI統合・API連携 / Tích hợp UI tính toán lộ trình AI với API

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/502

**Labels:** `frontend`, `enhancement`

**Story Points:** **4 SP** (4 giờ)

**Scope:**
- Thêm nút "AI計算" vào form báo giá
- API client integration (POST /api/quotation/routes/calculate)
- Loading state & error handling
- Tự động điền kết quả vào form (distance, end_time, highway_fee)
- Timeout handling (120s)
- UX improvements (disable button, progress indicator)
- Frontend unit tests

**Dependencies:** 
- **Blocking:** Issue #501 (Backend) phải hoàn thành trước
- Cần Backend API endpoint sẵn sàng để integration testing

**Estimated Effort Breakdown:**
- UI Implementation: 2-3 giờ
- Testing: 1 giờ
- **Total:** 3-4 giờ → **4 SP**

---

## Implementation Strategy

### Parallel Development (Recommended)

```
Week 1:
├── Backend Developer: Implement #501 (Phase 1-4)
│   ├── Day 1-2: Database & Models
│   ├── Day 2-3: Service Layer & AI Integration
│   └── Day 3-4: API Layer & Testing
│
└── Frontend Developer: Prepare #502 (Mock API)
    ├── Day 1-2: UI components với mock data
    └── Day 3-4: Chờ Backend hoàn thành

Week 2:
├── Backend Developer: Complete #501
│   └── Day 1: Bug fixes & Documentation
│
└── Frontend Developer: Complete #502
    ├── Day 1: Integrate real API
    └── Day 1: Integration testing
```

### Sequential Development (Alternative)

```
Phase 1: Backend (#501) - 15 SP
    ↓ (Backend complete, API ready)
Phase 2: Frontend (#502) - 4 SP
    ↓ (Frontend complete)
Phase 3: Integration Testing
```

---

## Story Points Registration

### Manual Registration (Script không khả dụng)

Vì script `setsp.ps` không tồn tại trong repo, bạn cần register SP manually qua GitHub Projects UI:

#### Cách 1: Qua GitHub Projects Board

1. Mở GitHub Projects board
2. Tìm issue #501 và #502
3. Click vào issue
4. Tìm field "Story Points" hoặc "SP"
5. Nhập giá trị:
   - Issue #501: **15**
   - Issue #502: **4**

#### Cách 2: Qua GitHub CLI (nếu biết project ID)

```bash
# Lấy project ID
gh project list --owner TeckVeho

# Register SP cho issue #501
gh project item-edit \
  --project-id <PROJECT_ID> \
  --field "Story Points" \
  --value 15 \
  <ITEM_ID_501>

# Register SP cho issue #502
gh project item-edit \
  --project-id <PROJECT_ID> \
  --field "Story Points" \
  --value 4 \
  <ITEM_ID_502>
```

#### Cách 3: Qua GitHub API (nếu cần automation)

```bash
# Cần có: PROJECT_ID, ITEM_ID, FIELD_ID
curl -X POST \
  -H "Authorization: Bearer $GITHUB_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"fieldId": "<FIELD_ID>", "value": {"number": 15}}' \
  https://api.github.com/graphql
```

---

## Total Effort

- **Backend:** 15 SP (15 giờ)
- **Frontend:** 4 SP (4 giờ)
- **Total:** **19 SP (19 giờ)** ≈ **2.5 ngày làm việc**

---

## Acceptance Criteria Summary

### Backend (#501) ✅ Complete When:
- [ ] 4 migrations chạy thành công
- [ ] 4 models với relationships hoạt động
- [ ] AIRouteCalculationService hoạt động (gọi AI, parse, lưu DB, lưu file)
- [ ] 4 API endpoints hoạt động đúng
- [ ] Validation hoạt động
- [ ] Cleanup command hoạt động
- [ ] Unit tests pass
- [ ] API documentation updated

### Frontend (#502) ✅ Complete When:
- [ ] Nút "AI計算" hiển thị và hoạt động
- [ ] API call thành công
- [ ] Loading state hiển thị đúng
- [ ] Kết quả tự động điền vào form
- [ ] Error handling hoạt động
- [ ] Timeout handling hoạt động
- [ ] Unit tests pass
- [ ] Browser compatibility OK

### Integration ✅ Complete When:
- [ ] FE gọi BE API thành công
- [ ] Data flow end-to-end hoạt động
- [ ] Error cases được xử lý đúng
- [ ] Performance acceptable (< 30s)

---

## Technical Notes

### Backend Considerations:
- OpenAI API key phải được cấu hình trong `.env`
- AI API call có thể mất 10-30 giây → Cần timeout 120s
- JSON files lưu tại `storage/app/ai_responses/quotation_routes/YYYY/MM/`
- Cleanup command nên chạy daily (cron job)

### Frontend Considerations:
- Disable nút khi đang tính toán (tránh double-click)
- Hiển thị progress indicator rõ ràng
- Handle timeout gracefully (120s)
- Xác nhận trước khi ghi đè dữ liệu cũ (nếu có)

### Integration Considerations:
- Backend phải deploy trước Frontend
- Test với real OpenAI API key trước khi deploy production
- Monitor AI API call success rate
- Setup alerting nếu fail rate > 10%

---

## Next Steps

1. ✅ **Backend Developer:** Start implementing #501
   - Clone repo, checkout branch `499-feat-aq-ai-calculation`
   - Create migrations
   - Implement models
   - Implement service layer
   - Implement API endpoints
   - Write tests

2. ⏳ **Frontend Developer:** Prepare for #502
   - Review API documentation
   - Design UI components
   - Setup mock API for local development
   - Wait for Backend completion

3. 🔄 **Integration:** After both complete
   - Deploy Backend to staging
   - Deploy Frontend to staging
   - Run integration tests
   - Fix bugs if any
   - Deploy to production

---

## References

- **Parent Issue:** #499
- **Plan Document:** `docs/issues/499/plan.md`
- **Database Design:** `docs/issues/499/database-design.md`
- **Backend Issue:** #501
- **Frontend Issue:** #502

---

## Questions / Issues

Nếu có câu hỏi hoặc vấn đề trong quá trình implementation:

1. Comment vào issue tương ứng (#501 hoặc #502)
2. Tag người có thể giúp đỡ
3. Update issue status nếu bị block
4. Liên hệ team lead nếu cần clarification

---

**Created:** 2025-12-12  
**Last Updated:** 2025-12-12  
**Status:** Ready for Implementation

