# イズミクラウド: IZUMI 連携機能の実装

**対象:** イズミクラウド側エンジニア

## 1. 概要

車両損益計算システム（IZUMI）の sync API を呼び出し、マスタデータ・経理データを送信する連携クライアントの実装。

### 達成目標

* [ ] 認証・権限（JWT 取得、連携用ユーザー、トークン管理）の実装
* [ ] マスタデータ連携（users、courses、vehicles、vehicle-monthly-costs、location-monthly-expenses、drivers）の実装
* [ ] トランザクションデータ連携（import、records/bulk）の実装
* [ ] PCA・ATMTC・ITP からのデータ集約と送信の実装
* [ ] 部門 ID の統一、エラーハンドリング、スケジュール実行の整備

---
## 2. 実装仕様

### 認証・権限

| 項目 | 仕様 |
|------|------|
| JWT 取得 | `POST /api/auth/login` で `userId`（または `email`）と `password` を送信 |
| 権限 | sync API 利用には **MASTER** 権限（DX または DX管理者ロール）が必要 |
| トークン管理 | JWT 有効期限 7 日。期限切れ前に再取得する仕組みを実装 |

### マスタデータ連携 API

| API | 実行タイミング |
|-----|----------------|
| POST /api/users/sync | ユーザー登録・更新時 |
| POST /api/courses/sync | コース登録・更新時 |
| POST /api/vehicles/sync | 車両登録・更新時（コース sync の後） |
| POST /api/vehicle-monthly-costs/sync | 月次（月初など）。ITP 由来の `fuelEfficiency`・`roadUsageFee` を含める |
| POST /api/location-monthly-expenses/sync | 月次（PCA 確定後） |
| POST /api/drivers/sync | ドライバー登録・更新時、ATMTC 連携後 |

### トランザクションデータ連携

| 方式 | 実行タイミング |
|------|----------------|
| POST /api/import | 経理データ確定後（月次） |
| POST /api/income-statement/records/bulk | 経理データ確定後（月次） |

### 他システムからのデータ集約

| データソース | 実装内容 |
|--------------|----------|
| PCA | 拠点別月額経費（20 科目）を取得し、location-monthly-expenses/sync で送信 |
| ATMTC | ドライバー・コース紐づきを取得し、drivers sync や courses sync に反映 |
| ITP | 燃費・道路使用料を取得し、vehicle-monthly-costs/sync の各車両データに含める |

### その他

* 部門 ID の統一（[department-id-standard.md](../docs/department-id-standard.md) 参照）
* 400/401/403/500 のレスポンスを処理し、リトライやアラートを検討
* マスタ・月次データの sync をバッチで定期実行する仕組み

---
## 3. 参考資料

- [external-integration-spec.md](../docs/external-integration-spec.md) - API 仕様
- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) - 1. イズミクラウド
- [department-id-standard.md](../docs/department-id-standard.md) - 部門 ID 運用

---
## 4. Implementation Tasks

- [ ] 認証（JWT 取得・トークン管理）の実装
- [ ] 連携用ユーザー（MASTER 権限）の作成
- [ ] POST /api/users/sync クライアント実装
- [ ] POST /api/courses/sync クライアント実装
- [ ] POST /api/vehicles/sync クライアント実装
- [ ] POST /api/vehicle-monthly-costs/sync クライアント実装（ITP データ含む）
- [ ] POST /api/location-monthly-expenses/sync クライアント実装（PCA データ含む）
- [ ] POST /api/drivers/sync クライアント実装（ATMTC 紐づき含む）
- [ ] POST /api/import クライアント実装
- [ ] POST /api/income-statement/records/bulk クライアント実装
- [ ] PCA からの拠点別月額経費データ取得・送信
- [ ] ATMTC からのドライバー・コース紐づき取得・反映
- [ ] ITP からの燃費・道路使用料データ取得・送信
- [ ] エラーハンドリング・リトライの実装
- [ ] バッチ・スケジュール実行の整備

---
# Izumi Cloud: Triển khai chức năng liên kết IZUMI

**Đối tượng:** Kỹ sư phía Izumi Cloud

## 1. Tổng quan

Triển khai client liên kết gọi sync API của hệ thống tính toán lãi lỗ xe (IZUMI) và gửi dữ liệu master・kế toán.

### Mục tiêu đạt được

* [ ] Xác thực・quyền (lấy JWT, người dùng liên kết, quản lý token)
* [ ] Liên kết dữ liệu master (users、courses、vehicles、vehicle-monthly-costs、location-monthly-expenses、drivers)
* [ ] Liên kết dữ liệu giao dịch (import、records/bulk)
* [ ] Tổng hợp và gửi dữ liệu từ PCA・ATMTC・ITP
* [ ] Thống nhất department ID, xử lý lỗi, lịch chạy batch

---
## 2. Thông số kỹ thuật triển khai

### Xác thực và quyền

| Mục | Thông số |
|-----|----------|
| Lấy JWT | Gửi `userId` (hoặc `email`) và `password` qua `POST /api/auth/login` |
| Quyền | sync API yêu cầu quyền **MASTER** (vai trò DX hoặc DX quản trị) |
| Quản lý token | JWT hiệu lực 7 ngày. Triển khai cơ chế lấy lại trước khi hết hạn |

### API liên kết dữ liệu master

| API | Thời điểm thực hiện |
|-----|---------------------|
| POST /api/users/sync | Khi đăng ký・cập nhật người dùng |
| POST /api/courses/sync | Khi đăng ký・cập nhật khóa học |
| POST /api/vehicles/sync | Khi đăng ký・cập nhật xe (sau courses sync) |
| POST /api/vehicle-monthly-costs/sync | Hàng tháng (đầu tháng, v.v.). Bao gồm `fuelEfficiency`・`roadUsageFee` từ ITP |
| POST /api/location-monthly-expenses/sync | Hàng tháng (sau khi PCA xác định) |
| POST /api/drivers/sync | Khi đăng ký・cập nhật tài xế, sau liên kết ATMTC |

### Liên kết dữ liệu giao dịch

| Phương thức | Thời điểm thực hiện |
|-------------|---------------------|
| POST /api/import | Sau khi xác định dữ liệu kế toán (hàng tháng) |
| POST /api/income-statement/records/bulk | Sau khi xác định dữ liệu kế toán (hàng tháng) |

### Tổng hợp từ hệ thống khác

| Nguồn | Nội dung triển khai |
|-------|---------------------|
| PCA | Lấy chi phí hàng tháng theo địa điểm (20 khoản), gửi qua location-monthly-expenses/sync |
| ATMTC | Lấy liên kết tài xế・khóa học, phản ánh vào drivers sync và courses sync |
| ITP | Lấy nhiên liệu・phí đường bộ, đưa vào dữ liệu từng xe của vehicle-monthly-costs/sync |

### Khác

* Thống nhất department ID (tham chiếu [department-id-standard.md](../docs/department-id-standard.md))
* Xử lý phản hồi 400/401/403/500, xem xét thử lại và cảnh báo
* Cơ chế chạy định kỳ batch cho sync master・dữ liệu hàng tháng

---
## 3. Tài liệu tham khảo

- [external-integration-spec.md](../docs/external-integration-spec.md) - Thông số API
- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) - 1. Izumi Cloud
- [department-id-standard.md](../docs/department-id-standard.md) - Vận hành department ID

---
## 4. Implementation Tasks

- [ ] Triển khai xác thực (lấy JWT・quản lý token)
- [ ] Tạo người dùng liên kết (quyền MASTER)
- [ ] Triển khai client POST /api/users/sync
- [ ] Triển khai client POST /api/courses/sync
- [ ] Triển khai client POST /api/vehicles/sync
- [ ] Triển khai client POST /api/vehicle-monthly-costs/sync (kèm dữ liệu ITP)
- [ ] Triển khai client POST /api/location-monthly-expenses/sync (kèm dữ liệu PCA)
- [ ] Triển khai client POST /api/drivers/sync (kèm liên kết ATMTC)
- [ ] Triển khai client POST /api/import
- [ ] Triển khai client POST /api/income-statement/records/bulk
- [ ] Lấy và gửi chi phí hàng tháng theo địa điểm từ PCA
- [ ] Lấy và phản ánh liên kết tài xế・khóa học từ ATMTC
- [ ] Lấy và gửi nhiên liệu・phí đường bộ từ ITP
- [ ] Triển khai xử lý lỗi・thử lại
- [ ] Hoàn thiện batch・lịch chạy
