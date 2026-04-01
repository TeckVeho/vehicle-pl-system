# Issue #763: IC Laravel version up for 12

## Thông tin Issue

- **Issue Number:** 763
- **Title:** IC Laravel version up for 12
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/763
- **Status:** OPEN
- **Created At:** 2026-02-09T08:23:23Z
- **Updated At:** 2026-02-09T08:23:23Z
- **Labels:** (không có)
- **Assignees:** phuongcodeunited

## Mô tả

Nâng cấp phiên bản Laravel lên Laravel 12 cho dự án IC (Izumi Cloud).

---

## Checklist Implementation

- [ ] Kiểm tra phiên bản Laravel hiện tại (`composer.json`)
- [ ] Đọc [Upgrade Guide Laravel 12](https://laravel.com/docs/upgrade)
- [ ] Cập nhật `composer.json` (laravel/framework, laravel/* packages)
- [ ] Chạy `composer update` và xử lý breaking changes
- [ ] Cập nhật config, service providers nếu có thay đổi
- [ ] Cập nhật code ứng dụng theo breaking changes (nếu có)
- [ ] Chạy test suite và sửa lỗi
- [ ] Kiểm tra compatibility với PHP version yêu cầu của Laravel 12
- [ ] Cập nhật tài liệu / README nếu cần

---

## Notes / Review

- Laravel 12 thường yêu cầu PHP 8.2+ (cần xác nhận theo tài liệu chính thức).
- Nên nâng cấp trong nhánh riêng và test kỹ trước khi merge.
- Kiểm tra compatibility của các package bên thứ ba (Spatie, Laravel Excel, v.v.) với Laravel 12.

---

## Output

- **Branch:** `763-feat-laravel-version-up-12`
- **Document:** `docs/issues/763/issue.md`
