# Breakdown #914 — Child issues

Chiến lược: **1 FE + 1 BE** (theo `/breakdown` mặc định). Code triển khai tại **TeckVeho/izumi-cloud**; issue con tạo trên **TeckVeho/Izumi_Issue-Requests-Repo** (cùng parent).

## Issue con

| Layer | Issue | SP (1 SP = 1h) | Ghi chú |
|-------|-------|----------------|---------|
| FE | [#923](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/923) | **5** | GitHub Actions: Node, ESLint, Jest, Mix `production`; doc troubleshooting |
| BE | [#924](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/924) | **13** | PHP CI + Pint + Composer auth + CD develop/staging + envbasic + docs |

**Tổng:** 18 SP (~18h), trong khoảng plan 16–34h (BE gói gọn ở mức tối đa 13 SP theo quy tắc tách issue).

## SP (根拠 / cơ sở)

- **FE 5:** Nhiều file workflow + cache + khả năng sửa lint/jest — 6 trục: code vừa, phụ thuộc CI env, không đổi kiến trúc app.
- **BE 13:** CI PHP + 2 pipeline CD + envbasic + secrets/environments — khối lượng lớn, nhiều phụ thuộc infra; **không vượt 13 SP/issue** nên không tách thêm BE (tổng issue cha vẫn &lt; 20 nếu chỉ 2 con).

## GitHub Project V2 (Izumi_Issue)

- `project_id`: `PVT_kwDOCjwUv84Ajq0M`
- Đã **add** #923, #924 vào project.
- Custom field **SP** (TEXT): đã set **5** và **13** qua GraphQL `updateProjectV2ItemFieldValue`.

## Label

- `sp:5` có trên repo → gán cho #923.
- `sp:13` **không tồn tại** trên repo → #924 tạo không có label `sp:13` (SP vẫn có trên Project board).

## Parent #914

- Đã **inject** tasklist vào body issue cha bằng **UTF-8** (`gh issue edit --body-file` qua Python).

## File body dùng khi tạo issue

- `breakdown-fe-issue-body.md`
- `breakdown-be-issue-body.md`
