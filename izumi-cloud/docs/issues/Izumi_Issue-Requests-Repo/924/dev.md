# Dev log — Issue #924 (child of #914)

**Issue:** [Izumi_Issue-Requests-Repo#924](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/924)  
**Code repo:** `TeckVeho/izumi-cloud` (workspace hiện tại)  
**Tham khảo:** `D:\CtyVeHo\izumi\izumi-smart-approval\.github\workflows\` — `ci-pipeline.yml`, `cd-pipeline-dev.yml`, `cd-pipeline-staging.yml`

## Đã triển khai

### 1. `ci-pipeline.yml`

- Trigger: `pull_request` + `push` lên `develop`, `staging`, `main`, `master` + `workflow_dispatch`.
- Job **php**: PHP 8.2, `composer install` (hỗ trợ `COMPOSER_AUTH` secret cho package private), `.env` từ `.env.example` + `key:generate`, `composer test`, Pint `--test`.
- Job **node**: Node 18, `npm ci`, `lint`, `test` (Jest), `production` (Mix).
- **Không** copy E2E Playwright / matrix nặng từ smart-approval (cloud không có `testing-e2e-playwrights` tương đương trong plan).

### 2. `cd-pipeline-dev.yml` / `cd-pipeline-staging.yml`

- `environment: develop` / `staging` — secrets **tách theo Environment** trên GitHub.
- `workflow_dispatch` (push có thể bật lại giống smart-approval).
- Ghép `.env`: `envbasic` + block secrets (cùng pattern smart-approval, rút gọn comment).
- Build FE: `npm run production`, kiểm tra `public/mix-manifest.json`.
- Deploy: `rsync` + SSH; remote: `composer install --no-dev`, `artisan migrate --force`, `optimize`.
- **Biến hóa so với smart-approval:** không hard-code host/path/URL; dùng secrets:
  - `DEPLOY_HOST_SERVER`, `DEPLOY_USER`, `DEPLOY_PATH`, `SSH_DEPLOY_PEM`
  - `DEPLOY_PHP_BIN` (tuỳ chọn, mặc định `php` trên server)
  - `HEALTH_CHECK_URL` (tuỳ chọn)
  - PM2: chỉ chạy khi `PM2_APP_NAME` có giá trị; `PM2_SSH_USER` (mặc định `ec2-user`), `SSH_EC2_USER_PEM` tuỳ chọn

### 3. `envbasic/`

- `.env.basic_develop`, `.env.basic_staging`: placeholder an toàn, **không** đặt mật khẩu/API thật.

## Việc cần làm thủ công (infra / GitHub)

1. Repo **Settings → Secrets and variables → Actions → Environments**: tạo `develop` và `staging`.
2. Thêm secrets (mỗi môi trường một bộ): `APP_KEY`, `JWT_SECRET`, DB, mail, AWS, Pusher, LineWorks, Sentry như trong workflow; deploy: `SSH_DEPLOY_PEM`, `DEPLOY_HOST_SERVER`, `DEPLOY_USER`, `DEPLOY_PATH`.
3. Repository secret (hoặc environment) **`COMPOSER_AUTH`**: JSON cho `veho-dev/s3-logger` nếu CI cần.
4. Chỉnh `APP_URL` / domain trong file envbasic cho đúng môi trường thật (hoặc override bằng secret nếu sau này bổ sung bước echo).

## Ghi chú

- Lỗi **Service Unavailable** từ Cursor là lỗi dịch vụ IDE, không liên quan pipeline.
- **Không** chạy `git commit` (theo `/dev`).
- Chạy kiểm tra local: `composer test`, `npm run lint`, `npm run test`, `npm run production`.
