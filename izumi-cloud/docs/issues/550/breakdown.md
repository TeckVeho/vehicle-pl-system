# Breakdown - Issue #550: 多言語対応機能_Multilingual features

## Tổng quan

Issue #550 đã được phân tích và breakdown thành **1 Backend issue** theo chiến lược mặc định (1 FE + 1 BE), tập trung vào phần Backend theo yêu cầu.

---

## Backend Issue đã tạo

### Issue #566: [BE] 多言語対応機能: API・設定・翻訳・ユニットテスト / Tính năng đa ngôn ngữ: API, cấu hình, dịch thuật và Unit Tests

**URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/566

**Labels:** 
- backend
- enhancement

**Story Points:** 12 SP (≈ 12 giờ)

**Phụ thuộc:** Không có (có thể phát triển độc lập)

---

## Chi tiết phân tích Story Points

### Tính toán cho Backend Issue #566: 12 SP

**Các yếu tố đánh giá:**

1. **Code Volume:** Medium-Large
   - Config files (config/app.php, config/language.php)
   - Middleware (SetLocale)
   - Service Provider (LanguageServiceProvider)
   - Controller (UserLanguageController)
   - Migration (add language column)
   - Translation files (3 languages × 5 files = 15 files)

2. **Complexity:** Medium
   - Middleware logic với authentication check
   - Validation logic cho language codes
   - Caching setup cho translations
   - Fallback handling

3. **Testing:** Extensive
   - API endpoint tests (POST/GET)
   - Middleware tests
   - Validation tests
   - Translation loading tests
   - Locale switching tests

4. **Architecture Impact:** Medium
   - New middleware trong request lifecycle
   - New service provider
   - Database migration
   - Config changes

5. **Integration Dependencies:** Low
   - Có thể phát triển độc lập
   - Cần test với mobile/frontend sau khi hoàn thành

6. **Uncertainty:** Low
   - Laravel localization là tính năng có sẵn
   - Pattern rõ ràng và đã được documented
   - Không có technical challenges lớn

**Breakdown chi tiết:**

| Task | SP | Mô tả |
|------|-----|-------|
| Config setup | 1.0 | Cập nhật config/app.php, tạo config/language.php, cấu hình .env |
| Database migration | 1.0 | Tạo migration, thêm column language, thêm index, chạy migration |
| Middleware development | 1.5 | Tạo SetLocale middleware, implement logic, đăng ký trong Kernel |
| Service Provider | 1.0 | Tạo LanguageServiceProvider, setup caching, đăng ký provider |
| API Controller | 2.0 | Tạo UserLanguageController, implement 2 endpoints, validation |
| Translation files | 2.0 | Tạo cấu trúc thư mục, 15 translation files (ja/en/zh × 5 files) |
| Unit tests | 2.5 | API tests, Middleware tests, Validation tests, Translation tests |
| Documentation & Integration | 1.0 | API documentation, integration testing với mobile |
| **TỔNG** | **12.0** | **≈ 12 giờ** |

---

## Nội dung Backend Issue

### Scope chính

1. **Cấu hình hệ thống đa ngôn ngữ**
   - config/app.php: locale settings
   - config/language.php: language configuration
   - .env: environment variables

2. **Database Migration**
   - Thêm column `language` vào bảng `users`
   - Type: VARCHAR(5), nullable, default: 'ja'
   - Index cho performance

3. **Middleware**
   - SetLocale middleware
   - Tự động set locale dựa trên user preference
   - Fallback về 'ja' nếu invalid

4. **Service Provider (Optional nhưng khuyến nghị)**
   - LanguageServiceProvider
   - Caching cho translations
   - Performance optimization

5. **API Endpoints**
   - POST /api/user/language - Cập nhật ngôn ngữ
   - GET /api/user/language - Lấy ngôn ngữ hiện tại
   - Validation: chỉ chấp nhận 'ja', 'en', 'zh'
   - Authentication required (Bearer Token)

6. **Translation Files**
   - Cấu trúc: resources/lang/{ja,en,zh}/
   - Files: auth.php, validation.php, messages.php, payslip.php, notification.php
   - Nội dung dịch cho Bảng lương và Thông báo

7. **Unit Tests**
   - API endpoint tests
   - Middleware tests
   - Validation tests
   - Translation loading tests

### Ngôn ngữ được hỗ trợ

- **Tiếng Nhật (ja)** - 日本語 (Mặc định)
- **Tiếng Anh (en)** - English
- **Tiếng Trung (zh)** - 中文

### Phạm vi áp dụng

**Có áp dụng đa ngôn ngữ:**
- ✅ Thông báo (お知らせ / Notifications)

**Lưu ý:** Hệ thống không có chức năng Bảng lương (Payslip).

**Không áp dụng đa ngôn ngữ:**
- ❌ Izumi手帳 (Sổ tay Izumi / Izumi Notebook)

### API Specification

#### POST /api/user/language
```
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

#### GET /api/user/language
```
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

### Database Schema

```sql
ALTER TABLE users 
ADD COLUMN language VARCHAR(5) NULL DEFAULT 'ja' AFTER email,
ADD INDEX idx_language (language);
```

---

## Tiêu chí chấp nhận

### Backend Issue #566

- [ ] usersテーブルにlanguageカラムが追加され、migrationが実行されている
- [ ] config/language.php が作成され、3言語の設定が完了している
- [ ] SetLocale middleware が実装され、app/Http/Kernel.php に登録されている
- [ ] POST /api/user/language API が実装され、言語設定を保存できる
- [ ] GET /api/user/language API が実装され、言語設定を取得できる
- [ ] 言語コードのバリデーション（ja/en/zhのみ許可）が実装されている
- [ ] resources/lang/ja/, en/, zh/ に翻訳ファイルが作成されている
- [ ] 給与明細とお知らせの翻訳コンテンツが3言語で用意されている
- [ ] Izumi手帳が言語切り替えの影響を受けない
- [ ] バックエンドユニットテストが作成され、すべて合格している
- [ ] プロジェクト規約に準拠している
- [ ] 既存機能への破壊的変更がない
- [ ] モバイルアプリおよび他の衛星システムから利用可能である

---

## Lưu ý kỹ thuật

### Hệ thống chính
- Hệ thống này có BE và DB là hệ thống chính, phục vụ cho hầu hết tất cả các hệ thống vệ tinh khác bao gồm cả mobile
- Cần đảm bảo API đa ngôn ngữ có thể được sử dụng bởi các ứng dụng mobile và các hệ thống vệ tinh khác

### Performance
- **Caching:** Sử dụng Laravel cache để lưu translation files
- **Lazy Loading:** Chỉ load translation files khi cần thiết
- **Index Database:** Thêm index cho column `language`

### Reliability
- **Fallback:** Luôn có fallback về tiếng Nhật nếu translation không tồn tại
- **Validation:** Validate language code trước khi lưu vào database
- **Middleware:** Tự động set locale cho mỗi request

---

## Timeline ước tính

**Backend Issue #566:** 12 SP ≈ 12 giờ ≈ 1.5 ngày làm việc

**Phân bổ thời gian:**
- Day 1 (Morning): Config setup + Database migration + Middleware (3.5 SP)
- Day 1 (Afternoon): Service Provider + API Controller (3 SP)
- Day 2 (Morning): Translation files creation (2 SP)
- Day 2 (Afternoon): Unit tests + Documentation (3.5 SP)

---

## Next Steps

1. ✅ Backend issue #566 đã được tạo
2. ✅ Issue đã được thêm vào GitHub Project #138
3. ⏳ Cần set Story Points (12 SP) trong GitHub Project
4. ⏳ Developer có thể bắt đầu với `/dev 566` khi sẵn sàng

---

## Ghi chú

- Issue được tạo với nội dung song ngữ (Nhật-Việt) đầy đủ
- Tuân thủ template chuẩn của breakdown command
- Story Points được tính toán dựa trên các yếu tố: Code Volume, Complexity, Testing, Architecture Impact, Integration Dependencies, Uncertainty
- Backend issue có thể phát triển độc lập, không phụ thuộc vào Frontend

---

**Created:** 2025-12-29
**Branch:** 550-feat-multilingual-support
**Parent Issue:** #550
**Backend Issue:** #566 (12 SP)

