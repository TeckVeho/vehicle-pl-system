# Issue #489: Breakdown Summary

## Overview

Task #489 đã được breakdown thành **2 GitHub issues** theo chiến lược mặc định (1 FE + 1 BE):

---

## Created Issues

### 1. Backend Issue #492

**Title:** `[BE] 車検満了日表示の統一: Model・API・Repository / Thống nhất hiển thị ngày hết hạn kiểm định: Model, API, Repository`

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/492

**Story Points:** 3 SP (~3 hours)

**Labels:** `backend`, `enhancement`

**Scope:**
- Model Layer: Thêm `$casts` và accessor cho date fields trong `Vehicle.php`
- Controller Layer: Cập nhật `VehicleController::show()` để format dates
- Repository Layer: Verify `DATE_FORMAT` queries trong `VehicleRepository.php`
- Backend unit tests

**Dependencies:** Không có (có thể triển khai độc lập)

**Key Files:**
- `app/Models/Vehicle.php`
- `app/Http/Controllers/Api/VehicleController.php`
- `app/Repositories/VehicleRepository.php`

---

### 2. Frontend Issue #493

**Title:** `[FE] 車検満了日表示の統一: UI・フォーマット関数・ユニットテスト / Thống nhất hiển thị ngày hết hạn kiểm định: UI, hàm format, Unit Tests`

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/493

**Story Points:** 4 SP (~4 hours)

**Labels:** `frontend`, `enhancement`

**Scope:**
- Tạo format functions: `formatDateDisplay()`, `formatYearMonthDisplay()`
- Cập nhật 4 Vue files: `detail.vue`, `index.vue`, `edit.vue`, `create.vue`
- Áp dụng format cho 3 date fields: `inspection_expiration_date`, `first_registration`, `vehicle_delivery_date`
- Frontend unit tests

**Dependencies:** Backend issue #492 (cho integration testing)

**Key Files:**
- `resources/js/pages/VehicleMaster/detail.vue`
- `resources/js/pages/VehicleMaster/index.vue`
- `resources/js/pages/VehicleMaster/edit.vue`
- `resources/js/pages/VehicleMaster/create.vue`

---

## Breakdown Strategy

**Approach:** Default Strategy (1 FE + 1 BE)

**Rationale:**
- ✅ Clear ownership per layer (1 BE developer + 1 FE developer)
- ✅ Reduced coordination overhead (2 issues thay vì nhiều issues nhỏ)
- ✅ Simpler dependency management
- ✅ Natural parallel development (BE và FE có thể làm đồng thời)
- ✅ Comprehensive testing within each layer

**Total SP:** 7 SP (~7 hours)

---

## Implementation Order

### Phase 1: Backend Development (3 SP)
**Issue #492** có thể bắt đầu ngay lập tức vì không có dependencies.

**Tasks:**
1. Thêm `$casts` configuration vào Vehicle Model (0.5h)
2. Implement accessor cho `first_registration` (0.5h)
3. Cập nhật VehicleController::show() (1h)
4. Verify Repository queries (0.5h)
5. Backend testing (0.5h)

### Phase 2: Frontend Development (4 SP)
**Issue #493** có thể bắt đầu song song với Backend, nhưng integration testing cần đợi Backend hoàn thành.

**Tasks:**
1. Tạo format utility methods (0.5h)
2. Cập nhật detail.vue (1h)
3. Cập nhật index.vue (1h)
4. Verify và cập nhật create.vue, edit.vue (0.5h)
5. Frontend testing (1h)

### Phase 3: Integration Testing
Sau khi cả BE và FE hoàn thành, thực hiện integration testing:
- API response format verification
- Frontend display consistency
- Filter/Sort functionality
- Export/Import functionality

---

## Story Points Registration

### Manual Registration Steps:

**Option 1: Using GitHub Web UI**
1. Mở GitHub Projects board
2. Tìm issue #492 và #493
3. Click vào issue
4. Tìm custom field "Story Points" hoặc "SP"
5. Nhập giá trị:
   - Issue #492: `3`
   - Issue #493: `4`

**Option 2: Using GitHub CLI (if script exists)**

Nếu có script `setsp.ps1` hoặc `setsp.ps`, chạy:

```powershell
# Windows PowerShell
pwsh docs/AI_driven_dedelopment/cursor/script/setsp.ps1 -IssueUrl "https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/492" -SpValue 3
pwsh docs/AI_driven_dedelopment/cursor/script/setsp.ps1 -IssueUrl "https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/493" -SpValue 4
```

```bash
# macOS/Linux
bash docs/AI_driven_dedelopment/cursor/script/setsp.ps https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/492 3
bash docs/AI_driven_dedelopment/cursor/script/setsp.ps https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/493 4
```

**Option 3: Using GitHub CLI directly**

```bash
# Cần tìm project ID và item ID trước
gh project item-edit --project-id PVT_kwDOCjwUv84BKFsm --field "Story Points" --value 3 <item_id_492>
gh project item-edit --project-id PVT_kwDOCjwUv84BKFsm --field "Story Points" --value 4 <item_id_493>
```

---

## SP Calculation Details

### Backend Issue #492 (3 SP)

**Factors:**
- **Code Volume:** Small-Medium (3 files, ~50 lines total)
- **Complexity:** Simple (straightforward casts và format logic)
- **Testing:** Standard PHPUnit tests
- **Architecture Impact:** Low (no DB schema changes, no breaking changes)
- **Integration Dependencies:** None
- **Uncertainty:** Low (well-defined requirements)

**Breakdown:**
- Model casts và accessor: 0.5h
- Controller format logic: 1h
- Repository verification: 0.5h
- Backend testing: 1h

**Total:** 3 hours = **3 SP**

---

### Frontend Issue #493 (4 SP)

**Factors:**
- **Code Volume:** Medium (4 files, ~100 lines total)
- **Complexity:** Simple-Medium (format functions + template updates)
- **Testing:** Standard Jest/Vue Test Utils
- **Architecture Impact:** Low (no breaking changes)
- **Integration Dependencies:** Depends on BE for integration testing
- **Uncertainty:** Low (clear requirements)

**Breakdown:**
- Utility methods: 0.5h
- detail.vue updates: 1h
- index.vue updates: 1h
- create.vue và edit.vue verify: 0.5h
- Frontend testing: 1h

**Total:** 4 hours = **4 SP**

---

## Next Steps

1. ✅ **Breakdown Complete:** 2 issues created (#492, #493)
2. ⏳ **Register SP:** Manually register SP values to GitHub Projects
3. 🚀 **Start Development:**
   - Backend team: `/dev 492`
   - Frontend team: `/dev 493` (có thể bắt đầu song song)
4. 🧪 **Testing:** Individual testing → Integration testing
5. 📝 **PR:** `/pr 489` (sau khi cả 2 issues hoàn thành)

---

## Related Files

- **Parent Issue:** #489
- **Issue Document:** `docs/issues/489/issue.md`
- **Plan Document:** `docs/issues/489/plan.md`
- **Breakdown Document:** `docs/issues/489/breakdown.md` (this file)
- **Backend Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/492
- **Frontend Issue:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/493

---

## Summary

✅ **Breakdown Strategy:** 1 FE + 1 BE (Default)  
✅ **Total Issues Created:** 2  
✅ **Total Story Points:** 7 SP (~7 hours)  
✅ **Parallel Development:** Possible (BE và FE độc lập)  
✅ **Dependencies:** Clear (FE depends on BE for integration testing only)  
✅ **Risk Level:** Low (no DB changes, no breaking changes)  

**Status:** Ready for development 🚀

