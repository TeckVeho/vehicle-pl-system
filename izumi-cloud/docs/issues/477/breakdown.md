# Issue #477: Breakdown - Frontend Implementation

## Parent Issue
- **Original Issue**: #477 - Change the reference destination of the item 記録年月日
- **Issue URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/477

---

## Breakdown Strategy

**Approach**: Single FE Issue (FE Only scope)
- No backend changes required
- All frontend changes grouped into one comprehensive issue

---

## Created Issues

### Issue #488: [FE] 記録年月日の参照先変更：Grantdateフィールドへの切り替え / Thay đổi nguồn tham chiếu 記録年月日: Chuyển sang trường Grantdate

**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/488

**Type**: Frontend Enhancement

**Story Points**: 1 SP (1 hour)

**Tasks Included**:
1. Update template display field reference (Line 628 in detail.vue)
   - Change from `ElectCertPublishDateE` to `GrantdateE`
   
2. Update data model field declaration (Line 1438 in detail.vue)
   - Change from `ElectCertPublishDateE: ''` to `GrantdateE: ''`
   
3. Update formatted date string composition (Line 1765 in detail.vue)
   - Change property name and all field references from `ElectCertPublishDate*` to `Grantdate*` (E/Y/M/D)

**Files to Edit**:
- `resources/js/pages/VehicleMaster/detail.vue` (3 locations)

**Dependencies**: None (independent task)

**Labels**: `frontend`, `enhancement`

---

## Story Points Calculation

**Task Analysis**:
- **Code Volume**: Small (3 simple field reference changes in 1 file)
- **Complexity**: Simple (straightforward find-and-replace of field names)
- **Testing**: Minimal (visual verification of date display)
- **Architecture Impact**: None (no architectural changes)
- **Integration Dependencies**: None (frontend display only)
- **Uncertainty**: Low (clear requirements, well-defined scope)

**Estimated Time Breakdown**:
- Code changes: 15-20 minutes
- Testing and verification: 20-30 minutes
- Buffer: 10-15 minutes

**Total**: 1 SP = 1 hour

---

## Implementation Order

Since this is a single FE issue with no dependencies, the implementation order is straightforward:

1. **Phase 1: Code Changes** (15-20 min)
   - Edit `resources/js/pages/VehicleMaster/detail.vue` at 3 locations
   - Update template display, data model, and formatted date composition

2. **Phase 2: Testing** (20-30 min)
   - Visual verification of 記録年月日 field display
   - Check date format rendering (Era Year Month Day)
   - Test with actual vehicle data containing Grantdate values
   - Verify no regression on other date fields

3. **Phase 3: Final Review** (10-15 min)
   - Code review
   - Edge case testing (missing/null/invalid Grantdate values)
   - Final verification

---

## Acceptance Criteria

✅ **Definition of Done**:
- [ ] All 3 code changes completed in detail.vue
- [ ] 記録年月日 field displays correctly with Grantdate values
- [ ] Date format (Era Year Month Day) renders properly
- [ ] No visual regression on shakensho display page
- [ ] Other date fields (RegGrantDate, FirstRegDate, ValidPeriodExpDate) unaffected
- [ ] Code follows project conventions
- [ ] No breaking changes to existing functionality

---

## Technical Notes

### Data Integrity
- Backend must provide `GrantdateE`, `GrantdateY`, `GrantdateM`, `GrantdateD` fields in API response
- Verify `handleRenderData()` method can access Grantdate fields correctly
- Confirm shakensho JSON file contains Grantdate values

### Compatibility
- Change only affects 記録年月日 field display
- No impact on other vehicle inspection certificate fields
- Language labels already exist in `resources/js/lang/subs/ja.js`

### Testing Recommendations
- Test with vehicles having linked shakensho JSON files
- Check edge cases: missing Grantdate values, null values, invalid date formats
- Verify both old and new format shakensho files (if applicable)

---

## Manual SP Registration

**⚠️ Note**: The automated setsp script was not found in the project. Please register SP manually using one of these methods:

### Method 1: GitHub Projects Web UI
1. Navigate to the GitHub Projects board
2. Find issue #488
3. Click on the issue card
4. Set "Story Points" field to **1**

### Method 2: GitHub CLI (if project is configured)
```bash
# Get project ID and item ID first
gh project list --owner TeckVeho

# Then set the SP value (replace PROJECT_ID and ITEM_ID)
gh project item-edit --field "Story Points" --project-id PROJECT_ID --id ITEM_ID --value 1
```

### Method 3: GitHub GraphQL API
Use GitHub's GraphQL API to set custom field values programmatically.

---

## Summary

**Total Issues Created**: 1
- Frontend: 1 issue (#488)
- Backend: 0 issues

**Total Story Points**: 1 SP (~1 hour)

**Development Approach**: Single developer can complete this FE-only task independently

**Risk Level**: Low (simple field reference changes, well-defined scope)

---

## Next Steps

1. ✅ Issue #488 created and labeled
2. ⚠️ Manually register SP = 1 in GitHub Projects
3. ➡️ Assign developer to issue #488
4. ➡️ Run `/dev 488` to start implementation
5. ➡️ Run `/test 488` after implementation
6. ➡️ Run `/pr` to create pull request
