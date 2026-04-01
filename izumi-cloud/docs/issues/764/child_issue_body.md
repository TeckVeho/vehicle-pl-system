## 日本語 / Japanese

### 親Issue
Parent: #764

### 説明
IC（Izumi Cloud）のLaravel 11→12 アップグレードに伴うバックエンド対応を一括で実施する子issueです。Composer依存関係の更新、config/app.php の providers/aliases 削除と bootstrap/providers.php への移行、Http Kernel 削除、RouteServiceProvider と HOME の扱い、Schedule と Console Kernel の整理、AppServiceProvider と .env の更新、例外・設定の確認までを含みます。

### 要件
- laravel/framework を ^12.0 に更新し、関連 Laravel パッケージ・サードパーティを L12 互換に更新する
- config/app.php から providers と aliases を削除し、bootstrap/providers.php で App/Auth/Event/Route ServiceProvider を登録する
- bootstrap/app.php の middleware を確認し、app/Http/Kernel.php を削除する
- RouteServiceProvider を維持するか廃止するか決定し、HOME 定数・RateLimiting を適切に移行する
- Schedule を routes/console.php に統一し、Console Kernel の schedule() を削除する（commands() は維持可）
- AppServiceProvider の configureDefaults・RateLimiting（廃止時）、.env.example の更新を行う
- 例外処理・database/queue/session/logging 設定を確認する
- 実装後、config:clear / route:list / schedule:list およびテスト・手動確認を行う

### 技術詳細
- 参照: docs/issues/763/LARAVEL_12_BASE_REFERENCE.md、docs/issues/764/plan.md
- Laravel 12 Upgrade Guide: https://laravel.com/docs/upgrade
- 変更対象: composer.json, config/app.php, bootstrap/providers.php, bootstrap/app.php, app/Http/Kernel.php（削除）, app/Providers/RouteServiceProvider.php, app/Http/Middleware/RedirectIfAuthenticated.php, app/Console/Kernel.php, routes/console.php, app/Providers/AppServiceProvider.php, .env.example

### 受け入れ基準
- [ ] 実装完了（Composer 更新・Bootstrap/Config・Kernel/Route/Schedule・AppServiceProvider/Env）
- [ ] ユニットテスト作成・合格（該当する場合）
- [ ] プロジェクト規約に準拠
- [ ] php artisan route:list / schedule:list が正常、既存 API/ログイン/queue/schedule が動作すること

### 依存関係
なし（親 #764 の最初の実装タスク）

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #764

### Mô tả
Child issue thực hiện toàn bộ phần backend cho nâng cấp Laravel 11→12 của IC (Izumi Cloud): cập nhật Composer, chuyển providers/aliases từ config/app.php sang bootstrap/providers.php, xóa Http Kernel, xử lý RouteServiceProvider và HOME, thống nhất Schedule với Console Kernel, cập nhật AppServiceProvider và .env, kiểm tra exception và config.

### Yêu cầu
- Nâng laravel/framework lên ^12.0 và cập nhật các package Laravel/bên thứ ba tương thích L12
- Xóa providers và aliases khỏi config/app.php; đăng ký App/Auth/Event/Route ServiceProvider trong bootstrap/providers.php
- Rà soát middleware trong bootstrap/app.php và xóa app/Http/Kernel.php
- Quyết định giữ hay bỏ RouteServiceProvider; chuyển HOME và RateLimiting phù hợp
- Thống nhất schedule trong routes/console.php, bỏ schedule() trong Console Kernel (giữ commands() nếu cần)
- Cập nhật AppServiceProvider (configureDefaults, rate limiting nếu bỏ RouteServiceProvider) và .env.example
- Kiểm tra exception handling và config database/queue/session/logging
- Sau khi implement: chạy config:clear, route:list, schedule:list và test/kiểm tra thủ công

### Chi tiết kỹ thuật
- Tham chiếu: docs/issues/763/LARAVEL_12_BASE_REFERENCE.md, docs/issues/764/plan.md
- Laravel 12 Upgrade Guide: https://laravel.com/docs/upgrade
- File thay đổi: composer.json, config/app.php, bootstrap/providers.php, bootstrap/app.php, app/Http/Kernel.php (xóa), RouteServiceProvider, RedirectIfAuthenticated, Console/Kernel.php, routes/console.php, AppServiceProvider, .env.example

### Tiêu chí chấp nhận
- [ ] Hoàn thành triển khai (Composer, Bootstrap/Config, Kernel/Route/Schedule, AppServiceProvider/Env)
- [ ] Tạo và vượt qua unit test (nếu có)
- [ ] Tuân thủ quy ước dự án
- [ ] php artisan route:list / schedule:list chạy đúng; API/đăng nhập/queue/schedule hiện có hoạt động

### Phụ thuộc
Không (đây là task triển khai đầu tiên của parent #764)
