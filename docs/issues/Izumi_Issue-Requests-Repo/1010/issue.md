# Issue #1010 — IC: Integrate ATMTC Transaction Data into IC

## Context / Codebase Paths (from pre-questions)

```yaml
# Issue tracking
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue

# Docs root = repo này (`vehicle-pl-system`). Primary app: Next.js root + API tại `backend/` (Prisma)
workspace_root: .
frontend_path: .
backend_path: ./backend
migrations_path: ./backend/prisma/migrations
api_docs_path: ./docs/external-integration-spec.md
tests_path:

# Multi-root — Laravel IC (cloud), relative từ docs root
izumi_cloud_path: ../cloud

# Multi-root — Laravel timesheet (cùng workspace; tham chiếu nếu issue ảnh hưởng)
izumi_timesheet_v2_path: ../izumi-timesheet-v2
```

**Đã quét workspace (multi-root): xác nhận paths — trả lời “ok” hoặc chỉnh từng dòng trong YAML trên**

| Path | Giá trị gợi ý | Ghi chú phát hiện |
|------|----------------|-------------------|
| `workspace_root` | `.` | Root repo chứa `.cursor` / docs workflow |
| `frontend_path` | `.` | `package.json` có `next` |
| `backend_path` | `./backend` | `backend/` + Prisma `schema.prisma` |
| `migrations_path` | `./backend/prisma/migrations` | Prisma |
| `api_docs_path` | `./docs/external-integration-spec.md` | Tồn tại |
| `tests_path` | *(trống)* | Chưa thấy `tests/` tiêu chuẩn tại root |
| `izumi_cloud_path` | `../cloud` | Laravel: `artisan`, `database/migrations`, `tests/` |
| `izumi_timesheet_v2_path` | `../izumi-timesheet-v2` | Laravel tương tự `cloud` |

Nếu cần chỉnh path, sửa khối YAML và giữ đồng bộ bản copy trong mọi workspace root (xem bước sync sau khi lưu `issue.md`).

---

## Metadata

| Field | Value |
|--------|--------|
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010 |
| **State** | OPEN |
| **Created** | 2026-04-03T06:51:51Z |
| **Updated** | 2026-04-07T02:15:41Z |
| **Assignees** | phuongcodeunited |
| **Labels** | — |

---

## Title

`IC: Integrate ATMTC Transaction Data into IC`

---

## Body (from GitHub)

# Integrate ATMTC Transaction Data into IC

## 1. 概要 (Overview)

### 背景 (Background)

* **現状の課題:** 依頼：ATMTCで管理している ドライバー - 車両 - コース の組み合わせトランザクションデータをICに逆に連携

* **ビジネス要求:** PLシステムにATMTCの乗車記録を連携することになります。ATMTCで管理している ドライバー - 車両 - コース の組み合わせトランザクションデータをICに逆に連携する必要があります。

* **ユーザーストーリー:** ユーザーとして、私はATMTCの乗車記録をPLシステムに連携させたい。

### 達成目標 (Goal)
* **あるべき姿:** 依頼：PLシステムにATMTCの乗車記録を連携することになります。ATMTCで管理している ドライバー - 車両 - コース の組み合わせトランザクションデータをICに逆に連携する必要があります。  
参考：[GitHub Issue](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/959)

* **完了条件 (Definition of Done):**
    * [ ] ATMTCのトランザクションデータがICに正しく連携されること。
    * [ ] PLシステムでATMTCの乗車記録が確認できること。
    * [ ] 連携プロセスにおけるエラーハンドリングが実装されていること。
    * [ ] ユーザーからのフィードバックを受けて、必要な改善が行われること。

---

## 2. 仕様 (Specification)

### 機能要件 (Functional Requirements)
* ATMTCで管理されているドライバー、車両、コースの組み合わせトランザクションデータをICに連携する機能を実装すること。
* 連携プロセス中にエラーが発生した場合、システムは適切なエラーメッセージをユーザーに表示すること。
* 連携が成功した場合、システムは成功メッセージを表示し、PLシステムにデータが反映されることを確認すること。
* ユーザーがデータを更新した際、システムは自動的に最新のデータをICに連携すること。

### タスクタイプ
要件

### 添付ファイル
-

### 参考資料
-

### メモ
-

### UI/UX (あれば)
* **デザイン:**
* **コンポーネント:**

### 起票者
Đào Thị Thư

---

# Tích hợp Dữ liệu Giao dịch ATMTC vào IC

## 1. Tổng quan (Overview)

### Bối cảnh (Background)

* **Vấn đề hiện tại:** Yêu cầu: Kết nối ngược dữ liệu giao dịch kết hợp giữa tài xế - xe - lộ trình được quản lý bởi **ATMTC** vào **IC**.

* **Yêu cầu kinh doanh:** Cần kết nối hồ sơ hành khách của **ATMTC** vào hệ thống **PL**. Cần thiết phải kết nối ngược dữ liệu giao dịch kết hợp giữa tài xế - xe - lộ trình được quản lý bởi **ATMTC** vào **IC**.

* **Câu chuyện người dùng:** Là người dùng, tôi muốn kết nối hồ sơ hành khách của **ATMTC** vào hệ thống **PL**.

### Mục tiêu đạt được (Goal)
* **Hình ảnh lý tưởng:** Yêu cầu: Cần kết nối hồ sơ hành khách của **ATMTC** vào hệ thống **PL**. Cần thiết phải kết nối ngược dữ liệu giao dịch kết hợp giữa tài xế - xe - lộ trình được quản lý bởi **ATMTC** vào **IC**.  
Tham khảo: [GitHub Issue](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/959)

* **Điều kiện hoàn thành (Definition of Done):**
    * [ ] Dữ liệu giao dịch của **ATMTC** được kết nối chính xác vào **IC**.
    * [ ] Hồ sơ hành khách của **ATMTC** có thể được xác nhận trong hệ thống **PL**.
    * [ ] Xử lý lỗi trong quá trình kết nối được triển khai.
    * [ ] Cải tiến cần thiết được thực hiện dựa trên phản hồi từ người dùng.

---

## 2. Thông số kỹ thuật (Specification)

### Yêu cầu chức năng (Functional Requirements)
* Triển khai chức năng kết nối dữ liệu giao dịch kết hợp giữa tài xế, xe, lộ trình được quản lý bởi **ATMTC** vào **IC**.
* Nếu có lỗi xảy ra trong quá trình kết nối, hệ thống sẽ hiển thị thông báo lỗi phù hợp cho người dùng.
* Nếu kết nối thành công, hệ thống sẽ hiển thị thông báo thành công và xác nhận dữ liệu đã được phản ánh trong hệ thống **PL**.
* Khi người dùng cập nhật dữ liệu, hệ thống sẽ tự động kết nối dữ liệu mới nhất vào **IC**.

### Loại tác vụ
Yêu cầu

### Tài liệu đính kèm
-

### Tài liệu tham khảo
-

### Ghi chú
-

### UI/UX (nếu có)
* **Thiết kế:**
* **Thành phần:**

### Người khởi tạo
Đào Thị Thư

---

## Implementation checklist

- [ ] Thống nhất contract dữ liệu giao dịch ATMTC (driver–vehicle–course) với spec hiện có và issue #959.
- [ ] Thiết kế luồng nhận/lưu vào IC (API hoặc job) + idempotency / retry nếu cần.
- [ ] Cập nhật model/DB (Prisma) nếu thiếu trường phục vụ hiển thị “乘車記録” trên PL.
- [ ] API backend + hiển thị trên Next (PL) theo DoD.
- [ ] Xử lý lỗi: log, thông báo người dùng, không làm hỏng trạng thái khi lỗi một phần.
- [ ] Kiểm thử (manual hoặc automated) theo kịch bản thành công / lỗi / cập nhật sau sync.

---

## Notes / review

- **Project V2:** `Izumi_Issue` (`PVT_kwDOCjwUv84Ajq0M`) — dùng khi `/breakdown` tạo issue con và add vào đúng project.
- **Liên quan:** [#959](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/959) (chuỗi ATMTC / cloud sync).
- **Git (docs root `vehicle-pl-system`):** nhánh hiện tại gợi ý cho issue này: `1010-feat-atmtc-transaction-ic`. **Không** chạy `git commit` trong luồng `/issue` — mọi thay đổi ở working tree.
- **Working tree:** repo docs root và các root khác (`cloud`, `izumi-timesheet-v2`) có thể đang dirty; khi cần checkout sạch, dùng stash / discard theo quy trình nội bộ (không commit chỉ để “dọn”).
