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
       ├── LocationMonthlyExpense（拠点別月額経費）
       ├── LocationCalculationParameter（燃料単価・道路割引率）
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
       │              ┌─────────────────┐
       └──────────────│   AccountItem   │
                      │   (勘定科目)    │
                      └────────┬────────┘
                               │ 1:N（LocationMonthlyExpense と紐づく）
         DriverMonthlyAmount（ドライバー別月次）も同じ AccountItem を参照

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

拠点（部門）マスタ。シード例では LOC001 〜 など複数件を投入。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| code | String | - | 拠点コード（ユニーク） |
| name | String | - | 拠点名 |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**リレーション**: Vehicle (1:N), Course (1:N), Driver (1:N), LocationMonthlyExpense (1:N), LocationCalculationParameter (1:N)

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

損益計算書の勘定科目マスタ。各科目の取得方法は [account-item-calculation-spec.md](account-item-calculation-spec.md) を参照。

**売上科目のデータソース**:
- 山崎製パン〜関東運輸: 各拠点スプレッドシート参照のみ
- その他・不動産収入・人材派遣収入: 手入力のみ（MonthlyRecord）

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| code | String | - | 科目コード |
| name | String | - | 科目名 |
| category | String | - | カテゴリ（後述） |
| sortOrder | Int | - | 表示順 |
| isSubtotal | Boolean | - | 小計行フラグ（デフォルト: false） |
| isVehicleRelated | Boolean | - | VPL版で表示する車両関連科目フラグ（デフォルト: false） |
| isDriverRelated | Boolean | - | ドライバー配賦対象科目（乗務員給料・通勤手当等。乗務員給料・通勤手当は表示時に乗車回数で按分、デフォルト: false） |
| revenuePricingType | String | ○ | 売上単価体系: `per_run`（回数単価） / `monthly`（月額単価） / null（均等割り）。日次サマリーの売上按分に使用 |
| linkageMethod | String | ○ | 連携方法（データ連携の見える化用） |
| effectiveFrom | String | ○ | 適用開始年月（YYYY-MM） |
| effectiveTo | String | ○ | 適用終了年月（YYYY-MM） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(code, name)`  
**インデックス**: `category`, `isVehicleRelated`, `isDriverRelated`  
**リレーション**: MonthlyRecord (1:N), MonthlyRecordHistory (1:N), DriverMonthlyAmount (1:N), LocationMonthlyExpense (1:N)

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

### 10. LocationMonthlyExpense（拠点別月額経費）

拠点×勘定科目×年月ごとの月額合計。PCA からイズミクラウド経由で連携し、車両数で按分して MonthlyRecord に反映。

| 勘定科目例 | 科目コード |
|------------|------------|
| 旅費交通費・消耗品・修繕費等 | 6150〜6189（20科目） |

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| locationId | String | - | 拠点ID（FK → Location） |
| accountItemId | String | - | 勘定科目ID（FK → AccountItem） |
| yearMonth | String | - | 年月（YYYY-MM） |
| amount | Float | - | 拠点の月額合計（車両数で按分前） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(locationId, accountItemId, yearMonth)`
**インデックス**: `yearMonth`, `locationId`
**削除時**: Location 削除で Cascade

---

### 11. VehicleMonthlyCost（車両月次費用）

車両×年月ごとの固定費用。イズミクラウド/ITPから連携し、損益計算書の以下の勘定科目で参照。

| 勘定科目 | カラム |
|----------|--------|
| リース車償却（6191） | leaseDepreciation |
| 車両償却費（6192） | vehicleDepreciation |
| 車両リース（6193） | vehicleLease |
| 損害保険料（6194） | insuranceCost |
| 賦課税（6195） | taxCost |
| 燃料費（6175） | fuelEfficiency × 燃料単価（計算） |
| 道路使用料（6176） | roadUsageFee × 使用料割引率（計算） |

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
| fuelEfficiency | Float | - | 燃費（L、ITP連携。燃料費 = 燃費 × 燃料単価） |
| roadUsageFee | Float | - | 道路使用料（ITP連携の生データ） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(vehicleId, yearMonth)`  
**インデックス**: `yearMonth`, `vehicleId`  
**削除時**: Vehicle 削除で Cascade

---

### 12. LocationCalculationParameter（拠点別計算パラメータ）

拠点×年月ごとの燃料単価・道路使用料割引率。燃料費・道路使用料の算出に使用。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| locationId | String | - | 拠点ID（FK → Location） |
| yearMonth | String | - | 年月（YYYY-MM） |
| fuelUnitPrice | Float | - | 燃料単価（円/L、デフォルト: 0） |
| roadUsageDiscountRate | Float | - | 使用料割引率（0〜1、デフォルト: 1） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(locationId, yearMonth)`  
**インデックス**: `yearMonth`, `locationId`  
**削除時**: Location 削除で Cascade

---

### 13. ArbitraryInsuranceMaster（任意保険マスタ）

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

### 14. MonthlyRecord（月次損益データ）

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

### 15. MonthlyRecordHistory（月次データ変更履歴）

MonthlyRecord の変更履歴を記録。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| vehicleId | String | - | 車両ID（FK → Vehicle） |
| accountItemId | String | - | 勘定科目ID（FK → AccountItem） |
| yearMonth | String | - | 年月 |
| oldAmount | Float | - | 変更前金額 |
| newAmount | Float | - | 変更後金額 |
| createdById | String | ○ | 変更実行者（FK → User、画面編集時など） |
| createdAt | DateTime | - | 記録日時 |

**インデックス**: `yearMonth`, `vehicleId`, `accountItemId`  
**削除時**: Vehicle / AccountItem 削除で Cascade / User 削除で SetNull

---

### 16. DataSyncLog（連携記録）

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
