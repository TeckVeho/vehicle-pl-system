# Issue #923 — [FE] CI: GitHub Actions（lint/Jest/Mix）/ CI Frontend: ESLint, Jest, Mix build

## Context / Codebase Paths (from pre-questions)

```yaml
# Issue tracking (GitHub)
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/923
# Code triển khai: workspace hiện tại (izumi-cloud)
code_repository: TeckVeho/izumi-cloud
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: ./resources/js
backend_path: .
migrations_path: ./database/migrations
api_docs_path:
tests_path: ./tests
workspace_root: .
```

Phát hiện paths: Laravel (`app/`, `artisan` tại root) → `backend_path: .`; Vue/Laravel Mix trong `resources/js` (eslint `resources/js` trong `package.json`) → `frontend_path: ./resources/js`; migrations Laravel → `./database/migrations`; PHPUnit/Dusk → `./tests`. Không thấy OpenAPI/Swagger tại root.

---

## Metadata

| Field | Value |
|-------|--------|
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/923 |
| **Title** | [FE] CI: GitHub Actions（lint/Jest/Mix）/ CI Frontend: ESLint, Jest, Mix build |
| **State** | OPEN |
| **Parent** | #914 |
| **Assignees** | tungnt183855 |
| **Labels** | enhancement, frontend, Child issue, sp:5 |
| **Created** | 2026-03-19T10:00:22Z |
| **Updated** | 2026-03-20T10:51:17Z |

---

## Body (Overview)

### Tóm tắt

- Thêm **job Frontend** trong GitHub Actions cho **TeckVeho/izumi-cloud**: Node, ESLint, Jest, `npm run production` (Laravel Mix). **Không** đổi logic Vue ứng dụng.
- Phụ thuộc: CI PHP/Composer (issue BE) dùng workflow riêng (vd. `backend-ci.yml`); deploy dev/staging dùng workflow riêng (`deploy-ci-*`) khi có task tương ứng — không gộp vào file FE.

### Yêu cầu chính

- `.github/workflows/frontend-ci.yml`: job `frontend` (chỉ FE trên GitHub Actions): `actions/checkout`, `setup-node`, `npm ci`, `npm run lint`, `npm run test`, `npm run production`. Các workflow khác (vd. `backend-ci`, deploy) do task/issue tương ứng.
- Xem xét `actions/cache` cho npm.
- Bổ sung **hướng dẫn khi FE CI fail** vào tài liệu CI/CD (issue tham chiếu `docs/issues/Izumi_Issue-Requests-Repo/914/cicd.md` hoặc đường dẫn theo plan #914 — trong repo izumi-cloud có thể cần tạo/khớp cấu trúc tương ứng).

### Chi tiết kỹ thuật (ghi nhận từ issue)

- Scripts: `package.json` — `lint` (eslint + `.vue`), `test` (jest), `production` (Laravel Mix).
- Node: thống nhất với môi trường dev (ví dụ 20 LTS — cần xác nhận).
- Lint/test đỏ sẵn: xử lý trong phạm vi issue hoặc ghi rõ rollout theo team.

### Trạng thái workspace (lúc tạo issue.md)

- `.github/workflows/`: có `release-labeling.yaml` và `frontend-ci.yml` (FE); backend/deploy dùng workflow/file riêng khi có task tương ứng.
- Thư mục `docs/issues/Izumi_Issue-Requests-Repo/914/` có thể chưa tồn tại; cần đồng bộ với parent #914 khi viết `cicd.md`.

---

## Implementation Checklist (Definition of Done)

- [ ] PR/push chạy job frontend CI (workflow định nghĩa rõ).
- [ ] `npm run lint`, `npm run test`, `npm run production` chạy trên CI (hoặc đã thống nhất / tài liệu hóa nếu rollout từng bước).
- [ ] Có cache npm hợp lý nếu áp dụng `actions/cache`.
- [ ] Tài liệu: bước **reproduce khi FE CI fail** đã được thêm theo đúng path plan (liên quan #914).
- [ ] Tuân thủ hướng dẫn Jest/unit test của dự án.
- [ ] Không thay đổi phá vỡ chức năng ứng dụng hiện có (theo phạm vi issue).

---

## Notes / Review

- **Nhánh dev (izumi-cloud):** `923-feat-ci-frontend-github-actions` — tên chứa `923` để GitHub Development liên kết issue.
- **Project V2:** `Izumi_Issue` — dùng `github_project_v2_id` ở trên khi `/breakdown` add issue con (nếu có).
- **Đặt tên workflow:** `frontend-ci.yml` chỉ chạy FE (lint / Jest / Mix); backend và deploy dev/staging dùng file/workflow riêng (`backend-ci`, `deploy-ci-dev`, `deploy-ci-staging`, …).
- **Git:** có thay đổi chưa commit trên `.cursor/commands/` (theo `git status` lúc checkout); không commit trong bước `/issue`. Nếu cần working tree sạch trước khi PR, xử lý stash/discard thủ công.
- Issue mô tả bằng JP/VN đầy đủ trên GitHub; bản trên chỉ tóm tắt cho `/plan`, `/dev`.
