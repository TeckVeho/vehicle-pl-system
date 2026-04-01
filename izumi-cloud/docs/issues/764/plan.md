# Issue #764: IC_Laravel version up - Implementation Plan

## 概要 (Overview)

Nâng cấp Laravel từ ^11.36 lên ^12.0 cho dự án Izumi Cloud (IC). Plan dựa trên toàn bộ phân tích trong **docs/issues/763/issue.md** và **docs/issues/763/LARAVEL_12_BASE_REFERENCE.md**. Đây là thay đổi **Backend (framework/bootstrap/config)**; không thay đổi nghiệp vụ FE/BE ứng dụng ngoài các breaking changes bắt buộc. Trạng thái hiện tại: Laravel 11, providers trong config/app.php, có Http/Kernel và Console/Kernel. Trạng thái mục tiêu: Laravel 12, cấu trúc L12 (providers trong bootstrap/providers.php, không dùng Http/Kernel, config/app không còn providers/aliases), toàn bộ package nghiệp vụ tương thích L12.

---

## FE (Frontend)

**変更なし (No frontend changes).**  
Laravel version up chỉ ảnh hưởng backend/framework. Frontend (Vue, assets, Vite/Webpack) giữ nguyên trừ khi Laravel 12 hoặc package (ví dụ laravel/ui) có breaking change yêu cầu chỉnh build/config.

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `composer.json`

##### 1.1.1. Nâng laravel/framework và các package Laravel lên phiên bản tương thích L12

**現在の実装:**

- `laravel/framework`: ^11.36
- Các package: laravel/sanctum, laravel/tinker, laravel/ui, laravel/dusk, v.v.

**変更内容:**

- Đổi `laravel/framework` thành `^12.0`.
- Kiểm tra và nâng phiên bản: `laravel/sanctum`, `laravel/tinker`, `laravel/ui`, `laravel/dusk`, `laravel/pail`, `laravel/pint`, `nunomaduro/collision`, `phpunit/phpunit` theo [Laravel 12 Upgrade Guide](https://laravel.com/docs/upgrade) và packagist.
- Kiểm tra compatibility từng package bên thứ ba: `maatwebsite/excel`, `spatie/laravel-permission`, `tymon/jwt-auth`, `darkaonline/l5-swagger`, `league/flysystem-aws-s3-v3`, `openai-php/client`, v.v. — nâng version nếu có bản hỗ trợ L12.
- Giữ nguyên toàn bộ package nghiệp vụ; chỉ sửa constraint khi cần.
- (Tùy chọn) Thêm script `lint`, `test` giống base L12; đặt `minimum-stability` thành `stable` nếu hiện tại là `dev`.

##### 1.1.2. Chạy composer update và xử lý lỗi

- Chạy `composer update` (hoặc `composer update laravel/framework --with-all-dependencies`).
- Xử lý conflict / deprecated / breaking changes theo thông báo composer và upgrade guide.

---

#### 1.2. File: `config/app.php`

##### 1.2.1. Xóa mảng `providers` và `aliases` (Laravel 11+ style)

**既存コード** (khoảng dòng 127–234):

- Mảng `'providers'` chứa Illuminate providers + App\Providers (AppServiceProvider, AuthServiceProvider, EventServiceProvider, RouteServiceProvider).
- Mảng `'aliases'` chứa facade (App, Arr, Artisan, …).

**変更内容:**

- Xóa toàn bộ block `'providers' => [ ... ]` (từ comment "Autoloaded Service Providers" đến hết mảng).
- Xóa toàn bộ block `'aliases' => [ ... ]` (từ comment "Class Aliases" đến hết mảng).
- Giữ nguyên: `name`, `env`, `debug`, `url`, `asset_url`, `timezone`, `locale`, `fallback_locale`, `faker_locale`, `cipher`, `key`, `previous_keys`, `maintenance`.
- Không thêm provider/alias vào config; chúng sẽ đăng ký qua `bootstrap/providers.php` và facade auto-discovery.

---

#### 1.3. File: `bootstrap/providers.php`

##### 1.3.1. Đăng ký toàn bộ App Service Providers

**既存コード** (line 1–5):

- Chỉ có `App\Providers\AppServiceProvider::class`.

**変更内容:**

- Thêm lần lượt: `AuthServiceProvider`, `EventServiceProvider`, `RouteServiceProvider` (nếu vẫn giữ RouteServiceProvider — xem 1.6).
- Kết quả gợi ý:
  ```php
  return [
      App\Providers\AppServiceProvider::class,
      App\Providers\AuthServiceProvider::class,
      App\Providers\EventServiceProvider::class,
      App\Providers\RouteServiceProvider::class,
  ];
  ```
- Đảm bảo thứ tự: AppServiceProvider trước, các provider còn lại sau (RouteServiceProvider thường cuối để load route).

---

#### 1.4. File: `bootstrap/app.php`

##### 1.4.1. Đảm bảo withRouting và withMiddleware đủ; không tham chiếu Http Kernel

**現在の実装** (line 10–50):

- `withRouting(web, api, commands, health)` — đã đúng.
- `withMiddleware`: groups `web`, `api` và alias (auth, role, permission, log.crud, auth.other, …), `trustProxies(at: '*')`.
- `withExceptions`: custom `ResponseService::handlerInstanceofEx`.

**変更内容:**

- Không bắt buộc sửa nếu đã đủ; chỉ rà soát theo LARAVEL_12_BASE_REFERENCE:
  - Nếu thiếu middleware so với `app/Http/Kernel.php` thì bổ sung vào closure.
  - Có thể thêm `encryptCookies(except: [...])` nếu cần (L12 base có).
- Không load hoặc tham chiếu `App\Http\Kernel` trong file này (hiện tại đã không load).

##### 1.4.2. (Tùy chọn) Bỏ RouteServiceProvider và khai báo route trực tiếp

- Chỉ thực hiện nếu chọn **Cách 2** trong tài liệu 763: bỏ RouteServiceProvider.
- Khi đó: route đã được load bởi `withRouting(web: ..., api: ...)` nên có thể xóa RouteServiceProvider khỏi `bootstrap/providers.php` và chuyển `configureRateLimiting()` sang `AppServiceProvider::boot()`; đồng thời xử lý `RouteServiceProvider::HOME` (xem 1.6).

---

#### 1.5. File: `app/Http/Kernel.php`

##### 1.5.1. Xóa file sau khi đã đảm bảo bootstrap/app.php có đủ middleware

**現在の実装:**

- Định nghĩa `$middleware`, `$middlewareGroups` (web, api), `$routeMiddleware` (alias). File **không** được `bootstrap/app.php` gọi (middleware thực tế nằm trong bootstrap).

**変更内容:**

- Grep toàn codebase: không còn `use App\Http\Kernel`, `HttpKernel`, hoặc `Kernel` (trong ngữ cảnh HTTP).
- So sánh từng middleware trong Kernel với `bootstrap/app.php`; nếu thiếu thì bổ sung vào bootstrap.
- Sau đó **xóa file** `app/Http/Kernel.php`.

---

#### 1.6. File: `app/Providers/RouteServiceProvider.php` và tham chiếu HOME

##### 1.6.1. Quyết định giữ hay bỏ RouteServiceProvider

**現在の実装:**

- `boot()`: `configureRateLimiting()` và đăng ký route `api` (prefix api, middleware api), `web` (middleware web). Route đã được khai báo trong `bootstrap/app.php` qua `withRouting` nên có thể trùng hoặc RouteServiceProvider vẫn chạy — cần kiểm tra L11 behavior: với `withRouting` có thể route chỉ load từ bootstrap, RouteServiceProvider chỉ còn rate limiting.
- Constant `HOME = '/home'` được dùng trong `app/Http/Middleware/RedirectIfAuthenticated.php` (line 26).

**変更内容:**

- **Cách 1 (giữ):** Giữ RouteServiceProvider; chỉ đảm bảo nó được đăng ký trong `bootstrap/providers.php`. Nếu L12/bootstrap không gọi `Route::` từ RouteServiceProvider nữa thì chuyển `configureRateLimiting()` sang `AppServiceProvider::boot()` (gọi `RateLimiter::for('api', ...)`) và giữ RouteServiceProvider chỉ để định nghĩa HOME hoặc chuyển HOME sang config.
- **Cách 2 (bỏ):** Xóa RouteServiceProvider; khai báo rate limiting trong `AppServiceProvider::boot()`; thay `RouteServiceProvider::HOME` bằng config: thêm `'home' => env('APP_HOME', '/home')` vào `config/app.php` và trong `RedirectIfAuthenticated` dùng `redirect(config('app.home', '/home'))`; sau đó xóa `RouteServiceProvider` khỏi `bootstrap/providers.php` và có thể xóa file `app/Providers/RouteServiceProvider.php`.

---

#### 1.7. File: `app/Http/Middleware/RedirectIfAuthenticated.php`

##### 1.7.1. Thay RouteServiceProvider::HOME bằng config (nếu bỏ RouteServiceProvider)

**既存コード** (line 26):

- `return redirect(RouteServiceProvider::HOME);`

**変更内容:**

- Chỉ sửa nếu đã bỏ RouteServiceProvider: đổi thành `return redirect(config('app.home', '/home'));` và đảm bảo `config/app.php` có key `'home'` (hoặc dùng constant trong AppServiceProvider).

---

#### 1.8. File: `app/Console/Kernel.php`

##### 1.8.1. Chuyển schedule sang routes/console.php; giữ hoặc bỏ Kernel

**現在の実装:**

- `schedule()`: rất nhiều Schedule::command / Schedule::job (DataConnection, JapanHoliday, DeleteFileExpired, Sync*, …).
- `commands()`: load `__DIR__.'/Commands'` và `require base_path('routes/console.php')`.

**変更内容:**

- **Schedule:** Toàn bộ logic trong `schedule()` đã có bản sao trong `routes/console.php` (Schedule::...). Đảm bảo `routes/console.php` chứa đầy đủ schedule (không trùng lặp với Kernel); xóa phần `schedule()` trong `app/Console/Kernel.php` hoặc xóa hẳn Kernel và chỉ dùng `routes/console.php` cho schedule.
- **Commands:** Laravel 11+ vẫn hỗ trợ `App\Console\Kernel` để load commands. Nếu giữ Kernel: chỉ giữ lại `commands()` (load Commands, require console.php), xóa `schedule()`. Nếu bỏ Kernel: cần đăng ký load commands ở nơi khác (Laravel 12 có thể auto-load thư mục `app/Console/Commands` qua bootstrap) — tra cứu doc L12. Khuyến nghị: **giữ Console/Kernel** chỉ với `commands()` và bỏ `schedule()` để tránh trùng với `routes/console.php`.

---

#### 1.9. File: `routes/console.php`

##### 1.9.1. Đảm bảo schedule đầy đủ và thống nhất với Console/Kernel (trước khi xóa schedule trong Kernel)

**現在の実装:**

- Đã có Schedule::command / Schedule::job (DataConnection, JapanHoliday, DeleteFileExpired, Sync*, FetchS3FolderJob, …) và Artisan::command('inspire').

**変更内容:**

- So sánh với `app/Console/Kernel.php` method `schedule()`: đảm bảo mọi job/command trong Kernel đều có trong `routes/console.php` (kể cả `UpdateRetirementDateCommand`, `withoutOverlapping`, `runInBackground`, v.v.).
- Nếu thiếu thì bổ sung; sau đó có thể xóa `schedule()` trong Kernel (task 1.8.1).

---

#### 1.10. File: `app/Providers/AppServiceProvider.php`

##### 1.10.1. (Tùy chọn) Thêm configureDefaults kiểu L12

**現在の実装:**

- `register()`: bind Repository interfaces; đăng ký DuskServiceProvider khi local/testing.
- `boot()`: `umask(0002)`.
- `$singletons`: ExceptionHandlerContract => Handler::class.

**変更内容:**

- Có thể thêm method `configureDefaults()` và gọi từ `boot()`: `Date::use(CarbonImmutable::class)`, `DB::prohibitDestructiveCommands(app()->isProduction())`, `Password::defaults(...)` như base L12 — tùy nhu cầu dự án.
- Nếu chọn bỏ RouteServiceProvider: thêm vào `boot()` gọi `RateLimiter::for('api', function (Request $request) { ... })` (copy từ RouteServiceProvider::configureRateLimiting).

---

#### 1.11. File: `.env.example` (và tài liệu .env)

##### 1.11.1. Thêm/sửa biến theo Laravel 12

**変更内容:**

- Thêm hoặc cập nhật theo L12: `APP_MAINTENANCE_DRIVER`, `APP_MAINTENANCE_STORE` (nếu dùng).
- Giữ nguyên mọi biến nghiệp vụ (DB_*, AUTH_GUARD, MAIL_*, AWS_*, JWT, v.v.).

---

#### 1.12. Kiểm tra Exception handling và các config khác

##### 1.12.1. Exception và config database/queue/session/logging

- **Exceptions:** Giữ nguyên `withExceptions` trong `bootstrap/app.php` (ResponseService::handlerInstanceofEx). Kiểm tra Laravel 12 có thay đổi signature hoặc cách đăng ký.
- **Config:** So sánh `config/database.php`, `config/queue.php`, `config/session.php`, `config/logging.php` với base L12 (nếu có file mẫu); chỉ cập nhật cấu trúc mới, giữ nguyên connection/channel nghiệp vụ.
- **app/Exceptions/Handler.php:** AppServiceProvider đang bind `ExceptionHandlerContract::class => Handler::class`; giữ nguyên trừ khi L12 deprecate hoặc đổi cách xử lý.

---

## 実装順序 (Implementation Order)

1. **Composer & dependencies**
   - Cập nhật `composer.json` (1.1.1); chạy `composer update` và xử lý lỗi (1.1.2).

2. **Bootstrap & Config (không phụ thuộc code nghiệp vụ)**
   - Chuyển providers: sửa `config/app.php` (1.2.1) và `bootstrap/providers.php` (1.3.1).
   - Rà soát `bootstrap/app.php` (1.4.1).

3. **Kernel & Middleware**
   - Đảm bảo bootstrap có đủ middleware; xóa `app/Http/Kernel.php` (1.5.1).

4. **RouteServiceProvider & HOME**
   - Quyết định giữ/bỏ RouteServiceProvider; nếu bỏ thì sửa RedirectIfAuthenticated (1.6.1, 1.7.1) và AppServiceProvider rate limiting (1.10.1).

5. **Schedule & Console**
   - Đồng bộ schedule giữa `routes/console.php` và Console/Kernel; bỏ schedule trong Kernel hoặc giữ chỉ commands() (1.8.1, 1.9.1).

6. **AppServiceProvider & Env**
   - (Tùy chọn) configureDefaults và rate limiting (1.10.1); cập nhật .env.example (1.11.1).

7. **Exception & config khác**
   - Kiểm tra exception handler và config (1.12.1).

8. **統合テスト**
   - `php artisan config:clear`, `php artisan route:list`, `php artisan schedule:list`, chạy test suite; test login/API/queue/schedule thủ công.

---

## 見積もり工数 (Estimated Effort)

- **Backend (Composer & dependency):** 1–2 時間 — nâng version, composer update, xử lý conflict/deprecated.
- **Backend (Config & Bootstrap):** 1–1.5 時間 — config/app, providers.php, bootstrap/app.
- **Backend (Kernel, Route, Schedule):** 1.5–2 時間 — xóa Http/Kernel, RouteServiceProvider/HOME, Console/Kernel schedule.
- **Backend (AppServiceProvider, Env, Exception):** 0.5–1 時間.
- **Test & fix:** 1–2 時間.

**合計**: 5–8.5 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:** Không thay đổi logic nghiệp vụ; chỉ cấu trúc framework. Auto-discovery facades và provider load có thể nhẹ hơn.
2. **既存機能との互換性:** Giữ nguyên toàn bộ middleware alias (auth, role, permission, log.crud, auth.other); giữ Repository bindings và Exception handler; không đổi route hay API contract.
3. **データ整合性:** Không thay đổi database schema hay migration; chỉ cấu hình và bootstrap.
4. **参照ドキュメント:** Luôn đối chiếu với `docs/issues/763/LARAVEL_12_BASE_REFERENCE.md` và [Laravel 12 Upgrade Guide](https://laravel.com/docs/upgrade).

---

**Issue:** 764  
**Output:** `docs/issues/764/plan.md`
