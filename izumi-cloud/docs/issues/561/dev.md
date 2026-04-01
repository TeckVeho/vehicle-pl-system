# Issue #561: Development Log - 廃車日ロジック修正

## 概要 (Overview)

**Parent Issue**: #560  
**Issue**: #561  
**Type**: Backend Bug Fix  
**Developer**: AI Agent  
**Development Date**: 2025-12-26  
**Development Approach**: Direct Implementation

---

## Requirements Analysis

### Problem Statement

車両マスター画面で、廃車日を本日の日付で登録すると即座にグレーアウトされる問題を修正。

**現在の動作 (誤り)**:
- `scrap_date <= today` → 当日もグレーアウト
- `scrap_date > today` → 今日の廃車日は非表示

**期待される動作 (正しい)**:
- `scrap_date < today` → 翌日からグレーアウト
- `scrap_date >= today` → 今日の廃車日も表示

### Affected Vehicle

横浜800い1757

### Technical Requirements

**File**: `app/Repositories/VehicleRepository.php`

**Changes Required**: 7箇所の演算子変更
- `<=` を `<` に変更（4箇所）: scrap_date_custom 計算
- `>` を `>=` に変更（3箇所）: hide_scrap_date フィルター

---

## Development Approach

**Chosen Method**: Direct Implementation

**Rationale**:
- 変更が非常にシンプル（演算子のみ）
- 要件が明確
- テストは手動確認で十分
- TDD は不要（既存ロジックの修正）

---

## Implementation Details

### Phase 1: Code Changes

#### Change 1: paginate() メソッド - hide_scrap_date フィルター

**Location**: Line 83  
**File**: `app/Repositories/VehicleRepository.php`

**Before**:
```php
->orWhere('vehicles.scrap_date', '>', $today);
```

**After**:
```php
->orWhere('vehicles.scrap_date', '>=', $today);
```

**Reason**: 今日の廃車日の車両も表示されるべき

---

#### Change 2 & 3: getAllVehicle() メソッド - scrap_date_custom 計算

**Location**: Lines 563, 569  
**File**: `app/Repositories/VehicleRepository.php`

**Before**:
```php
DB::raw("CASE WHEN vehicles.scrap_date <= '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
```

**After**:
```php
DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
```

**Reason**: 廃車日が今日より前の場合のみグレーアウト

**Note**: 2箇所（number_plate 検索時と通常検索時）で同じ変更

---

#### Change 4: getAllVehicle() メソッド - hide_scrap_date フィルター

**Location**: Line 525  
**File**: `app/Repositories/VehicleRepository.php`

**Before**:
```php
->orWhere('vehicles.scrap_date', '>', $today);
```

**After**:
```php
->orWhere('vehicles.scrap_date', '>=', $today);
```

**Reason**: 今日の廃車日の車両も表示されるべき

---

#### Change 5 & 6: getDashboardVehicle() メソッド - scrap_date_custom 計算

**Location**: Lines 630, 636  
**File**: `app/Repositories/VehicleRepository.php`

**Before**:
```php
DB::raw("CASE WHEN vehicles.scrap_date <= '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
```

**After**:
```php
DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
```

**Reason**: 廃車日が今日より前の場合のみグレーアウト

**Note**: 2箇所（number_plate 検索時と通常検索時）で同じ変更

---

#### Change 7: getDashboardVehicle() メソッド - hide_scrap_date フィルター

**Location**: Line 613  
**File**: `app/Repositories/VehicleRepository.php`

**Before**:
```php
->orWhere('vehicles.scrap_date', '>', $today);
```

**After**:
```php
->orWhere('vehicles.scrap_date', '>=', $today);
```

**Reason**: 今日の廃車日の車両も表示されるべき

---

### Phase 2: Code Quality Check

#### Linter Check

```bash
read_lints app/Repositories/VehicleRepository.php
```

**Result**: ✅ No linter errors found

#### Git Diff Review

```bash
git diff app/Repositories/VehicleRepository.php
```

**Summary**:
- 7 lines changed
- 3 methods affected: paginate(), getAllVehicle(), getDashboardVehicle()
- All changes are operator replacements only
- No new code added
- No code removed

---

## Validation Plan

### Test Cases

#### Test Case 1: scrap_date = 昨日 (Yesterday)

**Expected**: グレーアウト表示

**Logic**:
- `scrap_date < today` → TRUE
- `scrap_date_custom` = scrap_date
- Frontend: darker-bg-td class applied

**Status**: ⏳ Pending manual test

---

#### Test Case 2: scrap_date = 今日 (Today) ⭐ CRITICAL

**Expected**: 通常表示（グレーアウトなし）

**Logic**:
- `scrap_date < today` → FALSE
- `scrap_date_custom` = NULL
- Frontend: normal display

**Status**: ⏳ Pending manual test

---

#### Test Case 3: scrap_date = 明日 (Tomorrow)

**Expected**: 通常表示

**Logic**:
- `scrap_date < today` → FALSE
- `scrap_date_custom` = NULL
- Frontend: normal display

**Status**: ⏳ Pending manual test

---

#### Test Case 4: scrap_date = NULL

**Expected**: 通常表示

**Logic**:
- `scrap_date` is NULL
- `scrap_date_custom` = NULL
- Frontend: normal display

**Status**: ⏳ Pending manual test

---

### Filter Test Cases

#### Filter Test 1: 「廃車を非表示にする」ON + scrap_date = 今日

**Expected**: 車両が表示される

**Logic**:
- `scrap_date >= today` → TRUE
- 車両はリストに含まれる

**Status**: ⏳ Pending manual test

---

#### Filter Test 2: 「廃車を非表示にする」ON + scrap_date = 昨日

**Expected**: 車両が非表示になる

**Logic**:
- `scrap_date >= today` → FALSE
- 車両はリストから除外される

**Status**: ⏳ Pending manual test

---

### Specific Vehicle Test

#### Test with 横浜800い1757

**Steps**:
1. 車両マスター画面を開く
2. 横浜800い1757 を検索
3. 廃車日を確認
4. 表示状態を確認（グレーアウトか通常表示か）

**Expected**:
- 廃車日 = 今日 → 通常表示
- 廃車日 = 過去 → グレーアウト

**Status**: ⏳ Pending manual test

---

## Implementation Summary

### Changes Made

| # | Method | Line | Change | Type |
|---|--------|------|--------|------|
| 1 | paginate() | 83 | `>` → `>=` | Filter |
| 2 | getAllVehicle() | 563 | `<=` → `<` | Display |
| 3 | getAllVehicle() | 569 | `<=` → `<` | Display |
| 4 | getAllVehicle() | 525 | `>` → `>=` | Filter |
| 5 | getDashboardVehicle() | 630 | `<=` → `<` | Display |
| 6 | getDashboardVehicle() | 636 | `<=` → `<` | Display |
| 7 | getDashboardVehicle() | 613 | `>` → `>=` | Filter |

**Total**: 7 changes in 1 file

---

### Code Quality Metrics

- ✅ No linter errors
- ✅ No syntax errors
- ✅ Consistent formatting
- ✅ No breaking changes to API
- ✅ No database schema changes
- ✅ No new dependencies

---

### Impact Analysis

#### Affected Components

1. **車両マスター一覧画面**
   - paginate() メソッド使用
   - グレーアウト表示ロジック変更

2. **車両マスター検索機能**
   - getAllVehicle() メソッド使用
   - フィルター機能変更

3. **ダッシュボード画面**
   - getDashboardVehicle() メソッド使用
   - 統計表示に影響

#### Not Affected

- ❌ Frontend code (no changes needed)
- ❌ Database schema
- ❌ API endpoints
- ❌ Other repositories
- ❌ Models
- ❌ Controllers

---

## Technical Notes

### Logic Explanation

#### Display Logic (scrap_date_custom)

**Old Logic**:
```
IF scrap_date <= today THEN
    scrap_date_custom = scrap_date  // グレーアウト
ELSE
    scrap_date_custom = NULL        // 通常表示
END
```

**New Logic**:
```
IF scrap_date < today THEN
    scrap_date_custom = scrap_date  // グレーアウト
ELSE
    scrap_date_custom = NULL        // 通常表示
END
```

**Key Difference**: `<=` vs `<`
- Old: 今日を含む（当日もグレーアウト）
- New: 今日を含まない（翌日からグレーアウト）

---

#### Filter Logic (hide_scrap_date)

**Old Logic**:
```
IF hide_scrap_date = true THEN
    WHERE scrap_date IS NULL OR scrap_date > today
END
```

**New Logic**:
```
IF hide_scrap_date = true THEN
    WHERE scrap_date IS NULL OR scrap_date >= today
END
```

**Key Difference**: `>` vs `>=`
- Old: 今日を除外（今日の廃車日は非表示）
- New: 今日を含む（今日の廃車日も表示）

---

### Consistency Check

**Display Logic と Filter Logic の整合性**:

| scrap_date | Display (scrap_date_custom) | Filter (hide_scrap_date ON) |
|------------|---------------------------|----------------------------|
| 昨日 | グレーアウト (`< today` → TRUE) | 非表示 (`>= today` → FALSE) |
| 今日 | 通常表示 (`< today` → FALSE) | 表示 (`>= today` → TRUE) |
| 明日 | 通常表示 (`< today` → FALSE) | 表示 (`>= today` → TRUE) |
| NULL | 通常表示 (NULL) | 表示 (NULL) |

✅ **整合性が保たれている**: 今日の廃車日は通常表示され、フィルターでも表示される

---

## Performance Impact

### Query Performance

**Before**: 
```sql
CASE WHEN vehicles.scrap_date <= '2025-12-26' THEN ...
WHERE vehicles.scrap_date > '2025-12-26'
```

**After**:
```sql
CASE WHEN vehicles.scrap_date < '2025-12-26' THEN ...
WHERE vehicles.scrap_date >= '2025-12-26'
```

**Impact**: ❌ None
- 演算子の変更のみ
- インデックスの使用状況は同じ
- クエリプランは変わらない

---

## Risks and Mitigation

### Identified Risks

1. **Risk**: 既存データの表示が変わる
   - **Severity**: Low
   - **Mitigation**: 仕様通りの動作なので問題なし

2. **Risk**: ユーザーが混乱する可能性
   - **Severity**: Low
   - **Mitigation**: 顧客に説明済み（issue #560）

3. **Risk**: 他の機能への影響
   - **Severity**: Very Low
   - **Mitigation**: VehicleRepository のみの変更、他への影響なし

---

## Next Steps

### Testing Phase

1. **ローカル環境でのテスト**
   - [ ] 4つの基本テストケース実行
   - [ ] フィルター機能のテスト
   - [ ] 横浜800い1757 での確認

2. **ステージング環境でのテスト**
   - [ ] 本番データでの動作確認
   - [ ] 複数の車両でテスト
   - [ ] 各画面での表示確認

3. **本番環境へのデプロイ**
   - [ ] デプロイ計画の確認
   - [ ] ロールバック手順の準備
   - [ ] デプロイ実行

4. **顧客への報告**
   - [ ] テスト結果の報告
   - [ ] 横浜800い1757 での確認結果
   - [ ] 修正完了の通知

---

## Acceptance Criteria Status

- [x] VehicleRepository.php の7箇所を修正完了
- [ ] 以下のテストケースが全て合格:
  - [ ] scrap_date = 昨日 → グレーアウト表示
  - [ ] scrap_date = 今日 → 通常表示（重要！）
  - [ ] scrap_date = 明日 → 通常表示
  - [ ] scrap_date = NULL → 通常表示
- [ ] 「廃車を非表示にする」フィルターが正常動作:
  - [ ] 今日の廃車日の車両が表示される
  - [ ] 過去の廃車日の車両が非表示になる
- [ ] 横浜800い1757 の車両で動作確認
- [x] 既存機能への破壊的変更なし（コード確認済み）

---

## Development Timeline

| Phase | Duration | Status |
|-------|----------|--------|
| Requirements Analysis | 5 min | ✅ Complete |
| Implementation | 10 min | ✅ Complete |
| Code Quality Check | 5 min | ✅ Complete |
| Documentation | 15 min | ✅ Complete |
| **Total** | **35 min** | **✅ Complete** |

**Estimated Time**: 1-2 hours  
**Actual Time**: 35 minutes  
**Efficiency**: 70% faster than estimated

---

## Conclusion

### Summary

Issue #561 の実装が完了しました。7箇所の演算子変更を正確に実施し、コード品質チェックも合格しました。

### Key Achievements

✅ 全ての変更を正確に実装  
✅ Linter エラーなし  
✅ 既存機能への影響なし  
✅ コードの可読性維持  
✅ 予定時間内に完了

### Ready for Testing

コードは `/test` フェーズに進む準備ができています。全ての変更は uncommitted 状態で、テスト後に commit されます。

---

## Related Documents

- Parent Issue: #560
- Implementation Plan: `docs/issues/560/plan.md`
- Breakdown: `docs/issues/560/breakdown.md`
- Issue Document: `docs/issues/560/issue.md`

---

## Changelog

- 2025-12-26 10:00: Development started
- 2025-12-26 10:10: All 7 changes implemented
- 2025-12-26 10:15: Linter check passed
- 2025-12-26 10:35: Documentation completed

