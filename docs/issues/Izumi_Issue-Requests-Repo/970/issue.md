# Issue #970 — [BE] VPL同期: location-monthly-expenses・PCA / Chi phí điểm & PCA

## Context / Codebase Paths (from pre-questions)

```yaml
repository: TeckVeho/Izumi_Issue-Requests-Repo
repo: Izumi_Issue-Requests-Repo
issue_url: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/970
github_project_v2_id: PVT_kwDOCjwUv84Ajq0M
github_project_title: Izumi_Issue
frontend_path: .
backend_path: ./izumi-cloud
migrations_path: ./izumi-cloud/database/migrations
api_docs_path: ./docs/external-integration-spec.md
tests_path: ./izumi-cloud/tests
workspace_root: .
```

**Note:** Implementation targets **izumi-cloud** (Laravel): build payload from PCA (`pl_pca_data`) and call VPL. The **receiver** `POST /api/location-monthly-expenses/sync` lives in this repo under **`./backend`** (Node/Express + Prisma) — align request body with `backend/src/routes/location-monthly-expenses.ts`. Paths are relative to this workspace.

---

## Metadata

| Field | Value |
|--------|--------|
| **Title** | [BE] VPL同期: location-monthly-expenses・PCA / Chi phí điểm & PCA |
| **State** | OPEN |
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/970 |
| **Created** | 2026-03-27T03:39:48Z |
| **Updated** | 2026-03-30T03:50:47Z |
| **Assignees** | tungnt183855 |
| **Labels** | backend, enhancement, Child issue |

**Parent:** #956 (Plan Phase 3, `ic-sync-field-mapping.md` §6)

---

## Description (summary)

Implement **`LocationMonthlyExpenseSyncService`** in **izumi-cloud** (Laravel) to read PCA data from `pl_pca_data` table, transform it to VPL contract format, and send via `POST /api/location-monthly-expenses/sync`.

Register in `VplSyncCommand` as entity `location-monthly-expenses` with `--year-month=` parameter (same pattern as `vehicle-monthly-costs`).

---

## IC Data Model (source)

### Table: `pl_pca_data` (Model: `PLPCAData`)

Migration: `2023_05_29_174830_create_pl_pca_data_table.php`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment |
| `date` | date (indexed) | Ngày ghi nhận chi phí. Dùng `DATE_FORMAT(date, '%Y-%m')` để lọc theo `yearMonth` |
| `department_id` | bigint (indexed) | FK → `departments.id`. Transform sang `LOCxxx` format |
| `account_item_code` | string (indexed) | Mã khoa mục PCA. **Cùng format VPL** — filter 19 mã valid, skip phần còn lại (xem bảng bên dưới) |
| `cost` | double | Số tiền chi phí (VNĐ/JPY) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Fillable fields:** `date`, `department_id`, `account_item_code`, `cost`

---

## VPL Contract (receiver — vehicle-pl-system `./backend`)

### Endpoint: `POST /api/location-monthly-expenses/sync`

**File:** `backend/src/routes/location-monthly-expenses.ts`
**Auth:** `requireRole(ROLES.MASTER)` — cần user VPL có quyền DX hoặc DX管理者

### Request Body

```json
{
  "yearMonth": "2026-03",
  "expenses": [
    {
      "departmentId": "LOC001",
      "accountItemCode": "6150",
      "amount": 100000
    }
  ]
}
```

### Field Mapping (IC → VPL)

| VPL Field | IC Source | Transform |
|-----------|----------|-----------|
| `yearMonth` | `--year-month` option | `YYYY-MM` format, dùng filter `DATE_FORMAT(pl_pca_data.date, '%Y-%m')` |
| `expenses[].departmentId` | `pl_pca_data.department_id` | `CourseSyncService::toDepartmentCode($department_id)` → `"LOC" + str_pad(id, 3, '0')` |
| `expenses[].accountItemCode` | `pl_pca_data.account_item_code` | **Truyền trực tiếp** — PCA code đã cùng format VPL. Filter chỉ giữ 19 mã VPL valid (xem bảng bên dưới) |
| `expenses[].amount` | `pl_pca_data.cost` | Cast `(float)`, truyền nguyên giá trị |

### VPL Response

```json
{
  "success": true,
  "upserted": 15,
  "allocation": { "updated": 500 },
  "errors": ["Account item not found or not proration target: 9999"]
}
```

VPL sau khi upsert sẽ tự động chạy `runLocationExpenseAllocation(yearMonth)` để chia đều chi phí theo số xe từng tuyến.

---

## VPL Valid Account Item Codes (19 mã khoa mục)

Nguồn: `backend/src/lib/location-expense-proration.ts` (`LOCATION_EXPENSE_PRORATION_CODES`)

| Code | 勘定科目名 (Tên khoa mục) |
|------|---------------------------|
| `6150` | 旅費交通費 (Chi phí đi lại) |
| `6151` | 消耗品 (Vật tư tiêu hao) |
| `6154` | 修繕費 (Chi phí sửa chữa) |
| `6156` | 通信費 (Chi phí thông tin liên lạc) |
| `6159` | 水道光熱費 (Chi phí điện nước) |
| `6160` | 保険料 (Phí bảo hiểm) |
| `6162` | 租税公課 (Thuế & lệ phí công) |
| `6164` | 他手数料 (Phí dịch vụ khác) |
| `6165` | 交際接待費 (Chi phí giao tế) |
| `6166` | 会議費 (Chi phí hội nghị) |
| `6167` | 広告宣伝費 (Chi phí quảng cáo) |
| `6168` | 諸会費 (Hội phí) |
| `6171` | 地代家賃 (Tiền thuê đất) |
| `6172` | 借家料 (Tiền thuê nhà) |
| `6173` | 賃借料 (Tiền thuê mướn) |
| `6174` | 保守料 (Chi phí bảo trì) |
| `6177` | 図書研修費 (Chi phí đào tạo) |
| `6178` | 減価償却費 (Khấu hao) |
| `6188` | 雑費 (Chi phí tạp) |
| `6189` | 事故賠償費 (Chi phí bồi thường tai nạn) |

> **Note:** `external-integration-spec.md` §5.5 ghi 20 mã, code thực tế (`LOCATION_EXPENSE_PRORATION_CODES`) cũng xác nhận **20 mã**.

---

## PCA → VPL Account Code Mapping

> **✅ RESOLVED (2026-04-03):** Sau khi phân tích `PCADataImport.php` và `PLServiceRepository.php` trong izumi-cloud, xác nhận rằng **PCA `account_item_code` đã lưu trực tiếp mã số giống VPL** (6150, 6151, ..., 6189). **Không cần mapping/transform** — chỉ cần filter những code nằm trong danh sách 20 mã VPL.

### Phân tích chi tiết

**Nguồn:** `izumi-cloud/app/Imports/PCADataImport.php` (line 43-48)

PCA import chấp nhận **51 mã khoa mục** từ file CSV PCA:

| Nhóm | Mã (account_item_code) | Ghi chú |
|------|------------------------|----------|
| **VPL-compatible (20 mã)** | `6150`, `6151`, `6154`, `6156`, `6159`, `6160`, `6162`, `6164`, `6165`, `6166`, `6167`, `6168`, `6171`, `6172`, `6173`, `6174`, `6177`, `6178`, `6188`, `6189` | ✅ Trùng khớp 100% với `LOCATION_EXPENSE_PRORATION_CODES` → gửi trực tiếp sang VPL |
| **Non-VPL (31 mã)** | `6371`, `6372`, `6373`, `7037`, `7042`, `7045`, `7047`, `7049`, `7050`, `7053`, `7054`, `7055`, `7056`, `7057`, `7058`, `7059`, `7060`, `7062`, `7063`, `7064`, `7067`, `7068`, `7069`, `7071`, `7072`, `7073`, `7074`, `7075`, `7078`, `7220`, `7420` | ❌ Không thuộc VPL proration → **skip** |

### Triển khai: Filter thay vì Map

```php
/**
 * 20 mã VPL proration — trùng với LOCATION_EXPENSE_PRORATION_CODES
 * trong vehicle-pl-system/backend/src/lib/location-expense-proration.ts
 *
 * PCA account_item_code đã LƯU CÙNG FORMAT → không cần mapping.
 * Chỉ cần filter: giữ lại code ∈ VPL_VALID_CODES, skip phần còn lại.
 */
protected const VPL_VALID_ACCOUNT_CODES = [
    '6150', // 旅費交通費
    '6151', // 消耗品
    '6154', // 修繕費
    '6156', // 通信費
    '6159', // 水道光熱費
    '6160', // 保険料
    '6162', // 租税公課
    '6164', // 他手数料
    '6165', // 交際接待費
    '6166', // 会議費
    '6167', // 広告宣伝費
    '6168', // 諸会費
    '6171', // 地代家賃
    '6172', // 借家料
    '6173', // 賃借料
    '6174', // 保守料
    '6177', // 図書研修費
    '6178', // 減価償却費
    '6188', // 雑費
    '6189', // 事故賠償費
];
```

### PCA Department Map (tham khảo)

**Nguồn:** `PCADataImport.php` → `$dpMap` (line 20-41)

Khi import PCA CSV, cột thứ 6 (`row[5]`) chứa **PCA department number** — được map sang tên department IC:

| PCA Dept № | IC Department Name | Ghi chú |
|------------|-------------------|----------|
| `0` | 管理本部 | Trụ sở quản lý |
| `201` | 横浜第一 | Yokohama 1 |
| `202` | 平塚 | Hiratsuka |
| `203` | 横浜第二 | Yokohama 2 |
| `204` | 横浜第三 | Yokohama 3 |
| `205` | 静岡 | Shizuoka |
| `206` | 千葉 | Chiba |
| `207` | 東京 | Tokyo |
| `208` | 八千代 | Yachiyo |
| `209` | 古河 | Koga |
| `211` | 武蔵野 | Musashino |
| `215` | 新潟 | Niigata |
| `217` | 安城 | Anjō |
| `218` | 浜松 | Hamamatsu |
| `219` | 富山 | Toyama |
| `220` | 大阪 | Osaka |
| `221` | 神戸 | Kobe |
| `223` | 不動産管理 | Bất động sản |
| `224` | 名古屋 | Nagoya |
| `225` | 所沢 | Tokorozawa |

Trong `pl_pca_data`, cột `department_id` lưu **IC `departments.id`** (đã resolve qua `$dpMap` → tên → `departments` table). Khi gửi VPL, dùng `CourseSyncService::toDepartmentCode($department_id)` → `LOCxxx`.

**Behavior khi PCA code không nằm trong `VPL_VALID_ACCOUNT_CODES`:**
- **Skip** record đó
- **Log warning** với PCA code và department_id
- Không throw exception (để các record khác tiếp tục sync)

---

## Implementation Pattern (tham khảo)

Follow pattern của `VehicleMonthlyCostSyncService` (Issue #969):

### Service: `LocationMonthlyExpenseSyncService`

**File:** `izumi-cloud/app/Services/Vpl/LocationMonthlyExpenseSyncService.php`

```
Class interface:
├── __construct()                    — set logChannel
├── buildPayload(string $yearMonth)  — trả về ['yearMonth', 'expenses', 'skipped']
└── sync(VplClient $client, string $yearMonth) — gọi buildPayload + POST API
```

**`buildPayload($yearMonth)` logic:**

1. Query `PLPCAData` where `DATE_FORMAT(date, '%Y-%m') = $yearMonth`
2. Group by `department_id` + `account_item_code`, SUM `cost`
3. Transform:
   - `department_id` → `CourseSyncService::toDepartmentCode($id)` → `"LOCxxx"`
   - `account_item_code` → filter qua `VPL_VALID_ACCOUNT_CODES` (truyền trực tiếp, không cần map)
   - `cost` → `(float) amount`
4. Skip records with unknown PCA code → add to `$skipped` array

**`sync($client, $yearMonth)` logic:**

1. Call `buildPayload($yearMonth)`
2. POST to `/api/location-monthly-expenses/sync` via `$client->post()`
3. Handle `_error` response (throw RuntimeException)
4. Return `['total', 'synced', 'skipped', 'response']`

### VplSyncCommand Registration

**File:** `izumi-cloud/app/Console/Commands/VplSyncCommand.php`

Thay đổi cần thiết:
1. `use App\Services\Vpl\LocationMonthlyExpenseSyncService;`
2. Thêm `'location-monthly-expenses'` vào `$transactionEntities`
3. Thêm case trong `syncEntity()` — cùng pattern với `vehicle-monthly-costs` (cần `$yearMonth`)
4. Cập nhật error message valid entities
5. Cập nhật signature description

**Usage sau khi implement:**
```bash
php artisan vpl:sync --entity=location-monthly-expenses --year-month=2026-03
php artisan vpl:sync --entity=location-monthly-expenses --year-month=2026-03 --dry-run
```

---

## Dependencies

| Dependency | Status | Detail |
|------------|--------|--------|
| VPL foundation (#966) | ✅ Done | `VplClient`, JWT auth, Artisan command |
| VPL `POST /api/location-monthly-expenses/sync` | ✅ Available | `backend/src/routes/location-monthly-expenses.ts` |
| `Location.code` alignment (LOCxxx) | ✅ Done | `CourseSyncService::toDepartmentCode()` reuse |
| `PLPCAData` model + migration | ✅ Available | `app/Models/PLPCAData.php` |
| **PCA → VPL account code mapping** | ✅ Resolved | PCA code = VPL code (cùng format). Filter 20 mã VPL valid, skip 31 mã non-VPL |

---

## Implementation Checklist

- [ ] **`LocationMonthlyExpenseSyncService`** — `app/Services/Vpl/LocationMonthlyExpenseSyncService.php`
    - [ ] `buildPayload(string $yearMonth): array` — query `PLPCAData`, transform, return `['yearMonth', 'expenses', 'skipped']`
    - [ ] Query `PLPCAData` filter by `yearMonth` (dùng `whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$yearMonth])`)
    - [ ] Group by `department_id` + `account_item_code`, SUM `cost` (1 department + 1 account = 1 expense entry)
    - [ ] `departmentId` — reuse `CourseSyncService::toDepartmentCode($department_id)`
    - [ ] `accountItemCode` — filter qua `VPL_VALID_ACCOUNT_CODES` (truyền trực tiếp, không cần map)
    - [ ] Skip PCA codes không thuộc 20 mã VPL → add to `$skipped[]` with reason
    - [ ] `amount` — `(float) $cost`
    - [ ] `sync(VplClient $client, string $yearMonth): array` — send to VPL via `$client->post('/api/location-monthly-expenses/sync', ...)`
- [ ] **VplSyncCommand registration**
    - [ ] Add `'location-monthly-expenses'` to `$transactionEntities`
    - [ ] Add case in `syncEntity()` (same pattern as `vehicle-monthly-costs`)
    - [ ] Update entity validation / error messages
    - [ ] Validate `--year-month` when entity is `location-monthly-expenses`
- [ ] **PHPUnit** — `tests/Unit/Vpl/LocationMonthlyExpenseSyncServiceTest.php`
    - [ ] `buildPayload()` with `pl_pca_data` rows → correct VPL payload structure
    - [ ] PCA code filtering: 20 VPL-valid codes → pass through; 31 non-VPL codes → skipped
    - [ ] Unknown PCA code → skipped with reason
    - [ ] `departmentId` format `LOCxxx`
    - [ ] Empty data → empty expenses array
    - [ ] Multiple rows same department+account → SUM amount
- [ ] Conforms to project conventions
- [ ] No destructive changes to existing behavior

---

## Full issue body (reference)

<details>
<summary>Japanese / Vietnamese (from GitHub)</summary>

### 日本語

- Parent: #956
- `POST /api/location-monthly-expenses/sync`: PCA (`pl_pca_data`) → `yearMonth`, `departmentId` (LOC), `accountItemCode` PCA → VPL 20 codes 6150–6189, `amount`.
- Requirements: plan Phase 3, `ic-sync-field-mapping.md` §6; PHPUnit.
- Tech: izumi-cloud; BA/accounting must confirm PCA mapping.

### Tiếng Việt

- Issue cha: #956
- `location-monthly-expenses/sync`: PCA → payload với `yearMonth`, `departmentId` (LOC), filter `accountItemCode` PCA → 20 mã VPL, `amount`.
- Yêu cầu: plan Phase 3, `ic-sync-field-mapping.md` §6; PHPUnit.
- Chi tiết: izumi-cloud; cần bảng map PCA do BA/kế toán.

</details>

---

## Acceptance criteria (from issue)

- [ ] `LocationMonthlyExpenseSyncService` implemented with `buildPayload()` + `sync()`
- [ ] Registered in `VplSyncCommand` as `--entity=location-monthly-expenses`
- [ ] Unit tests created and passing
- [ ] PCA codes not in mapping are skipped with log warning (not exception)
- [ ] Conforms to project conventions (pattern matches `VehicleMonthlyCostSyncService`)
- [ ] No destructive changes to existing behavior
