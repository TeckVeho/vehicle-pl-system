# Issue #489: 車検証連携時の表示について - Implementation Plan

## 概要 (Overview)

**Vấn đề hiện tại:**
Trong màn hình 車両マスタ (Vehicle Master), hiển thị tháng của 車検満了日 (Ngày hết hạn kiểm định xe) không đồng nhất. Một số nơi hiển thị "08" (2 chữ số), nơi khác hiển thị "8" (1 chữ số).

**Nguyên nhân gốc rễ:**
Sau khi phân tích codebase, phát hiện:
1. **Frontend:** Có 2 phương thức format khác nhau:
   - `handleFormatDate()` - format Y-m (có pad 0 cho tháng < 10)
   - `formatMonth()` - format tháng riêng (có pad 0)
   - `formatMonthDay()` - format tháng/ngày riêng (có pad 0)
   - Tuy nhiên, khi hiển thị `first_registration` (初度登録年月) sử dụng `handleFormatDate()` đúng cách
   - Nhưng `inspection_expiration_date` được hiển thị trực tiếp từ database mà không qua format function

2. **Backend:** 
   - Database lưu trữ `inspection_expiration_date` dưới dạng DATE
   - VehicleRepository sử dụng `DATE_FORMAT()` trong SQL queries nhưng không đảm bảo format output nhất quán
   - API trả về dữ liệu raw từ database mà không format

**Giải pháp:**
Cần đảm bảo tất cả các nơi hiển thị ngày tháng đều sử dụng format nhất quán với tháng 2 chữ số.

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: resources/js/pages/VehicleMaster/detail.vue

##### 1.1.1. Sửa hiển thị inspection_expiration_date trong tab 基本情報

**Mô tả:**
Hiện tại `inspection_expiration_date` được hiển thị trực tiếp từ `formData.inspection_expiration_date` mà không qua format function. Cần format để đảm bảo tháng luôn là 2 chữ số.

**既存コード** (line 157-160):

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.INSPECTION_EXPIRATION_DATE'" />
    <vInput v-model="formData.inspection_expiration_date" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
</b-col>
```

**変更内容:**

1. Thay đổi từ `v-model` sang `:value` với computed property hoặc format function
2. Tạo computed property `formattedInspectionExpirationDate` để format ngày tháng
3. Hoặc sử dụng method `formatDateToJapanese()` đã có sẵn (line 1590-1601) để format

**Phương án đề xuất:**

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.INSPECTION_EXPIRATION_DATE'" />
    <vInput :value="formatDateDisplay(formData.inspection_expiration_date)" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
</b-col>
```

##### 1.1.2. Thêm method formatDateDisplay() để format ngày tháng nhất quán

**Mô tả:**
Tạo method mới để format ngày tháng với format YYYY-MM-DD (đảm bảo tháng và ngày đều 2 chữ số).

**変更内容:**

Thêm method vào section `methods` (sau line 1601):

```javascript
formatDateDisplay(dateString) {
    if (!dateString) {
        return '';
    }
    
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
},
```

##### 1.1.3. Sửa hiển thị first_registration để nhất quán

**Mô tả:**
Đảm bảo `first_registration` cũng sử dụng format nhất quán.

**既存コード** (line 152-155):

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FIRST_REGISTRATION'" />
    <vInput v-model="formData.first_registration" :type="'text'" placeholder="" disabled />
</b-col>
```

**変更内容:**

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.FIRST_REGISTRATION'" />
    <vInput :value="formatYearMonthDisplay(formData.first_registration)" :type="'text'" placeholder="" disabled />
</b-col>
```

Thêm method:

```javascript
formatYearMonthDisplay(dateString) {
    if (!dateString) {
        return '';
    }
    
    const date = new Date(dateString + '-01');
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    
    return `${year}-${month}`;
},
```

##### 1.1.4. Sửa hiển thị vehicle_delivery_date

**既存コード** (line 162-165):

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'車両納入日'" />
    <vInput v-model="formData.vehicle_delivery_date" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
</b-col>
```

**変更内容:**

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'車両納入日'" />
    <vInput :value="formatDateDisplay(formData.vehicle_delivery_date)" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
</b-col>
```

---

#### 1.2. File: resources/js/pages/VehicleMaster/edit.vue

##### 1.2.1. Kiểm tra và đảm bảo b-form-datepicker format đúng

**Mô tả:**
Kiểm tra xem `b-form-datepicker` đã có `date-format-options` với `month: '2-digit'` chưa.

**既存コード** (line 160-163):

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.INSPECTION_EXPIRATION_DATE'" />
    <span style="color: red;">*</span>
    <b-form-datepicker v-model="formData.inspection_expiration_date" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="inspection_expiration_date" />
</b-col>
```

**変更内容:**

Đã có `date-format-options="{ month: '2-digit', day: '2-digit' }"` - **KHÔNG CẦN THAY ĐỔI**

Tuy nhiên cần kiểm tra lại để đảm bảo không thiếu `year: 'numeric'`:

```vue
:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
```

##### 1.2.2. Kiểm tra method handleFormatFullDate()

**Mô tả:**
Method này đã có logic pad 0 cho tháng và ngày. Cần đảm bảo nó hoạt động đúng.

**既存コード** (line 1239-1258):

```javascript
handleFormatFullDate(date) {
    if (date) {
        const result = new Date(date);
        const year = result.getFullYear();
        let month = result.getMonth() + 1;
        let day = result.getDate();

        if (month < 10) {
            month = '0' + month;
        }

        if (day < 10) {
            day = '0' + day;
        }

        return year + '-' + month + '-' + day;
    }

    return date;
},
```

**変更内容:**

Method này đã đúng - **KHÔNG CẦN THAY ĐỔI**

##### 1.2.3. Kiểm tra method handleFormatDate()

**既存コード** (line 1259-1273):

```javascript
handleFormatDate(date) {
    if (date) {
        const result = new Date(date);
        const year = result.getFullYear();
        let month = result.getMonth() + 1;

        if (month < 10) {
            month = '0' + month;
        }

        return year + '-' + month;
    }

    return date;
},
```

**変更内容:**

Method này đã đúng - **KHÔNG CẦN THAY ĐỔI**

##### 1.2.4. Kiểm tra method formatMonth()

**既存コード** (line 858-864):

```javascript
formatMonth(month) {
    if (month < 10) {
        return `0${month}`;
    } else {
        return month;
    }
},
```

**変更内容:**

Method này đã đúng - **KHÔNG CẦN THAY ĐỔI**

---

#### 1.3. File: resources/js/pages/VehicleMaster/create.vue

##### 1.3.1. Kiểm tra b-form-datepicker format

**既存コード** (line 152-155):

```vue
<b-col cols="4">
    <VI18NLabel :text-label="'VEHICLE_MASTER.VEHICLE_INFORMATION.INSPECTION_EXPIRATION_DATE'" />
    <span style="color: red;">*</span>
    <b-form-datepicker v-model="formData.inspection_expiration_date" :locale="lang" :calendar-width="`290px`" :label-no-date-selected="'選択してください'" :label-help="'カーソルキーを使用してカレンダーの日付をナビゲートする'" :date-format-options="{ month: '2-digit', day: '2-digit' }" class="inspection_expiration_date" />
</b-col>
```

**変更内容:**

Thêm `year: 'numeric'` vào `date-format-options`:

```vue
:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
```

##### 1.3.2. Kiểm tra các methods format

Các methods `handleFormatFullDate()`, `handleFormatDate()`, `formatMonth()` trong file này giống với edit.vue và đã đúng - **KHÔNG CẦN THAY ĐỔI**

---

#### 1.4. File: resources/js/pages/VehicleMaster/index.vue

##### 1.4.1. Kiểm tra hiển thị trong table

**Mô tả:**
Trong table list, `inspection_expiration_date` được hiển thị trực tiếp từ `item[field.key]` mà không qua format.

**既存コード** (line 305-336):

```vue
<tr v-for="(item, rowIndex) in items" :key="item.id" @click="onClickDetail(item.id)" style="cursor: pointer;">
    <td
        :key="field.key"
        v-for="(field, fieldIndex) in displayedFields"
        :style="field.is_locked ? calculateLockedColumnStyleTd(field, fieldIndex, rowIndex, 2) : {}"
        :class="[field.tdClass ? field.tdClass(item[field.key], field.key, item) : '', field.is_locked ? 'locked-td' : '', getInspectionExpirationDateClass(item.inspection_expiration_date_flag)]"
    >
        <template v-if="field.key === 'department_id'">
            {{ item.department_names || '' }}
        </template>

        <template v-else-if="field.key === 'number_plate'">
            {{ getDepartmentName(item.plate_history[0] ? item.plate_history[0].no_number_plate : '') }}
        </template>

        <template v-else-if="field.key === 'detail'">
            <i class="fas fa-eye" @click.stop="onClickDetail(item.id)" />
        </template>

        <template v-else-if="field.key === 'delete'">
            <i class="fas fa-trash" @click.stop="() => { tempID = item.id; showModalConfirmDeletion = true; }" />
        </template>

        <template v-else-if="field.key === 'license_classification'">
            <span>{{ handleGetCertificateByVehicleTotalWeight(item.vehicle_total_weight) }}</span>
        </template>

        <template v-else>
            {{ isNumericField(field.key) ? formatNumberWithCommas(item[field.key]) : item[field.key] }}
        </template>
    </td>
</tr>
```

**変更内容:**

Thêm điều kiện xử lý riêng cho `inspection_expiration_date`:

```vue
<template v-else-if="field.key === 'inspection_expiration_date'">
    {{ formatDateDisplay(item[field.key]) }}
</template>

<template v-else-if="field.key === 'first_registration'">
    {{ formatYearMonthDisplay(item[field.key]) }}
</template>

<template v-else-if="field.key === 'vehicle_delivery_date'">
    {{ formatDateDisplay(item[field.key]) }}
</template>

<template v-else>
    {{ isNumericField(field.key) ? formatNumberWithCommas(item[field.key]) : item[field.key] }}
</template>
```

##### 1.4.2. Thêm methods format vào index.vue

**Mô tả:**
Thêm các methods format date vào section `methods` (sau line 1819):

**変更内容:**

```javascript
formatDateDisplay(dateString) {
    if (!dateString) {
        return '';
    }
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString;
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
    } catch (e) {
        return dateString;
    }
},
formatYearMonthDisplay(dateString) {
    if (!dateString) {
        return '';
    }
    
    try {
        if (dateString.length === 7 && dateString.includes('-')) {
            const [year, month] = dateString.split('-');
            return `${year}-${String(month).padStart(2, '0')}`;
        }
        
        const date = new Date(dateString + '-01');
        if (isNaN(date.getTime())) {
            return dateString;
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        
        return `${year}-${month}`;
    } catch (e) {
        return dateString;
    }
},
```

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: app/Repositories/VehicleRepository.php

##### 1.1.1. Đảm bảo DATE_FORMAT trong queries sử dụng %m (2 chữ số)

**Mô tả:**
Kiểm tra tất cả các nơi sử dụng `DATE_FORMAT` để đảm bảo format tháng là `%m` (2 chữ số) thay vì `%c` (1 chữ số).

**現在の実装** (line 92-106):

```php
if ($filter['inspection_expiration_date']) {
    $firstOfMonth = Carbon::parse($filter['inspection_expiration_date'])->firstOfMonth()->format('Y-m-d');
    $endOfMonth = Carbon::parse($filter['inspection_expiration_date'])->endOfMonth()->format('Y-m-d');
    $this->model = $this->model->whereBetween(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m-%d')"),
        [$firstOfMonth, $endOfMonth]);
}

$currentYearMonth = Carbon::now()->format('Y-m');
$nextMonth = Carbon::now()->addMonth()->format('Y-m');
$this->model = $this->model->addSelect(
    DB::raw("CASE
        WHEN DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m') = '{$currentYearMonth}' THEN 1
        WHEN DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m') = '{$nextMonth}' THEN 2
        ELSE 0
    END AS inspection_expiration_date_flag")
);
```

**変更内容:**

Đã sử dụng `%m` (2 chữ số) - **KHÔNG CẦN THAY ĐỔI**

Tuy nhiên cần kiểm tra thêm các nơi khác trong file:

##### 1.1.2. Kiểm tra method getDashboardVehicle()

**現在の実装** (line 597-598, 647-648):

```php
$nowMonth = Carbon::now()->format('Y-m');
$nextMonth = Carbon::now()->addMonth()->format('Y-m');

$totalNext = (clone $baseQuery)->where(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m')"), '=', $nextMonth)->groupBy('vehicles.id')->get()->count();
$totalNow = (clone $baseQuery)->where(DB::raw("DATE_FORMAT(vehicles.inspection_expiration_date, '%Y-%m')"), '=', $nowMonth)->groupBy('vehicles.id')->get()->count();
```

**変更内容:**

Đã sử dụng format đúng - **KHÔNG CẦN THAY ĐỔI**

##### 1.1.3. Thêm accessor để format inspection_expiration_date trong Model

**Mô tả:**
Để đảm bảo format nhất quán khi trả về API, nên thêm accessor vào Model.

**変更内容:**

Không cần thêm accessor vì Laravel tự động format DATE field theo format Y-m-d (đã đúng).

---

#### 1.2. File: app/Models/Vehicle.php

##### 1.2.1. Kiểm tra $casts và $dates

**現在の実装** (line 80-84):

```php
protected $dates = ['deleted_at'];

protected $casts = [
    'data' => 'array'
];
```

**変更内容:**

Thêm cast cho các trường date để đảm bảo format nhất quán:

```php
protected $dates = ['deleted_at'];

protected $casts = [
    'data' => 'array',
    'inspection_expiration_date' => 'date:Y-m-d',
    'first_registration' => 'string',
    'vehicle_delivery_date' => 'date:Y-m-d',
    'scrap_date' => 'date:Y-m-d',
];
```

**Lưu ý:** Với Laravel, khi cast thành `date`, nó sẽ tự động format theo `Y-m-d` (tháng 2 chữ số).

##### 1.2.2. Thêm accessor cho first_registration

**Mô tả:**
`first_registration` lưu dưới dạng Y-m (không có ngày), cần đảm bảo format tháng 2 chữ số.

**変更内容:**

Thêm accessor vào Model (sau line 84):

```php
public function getFirstRegistrationAttribute($value)
{
    if (!$value) {
        return $value;
    }
    
    if (strlen($value) === 7 && strpos($value, '-') === 4) {
        list($year, $month) = explode('-', $value);
        return sprintf('%04d-%02d', $year, $month);
    }
    
    return $value;
}
```

---

#### 1.3. File: app/Http/Controllers/Api/VehicleController.php

##### 1.3.1. Kiểm tra method show() - format date khi trả về

**現在の実装** (line 212-226):

```php
public function show($id)
{
    try {
        $vehicle = $this->repository->find($id);
        foreach ($vehicle->plate_history as $key => $value) {
            $value->date = date("Y-m-d", strtotime($value->date));
        }
        foreach ($vehicle->vehicle_department_history as $key => $value) {
            $value->date = date("Y-m-d", strtotime($value->date));
        }
        return $this->responseJson(200, new BaseResource($vehicle));
    } catch (\Exception $e) {
        throw $e;
    }
}
```

**変更内容:**

Đã format đúng với `Y-m-d` (tháng 2 chữ số) - **KHÔNG CẦN THAY ĐỔI**

Tuy nhiên, nên thêm format cho `inspection_expiration_date` để đảm bảo:

```php
public function show($id)
{
    try {
        $vehicle = $this->repository->find($id);
        foreach ($vehicle->plate_history as $key => $value) {
            $value->date = date("Y-m-d", strtotime($value->date));
        }
        foreach ($vehicle->vehicle_department_history as $key => $value) {
            $value->date = date("Y-m-d", strtotime($value->date));
        }
        
        if ($vehicle->inspection_expiration_date) {
            $vehicle->inspection_expiration_date = date("Y-m-d", strtotime($vehicle->inspection_expiration_date));
        }
        if ($vehicle->vehicle_delivery_date) {
            $vehicle->vehicle_delivery_date = date("Y-m-d", strtotime($vehicle->vehicle_delivery_date));
        }
        if ($vehicle->scrap_date) {
            $vehicle->scrap_date = date("Y-m-d", strtotime($vehicle->scrap_date));
        }
        if ($vehicle->first_registration) {
            if (strlen($vehicle->first_registration) === 7) {
                list($year, $month) = explode('-', $vehicle->first_registration);
                $vehicle->first_registration = sprintf('%04d-%02d', $year, $month);
            }
        }
        
        return $this->responseJson(200, new BaseResource($vehicle));
    } catch (\Exception $e) {
        throw $e;
    }
}
```

---

## 実装順序 (Implementation Order)

### 1. Backend 実装 (独立して実装可能)

**Phase 1: Model Layer**
- [ ] 1.2.1: Thêm casts cho date fields trong `Vehicle.php`
- [ ] 1.2.2: Thêm accessor cho `first_registration` trong `Vehicle.php`

**Phase 2: Controller Layer**
- [ ] 1.3.1: Cập nhật `VehicleController::show()` để format dates

**Phase 3: Repository Layer**
- [ ] 1.1.1: Verify `DATE_FORMAT` trong `VehicleRepository.php` (đã đúng, chỉ cần kiểm tra)
- [ ] 1.1.2: Verify `getDashboardVehicle()` (đã đúng, chỉ cần kiểm tra)

**Estimated time:** 2-3 giờ

---

### 2. Frontend 実装 (phụ thuộc vào Backend hoàn thành)

**Phase 1: Tạo utility methods**
- [ ] 1.4.2: Thêm `formatDateDisplay()` và `formatYearMonthDisplay()` vào `index.vue`
- [ ] 1.1.2: Thêm `formatDateDisplay()` và `formatYearMonthDisplay()` vào `detail.vue`

**Phase 2: Cập nhật hiển thị trong detail.vue**
- [ ] 1.1.1: Sửa hiển thị `inspection_expiration_date`
- [ ] 1.1.3: Sửa hiển thị `first_registration`
- [ ] 1.1.4: Sửa hiển thị `vehicle_delivery_date`

**Phase 3: Cập nhật hiển thị trong index.vue (list)**
- [ ] 1.4.1: Thêm template conditions cho date fields trong table

**Phase 4: Verify create.vue và edit.vue**
- [ ] 1.2.1: Kiểm tra `date-format-options` trong `edit.vue`
- [ ] 1.3.1: Cập nhật `date-format-options` trong `create.vue`
- [ ] 1.2.2, 1.2.3, 1.2.4: Verify các methods format (đã đúng)
- [ ] 1.3.2: Verify các methods format trong `create.vue` (đã đúng)

**Estimated time:** 3-4 giờ

---

### 3. 統合テスト (Integration Testing)

**Test scenarios:**

1. **List page (index.vue):**
   - [ ] Verify tất cả `inspection_expiration_date` hiển thị với tháng 2 chữ số
   - [ ] Verify `first_registration` hiển thị với tháng 2 chữ số
   - [ ] Test với tháng 1-9 (các tháng 1 chữ số)
   - [ ] Test với tháng 10-12

2. **Detail page (detail.vue):**
   - [ ] Verify hiển thị đúng format cho tất cả date fields
   - [ ] Test với nhiều records khác nhau

3. **Create page (create.vue):**
   - [ ] Verify datepicker hiển thị đúng format
   - [ ] Test tạo mới với tháng 1-9
   - [ ] Verify data được lưu đúng format vào DB

4. **Edit page (edit.vue):**
   - [ ] Verify datepicker hiển thị đúng format khi load data
   - [ ] Test update với tháng 1-9
   - [ ] Verify data được lưu đúng format vào DB

5. **API Testing:**
   - [ ] Test `/api/vehicle` endpoint - verify format trong response
   - [ ] Test `/api/vehicle/{id}` endpoint - verify format trong response
   - [ ] Test dashboard API - verify format

6. **Export/Import:**
   - [ ] Test export CSV - verify format trong file
   - [ ] Test import data - verify format được parse đúng

**Estimated time:** 2-3 giờ

---

## 見積もり工数 (Estimated Effort)

### Backend: 2-3 時間

- Model casts và accessor: 0.5 giờ
- Controller format logic: 1 giờ
- Repository verification: 0.5 giờ
- Backend testing: 0.5-1 giờ

### Frontend: 3-4 時間

- Tạo utility methods: 0.5 giờ
- Cập nhật detail.vue: 1 giờ
- Cập nhật index.vue: 1 giờ
- Verify create.vue và edit.vue: 0.5 giờ
- Frontend testing: 0.5-1 giờ

### Integration Testing: 2-3 時間

- Test all screens: 1.5 giờ
- Test API endpoints: 0.5 giờ
- Test export/import: 0.5 giờ
- Bug fixes và adjustments: 0.5 giờ

### **合計: 7-10 時間**

---

## 技術的な注意事項 (Technical Notes)

### 1. パフォーマンス考慮 (Performance Considerations)

- Format functions được gọi trong v-for loop, cần đảm bảo performance tốt
- Sử dụng try-catch để handle edge cases mà không làm crash UI
- Cân nhắc sử dụng computed properties thay vì methods nếu data không thay đổi thường xuyên

### 2. UX 考慮 (UX Considerations)

- Đảm bảo format nhất quán trên tất cả các màn hình
- Không làm thay đổi cách người dùng nhập liệu (datepicker vẫn hoạt động như cũ)
- Format chỉ áp dụng cho display, không ảnh hưởng đến data trong DB

### 3. データ整合性 (Data Integrity)

- Sử dụng Laravel casts để đảm bảo format nhất quán từ database
- Accessor trong Model đảm bảo format đúng khi truy xuất data
- Backend format trước khi trả về API để đảm bảo frontend luôn nhận đúng format

### 4. 既存機能との互換性 (Compatibility with Existing Features)

- **Backward compatibility:** Các thay đổi không ảnh hưởng đến data đã lưu trong DB
- **API compatibility:** Response format vẫn giữ nguyên structure, chỉ thay đổi format string
- **Filter functionality:** Các filter theo tháng vẫn hoạt động bình thường
- **Sort functionality:** Sort theo date vẫn hoạt động đúng
- **Export/Import:** Cần test kỹ để đảm bảo format trong CSV đúng

### 5. Edge Cases cần xử lý

- Null/undefined values
- Invalid date strings
- Timezone issues (nếu có)
- Date strings với format khác nhau từ các nguồn khác nhau (manual input, import, API)

### 6. Testing Strategy

- Unit test cho các format functions
- Integration test cho toàn bộ flow (create → list → detail → edit)
- Regression test để đảm bảo không break existing features
- Manual test với real data

### 7. Rollback Plan

Nếu có vấn đề sau khi deploy:
- Frontend changes có thể rollback dễ dàng (chỉ cần revert commits)
- Backend changes cũng có thể rollback (Model casts không ảnh hưởng đến DB schema)
- Không cần migration, không thay đổi DB structure

---

## Additional Notes

### Files không cần thay đổi:

1. **Database migrations:** Không cần thay đổi DB schema
2. **API routes:** Không cần thay đổi routing
3. **Validation rules:** Validation vẫn giữ nguyên
4. **Language files:** Không cần thay đổi translations

### Files cần review sau khi implement:

1. `resources/js/store/modules/filter.js` - Verify filter logic vẫn hoạt động
2. Export files (`app/Exports/VehicleExport.php`) - Verify format trong CSV
3. Import files (nếu có) - Verify parse date đúng

### Potential Issues:

1. **Japanese date format:** Một số nơi có thể sử dụng format 年月日, cần đảm bảo không conflict
2. **Timezone:** Nếu server và client ở timezone khác nhau, có thể gây ra vấn đề với date
3. **Browser compatibility:** `padStart()` không support IE11, nhưng project này có vẻ không cần support IE

---

## Conclusion

Đây là một task tương đối đơn giản nhưng cần thực hiện cẩn thận để đảm bảo tính nhất quán trên toàn bộ hệ thống. Phần lớn code đã có sẵn logic format đúng, chỉ cần áp dụng nhất quán ở tất cả các nơi hiển thị date.

**Priority:** Medium
**Complexity:** Low-Medium
**Risk:** Low (không thay đổi DB schema, không ảnh hưởng đến business logic)

