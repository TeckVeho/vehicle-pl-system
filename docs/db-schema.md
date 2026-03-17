# データベース構成

車両別損益計算システム（IZUMI）のデータベーススキーマ定義です。

- **開発環境**: SQLite (`file:./dev.db`)
- **本番環境**: AWS RDS (MySQL)
- **ORM**: Prisma

---

## ER図（リレーション）

```
┌─────────────┐
│    User     │  （独立・認証用）
└─────────────┘

┌─────────────┐       ┌─────────────┐
│  Location   │───────│   Course    │
│   (拠点)    │  1:N  │  (コース)   │
└──────┬──────┘       └──────┬──────┘
       │                     │
       │ 1:N                 │ 1:N
       ▼                     ▼
┌─────────────┐       ┌─────────────────────────────────┐
│   Driver    │       │           Vehicle              │
│ (ドライバー)│       │           (車両)               │
└──────┬──────┘       └──────┬──────────┬────────┬──────┘
       │                     │          │        │
       │ 1:N                 │ 1:N      │ 1:N    │ 1:N
       ▼                     ▼          ▼        ▼
┌──────────────────┐  ┌──────────────┐   ┌────────────────────┐  ┌──────────────────┐
│DailyDriverAssign │  │ MonthlyRecord │   │DailyOperatingRecord│  │VehicleMonthlyCost│
│  (日次乗務記録)   │  │ (月次損益)   │   │ (日次稼働・走行)    │  │ (車両月次費用)   │
└────────┬─────────┘  └──────┬───────┘   └────────────────────┘  └──────────────────┘
       ▲                     │
       │ 1:N                 │ 1:N
       │                     ▼
       │              ┌──────────────────────┐
       │              │ MonthlyRecordHistory │
       │              │   (変更履歴)          │
       │              └──────────────────────┘
       │                     │
       │                     │ N:1
       │                     ▼
       │              ┌─────────────┐
       └──────────────│ AccountItem │
         DriverMonthlyAmount  │ (勘定科目)  │
         (ドライバー別月次)   └─────────────┘

┌─────────────┐
│ DataSyncLog │  （連携記録・独立）
└─────────────┘

※ DailyDriverAssignment は Driver と Vehicle を紐づける（日次乗務記録）
```

---

## テーブル一覧

### 1. User（ユーザー）

認証・権限管理用。外部システム連携に対応。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| email | String | - | メールアドレス（ユニーク） |
| passwordHash | String | - | パスワードハッシュ |
| name | String | - | 氏名 |
| role | String | - | 権限（後述） |
| externalId | String | ○ | 外部システム連携用ID |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**インデックス**: `externalId`, `role`

**有効な role 値**: CREW, 事務員, TL, 事業部, 人事労務, 総務広報, 経理財務, 品質管理, 営業, 現場MG, 本社MG, 部長, 執行役員, 取締役, DX, DX管理者

---

### 2. Location（拠点）

15拠点を想定した拠点マスタ。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| code | String | - | 拠点コード（ユニーク） |
| name | String | - | 拠点名 |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**リレーション**: Vehicle (1:N), Course (1:N), Driver (1:N)

---

### 3. Course（コース）

拠点内のコース（ルート）マスタ。vehicleNo と対応。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| locationId | String | - | 拠点ID（FK → Location） |
| name | String | - | コース名 |
| code | String | - | 拠点内識別用コード |
| sortOrder | Int | - | 表示順（デフォルト: 0） |
| externalId | String | ○ | 外部システム連携用 |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(locationId, code)`  
**インデックス**: `locationId`, `externalId`  
**削除時**: Location 削除で Cascade

---

### 4. Vehicle（車両）

約500台の車両マスタ。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| locationId | String | - | 拠点ID（FK → Location） |
| courseId | String | ○ | コースID（FK → Course） |
| vehicleNo | String | - | 車両番号 |
| serviceType | String | ○ | 便種別（例: ＣＶＳ） |
| tonnage | Float | ○ | トン数（損害保険料(任意保険)計算用） |
| externalId | String | ○ | 外部システム連携用 |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(locationId, vehicleNo)`  
**インデックス**: `locationId`, `courseId`, `externalId`  
**削除時**: Location 削除で Cascade / Course 削除で SetNull

---

### 5. Driver（ドライバー）

タイムシート連携用のドライバーマスタ。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| locationId | String | - | 拠点ID（FK → Location） |
| code | String | - | 社員番号など（拠点内で一意） |
| name | String | - | 氏名 |
| externalId | String | ○ | 外部システム連携用 |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(locationId, code)`  
**インデックス**: `locationId`, `externalId`  
**削除時**: Location 削除で Cascade

---

### 6. DailyDriverAssignment（日次乗務記録）

ドライバー×車両×日付の乗務記録。タイムシート連携後、配賦計算で MonthlyRecord を更新。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| driverId | String | - | ドライバーID（FK → Driver） |
| vehicleId | String | - | 車両ID（FK → Vehicle） |
| date | String | - | 日付（YYYY-MM-DD） |
| yearMonth | String | - | 年月（YYYY-MM、検索用） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(driverId, vehicleId, date)`  
**インデックス**: `(yearMonth, vehicleId)`, `(yearMonth, driverId)`  
**削除時**: Driver / Vehicle 削除で Cascade

---

### 7. DriverMonthlyAmount（ドライバー別月次金額）

ドライバー×勘定科目×年月の金額。タイムシートから連携し、配賦計算で車両別に按分。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| driverId | String | - | ドライバーID（FK → Driver） |
| accountItemId | String | - | 勘定科目ID（FK → AccountItem、isDriverRelated のみ） |
| yearMonth | String | - | 年月（YYYY-MM） |
| amount | Float | - | 金額（デフォルト: 0） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(driverId, accountItemId, yearMonth)`  
**インデックス**: `yearMonth`, `driverId`  
**削除時**: Driver / AccountItem 削除で Cascade

---

### 8. AccountItem（勘定科目）

損益計算書の勘定科目マスタ。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| code | String | - | 科目コード |
| name | String | - | 科目名 |
| category | String | - | カテゴリ（後述） |
| sortOrder | Int | - | 表示順 |
| isSubtotal | Boolean | - | 小計行フラグ（デフォルト: false） |
| isVehicleRelated | Boolean | - | VPL版で表示する車両関連科目フラグ（デフォルト: false） |
| isDriverRelated | Boolean | - | ドライバー配賦対象科目（乗務員給料等、デフォルト: false） |
| linkageMethod | String | ○ | 連携方法（データ連携の見える化用） |
| effectiveFrom | String | ○ | 適用開始年月（YYYY-MM） |
| effectiveTo | String | ○ | 適用終了年月（YYYY-MM） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(code, name)`  
**インデックス**: `category`, `isVehicleRelated`, `isDriverRelated`

**category の値**:  
`revenue` | `expense` | `subtotal_revenue` | `subtotal_expense` | `subtotal_gross` | `summary`

---

### 9. DailyOperatingRecord（日次稼働・走行データ）

車両×日付の稼働・走行データ。外部API連携用（回数単価・月額単価計算に使用）。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| vehicleId | String | - | 車両ID（FK → Vehicle） |
| date | String | - | 日付（YYYY-MM-DD） |
| runCount | Int | - | その日の走行回数（per_run用、デフォルト: 0） |
| isOperating | Boolean | - | 稼働日か（monthly用、デフォルト: false） |
| yearMonth | String | - | 年月（YYYY-MM、検索用） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(vehicleId, date)`  
**インデックス**: `(yearMonth, vehicleId)`  
**削除時**: Vehicle 削除で Cascade

---

### 10. VehicleMonthlyCost（車両月次費用）

車両×年月ごとの固定費用。イズミクラウドから連携し、損益計算書の以下の勘定科目で参照。

| 勘定科目 | カラム |
|----------|--------|
| リース車償却（6191） | leaseDepreciation |
| 車両償却費（6192） | vehicleDepreciation |
| 車両リース（6193） | vehicleLease |
| 損害保険料（6194） | insuranceCost |
| 賦課税（6195） | taxCost |

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| vehicleId | String | - | 車両ID（FK → Vehicle） |
| yearMonth | String | - | 年月（YYYY-MM） |
| leaseDepreciation | Float | - | リース車償却（デフォルト: 0） |
| vehicleDepreciation | Float | - | 車両償却費（デフォルト: 0） |
| vehicleLease | Float | - | 車両リース（デフォルト: 0） |
| insuranceCost | Float | - | 損害保険料（デフォルト: 0） |
| taxCost | Float | - | 賦課税（デフォルト: 0） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(vehicleId, yearMonth)`  
**インデックス**: `yearMonth`, `vehicleId`  
**削除時**: Vehicle 削除で Cascade

---

### 11. ArbitraryInsuranceMaster（任意保険マスタ）

トン数別の月額保険料。損害保険料(任意保険)の計算に使用。編集のみ（新規追加・削除は不可）。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| tonnage | Float | - | トン数（ユニーク、2, 4, 8, 10 等） |
| amount | Float | - | 月額保険料（円、デフォルト: 0） |
| sortOrder | Int | - | 表示順（デフォルト: 0） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `tonnage`

---

### 12. MonthlyRecord（月次損益データ）

車両×勘定科目×年月ごとの金額を保持。損益計算の本体。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| vehicleId | String | - | 車両ID（FK → Vehicle） |
| accountItemId | String | - | 勘定科目ID（FK → AccountItem） |
| yearMonth | String | - | 年月（例: 2026-03） |
| amount | Float | - | 金額（デフォルト: 0） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(vehicleId, accountItemId, yearMonth)`  
**インデックス**: `yearMonth`, `vehicleId`  
**削除時**: Vehicle / AccountItem 削除で Cascade

---

### 13. MonthlyRecordHistory（月次データ変更履歴）

MonthlyRecord の変更履歴を記録。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| vehicleId | String | - | 車両ID（FK → Vehicle） |
| accountItemId | String | - | 勘定科目ID（FK → AccountItem） |
| yearMonth | String | - | 年月 |
| oldAmount | Float | - | 変更前金額 |
| newAmount | Float | - | 変更後金額 |
| createdAt | DateTime | - | 記録日時 |

**インデックス**: `yearMonth`, `vehicleId`, `accountItemId`  
**削除時**: Vehicle / AccountItem 削除で Cascade

---

### 14. DataSyncLog（連携記録）

外部システムからのデータ連携の成功記録（件数のみ）。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| source | String | - | 外部システム名（例: "手動インポート", "日次売上連携"） |
| syncType | String | - | 連携種別（例: "monthly_records", "driver_assignments"） |
| recordCount | Int | - | 連携件数（デフォルト: 0） |
| yearMonth | String | ○ | 対象年月（YYYY-MM） |
| locationId | String | ○ | 対象拠点ID |
| createdAt | DateTime | - | 記録日時 |

**インデックス**: `createdAt`, `source`

---

## スキーマファイル

定義元: `backend/prisma/schema.prisma`

### マイグレーション・初期化

```bash
cd backend
npm run db:push       # または npx prisma db push
npm run db:seed       # または npx prisma db seed
```
