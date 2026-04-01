# Issue #477: Change the reference destination of the item 記録年月日 - Implementation Plan

## 概要 (Overview)

**Current State:**
- Field 記録年月日 (Kiroku Nengappi - Record Date) in VehicleMaster detail page is currently displaying data from `ElectCertPublishdate*` fields (ElectCertPublishdateE/Y/M/D) from shakensho JSON file.

**Target State:**
- Change the field 記録年月日 to display data from `Grantdate*` fields (GrantdateE/Y/M/D) from shakensho JSON file instead.

**Impact:**
- Frontend display only - updating data reference for a single field in vehicle inspection certificate view
- No backend changes required (FE Only)
- Estimated time: ~1 hour

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/VehicleMaster/detail.vue`

This file contains the Vehicle Master detail page that displays the shakensho (vehicle inspection certificate) information including the 記録年月日 field.

##### 1.1.1. Update display field reference in template (Line 628)

**既存コード** (line 628):

```vue
<span>{{ handleRenderData('ElectCertPublishDateE') }}</span>
```

**変更内容:**
- Change the field reference from `ElectCertPublishDateE` to `GrantdateE` to display the Grantdate Era value instead of ElectCertPublishdate Era
- This is the main visible change that will show the correct 記録年月日 value on the page

**変更後:**

```vue
<span>{{ handleRenderData('GrantdateE') }}</span>
```

##### 1.1.2. Update data model field declaration (Line 1438)

**既存コード** (line 1438):

```javascript
ElectCertPublishDateE: '',
```

**変更内容:**
- Change the data field name from `ElectCertPublishDateE` to `GrantdateE` to match the new field reference
- This ensures the data model is aligned with the display logic

**変更後:**

```javascript
GrantdateE: '',
```

##### 1.1.3. Update formatted date string composition (Line 1765)

**既存コード** (line 1765):

```javascript
this.formData.vehicle_inspection_cert[i].ElectCertPublishDateE = `${this.handleRenderData('ElectCertPublishDateE')} ${this.handleRenderData('ElectCertPublishDateY')}年 ${this.handleRenderData('ElectCertPublishDateM')}月  ${this.handleRenderData('ElectCertPublishDateD')}日`;
```

**変更内容:**
- Change the property name from `ElectCertPublishDateE` to `GrantdateE`
- Update all field references in the template string from `ElectCertPublishDate*` to `Grantdate*` (E/Y/M/D)
- This creates the full formatted date string (e.g., "令和 5年 12月 8日") using the Grantdate fields instead

**変更後:**

```javascript
this.formData.vehicle_inspection_cert[i].GrantdateE = `${this.handleRenderData('GrantdateE')} ${this.handleRenderData('GrantdateY')}年 ${this.handleRenderData('GrantdateM')}月  ${this.handleRenderData('GrantdateD')}日`;
```

**Note:** The language labels in `resources/js/lang/subs/ja.js` (lines 659-662) already exist for Grantdate fields, so no changes are needed there:
```javascript
GRANTDATE_E: '付与年月日',
GRANTDATE_Y: '付与年月日',
GRANTDATE_M: '付与年月日',
GRANTDATE_D: '付与年月日',
```

---

## 実装順序 (Implementation Order)

1. **Frontend 実装** (独立タスク - no dependencies)
   - Task 1.1.1: Update template display field (line 628)
   - Task 1.1.2: Update data model declaration (line 1438)
   - Task 1.1.3: Update formatted date composition (line 1765)

2. **動作確認テスト**
   - Verify the 記録年月日 field displays correctly with Grantdate values
   - Check date format (Era Year Month Day) is rendered properly
   - Confirm no visual regression on the shakensho display page
   - Test with actual vehicle data that has Grantdate values in JSON

---

## 見積もり工数 (Estimated Effort)

- **Frontend**: 0.5-1 時間
  - Code changes in detail.vue: ~15-20 minutes
  - Testing and verification: ~20-30 minutes
  - Bug fixes (if any): ~10-15 minutes buffer

**合計**: ~1 時間

---

## 技術的な注意事項 (Technical Notes)

1. **データ整合性:**
   - Ensure backend is already providing `GrantdateE`, `GrantdateY`, `GrantdateM`, `GrantdateD` fields in the API response
   - Verify the `handleRenderData()` method can access these Grantdate fields correctly
   - Confirm the shakensho JSON file contains Grantdate values

2. **既存機能との互換性:**
   - This change only affects the 記録年月日 field display
   - Other date fields (RegGrantDate, FirstRegDate, ValidPeriodExpDate) remain unchanged
   - No impact on other vehicle inspection certificate fields

3. **UX 考慮:**
   - The visual appearance and format of the date should remain the same
   - Only the data source changes from ElectCertPublishdate to Grantdate
   - Users should see the correct 記録年月日 value as per the official shakensho document

4. **テスト推奨:**
   - Test with vehicles that have shakensho JSON files linked
   - Verify both old and new format shakensho files (if applicable)
   - Check edge cases: missing Grantdate values, null values, invalid date formats

---

## Backend Note (No changes required for FE Only scope)

Note: `app/Repositories/UploadDataRepository.php` (lines 586-589) currently maps ElectCertPublishdate fields from JSON. If backend mapping needs to be updated to provide Grantdate fields, that would be a separate backend task outside this FE-only scope.
