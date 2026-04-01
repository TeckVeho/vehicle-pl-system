# Issue #550: 多言語対応機能_Multilingual features

## Metadata

- **Tiêu đề:** 多言語対応機能_Multilingual features
- **Trạng thái:** CLOSED
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/550
- **Ngày tạo:** 2025-12-25T03:03:58Z
- **Ngày cập nhật:** 2025-12-26T09:44:07Z
- **Labels:** backend, enhancement, frontend, feature
- **Người được giao:** 
  - hathaiviet411 (Hà Thái Việt)
  - phuongcodeunited

---

## Mô tả Issue

### 1. Tổng quan (Overview)

#### Bối cảnh (Background)

* **Vấn đề hiện tại:** Hệ thống: イズミアプリ (Ứng dụng Izumi)
  - Muốn có khả năng chuyển đổi giữa tiếng Nhật, tiếng Anh và tiếng Trung.

* **Yêu cầu kinh doanh:** Cần cho phép chuyển đổi ngôn ngữ ở những phần cần thiết, giúp người dùng sử dụng ứng dụng một cách thoải mái.

* **Câu chuyện người dùng:** Là người dùng, tôi muốn chọn ngôn ngữ của ứng dụng từ tiếng Nhật, tiếng Anh và tiếng Trung.

#### Mục tiêu đạt được (Goal)

* **Hình ảnh lý tưởng:** Chỉ cho phép chuyển đổi ngôn ngữ ở những phần cần thiết. (Thêm nút chuyển đổi ngôn ngữ)

**Ưu tiên cao:**
- Bảng lương (給与明細)
- Thông báo (お知らせ)

**Ưu tiên thấp:**
- Izumi手帳 (Sổ tay Izumi - bao gồm nhiệm vụ/mission, v.v.)

#### Điều kiện hoàn thành (Definition of Done)

- [ ] Nút chuyển đổi ngôn ngữ đã được triển khai
- [ ] Bảng lương và thông báo hiển thị bằng ngôn ngữ đã chọn
- [ ] Chức năng chuyển đổi ngôn ngữ của Izumi手帳 bị vô hiệu hóa
- [ ] Văn bản của mỗi ngôn ngữ được dịch chính xác
- [ ] Ứng dụng phản ứng đúng khi người dùng chuyển đổi ngôn ngữ

---

### 2. Thông số kỹ thuật (Specification)

#### Yêu cầu chức năng (Functional Requirements)

1. Khi người dùng nhấp vào nút chuyển đổi ngôn ngữ, nội dung hiển thị của ứng dụng sẽ được cập nhật theo ngôn ngữ đã chọn.
2. Phần bảng lương và thông báo sẽ được hiển thị bằng ngôn ngữ đã chọn.
3. Phần Izumi手帳 sẽ không nằm trong phạm vi chuyển đổi ngôn ngữ và sẽ hiển thị bằng ngôn ngữ mặc định.
4. Việc chuyển đổi ngôn ngữ không ảnh hưởng đến các chức năng khác của ứng dụng.
5. Quy trình chuyển đổi ngôn ngữ phải mượt mà và không gây căng thẳng cho người dùng.

#### Loại tác vụ

Yêu cầu (Requirements)

#### Người khởi tạo

Đào Thị Thư

---

## Implementation Checklist

### Backend Tasks

#### A. Cấu hình hệ thống đa ngôn ngữ (Chuẩn bị trước)

- [ ] Cập nhật `config/app.php`:
  - [ ] Set `locale` mặc định: 'ja'
  - [ ] Set `fallback_locale`: 'ja'
  - [ ] Thêm `available_locales`: ['ja', 'en', 'zh']
- [ ] Tạo config file mới `config/language.php` cho các setting đa ngôn ngữ
- [ ] Cấu hình timezone phù hợp với từng locale
- [ ] Cập nhật `.env` với các biến môi trường cần thiết

#### B. Thiết lập cấu trúc translation files

- [ ] Tạo cấu trúc thư mục `resources/lang/ja/`, `resources/lang/en/`, `resources/lang/zh/`
- [ ] Tạo các file translation cơ bản:
  - [ ] `auth.php` - Các message liên quan authentication
  - [ ] `validation.php` - Các message validation
  - [ ] `messages.php` - Các message chung của hệ thống
  - [ ] `payslip.php` - Các label và message cho bảng lương
  - [ ] `notification.php` - Các label và message cho thông báo

#### C. Database Migration

- [ ] Tạo migration file để thêm column `language` vào bảng `users`
  - [ ] Column type: VARCHAR(5)
  - [ ] Nullable: true
  - [ ] Default: 'ja'
  - [ ] Index: true (để tối ưu query)
- [ ] Chạy migration

#### D. Tạo Middleware

- [ ] Tạo `SetLocale` middleware
  - [ ] Đọc language từ user đã authenticate
  - [ ] Set App::setLocale() cho mỗi request
  - [ ] Handle fallback nếu language không hợp lệ
- [ ] Đăng ký middleware trong `app/Http/Kernel.php`

#### E. Tạo Service Provider (Optional nhưng khuyến nghị)

- [ ] Tạo `LanguageServiceProvider`
  - [ ] Register language services
  - [ ] Setup caching cho translations
  - [ ] Load config files
- [ ] Đăng ký provider trong `config/app.php`

#### F. Tạo API Endpoints

- [ ] POST `/api/user/language` - Cập nhật ngôn ngữ người dùng
  - [ ] Validate input: chỉ chấp nhận 'ja', 'en', 'zh'
  - [ ] Lưu vào bảng `users`
  - [ ] Return success response
- [ ] GET `/api/user/language` - Lấy ngôn ngữ hiện tại của người dùng
  - [ ] Return user's language preference

#### G. Dịch nội dung cho các module

- [ ] Dịch nội dung cho Bảng lương (Payslip)
- [ ] Dịch nội dung cho Thông báo (Notifications)
- [ ] Đảm bảo Izumi手帳 không bị ảnh hưởng bởi chuyển đổi ngôn ngữ

### Frontend Tasks (Web)

- [ ] Cấu hình i18n
- [ ] Tạo các file translation cho frontend
- [ ] Thiết kế và triển khai UI nút chuyển đổi ngôn ngữ
- [ ] Implement logic chuyển đổi ngôn ngữ
- [ ] Áp dụng đa ngôn ngữ cho màn hình bảng lương và thông báo
- [ ] Đảm bảo Izumi手帳 hiển thị ngôn ngữ mặc định

### Mobile Tasks

- [ ] Cấu hình i18n cho mobile app
- [ ] Tạo translation files cho mobile
- [ ] Implement language selector UI
- [ ] Tích hợp API calls (lưu/lấy ngôn ngữ)
- [ ] Áp dụng đa ngôn ngữ cho bảng lương và thông báo
- [ ] Lưu ngôn ngữ vào local storage

### Testing Tasks

- [ ] Unit test cho backend API
- [ ] Unit test cho frontend components
- [ ] Integration test cho flow chuyển đổi ngôn ngữ
- [ ] Test hiển thị đúng nội dung cho từng ngôn ngữ
- [ ] Test performance khi chuyển đổi ngôn ngữ
- [ ] Test trên các thiết bị và trình duyệt khác nhau

### Documentation Tasks

- [ ] Tài liệu hướng dẫn sử dụng tính năng đa ngôn ngữ
- [ ] Tài liệu kỹ thuật về cấu trúc i18n
- [ ] Hướng dẫn thêm ngôn ngữ mới trong tương lai
- [ ] Tài liệu API endpoints cho mobile team

---

## Technical Notes

### Ngôn ngữ được hỗ trợ

1. **Tiếng Nhật (ja)** - 日本語 (Mặc định)
2. **Tiếng Anh (en)** - English
3. **Tiếng Trung (zh)** - 中文

### Phạm vi áp dụng

**Có áp dụng đa ngôn ngữ:**
- Thông báo (お知らせ / Notifications)

**Lưu ý:** Hệ thống không có chức năng Bảng lương (Payslip), do đó chỉ áp dụng đa ngôn ngữ cho Thông báo.

**Không áp dụng đa ngôn ngữ:**
- Izumi手帳 (Sổ tay Izumi / Izumi Notebook)
  - Missions
  - Các tính năng liên quan khác

---

## Cấu hình Backend cần chuẩn bị

### 1. Laravel Configuration Files

#### config/app.php
```php
'locale' => env('APP_LOCALE', 'ja'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'ja'),
'available_locales' => ['ja', 'en', 'zh'],
```

#### config/language.php (File mới - Tạo file này)
```php
<?php

return [
    'default' => 'ja',
    'fallback' => 'ja',
    
    'available' => [
        'ja' => [
            'code' => 'ja',
            'name' => '日本語',
            'native_name' => '日本語',
            'flag' => '🇯🇵',
            'enabled' => true,
        ],
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇬🇧',
            'enabled' => true,
        ],
        'zh' => [
            'code' => 'zh',
            'name' => 'Chinese',
            'native_name' => '中文',
            'flag' => '🇨🇳',
            'enabled' => true,
        ],
    ],
    
    'modules' => [
        'payslip' => true,
        'notification' => true,
        'izumi_notebook' => false,
    ],
    
    'cache' => [
        'enabled' => env('LANGUAGE_CACHE_ENABLED', true),
        'ttl' => env('LANGUAGE_CACHE_TTL', 3600),
        'prefix' => 'lang_',
    ],
];
```

### 2. Environment Variables (.env)
```env
APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja
LANGUAGE_CACHE_ENABLED=true
LANGUAGE_CACHE_TTL=3600
```

### 3. Translation File Structure (Cần tạo)
```
resources/
└── lang/
    ├── ja/
    │   ├── auth.php
    │   ├── validation.php
    │   ├── messages.php
    │   ├── payslip.php
    │   └── notification.php
    ├── en/
    │   ├── auth.php
    │   ├── validation.php
    │   ├── messages.php
    │   ├── payslip.php
    │   └── notification.php
    └── zh/
        ├── auth.php
        ├── validation.php
        ├── messages.php
        ├── payslip.php
        └── notification.php
```

### 4. Middleware Configuration

#### app/Http/Kernel.php
```php
protected $middlewareGroups = [
    'api' => [
        \App\Http\Middleware\SetLocale::class,
    ],
];
```

#### app/Http/Middleware/SetLocale.php (Cần tạo file này)
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = 'ja';
        
        if ($request->user()) {
            $userLocale = $request->user()->language;
            if ($userLocale && in_array($userLocale, config('language.available'))) {
                $locale = $userLocale;
            }
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
}
```

### 5. Service Provider (Optional - Khuyến nghị)

#### app/Providers/LanguageServiceProvider.php (Cần tạo file này)
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/language.php', 'language'
        );
    }

    public function boot()
    {
        if (config('language.cache.enabled')) {
            $this->setupTranslationCaching();
        }
    }
    
    protected function setupTranslationCaching()
    {
    }
}
```

Đăng ký trong `config/app.php`:
```php
'providers' => [
    App\Providers\LanguageServiceProvider::class,
],
```

### 6. Database Migration

#### database/migrations/xxxx_xx_xx_add_language_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('language', 5)->nullable()->default('ja')->after('email');
            $table->index('language');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['language']);
            $table->dropColumn('language');
        });
    }
};
```

### 7. Example Translation Files

#### resources/lang/ja/payslip.php
```php
<?php

return [
    'title' => '給与明細',
    'month' => '月',
    'basic_salary' => '基本給',
    'allowance' => '手当',
    'deduction' => '控除',
    'total' => '合計',
];
```

#### resources/lang/en/payslip.php
```php
<?php

return [
    'title' => 'Payslip',
    'month' => 'Month',
    'basic_salary' => 'Basic Salary',
    'allowance' => 'Allowance',
    'deduction' => 'Deduction',
    'total' => 'Total',
];
```

#### resources/lang/zh/payslip.php
```php
<?php

return [
    'title' => '工资单',
    'month' => '月',
    'basic_salary' => '基本工资',
    'allowance' => '津贴',
    'deduction' => '扣除',
    'total' => '总计',
];
```

---

## Flow xử lý ngôn ngữ cho Mobile

1. **Khi người dùng đăng nhập vào mobile:**
   - Mobile app gọi API đăng nhập
   - Sau khi đăng nhập thành công, mobile gọi API `POST /api/user/language` để lưu ngôn ngữ người dùng chọn
   - Backend lưu ngôn ngữ vào column `language` trong bảng `users`

2. **Khi người dùng sử dụng app:**
   - Middleware `SetLocale` tự động đọc language từ user đã authenticate
   - Backend set locale cho mỗi request
   - API response trả về nội dung đã được dịch theo ngôn ngữ của user

3. **Khi người dùng thay đổi ngôn ngữ:**
   - Mobile gọi API `POST /api/user/language` với ngôn ngữ mới
   - Backend cập nhật vào bảng `users`
   - Mobile app reload nội dung với ngôn ngữ mới

---

## API Endpoints

### 1. Cập nhật ngôn ngữ người dùng
```
POST /api/user/language
Headers: 
  Authorization: Bearer {token}
  Content-Type: application/json

Body: 
{
  "language": "ja" | "en" | "zh"
}

Response Success (200):
{
  "success": true,
  "message": "Language updated successfully",
  "data": {
    "language": "ja"
  }
}

Response Error (422):
{
  "success": false,
  "message": "Invalid language code",
  "errors": {
    "language": ["The selected language is invalid."]
  }
}
```

### 2. Lấy ngôn ngữ hiện tại
```
GET /api/user/language
Headers: 
  Authorization: Bearer {token}

Response Success (200):
{
  "success": true,
  "data": {
    "language": "ja",
    "available_languages": [
      {
        "code": "ja",
        "name": "日本語",
        "flag": "🇯🇵"
      },
      {
        "code": "en",
        "name": "English",
        "flag": "🇬🇧"
      },
      {
        "code": "zh",
        "name": "中文",
        "flag": "🇨🇳"
      }
    ]
  }
}
```

---

## Database Schema

### Bảng: users
```sql
ALTER TABLE users 
ADD COLUMN language VARCHAR(5) NULL DEFAULT 'ja' AFTER email,
ADD INDEX idx_language (language);
```

**Mô tả:**
- `language`: Lưu mã ngôn ngữ của người dùng
- Giá trị cho phép: 'ja', 'en', 'zh'
- Nullable: true (để tương thích với dữ liệu cũ)
- Default: 'ja' (tiếng Nhật)
- Index: Có (để tối ưu query)

---

## Lưu ý kỹ thuật quan trọng

### 1. Hệ thống chính
- Hệ thống này có BE và DB là hệ thống chính, phục vụ cho hầu hết tất cả các hệ thống vệ tinh khác bao gồm cả mobile
- Cần đảm bảo API đa ngôn ngữ có thể được sử dụng bởi các ứng dụng mobile và các hệ thống vệ tinh khác

### 2. Performance
- **Caching:** Sử dụng Laravel cache để lưu translation files, giảm I/O operations
- **Lazy Loading:** Chỉ load translation files khi cần thiết
- **Index Database:** Thêm index cho column `language` để tối ưu query

### 3. Reliability
- **Fallback:** Luôn có fallback về tiếng Nhật nếu translation không tồn tại
- **Validation:** Validate language code trước khi lưu vào database
- **Middleware:** Tự động set locale cho mỗi request dựa trên user preference

### 4. Consistency
- Đảm bảo format date, time, number theo locale
- Sử dụng Laravel's localization helpers: `trans()`, `__()`, `@lang`
- Đồng bộ translation keys giữa các ngôn ngữ

---

## Review & Notes

### Questions

1. ✅ Ngôn ngữ mặc định của hệ thống: **Tiếng Nhật (ja)**
2. Có cần hỗ trợ RTL (Right-to-Left) cho các ngôn ngữ trong tương lai không?
3. Translation sẽ được quản lý như thế nào? (Manual, Translation service, CMS?)
4. Có cần version control cho các translation không?

### Risks & Considerations

- Đảm bảo tất cả các hệ thống vệ tinh đều tương thích với API đa ngôn ngữ mới
- Cần test kỹ trên mobile apps để đảm bảo không có breaking changes
- Cân nhắc về performance khi load nhiều translation files
- Đảm bảo UX mượt mà khi chuyển đổi ngôn ngữ (không bị flicker, lag)

### Dependencies

- Backend: Laravel localization
- Frontend: i18n library (i18next, vue-i18n, hoặc tương tự)
- Database: Migration để thêm column `language` vào bảng `users`
- Mobile: Cần update để gọi API lưu ngôn ngữ sau khi đăng nhập

---

## Timeline & Milestones

_Sẽ được cập nhật trong quá trình planning và breakdown_

---

**Branch:** `550-feat-multilingual-support`
**Created:** 2025-12-26
