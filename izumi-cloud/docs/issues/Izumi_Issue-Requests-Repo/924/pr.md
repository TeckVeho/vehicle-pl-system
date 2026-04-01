# PR: CI/CD PHP, develop & staging (child of #914)

Closes TeckVeho/Izumi_Issue-Requests-Repo#924

## Tóm tắt

- Thêm **CI** (`.github/workflows/ci-pipeline.yml`): PHP 8.2 — `composer install` (`COMPOSER_AUTH`), `php artisan test --env=testing --compact`, Pint; Node 18 — `npm ci`, ESLint, Jest, Laravel Mix `production`. **Không** có job E2E/Playwright.
- Thêm **CD develop** / **CD staging**: `cd-pipeline-dev.yml`, `cd-pipeline-staging.yml` với `environment: develop` / `staging`, build FE trên runner, rsync + `composer install --no-dev` + migrate + optimize trên server (secrets `DEPLOY_*`, v.v.).
- Thêm **envbasic** an toàn: `.env.basic_develop`, `.env.basic_staging`.
- Tài liệu: `docs/issues/Izumi_Issue-Requests-Repo/924/dev.md`.

## Issue liên quan

- Parent tracking: TeckVeho/Izumi_Issue-Requests-Repo#914
- PR code: **TeckVeho/izumi-cloud** — issue #924 nằm repo **Izumi_Issue-Requests-Repo** (đóng bằng `Closes owner/repo#924`).

## Screenshots

_Không có (thay đổi infrastructure, không UI)._

## Evidence

⚠️ **Không có** file `docs/issues/Izumi_Issue-Requests-Repo/924/evidence/test-results.json`.

### 1. Backend (local — chưa chạy full suite trong phiên này)

**Command:**

```bash
php artisan test --env=testing --compact
```

**Result:**

- Chưa thực thi đầy đủ trong PR này; CI sẽ chạy PHPUnit với SQLite theo `phpunit.xml` trên GitHub Actions.

### 2. CI tương đương (sau merge)

**Command:**

- Xem job `php` / `node` trong `ci-pipeline.yml` trên tab Actions.

**Result:**

- Cần bật Actions + cấu hình secret `COMPOSER_AUTH` nếu cần cho package private.

### 3. Hạ tầng CD (sau merge)

**Command:**

- Tạo Environments `develop` / `staging` và điền secrets theo `dev.md`.

**Result:**

- Deploy thủ công qua `workflow_dispatch` cho đến khi team bật `push` trigger (đã comment trong YAML).
