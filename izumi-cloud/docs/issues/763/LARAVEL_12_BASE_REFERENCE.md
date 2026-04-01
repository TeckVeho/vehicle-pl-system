# Tham chiếu Base Laravel 12 → Áp dụng cho Izumi Cloud (BE)

Tài liệu tham khảo toàn bộ base Laravel 12 tại `E:\Docker_laravel\laravel_12_2026` để áp dụng cho dự án hiện tại `d:\CtyVeHo\izumi\cloud`. **Chưa viết code** — chỉ mapping và checklist.

---

## 1. Tổng quan hai base

| Hạng mục | Base Laravel 12 (tham chiếu) | Izumi Cloud (hiện tại) |
|----------|------------------------------|-------------------------|
| **Đường dẫn** | `E:\Docker_laravel\laravel_12_2026` | `d:\CtyVeHo\izumi\cloud` |
| **Laravel** | `laravel/framework: ^12.0` | `laravel/framework: ^11.36` |
| **PHP** | `^8.2` | `^8.2` |
| **Frontend** | Inertia + Vue + Vite + TypeScript | Laravel UI + Vue + Webpack/Mix (và Vite có thể đã có) |
| **Auth** | Laravel Fortify | JWT (tymon/jwt-auth) + Sanctum + session |
| **Cấu trúc app** | Thu gọn (Actions, Concerns, ít Providers) | Đầy đủ (Console/Kernel, Http/Kernel, nhiều Providers, Repositories, Services, …) |

---

## 2. Composer

### 2.1 Base L12 (tham chiếu)

- `laravel/framework`: **^12.0**
- `laravel/tinker`: ^2.10.1
- Không có: Sanctum, Dusk, Excel, JWT, Spatie Permission, Pusher, v.v. (base sạch)
- Dev: `laravel/boost`, `laravel/pail`, `laravel/pint`, `nunomaduro/collision`, `phpunit/phpunit` (phiên bản mới hơn)
- `minimum-stability`: **stable**
- Scripts: `setup`, `dev`, `dev:ssr`, `lint`, `test:lint`, `test`, `pre-package-uninstall`

### 2.2 Izumi Cloud (hiện tại) – cần chỉnh khi nâng L12

- Giữ toàn bộ package nghiệp vụ (Sanctum, Excel, JWT, Spatie, S3, OpenAI, …).
- Nâng: `laravel/framework` lên **^12.0**; các package `laravel/*` lên phiên bản tương thích L12.
- Kiểm tra từng package bên thứ ba (maatwebsite/excel, spatie/laravel-permission, tymon/jwt-auth, …) có hỗ trợ L12 chưa.
- Có thể bổ sung script `lint`, `test` giống base L12 (tùy chọn).

---

## 3. Cấu trúc thư mục & file khác biệt

### 3.1 Chỉ có trong Base L12 (tham chiếu)

- `AGENTS.md`, `boost.json`, `components.json`, `pint.json`, `.prettierrc`, `.prettierignore`
- `app/Actions/Fortify/`, `app/Concerns/`, `app/Http/Requests/Settings/`
- `app/Http/Middleware/HandleAppearance.php`, `HandleInertiaRequests.php`
- `app/Providers/FortifyServiceProvider.php`
- `config/fortify.php`, `config/inertia.php`
- `routes/settings.php`
- `vite.config.ts`, `tsconfig.json`, `eslint.config.js`
- Không có: `app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php` (L12 dùng bootstrap + config)
- Không có: `RouteServiceProvider`, `AuthServiceProvider`, `EventServiceProvider` trong config (chỉ `AppServiceProvider` + `FortifyServiceProvider` trong `bootstrap/providers.php`)

### 3.2 Chỉ có trong Izumi Cloud (giữ nguyên, không xóa theo L12)

- `app/Console/Kernel.php` (schedule + commands) → **sẽ chuyển** schedule sang `routes/console.php`, commands load từ thư mục như hiện tại.
- `app/Http/Kernel.php` → **sẽ bỏ** khi middleware chuyển hết vào `bootstrap/app.php` (giống L12).
- `app/Providers/RouteServiceProvider.php`, `AuthServiceProvider.php`, `EventServiceProvider.php` → **tích hợp** logic vào `bootstrap/app.php` + `AppServiceProvider` (hoặc giữ provider nhưng đăng ký trong `bootstrap/providers.php` thay vì `config/app.php`).
- Toàn bộ: `app/Repositories/`, `app/Services/`, `app/Enums/`, `app/Helpers/`, `app/Jobs/`, `app/Mail/`, `app/Imports/`, `app/Exports/`, `app/Events/`, `app/Policies/`, `app/Rules/`, v.v.
- `app/constants.php`, `app/Exceptions/Handler.php` (nếu L12 vẫn hỗ trợ custom exception trong `bootstrap/app.php` thì có thể giữ).
- `config/` riêng: `broadcasting.php`, `chunk-upload.php`, `common.php`, `cors.php`, `excel.php`, `google.php`, `jwt.php`, `permission.php`, `l5-swagger.php`, v.v. — **giữ nguyên**.

---

## 4. Bootstrap

### 4.1 `bootstrap/app.php`

**Base L12:**

- `Application::configure(basePath: dirname(__DIR__))`
- `withRouting(web, commands, health: '/up')` — không có `api` (base không dùng API).
- `withMiddleware(function (Middleware $middleware): void { ... })` — middleware đăng ký trong closure (encryptCookies except, web append).
- `withExceptions(function (Exceptions $exceptions): void { ... })` — rỗng.
- **Không** load `Http/Kernel`.

**Izumi Cloud hiện tại:**

- Đã dùng `Application::configure` và `withRouting(web, api, commands, health)`.
- Middleware đăng ký trong closure (web, api, alias: auth, role, permission, …).
- `withExceptions` dùng `ResponseService::handlerInstanceofEx`.
- **Vẫn có** `app/Http/Kernel.php` nhưng **không được bootstrap/app.php gọi** — có thể là legacy; khi nâng L12 nên **xóa Kernel** và đảm bảo mọi thứ nằm trong `bootstrap/app.php`.

**Áp dụng:**

- Giữ `withRouting(web, api, commands, health)`.
- Giữ toàn bộ middleware alias (auth, role, permission, log.crud, auth.other, …) trong `withMiddleware`.
- Giữ `withExceptions` hiện tại (custom handler).
- Tham khảo L12: cấu trúc closure, `encryptCookies(except: [...])`, `trustProxies(at: '*')` nếu cần.
- Sau khi chắc chắn không còn tham chiếu tới `Http\Kernel`: xóa `app/Http/Kernel.php`.

### 4.2 `bootstrap/providers.php`

**Base L12:**

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
];
```

**Izumi Cloud hiện tại:**

- File `bootstrap/providers.php` chỉ có `AppServiceProvider`.
- Các provider khác (Auth, Event, Route) đang trong `config/app.php` → mảng `'providers'`.

**Áp dụng:**

- Chuyển toàn bộ provider từ `config/app.php` sang `bootstrap/providers.php` (L11/L12 chuẩn).
- Danh sách gợi ý: `AppServiceProvider`, `AuthServiceProvider`, `EventServiceProvider`, `RouteServiceProvider` (hoặc bỏ RouteServiceProvider và khai báo route trong bootstrap/app.php giống L12 — xem mục 7).

---

## 5. Entry points

### 5.1 `artisan`

**Base L12:**

```php
$app = require_once __DIR__.'/bootstrap/app.php';
$status = $app->handleCommand(new ArgvInput);
exit($status);
```

**Izumi Cloud:** Đã dùng `(require_once ...)->handleCommand(new ArgvInput)` — tương đương, chỉ cần đảm bảo sau nâng L12 vẫn gọi đúng.

### 5.2 `public/index.php`

**Base L12:**

```php
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->handleRequest(Request::capture());
```

**Izumi Cloud:** Đã dùng `(require_once ...)->handleRequest(Request::capture())` — giống. Không cần đổi.

---

## 6. Config

### 6.1 `config/app.php`

**Base L12:**

- **Không có** `'providers'` — provider đăng ký trong `bootstrap/providers.php`.
- **Không có** `'aliases'` — Laravel 11+ auto-discovery facades.
- Có: `previous_keys` (array từ env), `maintenance` với `driver` và `store`.
- Có: `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE`.
- **Không có** `asset_url`, `timezone` trong snippet đã xem — có thể có ở file đầy đủ; Izumi cần giữ `APP_TIMEZONE` nếu đang dùng.

**Izumi Cloud hiện tại:**

- Có `'providers'` (Illuminate + App providers) và `'aliases'`.
- Có `asset_url`, `timezone`, `previous_keys`, `maintenance`.

**Áp dụng:**

- Xóa `'providers'` và `'aliases'` khỏi `config/app.php` (chuyển providers sang `bootstrap/providers.php`).
- Giữ các mục nghiệp vụ: `timezone`, `asset_url`, `locale`, v.v.
- Thêm/cập nhật `maintenance` theo L12 nếu có thay đổi.

### 6.2 Config chỉ có trong L12 (tham chiếu)

- `config/fortify.php`, `config/inertia.php` — chỉ cần nếu dự án sau này dùng Fortify/Inertia.

### 6.3 Config chỉ có trong Izumi (giữ)

- `broadcasting`, `chunk-upload`, `common`, `cors`, `excel`, `google`, `hashing`, `imap`, `jwt`, `king_of_time_conf`, `l5-swagger`, `laratrust`, `laratrust_seeder`, `line_works_conf`, `permission`, `s3logger`, `scrape_dusk`, `sentry`, `shipping`, `view` — giữ nguyên.

---

## 7. Routing và RouteServiceProvider

**Base L12:**

- Không có `RouteServiceProvider`.
- Route được đăng ký trong `bootstrap/app.php`: `withRouting(web: routes/web.php, commands: routes/console.php, health: '/up')`.
- Base không có `routes/api.php`.

**Izumi Cloud:**

- Có `RouteServiceProvider` đăng ký `routes/api.php` (prefix `api`, middleware `api`) và `routes/web.php` (middleware `web`).
- `RouteServiceProvider::HOME` được dùng trong `RedirectIfAuthenticated` (`/home`).

**Áp dụng:**

- **Cách 1:** Giữ `RouteServiceProvider`, chỉ đăng ký nó trong `bootstrap/providers.php` thay vì `config/app.php`; đảm bảo `withRouting` trong bootstrap **không** trùng khai báo route (L11 thường vẫn load route qua RouteServiceProvider).
- **Cách 2 (giống L12 hơn):** Bỏ `RouteServiceProvider`, khai báo route trong `bootstrap/app.php`:
  - `withRouting(web: ..., api: ..., commands: ..., health: '/up')` với đường dẫn tới `routes/web.php`, `routes/api.php`, `routes/console.php`.
  - Chuyển logic `configureRateLimiting()` (RateLimiter::for('api', ...)) vào `AppServiceProvider::boot()` hoặc file route.
- Khi bỏ RouteServiceProvider: thay `RouteServiceProvider::HOME` bằng config (ví dụ `config('app.home', '/home')`) hoặc constant trong `AppServiceProvider` / helper.

---

## 8. Middleware và Http/Kernel

**Base L12:** Không có `app/Http/Kernel.php`. Toàn bộ trong `bootstrap/app.php` → `withMiddleware(...)`.

**Izumi Cloud:**

- Có `app/Http/Kernel.php` (middleware groups `web`, `api`, route middleware alias) nhưng **không** được `bootstrap/app.php` load (bootstrap đã định nghĩa middleware riêng).
- Middleware thực tế đang dùng nằm trong `bootstrap/app.php`.

**Áp dụng:**

- Đảm bảo mọi alias và group trong Kernel đã có trong `bootstrap/app.php`.
- Sau đó xóa `app/Http/Kernel.php`.
- Kiểm tra toàn codebase không còn `Kernel` hoặc `Http\Kernel` (grep).

---

## 9. Schedule và Console/Kernel

**Base L12:** Không có `app/Console/Kernel.php`. Chỉ có `routes/console.php` với Artisan::command('inspire', ...).

**Izumi Cloud:**

- **app/Console/Kernel.php:** định nghĩa `schedule()` (rất nhiều job/command) và `commands()` (load thư mục Commands + require routes/console.php).
- **routes/console.php:** vừa có `Schedule::...` (trùng với Kernel) vừa có `Artisan::command('inspire')`.

**Áp dụng:**

- Laravel 11+ cho phép schedule trong `routes/console.php` qua `Schedule::` facade.
- Giữ **một nơi** định nghĩa schedule: nên **gộp hết vào `routes/console.php`** (xóa phần schedule trong `app/Console/Kernel.php`), giữ lại trong Kernel chỉ phần `commands()` (load Commands và require console routes) — **hoặc** chuyển hết load commands vào bootstrap (L12 style) và bỏ Console/Kernel.
- Tra cứu Laravel 12 doc: đăng ký schedule có thể chỉ trong `routes/console.php`; đăng ký command có thể qua `App\Console\Kernel` hoặc chỉ load thư mục. Nếu L12 không dùng Console Kernel thì cần đăng ký schedule + load commands ở chỗ khác (ví dụ `AppServiceProvider` hoặc `bootstrap/app.php`). **Tham chiếu:** base L12 không có schedule phức tạp nên chỉ cần `routes/console.php`; Izumi cần giữ toàn bộ schedule trong `routes/console.php` và đảm bảo commands vẫn được load (bằng cách giữ Console/Kernel chỉ phần `commands()` hoặc dùng cơ chế L12 nếu có).

---

## 10. AuthServiceProvider và EventServiceProvider

**Base L12:** Không có hai provider này (Fortify tự xử lý auth UI).

**Izumi Cloud:**

- **AuthServiceProvider:** đăng ký policies (Role → RolePolicy), `registerPolicies()`.
- **EventServiceProvider:** `$listen` (Registered → SendEmailVerificationNotification).

**Áp dụng:**

- **AuthServiceProvider:** Giữ và đăng ký trong `bootstrap/providers.php`. Hoặc chuyển `Gate::policy()` vào `AppServiceProvider::boot()` rồi xóa AuthServiceProvider.
- **EventServiceProvider:** Giữ và đăng ký trong `bootstrap/providers.php`. Hoặc đăng ký event/listener trong `AppServiceProvider` hoặc dùng discovery.

---

## 11. AppServiceProvider

**Base L12:** Có `configureDefaults()`: `Date::use(CarbonImmutable::class)`, `DB::prohibitDestructiveCommands(app()->isProduction())`, `Password::defaults(...)`.

**Izumi Cloud:** Cần đọc nội dung hiện tại; có thể bổ sung các default tương tự (Date, DB, Password) nếu muốn đồng bộ hành vi với base L12.

---

## 12. .env / .env.example

**Base L12:** Có `APP_MAINTENANCE_DRIVER`, `APP_MAINTENANCE_STORE` (comment), `BCRYPT_ROUNDS`, `SESSION_ENCRYPT`, `REDIS_CLIENT`, v.v. Không có biến nghiệp vụ.

**Izumi Cloud:** Nhiều biến riêng (DB, AUTH_GUARD, mail, AWS, JWT, …). Giữ nguyên; chỉ thêm/sửa theo L12 nếu có biến mới (ví dụ maintenance store).

---

## 13. Database, Logging, Queue, Session

- So sánh từng file `config/database.php`, `config/logging.php`, `config/queue.php`, `config/session.php` giữa L12 base và Izumi: chỉ cập nhật cấu trúc mới của L12 (nếu có), giữ nguyên connection/queue/session nghiệp vụ.

---

## 14. Public

- **Base L12:** `public/index.php` như trên; có `.htaccess`, favicon, robots.txt.
- **Izumi Cloud:** Có `web.config` (IIS). Giữ `web.config`; có thể tham khảo `.htaccess` của L12 nếu cần hỗ trợ Apache.

---

## 15. Checklist áp dụng (chưa code – chỉ tham chiếu)

- [ ] **Composer:** Nâng `laravel/framework` lên ^12.0; kiểm tra và nâng các package Laravel + bên thứ ba tương thích L12.
- [ ] **Bootstrap:** Chuyển toàn bộ provider từ `config/app.php` sang `bootstrap/providers.php`.
- [ ] **Config app:** Xóa `providers` và `aliases` khỏi `config/app.php`; giữ timezone, locale, asset_url, maintenance, previous_keys.
- [ ] **Middleware:** Đảm bảo mọi thứ trong `app/Http/Kernel.php` đã có trong `bootstrap/app.php`, sau đó xóa `app/Http/Kernel.php`.
- [ ] **Route:** Quyết định giữ hay bỏ `RouteServiceProvider`; nếu bỏ thì khai báo api/web trong `withRouting` và chuyển rate limiting + HOME sang AppServiceProvider/config.
- [ ] **Schedule:** Gộp schedule vào một nơi (`routes/console.php`); quyết định giữ hay bỏ `app/Console/Kernel.php` và cách load commands theo L12.
- [ ] **AuthServiceProvider / EventServiceProvider:** Giữ và đăng ký trong `bootstrap/providers.php`, hoặc gộp logic vào AppServiceProvider.
- [ ] **AppServiceProvider:** Tham khảo L12 (Date, DB::prohibitDestructiveCommands, Password::defaults) và bổ sung nếu cần.
- [ ] **Exception handling:** Giữ custom trong `withExceptions`; kiểm tra L12 có thay đổi gì với exception handler.
- [ ] **Env:** Thêm/sửa biến theo L12 (maintenance driver/store, v.v.); giữ nguyên biến nghiệp vụ.
- [ ] **Test & chạy thử:** Chạy `php artisan route:list`, `php artisan schedule:list`, test login/API, queue, schedule sau khi áp dụng.

---

## 16. File tham chiếu nhanh (Base L12)

| File | Mục đích tham chiếu |
|------|----------------------|
| `E:\Docker_laravel\laravel_12_2026\composer.json` | Phiên bản L12, scripts |
| `E:\Docker_laravel\laravel_12_2026\bootstrap\app.php` | withRouting, withMiddleware, withExceptions |
| `E:\Docker_laravel\laravel_12_2026\bootstrap\providers.php` | Danh sách provider |
| `E:\Docker_laravel\laravel_12_2026\config\app.php` | Không providers/aliases, maintenance, previous_keys |
| `E:\Docker_laravel\laravel_12_2026\artisan` | handleCommand |
| `E:\Docker_laravel\laravel_12_2026\public\index.php` | handleRequest |
| `E:\Docker_laravel\laravel_12_2026\app\Providers\AppServiceProvider.php` | configureDefaults (Date, DB, Password) |

Khi bắt đầu implement, ưu tiên: Composer + bootstrap/providers + config/app → middleware & Kernel → RouteServiceProvider & schedule → test toàn bộ.
