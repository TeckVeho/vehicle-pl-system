# Issue #967 — [BE] VPL同期: users・courses / Đồng bộ users & courses

## Context / Codebase Paths (from pre-questions)

**Issue lookup:** `#967` は **カレント git リポジトリ**（`TeckVeho/vehicle-pl-system`）には存在しません。トラッキングは **`TeckVeho/Izumi_Issue-Requests-Repo`** の #967 です（親: #956）。

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/967
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: ./izumi-cloud
migrations_path: ./izumi-cloud/database/migrations
api_docs_path:
tests_path: ./izumi-cloud/tests
workspace_root: .
```

**Note:** 実装の主対象は **`./izumi-cloud`**（Laravel / PHPUnit）。`frontend_path: .` はワークスペース直下の Next.js（VPL）；本 issue の記述では主に IC 側バックエンドが対象。フィールドマッピングは親ドキュメント `docs/issues/Izumi_Issue-Requests-Repo/956/ic-sync-field-mapping.md` を参照。

---

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [BE] VPL同期: users・courses / Đồng bộ users & courses |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/967 |
| **Created** | 2026-03-27T03:39:42Z |
| **Updated** | 2026-03-27T03:50:24Z |
| **Assignees** | tungnt183855 |
| **Labels** | backend, enhancement, Child issue |

---

## Description (summary)

**Parent:** #956

Implement ETL and callers for **`POST /api/users/sync`** and **`POST /api/courses/sync`** (Izumi Cloud → VPL).

- **`departmentId`:** `LOC` + zero-padded 3-digit `departments.id`
- **Course `name`:** No direct IC column — generate per plan / `ic-sync-field-mapping.md`
- **`externalId`:** IC `id`
- **Roles:** Map Spatie role → VPL `VALID_ROLES`

**Repository (implementation):** **izumi-cloud** (in this workspace: `./izumi-cloud`)

**Dependency:** Child issue “VPL 基盤”（HTTP client / JWT）マージ後に結合テスト可能

---

## Acceptance criteria (from issue body)

- [x] 実装完了 / Hoàn thành triển khai
- [x] ユニットテスト作成・合格 / Unit test đạt（サービス／トランスフォーマ）
- [x] プロジェクト規約に準拠 / Tuân thủ quy ước
- [x] 既存機能への破壊的変更なし / Không breaking change

---

## Implementation checklist

- [x] `ic-sync-field-mapping.md` §1–2 に沿った users / courses のマッピング
- [x] `POST /api/users/sync` 呼び出しと ETL
- [x] `POST /api/courses/sync` 呼び出しと ETL
- [x] `departmentId` = `LOC` + 3 桁ゼロ埋め `departments.id`
- [x] コース `name` の生成ロジック（plan / mapping doc 準拠）
- [x] `externalId` ← IC `id`
- [x] Spatie role → VPL `VALID_ROLES` マッピング
- [x] PHPUnit（サービス／トランスフォーマ）
- [ ] 基盤 issue（JWT・HTTP クライアント）との結合確認（マージ後）

---

## Notes / review

- **Paths:** `/plan`, `/breakdown`, `/dev` は上記 YAML と本ディレクトリ `docs/issues/Izumi_Issue-Requests-Repo/967/` を参照すること。
- **Project V2:** `/breakdown` で子 issue をプロジェクトに載せる際は `github_project_v2_id` を使用（**Izumi_Issue**）。
- **Role mapping fallback:** Đảm bảo class Mapping xử lý tốt trường hợp User ở IC chưa có Role hoặc có Role không nằm trong tập `VALID_ROLES` của VPL (cần có fallback default logic hoặc bỏ qua user đó để tránh văng Exception làm dừng toàn bộ tiến trình batch update).
