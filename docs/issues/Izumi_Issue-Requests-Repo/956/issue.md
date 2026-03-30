# Issue #956 — [005] イズミクラウド: IZUMI 連携機能の実装

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/956
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: ./backend
migrations_path: ./backend/prisma/migrations
api_docs_path:
tests_path:
workspace_root: .
```

**Note:** `migrations_path` is the conventional Prisma location for this repo; the `migrations` folder may not exist until first migrate. Work for this issue is tracked in **vehicle-pl-system**; paths above are relative to this workspace.

---

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [005] イズミクラウド: IZUMI 連携機能の実装 |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/956 |
| **Created** | 2026-03-25T12:24:23Z |
| **Updated** | 2026-03-25T12:32:27Z |
| **Assignees** | tungnt183855 |
| **Labels** | _(none)_ |

---

## Description (summary)

Implement an **Izumi Cloud → IZUMI** integration client that calls IZUMI sync APIs and sends master and accounting data.

**Goals (high level):**

- Authentication & authorization: JWT via `POST /api/auth/login`, MASTER role, token refresh before 7-day expiry
- Master sync: users, courses, vehicles, vehicle-monthly-costs, location-monthly-expenses, drivers
- Transaction sync: `POST /api/import`, `POST /api/income-statement/records/bulk`
- Aggregate from PCA, ATMTC, ITP and send via the appropriate sync endpoints
- Department ID alignment, error handling (400/401/403/500), retries/alerts, scheduled batch runs

**Reference docs (from issue body, paths relative to Izumi issue repo):**

- `../docs/external-integration-spec.md`
- `../docs/external-system-implementation-checklist.md`
- `../docs/department-id-standard.md`

---

## Implementation checklist

### Auth & setup

- [ ] JWT acquisition and token lifecycle (refresh before expiry)
- [ ] Integration user with MASTER permission (DX / DX admin)

### Master sync clients

- [ ] `POST /api/users/sync`
- [ ] `POST /api/courses/sync`
- [ ] `POST /api/vehicles/sync` (after courses sync)
- [ ] `POST /api/vehicle-monthly-costs/sync` (include ITP `fuelEfficiency`, `roadUsageFee`)
- [ ] `POST /api/location-monthly-expenses/sync` (PCA monthly location expenses)
- [ ] `POST /api/drivers/sync` (after ATMTC linkage)

### Transaction sync

- [ ] `POST /api/import`
- [ ] `POST /api/income-statement/records/bulk`

### External systems

- [ ] PCA → location-monthly-expenses (20 accounts)
- [ ] ATMTC → driver/course mapping → drivers & courses sync
- [ ] ITP → fuel & road usage → vehicle-monthly-costs

### Cross-cutting

- [ ] Department ID consistency
- [ ] Error handling, retries, alerting
- [ ] Batch / schedule for master and monthly syncs

---

## Notes / review

- **Project V2:** Issue is on GitHub Project **Izumi_Issue** (`PVT_kwDOCjwUv84Ajq0M`). Use this id when `/breakdown` adds child issues to the same project.
- **Scope:** Issue text targets **Izumi Cloud engineers** calling **this** system’s (IZUMI) APIs; implementation in **vehicle-pl-system** may mean exposing stable APIs, docs, or shared contracts—confirm with product before coding.
- **Local workspace:** Uncommitted changes existed at branch creation (e.g. `.gitignore`, lockfiles, `.idea/`, `backend/.env.dev`); nothing was committed for this issue doc.

---

## Full body (GitHub)

<details>
<summary>Click to expand original issue body</summary>

See GitHub issue URL above for the full Japanese/Vietnamese specification and task lists.

</details>
