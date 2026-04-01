# Issue #704: AQ address/calculation bug - Implementation Plan

## 概要 (Overview)

Issue này xử lý 3 bugs chính trong module Auto Quotation (AQ) trên môi trường Production:

1. **Địa chỉ bị mất sau khi lưu**: Khi người dùng nhập địa chỉ và lưu, địa chỉ đã nhập bị mất
2. **Phí cao tốc (高速料金) chỉ tính 1 chiều**: Cần thêm checkbox 往復 (2 chiều) và hiển thị điểm vào/ra cao tốc
3. **Lỗi tính toán 車両償却費 (phí khấu hao xe) và 車両購入価格 (giá mua xe)**

**Scope**: Backend Only - FE đã xử lý logic tính toán, BE chỉ cần lưu dữ liệu từ FE gửi lên.

**Hiện trạng**:
- Migration `2026_01_30_100000_change_decimal_to_text_in_quotations_table.php` đã thêm field `tow_way_highway` (kiểu `text`)
- Địa chỉ được lưu trong bảng `quotation_delivery_locations` (relation hasMany)
- Các field tính toán đã chuyển từ `decimal` sang `text`

**Cải thiện**:
- Fix bug địa chỉ bị mất
- Chuyển field `tow_way_highway` từ `text` sang `boolean`
- Cập nhật Model, Request validation, Resource để hỗ trợ field `tow_way_highway`
- Đảm bảo tất cả dữ liệu từ FE được lưu đúng vào database

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `database/migrations/2026_01_30_100000_change_decimal_to_text_in_quotations_table.php`

##### 1.1.1. Chuyển kiểu dữ liệu field `tow_way_highway` từ text sang boolean

**Vấn đề**: Field `tow_way_highway` đang là kiểu `text` nhưng nên là `boolean` cho checkbox.

**Hiện tại** (line 67):
```php
$table->text('tow_way_highway')->nullable();
```

**Thay đổi**:
- Giữ nguyên tên field `tow_way_highway`
- Chỉ đổi kiểu từ `text` sang `boolean` với default `false`

```php
$table->boolean('tow_way_highway')->default(false)->nullable();
```

**Down migration** (line 78):
- Giữ nguyên như hiện tại:
```php
$table->dropColumn('tow_way_highway');
```

---

#### 1.2. File: `app/Models/Quotation.php`

##### 1.2.1. Cập nhật $fillable array để thêm field `tow_way_highway`

**Hiện tại** (line 14-73):
- Array `$fillable` chứa các field hiện tại

**Thay đổi**:
Thêm field `tow_way_highway` vào cuối array `$fillable` (trước dòng 73):

```php
'tow_way_highway',
```

##### 1.2.2. Thêm $casts để cast kiểu dữ liệu

**Mục đích**: Đảm bảo `tow_way_highway` được cast sang boolean.

**Thêm sau $fillable array** (sau line 73):

```php
protected $casts = [
    'tow_way_highway' => 'boolean',
];
```

---

#### 1.3. File: `app/Http/Requests/CreateQuotationRequest.php`

##### 1.3.1. Thêm validation rule cho field `tow_way_highway`

**Hiện tại** (line 14-27):
- Validation rules cơ bản cho create quotation

**Thay đổi**:
Thêm validation rule cho field `tow_way_highway` vào array `rules()` (sau line 25):

```php
'tow_way_highway' => 'nullable|boolean',
```

**Giải thích**:
- `tow_way_highway`: Boolean, nullable (mặc định false)

---

#### 1.4. File: `app/Http/Requests/UpdateQuotationRequest.php`

##### 1.4.1. Thêm validation rule cho field `tow_way_highway`

**Hiện tại** (line 14-28):
- Validation rules với `sometimes` cho update

**Thay đổi**:
Thêm validation rule cho field `tow_way_highway` vào array `rules()` (sau line 25):

```php
'tow_way_highway' => 'sometimes|nullable|boolean',
```

---

#### 1.5. File: `app/Http/Controllers/Api/QuotationController.php`

##### 1.5.1. Cập nhật OpenAPI documentation cho endpoint POST /api/quotations

**Hiện tại** (line 214-285):
- OpenAPI schema cho create quotation

**Thay đổi**:
Thêm property `tow_way_highway` vào `@OA\Schema` (sau line 282, trước line 283):

```php
*            @OA\Property(property="tow_way_highway", type="boolean", example=false, description="Checkbox 往復 - tính phí cao tốc 2 chiều"),
```

##### 1.5.2. Cập nhật OpenAPI documentation cho endpoint PUT /api/quotations/{id}

**Hiện tại** (line 337-411):
- OpenAPI schema cho update quotation

**Thay đổi**:
Thêm property `tow_way_highway` vào `@OA\Schema` (sau line 409, trước line 410):

```php
*            @OA\Property(property="tow_way_highway", type="boolean", example=true, description="Checkbox 往復 - tính phí cao tốc 2 chiều"),
```

**Lưu ý**: Không cần thay đổi logic trong `store()` và `update()` methods vì chúng đã sử dụng `$request->all()` và repository pattern, sẽ tự động xử lý field mới.

---

#### 1.6. File: `app/Http/Resources/QuotationResource.php`

##### 1.6.1. Đảm bảo field `tow_way_highway` được trả về trong API response

**Hiện tại** (line 7-23):
- Resource sử dụng `parent::toArray($request)` từ `BaseResource`
- Chỉ customize `delivery_locations`

**Thay đổi**:
Không cần thay đổi gì vì `parent::toArray($request)` sẽ tự động include tất cả các attributes từ model, bao gồm cả field `tow_way_highway`.

**Kiểm tra**: Verify rằng `BaseResource` không filter out field mới.

---

#### 1.7. File: `app/Repositories/QuotationRepository.php`

##### 1.7.1. Kiểm tra logic lưu delivery_locations trong create()

**Hiện tại** (line 81-102):
- Method `create()` xử lý `delivery_locations` array
- Xóa `delivery_locations` khỏi attributes trước khi create
- Sau đó tạo các records trong `quotation_delivery_locations`

**Vấn đề tiềm ẩn**:
Nếu `delivery_locations` là array rỗng hoặc chứa empty strings, có thể gây ra vấn đề.

**Thay đổi**:
Cải thiện logic kiểm tra empty location (line 90-96):

**Hiện tại**:
```php
if (!empty($deliveryLocations)) {
    foreach ($deliveryLocations as $index => $location) {
        if (!empty($location)) {
            $quotation->deliveryLocations()->create([
                'location_name' => $location,
                'sequence_order' => $index + 1,
            ]);
        }
    }
}
```

**Thay đổi**:
```php
if (!empty($deliveryLocations) && is_array($deliveryLocations)) {
    foreach ($deliveryLocations as $index => $location) {
        // Trim và kiểm tra location không rỗng
        $trimmedLocation = is_string($location) ? trim($location) : '';
        if (!empty($trimmedLocation)) {
            $quotation->deliveryLocations()->create([
                'location_name' => $trimmedLocation,
                'sequence_order' => $index + 1,
            ]);
        }
    }
}
```

##### 1.7.2. Kiểm tra logic cập nhật delivery_locations trong update()

**Hiện tại** (line 104-131):
- Method `update()` xóa tất cả delivery_locations cũ
- Sau đó tạo lại các records mới

**Vấn đề tiềm ẩn**:
- Logic tương tự như `create()`, cần cải thiện việc kiểm tra empty location
- Khi `delivery_locations` là `null`, không nên xóa locations hiện có

**Thay đổi**:
Cải thiện logic (line 114-127):

**Hiện tại**:
```php
if ($deliveryLocations !== null) {
    $quotation->deliveryLocations()->delete();
    
    if (!empty($deliveryLocations)) {
        foreach ($deliveryLocations as $index => $location) {
            if (!empty($location)) {
                $quotation->deliveryLocations()->create([
                    'location_name' => $location,
                    'sequence_order' => $index + 1,
                ]);
            }
        }
    }
}
```

**Thay đổi**:
```php
if ($deliveryLocations !== null) {
    // Xóa tất cả delivery locations cũ
    $quotation->deliveryLocations()->delete();
    
    // Tạo lại delivery locations mới nếu có
    if (!empty($deliveryLocations) && is_array($deliveryLocations)) {
        foreach ($deliveryLocations as $index => $location) {
            // Trim và kiểm tra location không rỗng
            $trimmedLocation = is_string($location) ? trim($location) : '';
            if (!empty($trimmedLocation)) {
                $quotation->deliveryLocations()->create([
                    'location_name' => $trimmedLocation,
                    'sequence_order' => $index + 1,
                ]);
            }
        }
    }
}
```

**Giải thích**:
- Thêm `is_array()` check để tránh lỗi khi `delivery_locations` không phải array
- Thêm `trim()` để loại bỏ whitespace
- Thêm `is_string()` check để tránh lỗi khi element không phải string

---

#### 1.8. File: `app/Models/Quotation.php` (bổ sung)

##### 1.8.1. Kiểm tra và đảm bảo các field địa chỉ trong $fillable

**Mục đích**: Đảm bảo tất cả các field địa chỉ đều có trong `$fillable` để có thể lưu được.

**Kiểm tra** (line 14-73):
Verify các fields sau có trong `$fillable`:
- `departure_location` ✓ (line 18)
- `loading_location` ✓ (line 31)
- `delivery_location` ✓ (line 32)
- `return_location` ✓ (line 33)

**Kết quả**: Tất cả các field địa chỉ đã có trong `$fillable`, không cần thay đổi.

---

### 2. Files need to create:

**Không cần tạo file mới** - Chỉ cần sửa migration hiện có `2026_01_30_100000_change_decimal_to_text_in_quotations_table.php`

---

## 実装順序 (Implementation Order)

### 1. Database Migration (Độc lập - có thể chạy đầu tiên)

**Tasks**:
1. Sửa migration `2026_01_30_100000_change_decimal_to_text_in_quotations_table.php` - đổi `tow_way_highway` từ `text` sang `boolean` (Task 1.1.1)
2. Chạy migration trên local để test
3. Verify database schema đúng

**Dependencies**: Không có

---

### 2. Backend Model & Repository (Phụ thuộc vào Migration)

**Tasks**:
1. Cập nhật `Quotation` Model:
   - Thêm fields vào `$fillable` (Task 1.2.1)
   - Thêm `$casts` (Task 1.2.2)
2. Fix logic lưu địa chỉ trong `QuotationRepository`:
   - Fix `create()` method (Task 1.7.1)
   - Fix `update()` method (Task 1.7.2)

**Dependencies**: Migration phải chạy trước

---

### 3. Backend Request Validation (Độc lập - có thể song song với Task 2)

**Tasks**:
1. Cập nhật `CreateQuotationRequest` (Task 1.3.1)
2. Cập nhật `UpdateQuotationRequest` (Task 1.4.1)

**Dependencies**: Không có (có thể làm song song với Task 2)

---

### 4. Backend Controller & Resource (Phụ thuộc vào Task 2, 3)

**Tasks**:
1. Cập nhật OpenAPI docs trong `QuotationController`:
   - POST endpoint (Task 1.5.1)
   - PUT endpoint (Task 1.5.2)
2. Verify `QuotationResource` (Task 1.6.1)

**Dependencies**: Task 2 và 3 phải hoàn thành

---

### 5. Testing & Verification (Phụ thuộc vào tất cả tasks trên)

**Tasks**:
1. Test API create quotation với các field mới
2. Test API update quotation với các field mới
3. Test lưu và lấy delivery_locations
4. Verify địa chỉ không bị mất sau khi lưu
5. Test với các edge cases:
   - Empty delivery_locations array
   - Null delivery_locations
   - Whitespace trong location names
   - is_round_trip_highway = true/false

**Dependencies**: Tất cả tasks trên phải hoàn thành

---

## 見積もり工数 (Estimated Effort)

### Backend: 3-4 giờ

**Breakdown**:
- **Migration** (Task 1.1.1): 15 phút
  - Sửa migration file
  - Test migration up/down
  - Verify database schema

- **Model & Repository** (Tasks 1.2.1, 1.2.2, 1.7.1, 1.7.2): 1.5 giờ
  - Cập nhật Quotation Model: 15 phút
  - Fix QuotationRepository create(): 30 phút
  - Fix QuotationRepository update(): 30 phút
  - Testing: 15 phút

- **Request Validation** (Tasks 1.3.1, 1.4.1): 15 phút
  - Cập nhật CreateQuotationRequest: 7 phút
  - Cập nhật UpdateQuotationRequest: 8 phút

- **Controller & Resource** (Tasks 1.5.1, 1.5.2, 1.6.1): 30 phút
  - Cập nhật OpenAPI docs POST: 12 phút
  - Cập nhật OpenAPI docs PUT: 13 phút
  - Verify QuotationResource: 5 phút

- **Testing & Bug Fixes**: 45 phút
  - Unit tests: 20 phút
  - Integration tests: 15 phút
  - Bug fixes và adjustments: 10 phút

**Tổng cộng**: 2.5-3 giờ (tùy thuộc vào số lượng bugs phát hiện trong quá trình test)

---

## 技術的な注意事項 (Technical Notes)

### 1. Database Migration

**Lưu ý quan trọng**:
- Migration `2026_01_30_100000_change_decimal_to_text_in_quotations_table.php` chưa chạy trên production (file mới tạo)
- Có thể sửa trực tiếp migration này để đổi `tow_way_highway` từ `text` sang `boolean`
- Giữ nguyên tên field `tow_way_highway` (không đổi tên)

**Rollback strategy**:
- Nếu có vấn đề, có thể rollback migration
- Field `tow_way_highway` sẽ bị xóa khi rollback

### 2. Data Integrity

**Địa chỉ (delivery_locations)**:
- Luôn trim whitespace trước khi lưu
- Validate empty strings
- Sử dụng transaction để đảm bảo data consistency
- Khi update, xóa tất cả locations cũ trước khi tạo mới (tránh duplicate)

**Phí cao tốc (highway fee)**:
- Field `tow_way_highway` default là `false`
- FE sẽ tính toán phí cao tốc (x2 nếu round trip), BE chỉ lưu kết quả

**Phí khấu hao xe (vehicle depreciation)**:
- FE đã xử lý logic tính toán
- BE chỉ cần lưu giá trị `calc_vehicle_depreciation` từ FE
- Không cần thay đổi gì về logic tính toán

### 3. API Backward Compatibility

**Compatibility**:
- Field `tow_way_highway` là `nullable` với default `false`
- API cũ vẫn hoạt động bình thường nếu không gửi field này
- Response sẽ bao gồm field `tow_way_highway` (mặc định `false` hoặc `null`)

**Breaking changes**: Không có

### 4. Performance

**Query optimization**:
- Sử dụng `with(['deliveryLocations'])` để eager load delivery locations (tránh N+1 query)
- Transaction được sử dụng trong create/update để đảm bảo atomicity

**Indexing**:
- Không cần thêm index mới vì các field mới không được dùng để filter/search

### 5. Testing Strategy

**Unit Tests**:
- Test QuotationRepository create() với delivery_locations
- Test QuotationRepository update() với delivery_locations
- Test edge cases: empty array, null, whitespace

**Integration Tests**:
- Test POST /api/quotations với field `tow_way_highway`
- Test PUT /api/quotations/{id} với field `tow_way_highway`
- Test GET /api/quotations/{id} verify response có field `tow_way_highway`

**Manual Testing trên Staging**:
- Test full flow: tạo quotation → lưu → reload → verify địa chỉ vẫn còn
- Test với tow_way_highway = true/false

### 6. Production Deployment

**Deployment checklist**:
1. Backup database trước khi deploy
2. Chạy migration trong maintenance mode
3. Verify migration thành công
4. Deploy code mới
5. Test API endpoints
6. Monitor logs để phát hiện lỗi
7. Có rollback plan sẵn sàng

**Rollback plan**:
- Nếu có vấn đề: rollback code → rollback migration → restore backup (nếu cần)

### 7. Giải quyết Bug #1: Địa chỉ bị mất

**Root cause analysis**:
- Logic trong `QuotationRepository` đã đúng (sử dụng transaction)
- Vấn đề có thể do:
  - FE gửi empty strings thay vì null
  - FE gửi array chứa whitespace
  - Validation không đủ strict

**Solution**:
- Thêm `trim()` để loại bỏ whitespace
- Thêm `is_string()` và `is_array()` checks
- Validate empty strings

### 8. Giải quyết Bug #2: Phí cao tốc

**Hiện trạng**:
- FE đã xử lý logic tính toán (1 chiều vs 2 chiều)
- BE chỉ cần lưu kết quả

**Solution**:
- Thêm field `tow_way_highway` (boolean) để lưu trạng thái checkbox 往復
- FE sẽ gửi dữ liệu đã tính toán, BE chỉ validate và lưu
- Về điểm vào/ra cao tốc: Giữ nguyên các field hiện có, không thêm field mới

### 9. Giải quyết Bug #3: Phí khấu hao xe

**Hiện trạng**:
- FE đã xử lý logic tính toán
- BE lưu giá trị `calc_vehicle_depreciation` và `vehicle_price`

**Solution**:
- Không cần thay đổi gì về database schema
- Chỉ cần đảm bảo BE lưu đúng giá trị từ FE gửi lên
- Các fields liên quan đã có trong `$fillable`:
  - `calc_vehicle_depreciation`
  - `vehicle_price`
  - `lease_years`
  - `residual_value_rate`
  - `vehicle_lease`

**Verification**:
- Test API với các giá trị khác nhau
- Verify dữ liệu được lưu đúng vào database
- Verify response trả về đúng giá trị

---

## Notes

- **Priority**: HIGH (Production bug)
- **Scope**: Backend Only
- **Testing**: Cần test kỹ trên staging trước khi deploy lên production
- **Migration**: Cần chạy trong maintenance mode
- **Rollback**: Có rollback plan sẵn sàng

