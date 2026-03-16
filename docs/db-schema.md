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
┌─────────────────────────────────┐
│           Vehicle              │
│           (車両)               │
└──────┬──────────────────┬──────┘
       │                  │
       │ 1:N              │ 1:N
       ▼                  ▼
┌──────────────┐   ┌──────────────────────┐
│ MonthlyRecord│   │ MonthlyRecordHistory  │
│ (月次損益)   │   │   (変更履歴)          │
└──────┬───────┘   └──────────────────────┘
       │
       │ N:1
       ▼
┌─────────────┐
│ AccountItem │
│ (勘定科目)  │
└─────────────┘
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

**リレーション**: Vehicle (1:N), Course (1:N)

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
| externalId | String | ○ | 外部システム連携用 |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(locationId, vehicleNo)`  
**インデックス**: `locationId`, `courseId`, `externalId`  
**削除時**: Location 削除で Cascade / Course 削除で SetNull

---

### 5. AccountItem（勘定科目）

損益計算書の勘定科目マスタ。

| カラム | 型 | NULL | 説明 |
|--------|-----|------|------|
| id | String | - | 主キー（cuid） |
| code | String | - | 科目コード |
| name | String | - | 科目名 |
| category | String | - | カテゴリ（後述） |
| sortOrder | Int | - | 表示順 |
| isSubtotal | Boolean | - | 小計行フラグ（デフォルト: false） |
| linkageMethod | String | ○ | 連携方法（データ連携の見える化用） |
| effectiveFrom | String | ○ | 適用開始年月（YYYY-MM） |
| effectiveTo | String | ○ | 適用終了年月（YYYY-MM） |
| createdAt | DateTime | - | 作成日時 |
| updatedAt | DateTime | - | 更新日時 |

**ユニーク制約**: `(code, name)`  
**インデックス**: `category`

**category の値**:  
`revenue` | `expense` | `subtotal_revenue` | `subtotal_expense` | `subtotal_gross` | `summary`

---

### 6. MonthlyRecord（月次損益データ）

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

### 7. MonthlyRecordHistory（月次データ変更履歴）

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

## スキーマファイル

定義元: `backend/prisma/schema.prisma`

### マイグレーション・初期化

```bash
cd backend
npm run db:push       # または npx prisma db push
npm run db:seed       # または npx prisma db seed
```
