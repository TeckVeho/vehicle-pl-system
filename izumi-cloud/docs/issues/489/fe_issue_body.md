## 日本語 / Japanese

### 親 Issue

#489 に関連

### 説明

車両マスタ画面（一覧・詳細・作成・編集）における車検満了日（inspection_expiration_date）および関連日付フィールドの表示形式を統一します。現在、月の表示が「8」（1桁）と「08」（2桁）で不統一になっている問題を、フォーマット関数を適用して解決します。

### 要件

1. **共通フォーマット関数の作成:**
   - `formatDateDisplay()`: YYYY-MM-DD形式（月・日を2桁に統一）
   - `formatYearMonthDisplay()`: YYYY-MM形式（月を2桁に統一）

2. **4つのVueファイルを更新:**
   - `detail.vue`: 詳細画面の日付表示にフォーマット関数を適用
   - `index.vue`: 一覧テーブルの日付表示にフォーマット関数を適用
   - `edit.vue`: datepickerの`date-format-options`を検証
   - `create.vue`: datepickerの`date-format-options`を更新

3. **対象フィールド:**
   - `inspection_expiration_date` (車検満了日)
   - `first_registration` (初度登録年月)
   - `vehicle_delivery_date` (車両納入日)

### 技術詳細

#### File 1: resources/js/pages/VehicleMaster/detail.vue

**1.1. フォーマット関数を追加 (line 1601の後):**

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

**1.2. 表示を更新 (line 152-165):**

```vue
<!-- first_registration -->
<vInput :value="formatYearMonthDisplay(formData.first_registration)" :type="'text'" placeholder="" disabled />

<!-- inspection_expiration_date -->
<vInput :value="formatDateDisplay(formData.inspection_expiration_date)" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />

<!-- vehicle_delivery_date -->
<vInput :value="formatDateDisplay(formData.vehicle_delivery_date)" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
```

#### File 2: resources/js/pages/VehicleMaster/index.vue

**2.1. フォーマット関数を追加 (line 1819の後):**

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

**2.2. テーブル表示を更新 (line 332の前):**

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
```

#### File 3: resources/js/pages/VehicleMaster/edit.vue

**3.1. datepickerの`date-format-options`を検証 (line 162):**

```vue
:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
```

既存の`handleFormatFullDate()`, `handleFormatDate()`, `formatMonth()`は正しく実装済み → 変更不要

#### File 4: resources/js/pages/VehicleMaster/create.vue

**4.1. datepickerの`date-format-options`を更新 (line 154):**

```vue
:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
```

既存の`handleFormatFullDate()`, `handleFormatDate()`, `formatMonth()`は正しく実装済み → 変更不要

### 受け入れ基準

- [ ] `detail.vue`にフォーマット関数を追加完了
- [ ] `detail.vue`の3つの日付フィールド表示を更新完了
- [ ] `index.vue`にフォーマット関数を追加完了
- [ ] `index.vue`のテーブル表示を更新完了
- [ ] `edit.vue`の`date-format-options`を検証完了
- [ ] `create.vue`の`date-format-options`を更新完了
- [ ] Jest/Vue Test Utils でユニットテストを作成・合格
- [ ] 一覧画面で全ての日付が月2桁で表示されることを確認
- [ ] 詳細画面で全ての日付が月2桁で表示されることを確認
- [ ] 作成・編集画面のdatepickerが正しく動作することを確認
- [ ] 月が1-9のデータで正しく2桁表示されることを確認
- [ ] 既存機能への破壊的変更なし

### 依存関係

Backend issue #492 の完了後、統合テストを実施

### テスト項目

1. **一覧画面 (index.vue):**
   - 全ての`inspection_expiration_date`が月2桁で表示されることを確認
   - `first_registration`が月2桁で表示されることを確認
   - 月1-9のデータで正しく表示されることを確認

2. **詳細画面 (detail.vue):**
   - 全ての日付フィールドが正しいフォーマットで表示されることを確認
   - 複数のレコードでテスト

3. **作成画面 (create.vue):**
   - datepickerが正しいフォーマットで表示されることを確認
   - 月1-9で作成テスト
   - データがDBに正しく保存されることを確認

4. **編集画面 (edit.vue):**
   - datepickerがデータロード時に正しいフォーマットで表示されることを確認
   - 月1-9で更新テスト
   - データがDBに正しく保存されることを確認

5. **統合テスト (Backend #492完了後):**
   - API応答とフロントエンド表示の整合性を確認
   - Filter機能が正常動作することを確認
   - Sort機能が正常動作することを確認

---

## Tiếng Việt / Vietnamese

### Issue cha

Liên quan đến #489

### Mô tả

Thống nhất định dạng hiển thị ngày hết hạn kiểm định xe (inspection_expiration_date) và các trường ngày liên quan trong màn hình Vehicle Master (danh sách, chi tiết, tạo mới, chỉnh sửa). Giải quyết vấn đề hiển thị tháng không đồng nhất giữa "8" (1 chữ số) và "08" (2 chữ số) bằng cách áp dụng các hàm format.

### Yêu cầu

1. **Tạo các hàm format chung:**
   - `formatDateDisplay()`: Định dạng YYYY-MM-DD (tháng và ngày 2 chữ số)
   - `formatYearMonthDisplay()`: Định dạng YYYY-MM (tháng 2 chữ số)

2. **Cập nhật 4 file Vue:**
   - `detail.vue`: Áp dụng hàm format cho hiển thị ngày trong màn hình chi tiết
   - `index.vue`: Áp dụng hàm format cho hiển thị ngày trong bảng danh sách
   - `edit.vue`: Kiểm tra `date-format-options` của datepicker
   - `create.vue`: Cập nhật `date-format-options` của datepicker

3. **Các trường cần xử lý:**
   - `inspection_expiration_date` (Ngày hết hạn kiểm định)
   - `first_registration` (Tháng năm đăng ký lần đầu)
   - `vehicle_delivery_date` (Ngày giao xe)

### Chi tiết kỹ thuật

#### File 1: resources/js/pages/VehicleMaster/detail.vue

**1.1. Thêm hàm format (sau line 1601):**

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

**1.2. Cập nhật hiển thị (line 152-165):**

```vue
<!-- first_registration -->
<vInput :value="formatYearMonthDisplay(formData.first_registration)" :type="'text'" placeholder="" disabled />

<!-- inspection_expiration_date -->
<vInput :value="formatDateDisplay(formData.inspection_expiration_date)" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />

<!-- vehicle_delivery_date -->
<vInput :value="formatDateDisplay(formData.vehicle_delivery_date)" :type="'text'" placeholder="" disabled :class-name="'no-border-right fixed-height-input'" />
```

#### File 2: resources/js/pages/VehicleMaster/index.vue

**2.1. Thêm hàm format (sau line 1819):**

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

**2.2. Cập nhật hiển thị trong table (trước line 332):**

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
```

#### File 3: resources/js/pages/VehicleMaster/edit.vue

**3.1. Kiểm tra `date-format-options` của datepicker (line 162):**

```vue
:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
```

Các hàm `handleFormatFullDate()`, `handleFormatDate()`, `formatMonth()` đã được implement đúng → Không cần thay đổi

#### File 4: resources/js/pages/VehicleMaster/create.vue

**4.1. Cập nhật `date-format-options` của datepicker (line 154):**

```vue
:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
```

Các hàm `handleFormatFullDate()`, `handleFormatDate()`, `formatMonth()` đã được implement đúng → Không cần thay đổi

### Tiêu chí chấp nhận

- [ ] Hoàn thành thêm hàm format vào `detail.vue`
- [ ] Hoàn thành cập nhật hiển thị 3 trường ngày trong `detail.vue`
- [ ] Hoàn thành thêm hàm format vào `index.vue`
- [ ] Hoàn thành cập nhật hiển thị trong table của `index.vue`
- [ ] Hoàn thành kiểm tra `date-format-options` trong `edit.vue`
- [ ] Hoàn thành cập nhật `date-format-options` trong `create.vue`
- [ ] Tạo và vượt qua unit tests với Jest/Vue Test Utils
- [ ] Xác nhận tất cả ngày trong màn hình danh sách hiển thị tháng 2 chữ số
- [ ] Xác nhận tất cả ngày trong màn hình chi tiết hiển thị tháng 2 chữ số
- [ ] Xác nhận datepicker trong màn hình tạo/sửa hoạt động đúng
- [ ] Xác nhận dữ liệu có tháng 1-9 hiển thị đúng 2 chữ số
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc

Sau khi Backend issue #492 hoàn thành, thực hiện integration testing

### Các mục cần test

1. **Màn hình danh sách (index.vue):**
   - Xác nhận tất cả `inspection_expiration_date` hiển thị tháng 2 chữ số
   - Xác nhận `first_registration` hiển thị tháng 2 chữ số
   - Test với dữ liệu có tháng 1-9

2. **Màn hình chi tiết (detail.vue):**
   - Xác nhận tất cả trường ngày hiển thị đúng định dạng
   - Test với nhiều records khác nhau

3. **Màn hình tạo mới (create.vue):**
   - Xác nhận datepicker hiển thị đúng định dạng
   - Test tạo mới với tháng 1-9
   - Xác nhận dữ liệu được lưu đúng vào DB

4. **Màn hình chỉnh sửa (edit.vue):**
   - Xác nhận datepicker hiển thị đúng định dạng khi load dữ liệu
   - Test cập nhật với tháng 1-9
   - Xác nhận dữ liệu được lưu đúng vào DB

5. **Integration testing (sau khi Backend #492 hoàn thành):**
   - Xác nhận tính nhất quán giữa API response và hiển thị frontend
   - Xác nhận chức năng Filter hoạt động bình thường
   - Xác nhận chức năng Sort hoạt động bình thường

