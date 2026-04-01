# Issue #540 - Breakdown Summary

## 概要 / Overview

Issue #540 đã được breakdown thành **2 Backend Issues** vì đây là backend-only API project với 2 phần chính:
- **Part 1**: Quotation API với multiple delivery locations
- **Part 2**: AI Route Calculation Service update

---

## Created Issues

### Part 1: Quotation API

**Issue #541**: [BE] 見積時間計算: 出発地追加・複数届け地対応 / Tính toán thời gian: Thêm điểm xuất phát và hỗ trợ nhiều điểm giao hàng

- **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/541
- **Story Points**: 5 SP (≈ 5 giờ)
- **Labels**: `backend`, `enhancement`
- **Dependencies**: Không có (có thể phát triển độc lập)
- **Status**: ✅ Completed

**Scope bao gồm:**
1. Database Migrations (2 migrations)
   - Thêm cột `departure_location` vào bảng `quotations`
   - Tạo bảng `quotation_delivery_locations`

2. Models (2 models)
   - Update `Quotation` model
   - Tạo mới `QuotationDeliveryLocation` model

3. Repository Layer
   - Override `create()` method với DB Transaction
   - Override `update()` method với sync logic
   - Update eager loading trong search methods

4. Request Validation (2 files)
   - Update `CreateQuotationRequest`
   - Update `UpdateQuotationRequest`

5. Response Formatting
   - Update `QuotationResource` để format delivery_locations array

6. Controller Updates
   - Update eager loading trong `QuotationController`

7. Unit Tests
   - Test migrations
   - Test model relationships
   - Test repository create/update với transactions
   - Test API endpoints
   - Test cascade delete

---

### Part 2: AI Route Calculation Service (NEW)

**Issue #546**: [BE] AI経路計算: 複数届け地対応・新プロンプト / AI tính toán lộ trình: Hỗ trợ nhiều điểm giao hàng với prompt mới

- **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/546
- **Story Points**: 7 SP (≈ 7 giờ)
- **Labels**: `backend`, `enhancement`
- **Dependencies**: Issue #541 (Quotation API phải hoàn thành trước)
- **Status**: 🔄 Pending

**Scope bao gồm:**
1. Database Migration
   - Update bảng `quotation_routes` (thêm `start_location`, `delivery_locations`, `compliance_note`)
   - Update bảng `quotation_route_segments` (thêm `segment_type`)

2. Model Updates
   - Update `QuotationRoute` model với fields mới và JSON casting

3. Prompt Template
   - Backup old prompt
   - Tạo prompt mới với support cho multiple deliveries
   - Verify 9 variables mới

4. AI Service Updates (5 methods)
   - `buildPrompt()`: Handle multiple deliveries, new variables
   - `calculate()`: Accept new input fields
   - `saveLocations()`: Dynamic locations (start + multiple deliveries + return)
   - `saveSegments()`: Parse dynamic route_segments array
   - `parseAndSaveResponse()`: Parse new response format với compliance_info

5. API Controller
   - Update validation rules cho new fields
   - Accept `start_location` và `delivery_locations` array

6. Unit & Integration Tests
   - Test buildPrompt() với 1, 3, 5+ deliveries
   - Test saveLocations() tạo đúng số lượng locations
   - Test saveSegments() với dynamic segments
   - Test compliance calculations (430 rule, Labor Law)
   - Test với real Japanese addresses

---

## Story Points Breakdown

### Part 1: Quotation API (Issue #541)

| Component | Tasks | SP | Hours |
|-----------|-------|----|----|
| Database Setup | Migrations + Testing | 0.5 | 0.5h |
| Model Layer | Quotation + QuotationDeliveryLocation | 0.5 | 0.5h |
| Repository Layer | create/update/search với Transaction | 1.5 | 1.5h |
| Request Validation | CreateRequest + UpdateRequest | 0.3 | 0.3h |
| Response Formatting | QuotationResource | 0.5 | 0.5h |
| Controller Updates | Eager loading | 0.2 | 0.2h |
| Testing & QA | Unit tests + Integration tests | 1.5 | 1.5h |
| **TOTAL Part 1** | | **5 SP** | **5h** |

### Part 2: AI Service (Issue #TBD)

| Component | Tasks | SP | Hours |
|-----------|-------|----|----|
| Database Migration | quotation_routes + segments update | 0.5 | 0.5h |
| Model Updates | QuotationRoute với JSON casting | 0.3 | 0.3h |
| Prompt Template | Backup + Create new prompt | 0.5 | 0.5h |
| buildPrompt() | Handle arrays + new variables | 0.5 | 0.5h |
| calculate() | Accept new fields | 0.3 | 0.3h |
| saveLocations() | Dynamic locations logic | 1.0 | 1.0h |
| saveSegments() | Dynamic segments parsing | 1.0 | 1.0h |
| parseAndSaveResponse() | New format + compliance_info | 0.5 | 0.5h |
| Controller Updates | Validation rules | 0.3 | 0.3h |
| Testing & QA | Unit + Integration + Manual tests | 2.0 | 2.0h |
| **TOTAL Part 2** | | **7 SP** | **7h** |

### Grand Total

| Part | SP | Hours |
|------|----|----|
| Part 1: Quotation API | 5 SP | 5h |
| Part 2: AI Service | 7 SP | 7h |
| **GRAND TOTAL** | **12 SP** | **12h** |

---

## Implementation Order

### Part 1: Quotation API (Issue #541) - ✅ Completed

#### Phase 1: Database Setup
- Tạo và chạy migrations
- Verify foreign key và cascade delete

#### Phase 2: Model Layer  
- Tạo `QuotationDeliveryLocation` model
- Update `Quotation` model với field và relationship

#### Phase 3: Repository Layer
- Implement create() với transaction
- Implement update() với sync logic
- Update search methods với eager loading

#### Phase 4: Request/Response
- Update validation requests
- Update resource formatting

#### Phase 5: Controller
- Update eager loading

#### Phase 6: Testing
- Unit tests cho từng layer
- Integration tests cho API endpoints
- Test cascade delete và transaction rollback

---

### Part 2: AI Service (Issue #546) - 🔄 Pending

**Dependencies**: Part 1 phải hoàn thành trước

#### Phase 7: Database Schema for AI Routes
- Tạo migration cho `quotation_routes` table
- Thêm columns: `start_location`, `delivery_locations` (JSON), `compliance_note`
- Thêm `segment_type` column cho `quotation_route_segments`
- Run migration và verify

#### Phase 8: Prompt Template
- Backup old prompt: `route_calculation_prompt.txt.old`
- Tạo prompt mới với full template từ GitHub comment
- Verify 9 variables: start_location, pickup_location, delivery_locations, etc.

#### Phase 9: Model Updates
- Update `QuotationRoute` model
- Thêm fields vào `$fillable`
- Thêm JSON casting cho `delivery_locations`

#### Phase 10: AI Service Methods
- Update `buildPrompt()`: Handle delivery_locations array, new variables
- Update `calculate()`: Accept start_location và delivery_locations
- Update `saveLocations()`: Dynamic locations với start + multiple deliveries
- Update `saveSegments()`: Parse route_segments array dynamically
- Update `parseAndSaveResponse()`: Parse compliance_info section

#### Phase 11: Controller Updates
- Find controller using AIRouteCalculationService
- Update validation rules cho new fields

#### Phase 12: Testing AI Service
- Unit tests: buildPrompt() với 1, 3, 5+ deliveries
- Integration tests: Full flow với multiple scenarios
- Manual testing: Postman với real Japanese addresses
- Verify compliance calculations (430 rule, Labor Law)
- Test error handling

---

## Technical Highlights

### Part 1: Quotation API

**Key Design Decisions:**

1. **Separate Table for Delivery Locations**
   - ✅ Better performance (no JSON storage)
   - ✅ Easy to query and filter
   - ✅ Unlimited delivery locations support
   - ✅ Data integrity với foreign key

2. **DB Transactions**
   - ✅ Ensure data consistency
   - ✅ Auto rollback on error
   - ✅ Atomic operations for create/update

3. **Cascade Delete**
   - ✅ Auto cleanup when quotation deleted
   - ✅ No manual delete logic needed

4. **Eager Loading**
   - ✅ Avoid N+1 query problem
   - ✅ Better performance for list/detail APIs

5. **Backward Compatibility**
   - ✅ Keep old `delivery_location` field
   - ✅ Support both old and new format

---

### Part 2: AI Service

**Key Design Decisions:**

1. **JSON Storage for AI Routes**
   - ✅ Quick implementation (single column)
   - ✅ Flexible structure
   - ✅ Easy to serialize/deserialize
   - ⚠️ Trade-off: Harder to query individual locations

2. **Dynamic Route Segments**
   - ✅ Support unlimited delivery locations
   - ✅ Flexible segment types (回送/実車)
   - ✅ Proper sequence ordering
   - ✅ Maps to locations dynamically

3. **New AI Response Format**
   - ✅ Structured `route_segments` array
   - ✅ Compliance info section (430 rule, Labor Law)
   - ✅ Clear segment types and descriptions
   - ⚠️ Breaking change from old format

4. **Prompt Template Variables**
   - ✅ 9 variables support (up from 8)
   - ✅ Comma-separated delivery locations
   - ✅ Fallback to old single delivery_location
   - ✅ Default values for all parameters

5. **Compliance Calculations**
   - ✅ 430 Rule: 4時間運転 → 30分休憩
   - ✅ Labor Law: 6h+ → 45min, 8h+ → 60min
   - ✅ Auto-calculate required breaks
   - ✅ Store compliance note for audit

6. **Backward Compatibility**
   - ✅ Keep old `delivery_location` field in routes
   - ✅ Fallback logic in buildPrompt()
   - ⚠️ Old response format no longer supported (breaking change)

---

## Testing Strategy

### Part 1: Quotation API

**Unit Tests:**
- Repository create/update logic
- Model relationships
- Transaction rollback scenarios

**Integration Tests:**
- API POST with delivery_locations array
- API PUT update delivery_locations
- API GET return correct format
- Cascade delete verification

**Manual Testing:**
- Postman/Thunder Client
- Edge cases: empty array, large array, special characters

---

### Part 2: AI Service

**Unit Tests:**
- `buildPrompt()` với single delivery
- `buildPrompt()` với multiple deliveries (2-5)
- `buildPrompt()` với empty array
- `buildPrompt()` fallback to old delivery_location
- Delivery locations array → comma-separated conversion
- Default values (vehicle_type, loading_time, unloading_time=30)

**Integration Tests:**
- Full flow với 1 delivery location
- Full flow với 3 delivery locations
- Full flow với 5+ delivery locations
- Same start and return location
- Response parsing với new format
- `saveLocations()` tạo đúng số lượng locations
- `saveSegments()` tạo đúng số lượng segments
- Compliance calculations (430 rule, Labor Law)
- `date_change` flag
- Error handling với invalid AI response

**Manual Testing:**
- Postman/Thunder Client
- Real Japanese addresses:
  - 東京本社 → 東京倉庫 → 横浜倉庫 → 川崎センター → 千葉配送所 → 東京本社
- Verify route segments order
- Verify segment types (回送/実車)
- Verify distance calculations
- Verify toll calculations
- Verify break time calculations
- Verify end time calculations

---

## Success Criteria

### Part 1: Quotation API (Issue #541) - ✅ Completed

- [x] Issue #541 được assign cho developer
- [x] All migrations chạy thành công
- [x] All unit tests pass
- [x] API endpoints hoạt động đúng với delivery_locations array
- [x] Cascade delete hoạt động
- [x] Transaction rollback đúng khi có lỗi
- [x] Code review pass
- [x] Ready for QA testing

### Part 2: AI Service (Issue #546) - 🔄 Pending

- [ ] Issue được tạo và assign cho developer
- [ ] Migration cho quotation_routes chạy thành công
- [ ] Prompt template updated và verified
- [ ] `buildPrompt()` handles multiple deliveries correctly
- [ ] `saveLocations()` tạo đúng số lượng locations (start + deliveries + return)
- [ ] `saveSegments()` tạo dynamic segments correctly
- [ ] `parseAndSaveResponse()` parse new format successfully
- [ ] Controller validation rules updated
- [ ] All unit tests pass (buildPrompt, array conversion)
- [ ] All integration tests pass (1, 3, 5+ deliveries)
- [ ] Manual testing với real Japanese addresses successful
- [ ] Compliance calculations correct (430 rule verified)
- [ ] `date_change` flag works correctly
- [ ] Error handling robust
- [ ] Code review pass
- [ ] API documentation updated
- [ ] Ready for QA testing

---

## Risk Assessment

### Part 1: Quotation API - Low Risk ✅

- ✅ Straightforward database schema
- ✅ Well-established patterns (Repository, Transaction)
- ✅ No breaking changes (backward compatible)

### Part 2: AI Service - Medium-High Risk ⚠️

**High Risk Items:**
1. **Breaking Changes in AI Response Format**
   - Old code expects `route_details.section_1` format
   - New code uses `route_segments` array
   - **Mitigation**: Thorough testing, clear documentation

2. **Database Migration on Production**
   - Adding columns to existing table
   - **Mitigation**: Test on staging first, backup database, rollback plan ready

**Medium Risk Items:**
3. **Multiple Segments Logic Complexity**
   - Dynamic number of segments based on deliveries
   - Mapping segments to locations correctly
   - **Mitigation**: Comprehensive unit tests, detailed logging

4. **AI Response Validation**
   - AI might return unexpected format
   - **Mitigation**: Strict validation, error handling, fallback values

---

## Dependencies

```
Issue #540 (Parent)
    ├── Issue #541 (Part 1: Quotation API) ✅ Completed
    │   └── No dependencies
    │
    └── Issue #TBD (Part 2: AI Service) 🔄 Pending
        └── Depends on: Issue #541 (must complete first)
```

**Reason for Dependency:**
- AI Service needs the new `departure_location` and `delivery_locations` fields from Quotation API
- Integration testing requires both parts working together

---

## Notes

### General
- Backend-only implementation (không có frontend trong project này)
- API cung cấp cho external frontend application
- Story Points đã được add vào GitHub Project #138 (Github Issue)

### Part 1: Quotation API
- ✅ Completed and deployed
- Maintain backward compatibility với API version cũ
- Separate table design cho better performance

### Part 2: AI Service
- 🔄 Pending implementation
- **Breaking change**: Old AI response format no longer supported
- Requires coordination với AI prompt update
- Need to update API documentation after completion
- Consider phased rollout (staging → production)

---

## Next Steps

1. **Create Issue for Part 2**
   - Use GitHub CLI: `gh issue create`
   - Title: `[BE] AI経路計算: 複数届け地対応・新プロンプト / AI tính toán lộ trình: Hỗ trợ nhiều điểm giao hàng với prompt mới`
   - Add labels: `backend`, `enhancement`, `ai-service`
   - Set Story Points: 7 SP
   - Link to parent issue #540

2. **Assign Developer**
   - Assign to backend developer familiar with AI services
   - Ensure they have access to OpenAI API key

3. **Prepare for Implementation**
   - Review `plan.md` Part 2 section
   - Review `ai-prompt-update.md` for detailed guide
   - Set up staging environment for testing

4. **Testing Preparation**
   - Prepare test data with real Japanese addresses
   - Set up Postman collection for manual testing
   - Plan integration testing scenarios

5. **Documentation**
   - Update API documentation after completion
   - Document new prompt format
   - Document new response structure
   - Create migration guide for API consumers

