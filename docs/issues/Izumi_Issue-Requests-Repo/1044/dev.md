# Dev log — Issue #1044

**Parent:** [#1010](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1010)  
**Issue:** [#1044](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/1044)  
**Ngày:** 2026-04-10  

## Mục tiêu

Triển khai theo [`plan.md`](./plan.md) và phiếu PM: API tổng hợp ATMTC→VPL, lib hóa daily operating sync, **MASTER** cho `daily-operating/sync` và `atmtc-transactions` (**PM B5.1** đã chốt), **không** `runDriverAllocation` trên luồng ATMTC; IC: `VplClient` dùng credential MASTER; service + `vpl:sync --entity=atmtc-transactions`.

## Đã làm (code)

### BE-VPL (`vehicle-pl-system/backend`)

| File | Thay đổi |
|------|----------|
| `src/lib/daily-operating-records-sync.ts` | **Mới** — `syncDailyOperatingRecordsFromRows`, type `OperatingRow`. |
| `src/routes/daily-operating.ts` | `requireRole(ROLES.MASTER)` trên `POST /sync`; gọi lib; giữ `runSalaryRunCountAllocation` + `DataSyncLog`. |
| `src/routes/atmtc-transactions.ts` | **Mới** — `POST /sync`: resolve driver/vehicle, upsert assignment, gộp `weight` (mặc định 1) theo xe×ngày → lib operating; chỉ `runSalaryRunCountAllocation`; `DataSyncLog` `syncType: atmtc_transactions`, `source: ATMTC`. |
| `src/routes/index.ts` | Mount `/atmtc-transactions` với `requireRole(ROLES.MASTER)`. |
| `src/__tests__/sync-routes.contract.test.ts` | `daily-operating`: 401/403; `atmtc-transactions`: 401/403/400/200 (empty); mock `dailyDriverAssignment.upsert`. |

### FE-Web (`vehicle-pl-system/src`)

| File | Thay đổi |
|------|----------|
| `app/sync-logs/page.tsx` | Nhãn `atmtc_transactions`, `driver_assignments`. |

### BE-IC (`cloud`)

| File | Thay đổi |
|------|----------|
| `app/Services/Vpl/VplClient.php` | `postAtmtcTransactionsSync()`. |
| `app/Services/Vpl/AtmtcToVplSyncPayloadBuilder.php` | **Mới** — build payload `yearMonth` + `records[]` (B1.1: `weight` = 1). |
| `app/Services/Vpl/AtmtcToVplSyncService.php` | **Mới** — query `atmtc_delivery_data_results` theo tháng, skip dòng thiếu xe/tài xế (phía IC), `buildSyncPackage`, `sync` → `VplClient::postAtmtcTransactionsSync`. |
| `app/Console/Commands/VplSyncCommand.php` | Thêm entity **`atmtc-transactions`** (`--year-month`, `--department-code`, `--location-id`, `--dry-run`). |
| `app/Repositories/AtmtcToVplDataConnectionRepository.php` | **Mới** — hook **Data Connection** / `DataConnectionJob`: `syncAtmtcTransactionsToVpl` (tháng lịch = tháng của `$date` schedule). |
| `database/seeders/AtmtcToVplDataConnectionSeeder.php` | **Mới** — `data_code` **ICL_1044**, `dailyAt` **03:15** (sau import ICL_1039 02:45). |
| `config/vpl.php` | `data_connection_system_name` — nhãn System **to** trên UI DataConnect. |
| `tests/Unit/Services/Vpl/AtmtcToVplSyncPayloadBuilderTest.php` | **Mới** — 2 test; **đã chạy pass** (`php artisan test`). |
| `tests/Feature/Services/Vpl/AtmtcToVplSyncServiceTest.php` | **Mới** — 2 test (build package + query theo tháng); **đã chạy pass**. |
| `tests/Feature/Repositories/AtmtcToVplDataConnectionRepositoryTest.php` | **Mới** — success + fail VPL; **đã chạy pass**. |

## Chưa làm / việc tiếp

- **Môi trường:** chạy `php artisan db:seed --class=AtmtcToVplDataConnectionSeeder` (sau khi có System `イズミクラウド`). Lịch: **`routes/console.php`** tự đăng ký `schedule:data_connection {id}` cho mọi `DataConnection` `active` — không cần thêm route riêng.
- **Thông báo skip (PM B2.1)** — VPL trả `errors[]`; IC cần queue/mail sau khi gọi API.
- **Vitest (VPL):** dùng **Node qua nvm** (tránh `Program Files\nodejs` v14 mặc định). File **`backend/.nvmrc`** = `20.19.5` (khớp bản đã cài trong nvm-windows).  
  - **cmd / PowerShell:** `nvm use 20.19.5` → `cd backend` → `npm install` (lần đầu) → `npm test` hoặc `npx vitest run src/__tests__/sync-routes.contract.test.ts`.  
  - **Git Bash (PATH thủ công):** `export PATH="/c/Users/Administrator/AppData/Local/nvm/v20.19.5:$PATH"` rồi các lệnh trên.  
  - Đã chạy: **43 tests passed** (`sync-routes.contract.test.ts`).

## Ghi chú kiến trúc

- `ATMTC_TRANSACTIONS_SYNC_TYPE = 'atmtc_transactions'` export từ route file — đồng bộ với label FE.
- `runCountByVehicleDate` cộng dồn `weight` cho mỗi dòng assignment thành công; sau đó một lần `syncDailyOperatingRecordsFromRows` với các dòng đã gộp.

## Quy ước workspace

- **Không** `git commit` trong phase `/dev` (theo command).
- Sau chỉnh `dev.md`: copy `docs/issues/Izumi_Issue-Requests-Repo/1044/` sang `cloud` và `izumi-timesheet-v2`.
