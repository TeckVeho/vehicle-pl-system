# Issue #1044 — [BE] VPL集約API・IC→VPL同期・daily-operating整理 / API VPL và đồng bộ IC→VPL

## Context / Codebase Paths (from pre-questions)

**Quét multi-root:** `vehicle-pl-system` (VPL + docs workflow), `cloud` (IC Laravel), `izumi-timesheet-v2` (không trong phạm vi trực tiếp issue này).

**Bảng xác nhận (mặc định = phát hiện tự động; sửa YAML nếu cần rồi báo team):**

| Path | Giá trị | Ghi chú |
|------|---------|---------|
| Docs / workflow root | `d:/CtyVeHo/izumi/vehicle-pl-system` | Chứa `docs/issues/...` cho pipeline issue/plan/breakdown |
| VPL backend (API) | `./backend` | Express + Prisma, `index.ts`, routes |
| VPL migrations | `./backend/prisma/migrations` | Prisma |
| VPL tests (Vitest) | `./backend/src` | `__tests__/`, `*.test.ts` cạnh lib |
| IC (Laravel) | `d:/CtyVeHo/izumi/cloud` | `VplClient`, job/service đồng bộ ATMTC → VPL |
| IC tests (PHPUnit) | `d:/CtyVeHo/izumi/cloud/tests` | Theo chuẩn Laravel project |

```yaml
# Context / Codebase Paths — dùng cho /plan, /breakdown, /dev
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044
parent_issue: 1010
parent_issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010
parent_issue_title: "IC: Integrate ATMTC Transaction Data into IC"
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue

workspace_root: .
frontend_path: ""
backend_path: ./backend
migrations_path: ./backend/prisma/migrations
api_docs_path: ""
tests_path: ./backend/src

# Repo IC (multi-root) — đường dẫn tuyệt đối để tránh nhầm relative
ic_workspace_root: d:/CtyVeHo/izumi/cloud
ic_tests_path: d:/CtyVeHo/izumi/cloud/tests
ic_vpl_client_path: d:/CtyVeHo/izumi/cloud/app/Services/Vpl/VplClient.php

plan_reference: docs/issues/Izumi_Issue-Requests-Repo/1010/plan.md
plan_issue_1044: docs/issues/Izumi_Issue-Requests-Repo/1044/plan.md
pm_confirm_1044: docs/issues/Izumi_Issue-Requests-Repo/1044/pm-confirm-1044-vpl-atmtc-sync.md
```

---

## Metadata

| Field | Value |
|--------|--------|
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044 |
| **State** | OPEN |
| **Created** | 2026-04-09T03:17:06Z |
| **Updated** | 2026-04-09T03:17:06Z |
| **Assignees** | — |
| **Labels** | backend, enhancement, Child issue, sp:14 |

---

## Title

[BE] VPL集約API・IC→VPL同期・daily-operating整理 / API VPL và đồng bộ IC→VPL

---

## Body (from GitHub)
## 日本語 / Japanese

### 親Issue
Parent: #1010

### 説明
**vehicle-pl-system**（VPL）で ATMTC 由来データを **DailyDriverAssignment** / **DailyOperatingRecord** に反映し、**IC** から `VplClient` で呼べるようにする。親 plan.md §BE 1.6–1.9、実装順 3–4 に従う。**実装詳細・PM 確認済みの前提**は子 plan [`1044/plan.md`](./plan.md) と [`pm-confirm-1044-vpl-atmtc-sync.md`](./pm-confirm-1044-vpl-atmtc-sync.md)（[EN](./pm-confirm-1044-vpl-atmtc-sync.en.md)）に合わせる。

### 要件
1. **VPL:** 新規 `POST /api/atmtc-transactions/sync`（`requireRole(ROLES.MASTER)`）、`index.ts` 登録。driver / vehicle 解決、assignment upsert、**1 行 = 1 run** で `runCount` を **車両×日**に集計→`DailyOperatingRecord`、**`runSalaryRunCountAllocation` のみ**（**`runDriverAllocation` は呼ばない** — PM B4.2 / timesheet フル配賦との二重計上回避）、`DataSyncLog`。未マッチ行は **スキップ**し **部分成功**（`errors[]` 等）— PM B2.1。
2. **共通化:** `daily-operating.ts` の upsert ロジックを関数化し再利用。**B5.1:** `POST /daily-operating/sync` は **MASTER のみ**（全 caller が管理者アカウントで呼ぶ）。タイムシート連携が非 MASTER の場合は **別ルート**または caller 側の **MASTER 化**（PM B5.2 は例外が出た場合のみ）。
3. **IC:** `VplClient` に VPL 新エンドポイント向けメソッド。`atmtc_delivery_data_results`（または正規化済みペイロード）から VPL へ送信するジョブ／サービス。**マッピング**（`driver_code` 等→`externalId`）と **runCount 集計ルール**をコード化。**スキップ件**について **翌営業日前**に担当者へ件数・明細を通知できるようログ／通知設計（PM B2.1）。
4. **`GET /api/daily-summary` の `runCount` 露出・UI 表示** — **本リリースではスコープ外**（PM B6.2）。突合は **DataSyncLog** を主とする。将来リリースで plan §1.5 / FE §1.2 を検討。
5. **ユニットテスト:** VPL（該当ルート／lib）、IC（VplClient／マッピング）の範囲で PHPUnit / プロジェクト標準に準拠。

### 技術詳細
- VPL: `d:/CtyVeHo/izumi/vehicle-pl-system`
- IC: `d:/CtyVeHo/izumi/cloud`
- plan 親: `vehicle-pl-system/docs/issues/.../1010/plan.md`
- plan #1044 / PM: `docs/issues/.../1044/plan.md`, `.../1044/pm-confirm-1044-vpl-atmtc-sync.md`

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
- **ブロッキング:** 子 issue（IC `atmtc_delivery_data_results` 取込）でテーブルにデータが入ること（結合テスト時）。API 契約は先行実装可。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #1010

### Mô tả
Trên **vehicle-pl-system** (VPL), đồng bộ dữ liệu ATMTC vào **DailyDriverAssignment** / **DailyOperatingRecord**; phía **IC** gọi qua **`VplClient`**. Theo plan §BE 1.6–1.9 và thứ tự triển khai 3–4. **Chi tiết triển khai và các điều kiện đã PM chốt** phải khớp [`plan.md`](./plan.md) và [`pm-confirm-1044-vpl-atmtc-sync.md`](./pm-confirm-1044-vpl-atmtc-sync.md) ([EN](./pm-confirm-1044-vpl-atmtc-sync.en.md)).

### Yêu cầu
1. **VPL:** Route mới `POST /api/atmtc-transactions/sync` (MASTER), đăng ký trong `index.ts`. Resolve driver/vehicle, upsert assignment; **mỗi dòng = 1 run**, gộp **theo xe × ngày** → `DailyOperatingRecord`; chỉ gọi **`runSalaryRunCountAllocation`** (**không** gọi **`runDriverAllocation`** — PM B4.2, tránh double-count với luồng timesheet); ghi `DataSyncLog`. Dòng không ghép được **bỏ qua**, phản hồi **thành công một phần** (ví dụ `errors[]`) — PM B2.1.
2. **Tái sử dụng code:** Tách logic upsert từ `daily-operating.ts`. **B5.1 đã chốt:** `POST /daily-operating/sync` chỉ **MASTER**; mọi hệ thống gọi API đồng bộ nhóm này dùng tài khoản quản trị cao trên VPL. Nếu timesheet không dùng được MASTER → tách route hoặc đổi credential (B5.2 chỉ khi PM ghi nhận ngoại lệ).
3. **IC:** Thêm method trên `VplClient` gọi API VPL mới; job/service đẩy dữ liệu từ `atmtc_delivery_data_results` (hoặc payload đã map). **Mapping** mã ATMTC → `externalId` và **quy tắc runCount** phải được code hóa. Với dòng bị skip: thiết kế **thông báo trước ngày làm việc tiếp theo** kèm số lượng và chi tiết — PM B2.1.
4. **Mở rộng `GET /api/daily-summary` / UI hiển thị `runCount`** — **không** thuộc phạm vi release này (PM B6.2); đối soát qua **nhật ký đồng bộ**. Hạng mục sau này theo plan §1.5, FE §1.2.
5. **Unit test:** Theo chuẩn từng repo (VPL backend, IC PHPUnit).

### Chi tiết kỹ thuật
- VPL: `d:/CtyVeHo/izumi/vehicle-pl-system`
- IC: `d:/CtyVeHo/izumi/cloud`
- Plan #1044 & phiếu PM: `./plan.md`, `./pm-confirm-1044-vpl-atmtc-sync.md`

### Tiêu chí chấp nhận
- [ ] Hoàn thành triển khai
- [ ] Tạo và pass unit tests
- [ ] Tuân thủ quy ước
- [ ] Không breaking change không cần thiết

### Phụ thuộc
- Nên có issue IC import (#1010 child BE-IC) với bảng đã có dữ liệu để integration test. API có thể implement song song.


---

## Implementation checklist

- [ ] **VPL:** `POST /api/atmtc-transactions/sync` với `requireRole(ROLES.MASTER)`, đăng ký trong `index.ts`.
- [ ] **VPL:** Resolve driver/vehicle, upsert assignment; **runCount** = tổng theo **xe × ngày** (quy tắc 1 dòng = 1 run — PM B1.1); **không** gọi `runDriverAllocation`, chỉ `runSalaryRunCountAllocation` + `DataSyncLog` (PM B4.2).
- [ ] **VPL:** Dòng không resolve được: skip, trả **partial success** + `errors[]` (PM B2.1).
- [ ] **VPL:** Refactor upsert từ `daily-operating.ts` thành hàm dùng lại; `POST /daily-operating/sync` đã **MASTER** (B5.1); tách route chỉ nếu có ngoại lệ B5.2.
- [ ] **IC:** Mở rộng `VplClient` + job/service từ `atmtc_delivery_data_results` (hoặc payload đã chuẩn hóa); mapping và quy tắc `runCount` trong code; cơ chế **thông báo** skip trước ngày làm việc tiếp theo (PM B2.1).
- [ ] **(Sau release này)** `GET /api/daily-summary` / FE daily-summary — `runCount` (PM B6.2; hiện không bắt buộc).
- [ ] **Tests:** Vitest (VPL routes/lib), PHPUnit (IC `VplClient` / mapping).
- [ ] Đối chiếu `docs/issues/.../1010/plan.md` §BE 1.6–1.9, **`1044/plan.md`**, và **`pm-confirm-1044-vpl-atmtc-sync.md`**.

---

## Notes / review

- **Plan & PM:** [`plan.md`](./plan.md); [`pm-confirm-1044-vpl-atmtc-sync.md`](./pm-confirm-1044-vpl-atmtc-sync.md). **B5.1** đã chốt — mọi caller dùng **MASTER**; **B5.2/B5.3** mở nếu cần ngoại lệ / rà soát. **B6** daily-summary `runCount` hoãn.
- **GitHub Project V2:** `PVT_kwDOCjwUv84Ajq0M` / **Izumi_Issue** — dùng khi `/workspace-breakdown` gán issue con vào đúng project.
- **Multi-root:** Code VPL tại repo `vehicle-pl-system`; code IC tại `cloud` (đường dẫn tuyệt đối trong YAML).
- **Nhánh làm việc (vehicle-pl-system):** `1044-feat-vpl-atmtc-api-ic-sync` — đã checkout; **không** chạy `git commit` theo quy ước workflow issue.
- **Working tree:** Cả `vehicle-pl-system` và `cloud` đang có nhiều thay đổi chưa commit. Trước khi code sạch cho #1044, cân nhắc stash hoặc tách WIP nếu trộn với issue khác (#1010, v.v.).
