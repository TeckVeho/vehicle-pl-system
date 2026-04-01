# Issue #914: Excute CICD automatically_IC

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/914
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: .
migrations_path: database/migrations
api_docs_path:   # optional
tests_path: tests
workspace_root: .
```

*Triển khai code tại repo **cloud** (repo hiện tại).*

---

## Metadata

- **Title:** Excute CICD automatically_IC
- **Status:** OPEN
- **Created:** 2026-03-19T07:02:47Z
- **Updated:** 2026-03-19T09:31:19Z
- **Assignee:** @phuongcodeunited
- **Labels:** (none)
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/914

---

## Description

**Nguyên tắc:** develop / staging tách Environment, Secrets, `envbasic`, workflow CD. Triển khai code tại repo **`cloud`**.

### Chung

- [ ] **1.** Chốt phạm vi & đủ hạ tầng (URL/SSH/DB) cho develop và staging.
- [ ] **2.** Bật GitHub Actions trên repo code.
- [ ] **3.** Thêm workflow **CI** (test PHP, lint/build FE trên PR/push).
- [ ] **4.** Viết tài liệu ngắn: CI, deploy develop vs staging, link workflow.
- [ ] **5.** *(Tuỳ chọn)* Branch protection — bắt CI xanh mới merge.

### Develop

- [ ] **6.** Tạo GitHub Environment **`develop`** (rule + người chạy/approve).
- [ ] **7.** Nhập **Secrets** chỉ cho develop (không dùng chung staging).
- [ ] **8.** Thêm `.github/workflows/envbasic/.env.basic_develop` (không mật khẩu trong git).
- [ ] **9.** Thêm **`cd-pipeline-dev.yml`** — `environment: develop`, đúng server/path/health.
- [ ] **10.** Chạy thử CD develop + smoke test URL develop.

### Staging

- [ ] **11.** Tạo GitHub Environment **`staging`**.
- [ ] **12.** Nhập **Secrets** riêng staging. **~1–3h** / infra
- [ ] **13.** Thêm `envbasic/.env.basic_staging`.
- [ ] **14.** Thêm **`cd-pipeline-staging.yml`** — `environment: staging`.
- [ ] **15.** Chạy thử CD staging + smoke/UAT URL staging.

---

## Implementation checklist

- [ ] Phạm vi & hạ tầng develop/staging
- [ ] GitHub Actions bật trên repo
- [ ] Workflow CI (PHP test, FE lint/build)
- [ ] Tài liệu CI + deploy
- [ ] (Optional) Branch protection
- [ ] Environment `develop` + Secrets + envbasic + cd-pipeline-dev.yml
- [ ] Smoke test develop
- [ ] Environment `staging` + Secrets + envbasic + cd-pipeline-staging.yml
- [ ] Smoke/UAT staging

---

## Notes / Review

- Các lệnh `/plan`, `/breakdown`, `/dev` đọc **Codebase Paths** ở trên và dùng doc path `docs/issues/Izumi_Issue-Requests-Repo/914/`.
- Issue nằm trong GitHub Project **Izumi_Issue** (`github_project_v2_id` dùng cho `/breakdown` khi add issue con).
