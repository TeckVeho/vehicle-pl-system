# Issue #560: Retired user/ vehicle grey out logic error

## Metadata
- **Title**: Retired user/ vehicle grey out logic error
- **Status**: OPEN
- **Created**: 2025-12-26T04:43:21Z
- **Updated**: 2025-12-26T04:43:21Z
- **Assignee**: @phuongcodeunited
- **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/560

## Description

### Vấn đề từ khách hàng (Japanese)
課題表No.712　退職者ユーザー/車両廃車のロジック修正について
こちら、車両廃車についても本番反映済でしょうか？
本番環境で、廃車日を未来の日付で登録したとき、即座にグレーアウトして廃車扱いとなってしまいました。
想定では、廃車日を設定した日の翌日から廃車扱いにする。
⇒車両No　横浜800い1757
ご確認よろしくお願いします。

### Vấn đề (Vietnamese)
Đây là xe bị hủy, khách đăng ký ngày hủy là ngày trong tương lai, nhưng sau khi đăng ký ngày hủy (廃車日) thì xe đó bị bôi xám luôn. Đúng spec là từ ngày sau ngày hủy bỏ (廃車日) nó mới bị bôi xám.

**Xe bị ảnh hưởng**: 横浜800い1757

## Phân tích nguyên nhân

### 1. Logic hiện tại (SAI)

#### Backend - VehicleRepository.php
Tại file `app/Repositories/VehicleRepository.php`, dòng 563 và 569:

```php
DB::raw("CASE WHEN vehicles.scrap_date <= '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
```

**Vấn đề**: Logic này kiểm tra `scrap_date <= today`, nghĩa là:
- Nếu ngày hủy xe <= ngày hôm nay → hiển thị scrap_date (xe bị coi là đã hủy)
- Nếu ngày hủy xe > ngày hôm nay → NULL (xe chưa bị hủy)

**Ví dụ sai**:
- Hôm nay: 2025-12-26
- Khách đăng ký scrap_date = 2025-12-26 (hôm nay)
- Logic kiểm tra: 2025-12-26 <= 2025-12-26 → TRUE
- Kết quả: Xe BỊ BÔI XÁM NGAY LẬP TỨC (SAI!)

#### Frontend - index.vue
Tại file `resources/js/pages/VehicleMaster/index.vue`, dòng 1358-1364:

```javascript
handleRenderCellClass(value, key, item) {
    if (item['scrap_date'] !== null) {
        return 'text-center darker-bg-td';
    } else {
        return 'text-center';
    }
}
```

**Vấn đề**: 
1. Frontend đang check `item['scrap_date']` - đây là field **gốc** từ database
2. Backend trả về cả `scrap_date` (gốc) và `scrap_date_custom` (tính toán)
3. Frontend cần check `item['scrap_date_custom']` thay vì `item['scrap_date']` để sử dụng logic đúng từ backend
4. Nếu không sửa Frontend, dù Backend đã tính toán đúng `scrap_date_custom`, Frontend vẫn check field gốc → Logic vẫn SAI

### 2. Logic đúng theo SPEC

Theo yêu cầu của khách hàng:
> **想定では、廃車日を設定した日の翌日から廃車扱いにする**
> (Dự kiến là từ ngày SAU ngày đặt ngày hủy xe thì mới coi là xe bị hủy)

**Logic đúng phải là**: `scrap_date < today` (không bao gồm ngày hôm nay)

**Ví dụ đúng**:
- Hôm nay: 2025-12-26
- Khách đăng ký scrap_date = 2025-12-26 (hôm nay)
- Logic kiểm tra: 2025-12-26 < 2025-12-26 → FALSE
- Kết quả: Xe CHƯA BỊ BÔI XÁM (ĐÚNG!)

- Ngày mai: 2025-12-27
- Logic kiểm tra: 2025-12-26 < 2025-12-27 → TRUE
- Kết quả: Xe BỊ BÔI XÁM (ĐÚNG!)

### 3. Các file cần sửa

#### Backend (BE)

1. **app/Repositories/VehicleRepository.php**
   - Dòng 563: Trong hàm `getAllVehicle()` - scrap_date_custom calculation (number_plate search)
   - Dòng 569: Trong hàm `getAllVehicle()` - scrap_date_custom calculation (normal search)
   - Dòng 630: Trong hàm `getDashboardVehicle()` - scrap_date_custom calculation (number_plate search)
   - Dòng 636: Trong hàm `getDashboardVehicle()` - scrap_date_custom calculation (normal search)
   - Dòng 83: Trong hàm `paginate()` - hide_scrap_date filter logic
   - Dòng 525: Trong hàm `getAllVehicle()` - hide_scrap_date filter logic
   - Dòng 613: Trong hàm `getDashboardVehicle()` - hide_scrap_date filter logic

**Tổng cộng**: 7 vị trí cần sửa

#### Frontend (FE)

2. **resources/js/pages/VehicleMaster/index.vue**
   - Dòng 1359: Logic hiển thị màu xám - cần sửa từ `item['scrap_date']` thành `item['scrap_date_custom']`

**Vấn đề Frontend**:
- Backend trả về cả `scrap_date` (gốc từ DB) và `scrap_date_custom` (tính toán)
- Frontend hiện tại đang check `item['scrap_date']` (field gốc) → Logic SAI
- Cần sửa để check `item['scrap_date_custom']` (field đã được tính toán đúng)

**Lý do cần sửa Frontend**:
- Backend chỉ tính toán `scrap_date_custom` nhưng không override `scrap_date` gốc (vì form edit/create vẫn cần `scrap_date` gốc)
- Frontend cần sử dụng `scrap_date_custom` để hiển thị đúng
- Nếu không sửa Frontend, dù Backend đã sửa đúng, logic vẫn sẽ sai vì frontend check field gốc

**Giải pháp Frontend**:
- Sửa dòng 1359: `if (item['scrap_date'] !== null)` → `if (item['scrap_date_custom'] !== null)`
- Đây là thay đổi tối thiểu, chỉ 1 dòng code
- Không ảnh hưởng đến form edit/create (vẫn dùng `scrap_date` gốc)

## Câu trả lời cho khách hàng

### Tiếng Việt (để dev hiểu)
**Nguyên nhân**: 
Hệ thống hiện tại đang sử dụng logic sai khi kiểm tra ngày hủy xe. Logic hiện tại kiểm tra `scrap_date <= today` (nhỏ hơn hoặc BẰNG ngày hôm nay), nên khi khách hàng đăng ký ngày hủy xe là ngày hôm nay, xe sẽ bị bôi xám ngay lập tức.

**Khi nào sửa xong**:
Chúng tôi cần sửa logic trong cả backend (PHP) và frontend (JavaScript) để thay đổi điều kiện từ `<=` thành `<`. Việc sửa chữa sẽ mất khoảng 1-2 giờ để:
1. Sửa code trong 3 hàm của VehicleRepository
2. Test kỹ lưỡng với các trường hợp
3. Deploy lên production

### Tiếng Nhật (gửi cho khách hàng)

**原因について：**

現在のシステムでは、廃車日のチェックロジックに誤りがございます。

現在の実装：`廃車日 <= 今日` （廃車日が今日以前の場合、グレーアウト）
正しい実装：`廃車日 < 今日` （廃車日が今日より前の場合のみ、グレーアウト）

そのため、廃車日を本日の日付で登録された場合、即座にグレーアウトされてしまいます。

**例：**
- 本日：2025年12月26日
- 廃車日登録：2025年12月26日
- 現在の動作：即座にグレーアウト（❌ 誤り）
- 正しい動作：翌日（12月27日）からグレーアウト（✅ 正しい）

**修正時期について：**

こちらの問題は、バックエンド（PHP）とフロントエンド（JavaScript）の両方で修正が必要です。

修正作業時間：約1〜2時間
- コード修正（3箇所）
- テスト実施
- 本番環境へのデプロイ

修正完了後、横浜800い1757の車両を含め、すべての車両が正しく動作するようになります。

**ご確認事項：**
修正作業を開始してもよろしいでしょうか？

## Implementation Checklist

### Backend (BE)
- [x] Sửa logic trong `VehicleRepository::getAllVehicle()` - dòng 563, 569 (scrap_date_custom)
- [x] Sửa logic trong `VehicleRepository::getDashboardVehicle()` - dòng 630, 636 (scrap_date_custom)
- [x] Sửa logic trong `VehicleRepository::paginate()` - dòng 83 (hide_scrap_date filter)
- [x] Sửa logic filter hide_scrap_date trong `getAllVehicle()` - dòng 525
- [x] Sửa logic filter hide_scrap_date trong `getDashboardVehicle()` - dòng 613

### Frontend (FE)
- [ ] Sửa logic hiển thị trong `VehicleMaster/index.vue` - dòng 1359
  - [ ] Đổi `item['scrap_date']` thành `item['scrap_date_custom']`

### Testing
- [ ] Test với xe 横浜800い1757
- [ ] Test với các trường hợp:
  - [ ] scrap_date = hôm nay → không bôi xám
  - [ ] scrap_date = ngày mai → không bôi xám
  - [ ] scrap_date = hôm qua → bôi xám
  - [ ] scrap_date = null → không bôi xám
- [ ] Test form edit/create vẫn hoạt động bình thường (dùng scrap_date gốc)

### Deployment
- [ ] Deploy lên production
- [ ] Xác nhận lại với khách hàng

## Notes

- Issue này liên quan đến Issue #712 trong bảng quản lý task
- Cần kiểm tra xem có logic tương tự cho "退職者ユーザー" (retired users) không
- Sau khi sửa, cần thông báo cho khách hàng test lại

