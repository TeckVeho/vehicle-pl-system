# 外部システム別 連携機能実装一覧

車両損益計算システム（IZUMI）へのデータ連携において、**各外部システム側で実装が必要な内容**をまとめたドキュメントです。本システムの受け入れ側の仕様は [external-integration-spec.md](external-integration-spec.md) を参照してください。

---

## 1. イズミクラウド

車両損益計算システムの中心的な連携元。マスタデータ・経理データの集約と送信を担当します。

### 1.1 認証・権限

| 実装内容 | 説明 |
|----------|------|
| **JWT 取得** | `POST /api/auth/login` で `userId`（または `email`）と `password` を送信し、JWT を取得 |
| **連携用ユーザー** | sync API 利用には **MASTER** 権限（DX または DX管理者ロール）が必要。連携専用ユーザーを作成し、権限を付与 |
| **トークン管理** | JWT 有効期限 7 日。期限切れ前に再取得する仕組みを実装 |

### 1.2 マスタデータ連携クライアント

| API | 実装内容 | 実行タイミング |
|-----|----------|----------------|
| **POST /api/users/sync** | ユーザーマスタを JSON 形式で送信。`userId`（externalId）をキーに upsert | ユーザー登録・更新時 |
| **POST /api/courses/sync** | コースマスタ送信。`departmentId`、`code`、`name`、`externalId` を含める | コース登録・更新時 |
| **POST /api/vehicles/sync** | 車両マスタ送信。`courseId` / `courseExternalId` でコースと紐づけ | 車両登録・更新時（コース sync の後） |
| **POST /api/vehicle-monthly-costs/sync** | 車両別・年月別の固定費用（償却・リース・自賠責・賦課税）を送信。ITP 由来の `fuelEfficiency`・`roadUsageFee` も含める | 月次（月初など） |
| **POST /api/location-monthly-expenses/sync** | PCA から取得した拠点別月額経費（20 科目）を送信 | 月次（PCA 確定後） |
| **POST /api/drivers/sync** | ドライバーマスタ送信。ATMTC のドライバー・コース紐づきを反映 | ドライバー登録・更新時、ATMTC 連携後 |

### 1.3 トランザクションデータ連携

| 方式 | 実装内容 | 実行タイミング |
|------|----------|----------------|
| **POST /api/import** | CSV/Excel を multipart/form-data で送信。`locationId`、`yearMonth` を指定 | 経理データ確定後（月次） |
| **POST /api/income-statement/records/bulk** | 月次損益を JSON で一括登録。`vehicleId`、`accountItemId`、`amount` を指定 | 経理データ確定後（月次） |

### 1.4 他システムからのデータ集約

| データソース | 実装内容 |
|--------------|----------|
| **PCA** | 拠点別月額経費（旅費交通費・消耗品・修繕費等 20 科目）を取得し、`POST /api/location-monthly-expenses/sync` で送信 |
| **ATMTC** | ドライバー・コース紐づきを取得し、ドライバー sync やコース sync に反映して送信 |
| **ITP** | 燃費（`fuelEfficiency`）・道路使用料（`roadUsageFee`）を取得し、`vehicle-monthly-costs/sync` の各車両データに含めて送信 |

### 1.5 その他

| 実装内容 | 説明 |
|----------|------|
| **部門 ID の統一** | 本システムと同一の department id を使用（[department-id-standard.md](department-id-standard.md) 参照） |
| **連携ログ登録** | 連携成功時に `POST /api/sync-logs` で記録（任意） |
| **エラーハンドリング** | 400/401/403/500 のレスポンスを処理し、リトライやアラートを検討 |
| **スケジュール実行** | マスタ・月次データの sync をバッチで定期実行する仕組み |

---

## 2. タイムシート

日次稼働・乗務記録・ドライバー給与データを送信します。

### 2.1 認証・権限

| 実装内容 | 説明 |
|----------|------|
| **JWT 取得** | `POST /api/auth/login` でログインし、JWT を取得 |
| **MASTER 権限** | sync API 利用には MASTER 権限が必要。イズミクラウドで連携用ユーザーを作成し、権限を付与 |

### 2.2 連携 API クライアント

| API | 実装内容 | 実行タイミング |
|-----|----------|----------------|
| **POST /api/daily-operating/sync** | 日次稼働・走行データ（`vehicleId`/`vehicleExternalId`、`date`、`runCount`、`isOperating`）を送信 | 日次（翌日など） |
| **POST /api/driver-assignments/sync** | 日次乗務記録（ドライバー×車両×日付）を送信。連携後にドライバー配賦が自動実行される | 日次（翌日など） |
| **POST /api/driver-monthly-amounts/sync** | ドライバー別月次金額（乗務員給料・通勤手当・法定福利費など）を送信。`accountItemCode` 6138/6147/6148 を指定 | 月次（給与確定後） |

### 2.3 データ要件

| 項目 | 説明 |
|------|------|
| **車両・ドライバー識別** | `vehicleExternalId`、`driverExternalId` を使用する場合、イズミクラウドの sync で登録済みの externalId と一致させる |
| **部門指定** | `departmentId` で部門を指定し、対象部門のデータのみ送信 |
| **勘定科目** | ドライバー配賦対象は乗務員給料(6138)、通勤手当(6147)、法定福利費(6148)。業務給料(6139)・福利厚生費(6149)は手動インポートのため対象外 |

### 2.4 その他

| 実装内容 | 説明 |
|----------|------|
| **連携順序** | ドライバー sync（イズミクラウド）→ 乗務記録・日次稼働・ドライバー別月次金額の順で連携 |
| **スケジュール** | 日次データは翌日、月次データは給与確定後に送信するバッチを検討 |

---

## 3. 各拠点のスプレッドシート（売上データ）

売上（山崎製パン〜関東運輸）は本システムがスプレッドシートを参照する想定です。

### 3.1 拠点側で必要な対応

| 実装内容 | 説明 |
|----------|------|
| **フォーマットの統一** | 本システムの `getRevenueFromSpreadsheets` 実装時に、車両番号（vehicleNo）と勘定科目（または科目コード）から金額を取得できる形式を定義 |
| **アクセス権限** | 本システムが参照するためのアクセス権（サービスアカウント等）の付与 |
| **更新タイミング** | 月次で売上を確定し、参照可能な状態にしておく |

※ 現状、`spreadsheet-revenue.ts` はスタブのため、具体的なシート構造・列定義は未確定。P0 タスクで確定予定。

---

## 4. PCA（イズミクラウド経由）

拠点別月額経費は PCA → イズミクラウド → 本システムの流れです。

### 4.1 PCA 側で必要な対応

| 実装内容 | 説明 |
|----------|------|
| **データエクスポート** | 拠点別・勘定科目別の月額経費をイズミクラウドが取得できる形式で提供（API、ファイル、DB 連携など） |
| **科目マッピング** | 旅費交通費(6150)、消耗品(6151)、修繕費(6154) など 20 科目の科目コードとの対応 |

※ 実際の `location-monthly-expenses/sync` 呼び出しはイズミクラウドが担当。

---

## 5. ATMTC（イズミクラウド経由）

ドライバー・コース紐づきは ATMTC → イズミクラウド → 本システムの流れです。

### 5.1 ATMTC 側で必要な対応

| 実装内容 | 説明 |
|----------|------|
| **紐づきデータの提供** | ドライバーとコース（または車両）の紐づきをイズミクラウドが取得できる形式で提供 |
| **識別子の整合** | イズミクラウドのドライバー・コースの externalId と整合する識別子の使用 |

※ 実際の `drivers/sync` や `courses/sync` の呼び出しはイズミクラウドが担当。

---

## 6. ITP（イズミクラウド経由）

燃費・道路使用料は ITP → イズミクラウド → 本システムの流れです。

### 6.1 ITP 側で必要な対応

| 実装内容 | 説明 |
|----------|------|
| **燃費データの提供** | 車両別・月別の燃費（L）をイズミクラウドが取得できる形式で提供 |
| **道路使用料データの提供** | 車両別・月別の道路使用料（割引適用前の生データ）を提供 |

※ 実際の `vehicle-monthly-costs/sync` 呼び出しはイズミクラウドが担当。`fuelEfficiency`・`roadUsageFee` を各車両の cost に含めて送信。

---

## 7. 連携フロー（実行順序の目安）

```
1. 部門（Department）… 本システム内で管理、department id で整合
2. コース sync（イズミクラウド）
3. 車両 sync（イズミクラウド）
4. 車両月次費用 sync（イズミクラウド、ITP データ含む）
4a. 拠点別計算パラメータ（本システムで PUT）
4b. 拠点別月額経費 sync（イズミクラウド、PCA データ含む）
5. ドライバー sync（イズミクラウド、ATMTC 紐づき含む）
6. 月次損益（イズミクラウド等、import または bulk）
7. 日次稼働 sync（タイムシート）
8. 乗務記録 sync（タイムシート）
9. ドライバー別月次金額 sync（タイムシート）
```

---

## 8. 関連ドキュメント

| ドキュメント | 内容 |
|--------------|------|
| [external-integration-spec.md](external-integration-spec.md) | API 仕様・リクエスト形式 |
| [driver-allocation-api.md](driver-allocation-api.md) | ドライバー配賦関連 API |
| [department-id-standard.md](department-id-standard.md) | 部門 ID の運用 |
| [account-item-calculation-spec.md](account-item-calculation-spec.md) | 勘定科目ごとの取得・計算方法 |
| [engineering-backlog.md](engineering-backlog.md) | 本システム側の残タスク |
