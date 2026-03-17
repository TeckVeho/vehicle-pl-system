# 車両損益計算書 外部連携仕様書

車両別損益計算システム（IZUMI）の車両損益計算書作成に必要な外部システム連携の技術仕様です。エンジニア向けに API 仕様・データフロー・認証方式を記載します。

---

## 1. 概要

### 1.1 目的

本システムは約500台の車両×勘定科目×年月ごとの損益データを管理し、車両損益計算書（VPL）を生成します。自社システム「**イズミクラウド**」および「**タイムシート**」からデータを連携し、損益計算書を自動構築するための API を提供します。

### 1.2 データ連携元

| 種別 | 連携元システム | 説明 |
|------|----------------|------|
| **マスタデータ** | **イズミクラウド** | ユーザー、部門（Department）、車両、コース、ドライバーなどのマスタは自社システム「イズミクラウド」から連携 |
| **トランザクションデータ** | **タイムシート** | 日次乗務記録、ドライバー別月次金額、日次稼働・走行データは「タイムシート」から連携 |
| **ドライバー・コース紐づき** | **ATMTC** → **イズミクラウド** | 日次乗務記録のためのドライバーとコースの紐づきは「ATMTC」で取得したものを「イズミクラウド」経由で本システムに連携 |

### 1.3 連携対象データ一覧

| 種別 | データ | 連携元 | 連携方式 | 備考 |
|------|--------|--------|----------|------|
| マスタ | ユーザー | イズミクラウド | POST /api/users/sync | 認証・権限管理（user_id で連携） |
| マスタ | 部門（Department） | イズミクラウド | 本システム内で管理 | department id で識別 |
| マスタ | 車両 | イズミクラウド | POST /api/vehicles/sync | 部門・コース紐づけ |
| マスタ | 車両月次費用 | イズミクラウド | POST /api/vehicle-monthly-costs/sync | リース車償却・車両償却費・車両リース・損害保険料・賦課税 |
| マスタ | コース | イズミクラウド | POST /api/courses/sync | 車両番号と1:1対応 |
| マスタ | ドライバー | イズミクラウド | POST /api/drivers/sync | ATMTC の紐づきを経由 |
| トランザクション | 月次損益 | イズミクラウド等 | POST /api/import または records/bulk | 経理・売上データ |
| トランザクション | 日次稼働・走行 | タイムシート | POST /api/daily-operating/sync | 回数単価・月額単価計算用 |
| トランザクション | 日次乗務記録 | タイムシート | POST /api/driver-assignments/sync | ドライバー配賦の入力 |
| トランザクション | ドライバー別月次金額 | タイムシート | POST /api/driver-monthly-amounts/sync | 乗務員給料等の配賦元 |

### 1.4 技術スタック

| 項目 | 内容 |
|------|------|
| API ベースURL | `NEXT_PUBLIC_API_URL` で指定（未設定時: `/api` プロキシ経由） |
| 認証 | Cookie（httpOnly）または Bearer Token |
| データ形式 | JSON（multipart/form-data はインポートのみ） |

---

## 2. 認証

### 2.1 認証方式

- **Cookie 認証**: ブラウザ経由の場合は `auth-token` Cookie に JWT を格納
- **Bearer Token**: 外部システム連携時は `Authorization: Bearer <JWT>` ヘッダーを推奨

JWT は `POST /api/auth/login` で取得。有効期限は 7 日間。

### 2.2 ログイン（user_id 対応）

**POST /api/auth/login**

ログインは **user_id**（イズミクラウドのユーザーID）またはメールアドレスで可能です。

```json
{
  "userId": "izumi-user-123",
  "password": "xxx"
}
```

または

```json
{
  "email": "user@example.com",
  "password": "xxx"
}
```

- `userId`: イズミクラウドのユーザーID（本システムの `externalId` に相当）
- `email`: メールアドレス（従来互換）
- いずれか一方を指定

### 2.3 権限による API 制御

| 権限 | 対象ロール | 利用可能 API |
|------|------------|--------------|
| EDIT_PL | 現場MG, 本社MG, 経理財務, 部長, 執行役員, 取締役, DX, DX管理者 | インポート、損益データ編集 |
| MASTER | DX, DX管理者 | 車両/コース/ドライバー sync、勘定科目編集 |
| USER_ADMIN | DX管理者 | ユーザー管理・sync |

外部連携用 sync API の多くは **MASTER** 権限が必要です。連携用ユーザーは DX または DX管理者 ロールを付与してください。

---

## 3. 部門識別子（Department）

### 3.1 標準識別子: department id

部門の共通識別子として **department id** を使用します。イズミクラウドと同一の department id を共有してください。

| 項目 | 内容 |
|------|------|
| 識別子 | department id（イズミクラウドの部門ID） |
| 取得 | GET /api/locations で一覧取得（内部では Location.code にマッピング） |
| sync API | `departmentId` パラメータで指定 |

詳細は [department-id-standard.md](department-id-standard.md) を参照。

---

## 4. データ連携の順序と依存関係

```
[1] 部門（Department）   … 本システム内で管理、イズミクラウドと department id で整合
[2] コース（Course）     … イズミクラウドから POST /api/courses/sync
[3] 車両（Vehicle）      … イズミクラウドから POST /api/vehicles/sync（courseId/courseExternalId で紐づけ）
[4] 車両月次費用（VehicleMonthlyCost） … イズミクラウドから POST /api/vehicle-monthly-costs/sync（リース車償却・車両償却費・車両リース・損害保険料・賦課税）
[5] ドライバー（Driver）  … イズミクラウドから POST /api/drivers/sync（ATMTC の紐づきを経由）
[6] 月次損益（MonthlyRecord） … インポート or records/bulk
[7] 日次稼働（DailyOperatingRecord） … タイムシートから POST /api/daily-operating/sync
[8] 乗務記録（DailyDriverAssignment） … タイムシートから POST /api/driver-assignments/sync
[9] ドライバー別月次金額（DriverMonthlyAmount） … タイムシートから POST /api/driver-monthly-amounts/sync
```

- 車両 sync はコース sync の後に行う（courseId / courseExternalId で紐づけ可能）
- ドライバー・コースの紐づきは ATMTC で取得し、イズミクラウド経由で連携する
- 乗務記録・ドライバー別月次金額 sync 後は、自動でドライバー配賦が実行され MonthlyRecord が更新される

---

## 5. マスタデータ連携 API（イズミクラウドから）

### 5.1 ユーザー一括同期

**POST /api/users/sync**

イズミクラウドからユーザーマスタを連携。**user_id** を識別子として使用します。

| 項目 | 内容 |
|------|------|
| 権限 | USER_ADMIN（DX管理者） |
| Content-Type | application/json |

**リクエストボディ**

```json
{
  "users": [
    {
      "userId": "izumi-user-123",
      "email": "user@example.com",
      "name": "山田太郎",
      "role": "経理財務",
      "password": "optional"
    }
  ]
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| userId | ○ | イズミクラウドのユーザーID（upsert キー、本システムの externalId に格納） |
| email | ○ | メールアドレス |
| name | ○ | 氏名 |
| role | ○ | 権限（VALID_ROLES 参照） |
| password | - | 未指定時は "changeme" |

**有効な role**: CREW, 事務員, TL, 事業部, 人事労務, 総務広報, 経理財務, 品質管理, 営業, 現場MG, 本社MG, 部長, 執行役員, 取締役, DX, DX管理者

---

### 5.2 コース一括同期

**POST /api/courses/sync**

イズミクラウドからコースマスタを連携。

| 項目 | 内容 |
|------|------|
| 権限 | MASTER |
| Content-Type | application/json |

**リクエストボディ**

```json
{
  "courses": [
    {
      "departmentId": "DEPT001",
      "code": "001-001",
      "name": "山崎製パンＡ便",
      "sortOrder": 0,
      "externalId": "ext-course-456"
    }
  ]
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| departmentId | ○ | 部門ID（イズミクラウドの department id） |
| code | ○ | 部門内識別用（vehicleNo と対応） |
| name | ○ | コース名 |
| sortOrder | - | 表示順（デフォルト: 0） |
| externalId | - | 外部システム識別子（upsert キー） |

※ API 実装では `departmentId` は `locationCode` として受け付け（Location.code にマッピング）

---

### 5.3 車両一括同期

**POST /api/vehicles/sync**

イズミクラウドから車両マスタを連携。

| 項目 | 内容 |
|------|------|
| 権限 | MASTER |
| Content-Type | application/json |

**リクエストボディ**

```json
{
  "vehicles": [
    {
      "departmentId": "DEPT001",
      "vehicleNo": "001-001",
      "serviceType": "ＣＶＳ",
      "tonnage": 4,
      "externalId": "ext-vehicle-123",
      "courseId": "clxxx",
      "courseExternalId": "ext-course-456"
    }
  ]
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| departmentId | ○ | 部門ID（イズミクラウドの department id） |
| vehicleNo | ○ | 車両番号（部門内ユニーク） |
| serviceType | - | 便種別（例: ＣＶＳ） |
| tonnage | - | トン数（損害保険料(任意保険)計算用） |
| externalId | - | 外部システム識別子（upsert キー） |
| courseId / courseExternalId | - | コース紐づけ（いずれか） |

※ API 実装では `departmentId` は `locationCode` として受け付け

---

### 5.4 車両月次費用一括同期

**POST /api/vehicle-monthly-costs/sync**

イズミクラウドから車両別・年月別の固定費用を連携。以下の勘定科目は本 API で連携した値を損益計算書で参照します。

| 勘定科目 | 科目コード | 説明 |
|----------|------------|------|
| リース車償却 | 6191 | リース車両の償却費 |
| 車両償却費 | 6192 | 車両の減価償却費 |
| 車両リース | 6193 | 車両リース料 |
| 損害保険料(自賠責) | 6194 | 自賠責保険料 |
| 賦課税(自動車税) | 6195 | 自動車税 |

| 項目 | 内容 |
|------|------|
| 権限 | MASTER |
| Content-Type | application/json |

**リクエストボディ**

```json
{
  "yearMonth": "2026-03",
  "costs": [
    {
      "vehicleId": "clxxx",
      "vehicleExternalId": "ext-vehicle-123",
      "vehicleNo": "001-001",
      "departmentId": "DEPT001",
      "leaseDepreciation": 50000,
      "vehicleDepreciation": 0,
      "vehicleLease": 80000,
      "insuranceCost": 15000,
      "taxCost": 25000
    }
  ]
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| yearMonth | ○ | 対象年月（YYYY-MM） |
| costs | ○ | 車両別費用の配列 |
| costs[].vehicleId / vehicleExternalId / (vehicleNo + departmentId) | いずれか | 車両の特定 |
| costs[].leaseDepreciation | - | リース車償却（6191） |
| costs[].vehicleDepreciation | - | 車両償却費（6192） |
| costs[].vehicleLease | - | 車両リース（6193） |
| costs[].insuranceCost | - | 損害保険料（6194） |
| costs[].taxCost | - | 賦課税（6195） |

※ 損益計算書表示時、上記5科目は VehicleMonthlyCost の値を優先し、MonthlyRecord の値は参照しません。

---

### 5.5 ドライバー一括同期

**POST /api/drivers/sync**

イズミクラウドからドライバーマスタを連携。ドライバーとコースの紐づきは ATMTC で取得したものをイズミクラウド経由で連携します。

| 項目 | 内容 |
|------|------|
| 権限 | MASTER |
| Content-Type | application/json |

**リクエストボディ**

```json
{
  "drivers": [
    {
      "departmentId": "DEPT001",
      "code": "EMP001",
      "name": "山田太郎",
      "externalId": "ext-driver-123"
    }
  ]
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| departmentId | ○ | 部門ID（イズミクラウドの department id） |
| code | ○ | 社員番号など（部門内ユニーク） |
| name | - | 氏名（未指定時は code を使用） |
| externalId | - | 外部システム識別子（upsert キー） |

※ API 実装では `departmentId` は `locationCode` として受け付け

---

## 6. トランザクションデータ連携 API（タイムシートから）

### 6.1 月次損益データ（車両損益計算書の本体）

イズミクラウド等から経理・売上データを連携。

#### 方式A: ファイルインポート（CSV/Excel）

**POST /api/import**

| 項目 | 内容 |
|------|------|
| 権限 | EDIT_PL |
| Content-Type | multipart/form-data |

| パラメータ | 必須 | 説明 |
|------------|------|------|
| file | ○ | CSV または Excel（.xlsx, .xls） |
| locationId | ○ | 部門ID（内部 Location.id） |
| yearMonth | ○ | 年月（YYYY-MM） |

**CSV 形式**

```csv
コース名,勘定科目名,金額
001-001,売上高,1500000
001-001,燃料費,80000
```

- 1行目: ヘッダー（コース名,勘定科目名,金額）
- 2行目以降: 車両番号（vehicleNo）、勘定科目名または科目コード、金額
- 勘定科目は `AccountItem.name` または `AccountItem.code` で照合
- 車両は `Vehicle.vehicleNo` で照合
- **手入力専用科目**（その他、不動産収入、人材派遣収入）はインポート不可。損益計算書画面から直接入力

**Excel 形式**: 同様に A列=車両番号、B列=勘定科目、C列=金額

**レスポンス**

```json
{
  "success": 15,
  "errors": ["3行目: 勘定科目「XXX」が見つかりません"]
}
```

#### 方式B: API 一括登録

**POST /api/income-statement/records/bulk**

| 項目 | 内容 |
|------|------|
| 権限 | EDIT_PL |
| Content-Type | application/json |

**リクエストボディ**

```json
{
  "yearMonth": "2026-03",
  "records": [
    {
      "vehicleId": "clxxx",
      "accountItemId": "clxxx",
      "amount": 150000
    }
  ]
}
```

- `vehicleId`, `accountItemId` は内部 ID を指定（GET /api/vehicles, GET /api/account-items で取得）
- 変更履歴（MonthlyRecordHistory）が自動記録される
- **手入力専用科目**（その他、不動産収入、人材派遣収入）は一括登録不可。該当レコードはスキップされる

---

### 6.2 日次稼働・走行データ

**POST /api/daily-operating/sync**

**タイムシート**から連携。回数単価・月額単価計算に使用。

**リクエストボディ**

```json
{
  "yearMonth": "2026-03",
  "departmentId": "DEPT001",
  "records": [
    {
      "vehicleId": "clxxx",
      "vehicleExternalId": "ext-vehicle-123",
      "date": "2026-03-05",
      "runCount": 2,
      "isOperating": true
    }
  ]
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| yearMonth | ○ | YYYY-MM |
| departmentId | - | 指定時は該当部門の車両のみ（API では locationId として受け付け可） |
| records[].vehicleId / vehicleExternalId | いずれか | 車両指定 |
| records[].date | ○ | YYYY-MM-DD（yearMonth に属すること） |
| records[].runCount | - | 走行回数（デフォルト: 0） |
| records[].isOperating | - | 稼働日フラグ（デフォルト: false） |

---

### 6.3 日次乗務記録（ドライバー配賦用）

**POST /api/driver-assignments/sync**

**タイムシート**から連携。ドライバーとコースの紐づきは ATMTC で取得したものをイズミクラウド経由で事前に連携しておく必要があります。連携後にドライバー配賦を自動実行し、MonthlyRecord を更新。

| 項目 | 内容 |
|------|------|
| 権限 | MASTER |
| 詳細 | [driver-allocation-api.md](driver-allocation-api.md) 参照 |

**リクエストボディ**

```json
{
  "yearMonth": "2026-03",
  "departmentId": "DEPT001",
  "records": [
    {
      "driverId": "clxxx",
      "driverExternalId": "ext-driver-123",
      "vehicleId": "clxxx",
      "vehicleExternalId": "ext-vehicle-456",
      "date": "2026-03-05"
    }
  ]
}
```

---

### 6.4 ドライバー別月次金額（ドライバー配賦用）

**POST /api/driver-monthly-amounts/sync**

**タイムシート**から連携。乗務員給料・業務給料・通勤手当・法定福利費・福利厚生費などを連携。連携後に配賦計算を実行。

| 項目 | 内容 |
|------|------|
| 権限 | MASTER |
| 詳細 | [driver-allocation-api.md](driver-allocation-api.md) 参照 |

**リクエストボディ**

```json
{
  "yearMonth": "2026-03",
  "departmentId": "DEPT001",
  "records": [
    {
      "driverId": "clxxx",
      "driverExternalId": "ext-driver-123",
      "accountItemId": "clxxx",
      "accountItemCode": "6138",
      "amount": 150000
    }
  ]
}
```

- 勘定科目は `isDriverRelated: true` のもののみ対象
- 科目コード例: 乗務員給料(6138), 業務給料(6139), 通勤手当(6147), 法定福利費(6148), 福利厚生費(6149)

---

## 7. 連携記録（DataSyncLog）

### 7.1 連携ログの取得

**GET /api/sync-logs**

```json
[
  {
    "id": "clxxx",
    "source": "タイムシート連携",
    "syncType": "driver_assignments",
    "recordCount": 120,
    "yearMonth": "2026-03",
    "locationId": "clxxx",
    "locationName": "横浜第1",
    "createdAt": "2026-03-17T10:00:00.000Z"
  }
]
```

### 7.2 外部システムからのログ登録

**POST /api/sync-logs**

連携成功時に記録を登録できます。

```json
{
  "source": "イズミクラウド連携",
  "syncType": "monthly_records",
  "recordCount": 500,
  "yearMonth": "2026-03",
  "locationId": "clxxx"
}
```

| フィールド | 必須 | 説明 |
|------------|------|------|
| source | ○ | 外部システム名（例: イズミクラウド, タイムシート連携） |
| syncType | ○ | 連携種別（例: monthly_records, driver_assignments） |
| recordCount | - | 連携件数 |
| yearMonth | - | 対象年月 |
| locationId | - | 対象部門ID |

---

## 8. 参照用 API

| メソッド | パス | 説明 |
|----------|------|------|
| GET | /api/locations | 部門一覧（department id 取得用、locationCode で返却） |
| GET | /api/vehicles | 車両一覧（locationId で絞り込み可） |
| GET | /api/courses | コース一覧 |
| GET | /api/drivers | ドライバー一覧 |
| GET | /api/account-items | 勘定科目一覧（yearMonth で有効期間フィルタ可） |
| GET | /api/income-statement/metadata | 勘定科目・部門（yearMonth 必須） |
| GET | /api/income-statement | 損益データ取得（yearMonth, locationId 必須） |

---

## 9. エラーレスポンス

| HTTP | 内容 |
|------|------|
| 400 | リクエスト不正（必須パラメータ欠落、形式エラー） |
| 401 | 認証が必要 / トークン無効 |
| 403 | 権限不足 |
| 500 | サーバーエラー |

エラー時は `{ "error": "メッセージ" }` 形式で返却。

---

## 10. 車両損益計算書のデータフロー（全体像）

```
┌─────────────────────┐     ┌─────────────────────┐     ┌─────────────────────┐
│  イズミクラウド      │     │  タイムシート        │     │  ATMTC              │
│  ┌───────────────┐  │     │                     │     │  （ドライバー・      │
│  │ マスタデータ   │  │     │ ・日次乗務記録      │     │   コース紐づき）    │
│  │ ユーザー/車両  │  │     │ ・ドライバー別月次  │     └──────────┬──────────┘
│  │ コース/ドライバー│  │     │ ・日次稼働・走行   │                │
│  └───────┬───────┘  │     └────────────┬────────┘                │
│          │           │                 │                         │
│          │ 経理データ  │                 │                         │
│          │ (CSV/bulk) │                 │                         │
└──────────┼───────────┘                 │                         │
           │                              │                         ▼
           │                              │              ┌─────────────────────┐
           │                              │              │  イズミクラウド      │
           │                              │              │  （経由で連携）      │
           │                              │              └──────────┬──────────┘
           │                              │                         │
           ▼                              ▼                         ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         IZUMI API (Express)                                      │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────────┐              │
│  │ MonthlyRecord│  │ Driver配賦  │  │ DailyOperatingRecord        │              │
│  │ (月次損益)   │◄─│ (自動計算)   │  │ (日次稼働・走行)            │              │
│  └─────────────┘  └─────────────┘  └─────────────────────────────┘              │
└─────────────────────────────────────────────────────────────────────────────────┘
           │
           ▼
┌─────────────────┐
│ 車両損益計算書   │
│ (VPL)           │
└─────────────────┘
```

---

## 11. 用語対応表（本システム内部との対応）

| 本仕様書での用語 | 本システム内部（DB/API） | 備考 |
|------------------|-------------------------|------|
| Department（部門） | Location（拠点） | 同一概念 |
| department id | Location.code | イズミクラウドの部門IDと同一 |
| user_id | User.externalId | イズミクラウドのユーザーID |
| departmentId（API パラメータ） | locationCode | sync API では locationCode として受け付け |

---

## 12. 関連ドキュメント

| ドキュメント | 内容 |
|--------------|------|
| [system-overview.md](system-overview.md) | システム概要・アーキテクチャ |
| [db-schema.md](db-schema.md) | データベーススキーマ |
| [department-id-standard.md](department-id-standard.md) | 部門IDの共通化ルール |
| [driver-allocation-api.md](driver-allocation-api.md) | ドライバー配賦 API 詳細 |
