# Issue #955 — [003] P2: 自動テストの導入

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/955
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: ./backend
migrations_path:
api_docs_path:
tests_path:
workspace_root: .
```

**Note:** Scope is **vehicle-pl-system**: Next.js app at repo root; API and allocation logic live under **`./backend`** (`backend/src/lib/`, sync routes under `backend/src/routes/`). Prisma schema is at `./backend/prisma/schema.prisma` (no `prisma/migrations` tree in-repo yet). Paths are relative to **`/var/www/html/vehicle-pl-system`** (this workspace).

---

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [003] P2: 自動テストの導入 |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/955 |
| **Created** | 2026-03-25T12:24:21Z |
| **Updated** | 2026-03-25T12:31:28Z |
| **Assignees** | tungnt183855 |
| **Labels** | _(none)_ |

---

## Description (summary)

Introduce a test runner (Vitest or Jest) on the **backend**, run via `npm test`. Add unit tests for allocation/proration modules under `backend/src/lib/` (e.g. driver-allocation, location-expense-allocation, location-expense-proration, salary-daily-proration, salary-run-count-allocation) and API contract tests for main sync routes (vehicles, courses, driver-assignments, etc.). **CI / deploy pipeline is out of scope.**

---

## Implementation checklist

- [ ] Add Vitest or Jest to **backend**; `npm test` runs tests locally.
- [ ] Unit tests for allocation/proration logic in `backend/src/lib/` (driver-allocation, location-expense-allocation, salary-daily-proration, etc.).
- [ ] API contract tests for main sync routes (vehicles, courses, driver-assignments, …).
- [ ] Meaningful coverage of the main paths for the above logic.

---

## Reference pointers (from issue body)

- `./docs/engineering-backlog.md` P2
- `backend/src/lib/driver-allocation.ts`
- `backend/src/lib/location-expense-allocation.ts`
- `backend/src/lib/salary-daily-proration.ts`

---

## Notes / review

- Parent issue source file cited: `003-自動テストの導入.md` (vehicle-pl-system/issues).
- **Do not** commit workflow artifacts unless the team’s `/pr` step says otherwise; this file is for `/plan`, `/breakdown`, `/dev` context.

---

## Description (full body from GitHub)

See issue URL for the canonical copy. Snapshot below mirrors the fetched `body` at documentation time.

```markdown
# P2: 自動テストの導入

## 1. 概要 (Overview)

### 背景 (Background)

* **現状の課題:** ルート・バックエンドの `package.json` に Vitest/Jest 等のテストランナー設定が無い。配賦・按分ロジック（driver-allocation、location-expense-allocation、salary-daily-proration 等）や sync API の品質保証が手動に依存している。
* **ビジネス要求:** リグレッションを防ぎ、リファクタリング時の安全性を高めるため、自動テストを導入すること。
* **ユーザーストーリー:** 開発者として、私は `npm test` で主要ロジックの動作を確認し、変更時のリグレッションを早期に検知したい。

### 達成目標 (Goal)

* **あるべき姿:** バックエンドにテストランナーが導入され、配賦・按分ロジックおよび主要 sync API の主要パスがテストでカバーされていること。
* **完了条件 (Definition of Done):**
* [ ] バックエンドに Vitest または Jest を導入し、`npm test` で実行可能であること。
* [ ] 配賦・按分ロジック（driver-allocation、location-expense-allocation、salary-daily-proration 等）のユニットテストが実装されていること。
* [ ] 主要 sync ルート（vehicles 等）の API 契約テストが実装されていること。
* [ ] 上記ロジックの主要パスがカバーされていること。

---

## 2. 仕様 (Specification)

### 機能要件 (Functional Requirements)

* バックエンドに Vitest または Jest を導入し、`npm test` で実行可能にすること。
* `backend/src/lib/` の driver-allocation、location-expense-allocation、location-expense-proration、salary-daily-proration、salary-run-count-allocation 等のユニットテストを実装すること。
* 主要 sync ルート（vehicles、courses、driver-assignments 等）の API 契約テストを実装すること。
* テストはローカルで実行可能であること。

### タスクタイプ
技術改善

### 添付ファイル

### 参考資料
- [engineering-backlog.md](../docs/engineering-backlog.md) P2
- `backend/src/lib/driver-allocation.ts`
- `backend/src/lib/location-expense-allocation.ts`
- `backend/src/lib/salary-daily-proration.ts`

### メモ
- CI への組み込み、デプロイパイプラインはスコープ外（インフラに触れない範囲）

### UI/UX (あれば)
* **デザイン:** なし
* **コンポーネント:** なし

### 起票者
-

---
# P2: Giới thiệu kiểm thử tự động

## 1. Tổng quan

### Bối cảnh

* **Vấn đề hiện tại:** `package.json` của root và backend không có cấu hình test runner như Vitest/Jest. Đảm bảo chất lượng logic phân bổ, phân bổ tỷ lệ (driver-allocation, location-expense-allocation, salary-daily-proration, v.v.) và sync API phụ thuộc vào thao tác thủ công.
* **Yêu cầu kinh doanh:** Giới thiệu kiểm thử tự động để ngăn chặn hồi quy và tăng độ an toàn khi refactoring.
* **Câu chuyện người dùng:** Là nhà phát triển, tôi muốn xác nhận hoạt động của logic chính qua `npm test` và phát hiện sớm hồi quy khi thay đổi.

### Mục tiêu đạt được

* **Hình ảnh lý tưởng:** Test runner được giới thiệu vào backend, và các đường dẫn chính của logic phân bổ, phân bổ tỷ lệ và sync API chính được bao phủ bởi kiểm thử.
* **Điều kiện hoàn thành (Definition of Done):**
* [ ] Vitest hoặc Jest được giới thiệu vào backend và có thể chạy qua `npm test`.
* [ ] Unit test cho logic phân bổ, phân bổ tỷ lệ (driver-allocation, location-expense-allocation, salary-daily-proration, v.v.) được triển khai.
* [ ] API contract test cho các sync route chính (vehicles, v.v.) được triển khai.
* [ ] Các đường dẫn chính của logic trên được bao phủ.

---
## 2. Thông số kỹ thuật (Specification)

### Yêu cầu chức năng (Functional Requirements)

* Giới thiệu Vitest hoặc Jest vào backend và có thể chạy qua `npm test`.
* Triển khai unit test cho driver-allocation, location-expense-allocation, location-expense-proration, salary-daily-proration, salary-run-count-allocation trong `backend/src/lib/`.
* Triển khai API contract test cho các sync route chính (vehicles, courses, driver-assignments, v.v.).
* Kiểm thử có thể chạy cục bộ.

### Loại nhiệm vụ
Cải thiện kỹ thuật

### Tài liệu đính kèm

### Tài liệu tham khảo
- [engineering-backlog.md](../docs/engineering-backlog.md) P2
- `backend/src/lib/driver-allocation.ts`
- `backend/src/lib/location-expense-allocation.ts`
- `backend/src/lib/salary-daily-proration.ts`

### Ghi chú
- Tích hợp CI, pipeline triển khai nằm ngoài phạm vi (không chạm vào hạ tầng)

### UI/UX (nếu có)
* **Thiết kế:** Không
* **Thành phần:** Không

### Người khởi tạo
-

## Implementation Tasks
- [ ] テストランナー導入: バックエンドに Vitest または Jest を導入し、`npm test` で実行可能にする
- [ ] ユニットテスト: driver-allocation、location-expense-allocation、salary-daily-proration 等のユニットテスト
- [ ] 契約テスト: 主要 sync ルート（vehicles、courses、driver-assignments 等）の API 契約テスト


---

_Source file: `003-自動テストの導入.md` (vehicle-pl-system/issues)_
```
