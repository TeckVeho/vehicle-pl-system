# タイムシート: IZUMI 連携機能の実装

**対象:** タイムシート側エンジニア

## 1. 概要

車両損益計算システム（IZUMI）の sync API を呼び出し、日次稼働・乗務記録・ドライバー給与データを送信する連携クライアントの実装。

### 達成目標

* [ ] 認証（JWT 取得）と MASTER 権限の連携用ユーザーが設定されていること
* [ ] POST /api/daily-operating/sync が実装され、日次稼働・走行データが送信されること
* [ ] POST /api/driver-assignments/sync が実装され、日次乗務記録が送信されること
* [ ] POST /api/driver-monthly-amounts/sync が実装され、ドライバー別月次金額が送信されること
* [ ] 車両・ドライバー識別（externalId）、部門指定、勘定科目の要件を満たしていること
* [ ] 連携順序（ドライバー sync 後）とスケジュールが整備されていること

---
## 2. 実装仕様

### 認証・権限

| 項目 | 仕様 |
|------|------|
| JWT 取得 | `POST /api/auth/login` でログイン |
| 権限 | sync API 利用には **MASTER** 権限が必要。イズミクラウドで連携用ユーザーを作成し、権限を付与 |

### 連携 API クライアント

| API | 実装内容 | 実行タイミング |
|-----|----------|----------------|
| POST /api/daily-operating/sync | 日次稼働・走行データ（`vehicleId`/`vehicleExternalId`、`date`、`runCount`、`isOperating`） | 日次（翌日など） |
| POST /api/driver-assignments/sync | 日次乗務記録（ドライバー×車両×日付）。連携後にドライバー配賦が自動実行 | 日次（翌日など） |
| POST /api/driver-monthly-amounts/sync | ドライバー別月次金額。`accountItemCode` 6138/6147/6148 を指定 | 月次（給与確定後） |

### データ要件

| 項目 | 仕様 |
|------|------|
| 車両・ドライバー識別 | `vehicleExternalId`、`driverExternalId` はイズミクラウド sync で登録済みの externalId と一致させる |
| 部門指定 | `departmentId` で部門を指定し、対象部門のデータのみ送信 |
| 勘定科目 | 乗務員給料(6138)、通勤手当(6147)、法定福利費(6148)。業務給料(6139)・福利厚生費(6149)は対象外 |

### 連携順序

1. ドライバー sync（イズミクラウド）が完了していること
2. 乗務記録・日次稼働・ドライバー別月次金額の順で連携

---
## 3. 参考資料

- [external-integration-spec.md](../docs/external-integration-spec.md) - API 仕様
- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) - 2. タイムシート
- [driver-allocation-api.md](../docs/driver-allocation-api.md) - ドライバー配賦 API

---
## 4. Implementation Tasks

- [ ] 認証（JWT 取得）の実装
- [ ] 連携用ユーザー（MASTER 権限）の設定
- [ ] POST /api/daily-operating/sync クライアント実装
- [ ] POST /api/driver-assignments/sync クライアント実装
- [ ] POST /api/driver-monthly-amounts/sync クライアント実装
- [ ] vehicleExternalId / driverExternalId とイズミクラウドの externalId の整合
- [ ] departmentId による部門指定の実装
- [ ] 日次・月次バッチのスケジュール整備

---
# Timesheet: Triển khai chức năng liên kết IZUMI

**Đối tượng:** Kỹ sư phía Timesheet

## 1. Tổng quan

Triển khai client liên kết gọi sync API của hệ thống tính toán lãi lỗ xe (IZUMI) và gửi dữ liệu vận hành hàng ngày, ghi chép phục vụ và lương tài xế.

### Mục tiêu đạt được

* [ ] Xác thực (lấy JWT) và người dùng liên kết có quyền MASTER đã được thiết lập
* [ ] POST /api/daily-operating/sync đã triển khai, dữ liệu vận hành・chạy xe hàng ngày được gửi
* [ ] POST /api/driver-assignments/sync đã triển khai, ghi chép phục vụ hàng ngày được gửi
* [ ] POST /api/driver-monthly-amounts/sync đã triển khai, số tiền hàng tháng theo tài xế được gửi
* [ ] Đáp ứng yêu cầu nhận dạng xe・tài xế (externalId), chỉ định bộ phận, khoản mục kế toán
* [ ] Thứ tự liên kết (sau drivers sync) và lịch trình đã được hoàn thiện

---
## 2. Thông số kỹ thuật triển khai

### Xác thực và quyền

| Mục | Thông số |
|-----|----------|
| Lấy JWT | Đăng nhập qua `POST /api/auth/login` |
| Quyền | sync API yêu cầu quyền **MASTER**. Tạo người dùng liên kết trên Izumi Cloud và cấp quyền |

### Client API liên kết

| API | Nội dung triển khai | Thời điểm |
|-----|---------------------|-----------|
| POST /api/daily-operating/sync | Dữ liệu vận hành・chạy xe hàng ngày (`vehicleId`/`vehicleExternalId`, `date`, `runCount`, `isOperating`) | Hàng ngày (ngày hôm sau, v.v.) |
| POST /api/driver-assignments/sync | Ghi chép phục vụ hàng ngày (tài xế×xe×ngày). Sau liên kết tự động chạy phân bổ tài xế | Hàng ngày (ngày hôm sau, v.v.) |
| POST /api/driver-monthly-amounts/sync | Số tiền hàng tháng theo tài xế. Chỉ định `accountItemCode` 6138/6147/6148 | Hàng tháng (sau khi xác nhận lương) |

### Yêu cầu dữ liệu

| Mục | Thông số |
|-----|----------|
| Nhận dạng xe・tài xế | `vehicleExternalId`, `driverExternalId` khớp với externalId đã đăng ký qua sync Izumi Cloud |
| Chỉ định bộ phận | Chỉ định bộ phận qua `departmentId`, chỉ gửi dữ liệu bộ phận đó |
| Khoản mục kế toán | Lương lái xe(6138), phụ cấp đi lại(6147), phúc lợi pháp định(6148). Lương nghiệp vụ(6139)・phúc lợi(6149) không thuộc đối tượng |

### Thứ tự liên kết

1. drivers sync (Izumi Cloud) đã hoàn thành
2. Liên kết theo thứ tự: ghi chép phục vụ → vận hành hàng ngày → số tiền hàng tháng theo tài xế

---
## 3. Tài liệu tham khảo

- [external-integration-spec.md](../docs/external-integration-spec.md) - Thông số API
- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) - 2. Timesheet
- [driver-allocation-api.md](../docs/driver-allocation-api.md) - API phân bổ tài xế

---
## 4. Implementation Tasks

- [ ] Triển khai xác thực (lấy JWT)
- [ ] Thiết lập người dùng liên kết (quyền MASTER)
- [ ] Triển khai client POST /api/daily-operating/sync
- [ ] Triển khai client POST /api/driver-assignments/sync
- [ ] Triển khai client POST /api/driver-monthly-amounts/sync
- [ ] Đồng bộ vehicleExternalId / driverExternalId với externalId Izumi Cloud
- [ ] Triển khai chỉ định bộ phận qua departmentId
- [ ] Hoàn thiện lịch batch hàng ngày・hàng tháng
