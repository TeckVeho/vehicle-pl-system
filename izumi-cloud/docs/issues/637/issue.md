# Issue #637: Fix sorting not working correctly (vehicle master)

## Metadata

- **Issue Number**: 637
- **Title**: Fix sorting not working correctly (vehicle master)
- **Status**: OPEN
- **Labels**: backend, bug
- **Assignees**: phuongcodeunited
- **Created At**: 2026-01-12T09:48:51Z
- **Updated At**: 2026-01-13T07:11:03Z
- **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/637

## Issue Description

Chức năng sort hoạt động có vấn đề. Một số cột sort sai, ví dụ như cột trong ảnh.

## Investigation Summary

### Backend Issues

#### 1. **Critical Logic Error in VehicleRepository.php**

**Location**: 
- `app/Repositories/VehicleRepository.php` line 126
- `app/Repositories/VehicleRepository.php` line 561

**Problem**:
```php
if ($filter['number_plate'] || isset($sort['sort_by']) == 'number_plate') {
```

**Issue**: 
- `isset($sort['sort_by'])` returns a boolean (true/false), not a string
- Comparing boolean with string `'number_plate'` will always be false
- **Additional Issue**: Frontend gửi `sort_by: 'no_number_plate'` (field key từ backend), nhưng backend check `== 'number_plate'`
- This means when sorting by `no_number_plate`, the required JOIN with `vehicle_no_number_plate_history` table is never executed
- Result: Sorting by `no_number_plate` fails or sorts incorrectly

**Correct Logic Should Be**:
```php
if ($filter['number_plate'] || (isset($sort['sort_by']) && $sort['sort_by'] == 'no_number_plate')) {
```

#### 2. **Missing Special Handling for `number_plate` Sorting**

**Location**: `app/Repositories/VehicleRepository.php` lines 142-151

**Problem**:
- When sorting by `number_plate`, the code needs to:
  1. JOIN `vehicle_no_number_plate_history` table (currently broken due to issue #1)
  2. Order by the correct field from the joined table
- Currently, even if the JOIN happens, the `orderBy` uses `$sort['sort_by']` directly, which may not exist in the main `vehicles` table

**Current Code**:
```php
if (isset($sort['sort_by']) && isset($sort['sort_type'])) {
    if($sort['sort_by'] == 'leasing_period') {
        $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
        $this->model = $this->model->orderBy('maintenance_leases.leasing_period', $sort['sort_type']);
    } else if($sort['sort_by'] == 'department_name') {
        $this->model = $this->model->orderBy('departments.position', $sort['sort_type']);
    } else {
        $this->model = $this->model->orderBy($sort['sort_by'], $sort['sort_type']);
    }
}
```

**Issue**: 
- No special case for `number_plate` sorting. When `sort_by == 'number_plate'`, it tries to order by `vehicles.number_plate` which doesn't exist.
- **Additional Complexity**: The `vehicle_no_number_plate_history` table stores historical records. Each vehicle can have multiple records with different `no_number_plate` values over time. When sorting, we need to:
  - Get the **latest** `no_number_plate` for each vehicle (ordered by `date DESC`)
  - Sort vehicles by this latest `no_number_plate` value
  - This requires either:
    - A subquery to get the latest record per vehicle, OR
    - A JOIN with proper GROUP BY and MAX(date) handling

**Database Structure**:
- Table: `vehicle_no_number_plate_history`
- Fields: `id`, `vehicle_id`, `date`, `no_number_plate`
- Frontend displays: `plate_history[0].no_number_plate` (latest record, already ordered by date DESC in relationship)

#### 3. **Inconsistent Field Names**

**Location**: Multiple files

**Problem**:
- Frontend uses `number_plate` as the field key
- Backend database uses `no_number_plate` in `vehicle_no_number_plate_history` table
- Frontend displays data from `plate_history[0].no_number_plate`
- This mismatch can cause confusion in sorting logic

### Frontend Issues

#### 1. **Field Key Mismatch trong Template**

**Location**: `resources/js/pages/VehicleMaster/index.vue` line 316

**Problem**:
- Backend gửi field key là `'no_number_plate'` (từ constant VEHICLE)
- Frontend template check `field.key === 'number_plate'` (line 316)
- **Kết quả**: Template này KHÔNG BAO GIỜ được render vì field key không match!
- Sortable list có `'no_number_plate'` (line 1650) - ✅ ĐÚNG
- Khi user click sort, Frontend gửi `sort_by: 'no_number_plate'` - ✅ ĐÚNG
- Nhưng Backend check `== 'number_plate'` - ❌ SAI

**Current Code**:
```vue
<template v-else-if="field.key === 'number_plate'">  <!-- ❌ SAI -->
    {{ getDepartmentName(item.plate_history[0] ? item.plate_history[0].no_number_plate : '') }}
</template>
```

**Should Be**:
```vue
<template v-else-if="field.key === 'no_number_plate'">  <!-- ✅ ĐÚNG -->
    {{ getDepartmentName(item.plate_history[0] ? item.plate_history[0].no_number_plate : '') }}
</template>
```

**Current Code**:
```javascript
isFieldSortable(fieldKey) {
    const sortableFields = [
        'department_name',
        'vehicle_identification_number',
        'scrap_date',
        'no_number_plate',  // <-- This is in the list
        // ...
    ];
    return sortableFields.includes(fieldKey);
}
```

But the field key in the table is `'number_plate'`, not `'no_number_plate'`.

#### 2. **Field Key Mismatch in Sortable List**

**Location**: `resources/js/pages/VehicleMaster/index.vue` line 1645-1669

**Problem**:
- The sortable field list includes `'no_number_plate'` (line 1650)
- But the actual field key used in the table is `'number_plate'` (line 316)
- This means `'no_number_plate'` will never be sortable because the field key doesn't match
- When user clicks to sort, it sends `sort_by: 'number_plate'` to backend (if field key is `'number_plate'`)
- But `isFieldSortable('number_plate')` returns `false` because only `'no_number_plate'` is in the list

**Current Sortable Fields List**:
```javascript
const sortableFields = [
    'department_name',           // ✓ Has special handling in backend
    'vehicle_identification_number', // ✓ Direct column in vehicles table
    'scrap_date',                // ✓ Direct column in vehicles table
    'no_number_plate',           // ✗ Field key mismatch (should be 'number_plate')
    'inspection_expiration_date', // ✓ Direct column in vehicles table
    'first_registration',        // ✓ Direct column in vehicles table
    'voluntary_premium',         // ✓ Direct column in vehicles table
    'gate',                      // ✓ Direct column in vehicles table
    'humidifier',                // ✓ Direct column in vehicles table
    'displacement',              // ✓ Direct column in vehicles table
    'length',                    // ✓ Direct column in vehicles table
    'width',                     // ✓ Direct column in vehicles table
    'height',                    // ✓ Direct column in vehicles table
    'maximum_loading_capacity',  // ✓ Direct column in vehicles table
    'vehicle_total_weight',      // ✓ Direct column in vehicles table
    'd1d_not_installed',         // ✓ Direct column in vehicles table
    'optional_detail',           // ✓ Direct column in vehicles table
    'monthly_mileage',           // ✓ Direct column in vehicles table
    'maintenance_lease_fee',     // ✓ Direct column in vehicles table
    'door_number',               // ✓ Direct column in vehicles table
];
```

**Note**: Most fields are direct columns in the `vehicles` table and should work with simple `orderBy()`. Only `department_name`, `leasing_period`, and `number_plate` need special handling.

## Files to Investigate/Modify

### Backend Files

1. **app/Repositories/VehicleRepository.php**
   - Line 126: Fix logic error for `number_plate` filter/sort check
   - Line 142-151: Add special handling for `number_plate` sorting
   - Line 561: Fix same logic error in `getAllVehicle()` method
   - Line 577-585: Add special handling for `number_plate` sorting in `getAllVehicle()`

2. **app/Http/Controllers/Api/VehicleController.php**
   - Verify that sort parameters are passed correctly to repository

### Frontend Files

1. **resources/js/pages/VehicleMaster/index.vue**
   - Line 1645-1669: Review `isFieldSortable()` method
   - Line 316-317: Verify field key consistency (`number_plate` vs `no_number_plate`)
   - Line 1401-1413: Verify `handleSort()` method sends correct field names

## Implementation Checklist

### Backend Fixes

- [ ] Fix logic error at line 126 in `VehicleRepository.php::paginate()`
  - Change: `isset($sort['sort_by']) == 'number_plate'` → `isset($sort['sort_by']) && $sort['sort_by'] == 'no_number_plate'`
  
- [ ] Fix logic error at line 561 in `VehicleRepository.php::getAllVehicle()`
  - Change: `isset($sort_by) == 'number_plate'` → `isset($sort_by) && $sort_by == 'no_number_plate'`

- [ ] Add special handling for `no_number_plate` sorting in `paginate()` method
  - When `sort_by == 'number_plate'`, ensure JOIN is done and order by correct field
  - **Solution Options**:
    1. Use subquery to get latest `no_number_plate` per vehicle, then JOIN and order by it
    2. Use JOIN with `MAX(date)` and GROUP BY to get latest record
    3. Use window function (if MySQL 8.0+) to get latest record per vehicle
  - Need to order by `vehicle_no_number_plate_history.no_number_plate` from the latest record

- [ ] Add special handling for `no_number_plate` sorting in `getAllVehicle()` method
  - Same as above
  - Note: This method already has `orderBy('departments.position', 'ASC')` as default, need to maintain this when adding no_number_plate sorting

- [ ] Test sorting for all sortable fields to identify other issues
  - `department_name` ✓ (already handled)
  - `leasing_period` ✓ (already handled)
  - `no_number_plate` ✗ (needs fix)
  - Other fields: verify each one

### Frontend Fixes

- [ ] **CRITICAL**: Fix field key mismatch in template (line 316)
  - Change `field.key === 'number_plate'` → `field.key === 'no_number_plate'`
  - This is critical because template không bao giờ render với field key hiện tại

- [ ] Test sorting UI for all sortable columns
  - Verify sort icons appear correctly
  - Verify sort direction toggles correctly
  - Verify API calls send correct parameters

## Testing Plan

1. **Test `number_plate` sorting**
   - Sort ascending: verify vehicles are sorted by number plate correctly
   - Sort descending: verify reverse order
   - Verify JOIN is executed (check SQL logs)

2. **Test other sortable fields**
   - Test each field in `isFieldSortable()` list
   - Verify results are sorted correctly
   - Check for any SQL errors

3. **Test edge cases**
   - Sort when filter is applied
   - Sort when no data
   - Sort when multiple filters are active

4. **Test with different user roles**
   - Verify sorting works for all roles (crew, clerks, tl, etc.)

## Notes / Review Section

### Key Findings

1. **Critical Bug**: Logic error prevents `no_number_plate` sorting from working at all
2. **Critical Bug**: Template mismatch - Frontend template check `'number_plate'` nhưng backend gửi `'no_number_plate'`
3. **Missing Implementation**: No special handling for `no_number_plate` in orderBy clause
4. **Field Name Inconsistency**: 
   - Backend constant: `'no_number_plate'` ✅
   - Frontend sortable list: `'no_number_plate'` ✅
   - Frontend template: `'number_plate'` ❌
   - Backend sort check: `'number_plate'` ❌

### Questions to Resolve

1. **When sorting by `no_number_plate`, should we sort by:**
   - ✅ **Answer**: The latest `no_number_plate` from `vehicle_no_number_plate_history` (confirmed by frontend using `plate_history[0]`)
   - This matches how the frontend displays the data

2. **Are there other fields that need special sorting handling that we haven't identified yet?**
   - Need to test all fields in `isFieldSortable()` list
   - Check if any other fields require JOINs or special handling
   - Currently identified: `department_name`, `leasing_period`, `no_number_plate`

3. **Field key standardization:**
   - ✅ **Answer**: Backend constant đã định nghĩa `'no_number_plate'` - đây là source of truth
   - Frontend sortable list đã đúng: `'no_number_plate'`
   - **Cần fix**: Frontend template và Backend sort check phải dùng `'no_number_plate'`

### Technical Implementation Notes

**For `no_number_plate` sorting, recommended approach:**

```php
// Option 1: Subquery approach (most reliable)
if($sort['sort_by'] == 'no_number_plate') {
    $subquery = DB::table('vehicle_no_number_plate_history')
        ->select('vehicle_id', DB::raw('MAX(date) as max_date'))
        ->groupBy('vehicle_id');
    
    $this->model = $this->model
        ->join('vehicle_no_number_plate_history', function($join) use ($subquery) {
            $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'vehicles.id')
                 ->joinSub($subquery, 'latest_plate', function($join) {
                     $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'latest_plate.vehicle_id')
                          ->on('vehicle_no_number_plate_history.date', '=', 'latest_plate.max_date');
                 });
        })
        ->orderBy('vehicle_no_number_plate_history.no_number_plate', $sort['sort_type']);
}

// Option 2: Simpler approach if we can ensure only latest record is joined
// (Requires fixing the JOIN condition to only get latest record)
```

### Related Issues

- Check if similar sorting issues exist in other master pages (EmployeeMaster, RouteMaster, etc.)
