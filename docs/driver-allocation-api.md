# ドライバー配賦 API

**タイムシート**からドライバー乗務記録・給与データを連携し、乗務員給料・業務給料・通勤手当・福利厚生費などを車両別に按分する機能の API 仕様です。ドライバーマスタは**イズミクラウド**から連携し、ドライバーとコースの紐づきは**ATMTC**で取得したものをイズミクラウド経由で連携します。

---

## 概要

- **パターンA**: ドライバー別の月次総額を連携し、自システムで乗務日数に応じて車両へ按分
- **配賦ロジック**: 1日複数車両に乗務する場合は、その日のウェイトを 1/車両数 で按分
- **実行タイミング**: 乗務記録・給与データの sync のたびに配賦を再計算し、MonthlyRecord を更新

---

## API 一覧

| メソッド | パス | 説明 |
|----------|------|------|
| GET | /api/drivers | ドライバー一覧（locationId で絞り込み可） |
| POST | /api/drivers/sync | ドライバーマスタ一括同期 |
| POST | /api/driver-assignments/sync | 日次乗務記録一括同期 |
| POST | /api/driver-monthly-amounts/sync | ドライバー別月次金額一括同期 |

※ いずれも認証必須。sync 系は DX/DX管理者 権限が必要。

---

## POST /api/drivers/sync

```json
{
  "drivers": [
    {
      "locationId": "clxxx",
      "locationCode": "LOC001",
      "departmentId": "LOC001",
      "code": "EMP001",
      "name": "山田太郎",
      "externalId": "ext-driver-123"
    }
  ]
}
```

- `locationId`、`locationCode`、`departmentId` のいずれか必須（departmentId はイズミクラウドの部門ID）
- `code`: 社員番号など（拠点内で一意）
- `externalId`: 外部システムの識別子（指定時は upsert のキーに使用）

---

## POST /api/driver-assignments/sync

```json
{
  "yearMonth": "2026-03",
  "locationId": "clxxx",
  "departmentId": "LOC001",
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

- `locationId` または `departmentId` のいずれかで部門を指定（departmentId は部門コード、内部で Location に解決）
- `driverId` または `driverExternalId` のいずれか必須
- `vehicleId` または `vehicleExternalId` のいずれか必須
- `date`: YYYY-MM-DD（yearMonth に属する日付であること）
- 連携後に配賦計算を自動実行し、MonthlyRecord を更新
- レスポンスに `allocation`（配賦結果サマリー）を含む

---

## POST /api/driver-monthly-amounts/sync

```json
{
  "yearMonth": "2026-03",
  "locationId": "clxxx",
  "departmentId": "LOC001",
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

- `locationId` または `departmentId` のいずれかで部門を指定（任意）
- `accountItemId` または `accountItemCode` のいずれか必須
- 勘定科目は `isDriverRelated: true` のもののみ対象（乗務員給料、業務給料、通勤手当、法定福利費、福利厚生費など）
- 連携後に配賦計算を自動実行
- レスポンスに `allocation`（配賦結果サマリー）を含む

---

## 勘定科目のドライバー配賦フラグ

勘定科目マスタで `isDriverRelated` を true に設定した科目が配賦対象です。

シードで設定済みの科目:
- 乗務員給料 (6138)
- 業務給料 (6139)
- 通勤手当 (6147)
- 法定福利費 (6148)
- 福利厚生費 (6149)
