# PR: VPL sync users & courses (Izumi Cloud ↔ vehicle-pl-system)

Closes TeckVeho/Izumi_Issue-Requests-Repo#967

## Summary

Implements and supports **Izumi Cloud → VPL** master sync for **users** and **courses** (issue #967, parent #956): resilient API behavior on the VPL backend, larger JSON payloads, persisted user timestamps, and local issue documentation.

### vehicle-pl-system (this repo)

1. **`express.json({ limit: "5mb" })`** (`backend/src/index.ts`) — avoid **413** on bulk `POST /api/users/sync` and `POST /api/courses/sync`.
2. **`POST /api/users/sync`** (`backend/src/routes/users.ts`) — accept **`createdAt` / `updatedAt`** from the payload on create and update (IC → VPL timestamp alignment).
3. **Prisma `User`** (`backend/prisma/schema.prisma`) — optional **`deletedAt`** for future IC soft-delete; datasource aligned for deployment (**MySQL** in current schema).
4. **`POST /api/courses/sync`** (`backend/src/routes/courses.ts`) — upsert: resolve existing row by **`externalId`**, else by **`(locationId, code)`**, to prevent **`P2002`** / HTTP 500 when IC resends the same location+code with a new external id.

### Documentation (tracked under `docs/issues/`)

- **#956:** `issue.md`, `plan.md`, `ic-sync-field-mapping.md`, breakdown summaries and child issue bodies.
- **#967:** `issue.md`, `dev.md` (Artisan usage, env, mapping notes).

### Not in this PR branch (local / optional follow-up)

- **`izumi-cloud/`** Laravel services (`VplClient`, `UserSyncService`, `CourseSyncService`, `vpl:sync`) may exist in the workspace but are **untracked** unless you add them in a separate commit.
- **`docs/issues/.../967/evidence/test-results.json`** — not present; see Evidence below.

## Evidence

⚠️ **No test results file** — `docs/issues/Izumi_Issue-Requests-Repo/967/evidence/test-results.json` was not found. Run tests locally before merge if required by team policy.

### Manual / suggested checks

```bash
# Izumi Cloud — VPL unit tests (when izumi-cloud is in the repo)
cd izumi-cloud && ./vendor/bin/phpunit tests/Unit/Vpl/

# VPL backend — typecheck (from repo root)
cd backend && npx tsc --noEmit
```

## Screenshots

None (API and sync work; no UI change required for this issue).
