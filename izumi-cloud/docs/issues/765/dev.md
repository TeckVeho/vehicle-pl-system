# Issue #765 — Development Log (Child of #764)

**Parent:** [#764](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/764) — IC_Laravel version up  
**Child:** [#765](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/765) — [BE] IC Laravel 12 アップグレード / Nâng cấp Laravel 12 IC

---

## 1. Phạm vi thực hiện

- Cấu trúc Laravel 12: bootstrap/providers, config/app (bỏ providers/aliases), xóa Http Kernel, gộp schedule vào routes/console, cập nhật composer.json.
- **Chưa chạy `composer update`** — cần chạy thủ công và xử lý conflict/deprecated sau khi review.

---

## 2. Thay đổi đã thực hiện

### 2.1. `bootstrap/providers.php`
- Thêm: `AuthServiceProvider`, `EventServiceProvider`, `RouteServiceProvider`.
- Thứ tự: AppServiceProvider → AuthServiceProvider → EventServiceProvider → RouteServiceProvider.

### 2.2. `config/app.php`
- Xóa toàn bộ mảng `'providers'` và `'aliases'`.
- Giữ: name, env, debug, url, asset_url, timezone, locale, fallback_locale, faker_locale, cipher, key, previous_keys, maintenance.

### 2.3. `app/Http/Kernel.php`
- **Đã xóa.** Middleware thực tế nằm trong `bootstrap/app.php`; không có tham chiếu tới Http\Kernel trong codebase.

### 2.4. `app/Console/Kernel.php`
- Xóa toàn bộ nội dung `schedule()` (schedule được định nghĩa trong `routes/console.php`).
- Giữ `commands()` (load `app/Console/Commands`, require `routes/console.php`).
- Bỏ các use không dùng (Commands, Jobs, DataConnection, Arr, Carbon).

### 2.5. `routes/console.php`
- Thêm `use App\Console\Commands\UpdateRetirementDateCommand`.
- Thêm: `Schedule::command(UpdateRetirementDateCommand::class)->daily()->withoutOverlapping();`
- Thêm `->withoutOverlapping()` cho `JapanHolidayCommand::class`.

### 2.6. `composer.json`
- `laravel/framework`: ^11.36 → **^12.0**
- `laravel/tinker`: ^2.9 → **^2.10**
- `laravel/pail`: ^1.1 → **^1.2**
- `laravel/pint`: ^1.13 → **^1.24**
- `laravel/sail`: ^1.26 → **^1.41**
- `nunomaduro/collision`: ^8.1 → **^8.6**
- `phpunit/phpunit`: ^11.0.1 → **^11.5**

### 2.7. Cache bootstrap
- Xóa `bootstrap/cache/config.php` và `bootstrap/cache/services.php` (cache cũ chứa LanguageServiceProvider không tồn tại, gây lỗi khi chạy artisan).

---

## 3. Kiểm tra sau thay đổi

- `php artisan config:clear` — chạy thành công.
- `php artisan route:list` — hiển thị đầy đủ route (web, api).
- `php artisan schedule:list` — hiển thị đầy đủ schedule (data_connection, update_retirement_date, SaveItpS3Data, v.v.).

---

## 4. Việc cần làm tiếp (sau dev)

1. **Chạy `composer update`** (hoặc `composer update laravel/framework --with-all-dependencies`) để nâng vendor lên Laravel 12. Có thể phát sinh conflict với các package bên thứ ba (maatwebsite/excel, spatie/laravel-permission, tymon/jwt-auth, l5-swagger, …); cần xử lý theo từng báo lỗi và [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade).
2. Chạy test suite (`php artisan test` hoặc phpunit) sau khi composer update xong.
3. Kiểm tra thủ công: đăng nhập, API, queue, schedule chạy đúng.

---

## 5. File đã sửa / xóa

| File | Hành động |
|------|-----------|
| `bootstrap/providers.php` | Sửa (thêm 3 provider) |
| `config/app.php` | Sửa (xóa providers, aliases) |
| `app/Http/Kernel.php` | Xóa |
| `app/Console/Kernel.php` | Sửa (rỗng schedule, giữ commands) |
| `routes/console.php` | Sửa (thêm UpdateRetirementDateCommand, withoutOverlapping JapanHoliday) |
| `composer.json` | Sửa (L12 + package versions) |
| `bootstrap/cache/config.php` | Xóa (cache cũ) |
| `bootstrap/cache/services.php` | Xóa (cache cũ) |

---

## 6. Trạng thái

- **Chưa commit** (đúng quy trình /dev).
- Cấu trúc L12 (bootstrap + config) đã áp dụng; ứng dụng vẫn chạy trên Laravel 11 từ vendor cho đến khi chạy `composer update`.
