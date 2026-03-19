# 勘定科目ごとの取得・計算方法

車両損益計算書（VPL）における各勘定科目のデータ取得方法をまとめた資料です。エンジニアの実装・外部連携の参照用としても利用します。

**UI での表示**: 勘定科目マスタ（/account-items）の一覧で、各勘定科目の取得方法・計算ロジックが表示されます。`src/lib/account-item-calculation.ts` が本仕様に基づく表示ロジックを実装しています。

---

## 1. 取得方法の分類

| 分類 | 説明 | 主な連携元 |
|------|------|------------|
| **手動入力** | CSVインポート、API一括登録、画面からの手動編集 | イズミクラウド等（経理データ） |
| **イズミクラウド連携** | 車両月次費用 sync API で連携 | イズミクラウド |
| **ドライバー配賦** | タイムシート連携後、自動で車両別に按分計算 | タイムシート（DriverMonthlyAmount） |
| **スプレッドシート参照** | 各拠点のスプレッドシートから売上データを参照 | 各拠点スプレッドシート（ロジック別途作成） |
| **集計・計算** | 他科目の合計・差額から算出 | システム内計算 |

---

## 2. 勘定科目一覧（取得方法別）

### 2.1 売上科目（revenue）

**売上データの取得方針**: 各拠点のスプレッドシートで売上を管理しているため、山崎製パン〜関東運輸は**スプレッドシート参照のみ**。その他・不動産収入・人材派遣収入は手入力専用で MonthlyRecord に保存。

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 5010 | 山崎製パン | スプレッドシート参照 | 各拠点スプレッドシート | |
| 5010 | ヤマザキ物流 | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | サンロジスティックス | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | 末広製菓 | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | 富士エコー | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | パスコ | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | 日立物流 | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | 菱倉運輸 | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | ロジスティクス・ネットワーク | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | ダイセーロジスティクス | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | 関東運輸 | スプレッドシート参照 | 各拠点スプレッドシート | 同上 |
| 5010 | その他 | 手入力のみ | MonthlyRecord | 画面編集のみ（CSV/API一括不可） |
| 5010 | 不動産収入 | 手入力のみ | MonthlyRecord | 同上 |
| 5010 | 人材派遣収入 | 手入力のみ | MonthlyRecord | 同上 |
| SUBTOTAL_REV | 純売上高 | 集計 | 計算 | 売上科目の合計 |

**日次サマリーでの按分**: 売上科目のうち**関東運輸まで**（山崎製パン〜関東運輸）は DailyOperatingRecord（日次稼働・走行）があれば、勘定科目の `revenuePricingType` に応じて日次按分されます。その他・不動産収入・人材派遣収入は按分対象外です。

| revenuePricingType | 按分ロジック |
|-------------------|--------------|
| per_run（回数単価） | 月次合計 ÷ 月間走行回数 × その日の runCount |
| monthly（月額単価） | 月次合計 ÷ 稼働日数（isOperating=true の日数）を稼働日のみに配賦 |
| null（未設定） | 月次合計 ÷ 日数で均等割り |

---

### 2.2 ドライバー配賦科目（isDriverRelated: true）

| 科目コード | 勘定科目名 | 取得方法 | 計算ロジック | 備考 |
|------------|------------|----------|--------------|------|
| 6138 | 乗務員給料 | ドライバー配賦 | 乗車回数ベース配賦 | 各ドライバーの1日単価×乗車回数を車両別に累計し MonthlyRecord に保存 |
| 6147 | 通勤手当 | ドライバー配賦 | 乗車回数ベース配賦 | 同上 |
| 6148 | 法定福利費 | ドライバー配賦 | 乗務日数按分 | 別途計算ロジックあり |

**配賦ロジック（法定福利費）**:
1. タイムシートから `POST /api/driver-monthly-amounts/sync` でドライバー別月次金額を連携
2. タイムシートから `POST /api/driver-assignments/sync` で日次乗務記録を連携
3. 乗務日数按分: 1日複数車両に乗務した場合は、その日のウェイトを 1/車両数 で按分
4. 配賦結果を MonthlyRecord に書き込み

**乗務員給料・通勤手当の乗車回数ベース配賦**（給与は翌月払いのため、表示月には前月分の給与データを使用）:
1. 各ドライバーの1日単価 = 前月の DriverMonthlyAmount ÷ 前月の日数
2. 各 (driver, vehicle, date) について、その車両のその日の runCount × 1日単価 を累計
3. 車両別の合計を MonthlyRecord に保存（driver-assignments / driver-monthly-amounts / daily-operating の sync 時に実行）
4. 損益計算書の表示額 = 前月の MonthlyRecord をそのまま表示
5. 日次サマリーの各日: 単価（前月月次 ÷ 表示月の乗車回数合計）× その日の乗車回数

---

### 2.3 車両月次費用科目（イズミクラウド連携）

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 6191 | リース車償却 | イズミクラウド連携 | VehicleMonthlyCost.leaseDepreciation | **VehicleMonthlyCost を優先**（MonthlyRecord は参照しない） |
| 6192 | 車両償却費 | イズミクラウド連携 | VehicleMonthlyCost.vehicleDepreciation | 同上 |
| 6193 | 車両リース | イズミクラウド連携 | VehicleMonthlyCost.vehicleLease | 同上 |
| 6194 | 損害保険料 | イズミクラウド連携 | VehicleMonthlyCost.insuranceCost | 自賠責保険料。同上 |
| 6195 | 賦課税 | イズミクラウド連携 | VehicleMonthlyCost.taxCost | 自動車税。同上 |

**連携 API**: `POST /api/vehicle-monthly-costs/sync`

※ 損益計算書表示時、上記5科目は VehicleMonthlyCost の値を優先し、MonthlyRecord の値は参照しません。

---

### 2.4 拠点別経費按分科目（PCA 連携）

PCA からイズミクラウド経由で拠点ごとに月額データを連携し、各拠点の車両数で按分して車両別に配賦する。

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 6150 | 旅費交通費 | 拠点別経費連携 | LocationMonthlyExpense | 拠点月額 ÷ 車両数で按分。POST /api/location-monthly-expenses/sync |
| 6151 | 消耗品 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6154 | 修繕費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6156 | 通信費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6159 | 水道光熱費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6160 | 保険料 | 拠点別経費連携 | LocationMonthlyExpense | 同上（任意保険マスタは別途） |
| 6162 | 租税公課 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6164 | 他手数料 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6165 | 交際接待費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6166 | 会議費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6167 | 広告宣伝費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6168 | 諸会費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6171 | 地代家賃 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6172 | 借家料 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6173 | 賃借料 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6174 | 保守料 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6177 | 図書研修費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6178 | 減価償却費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6188 | 雑費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |
| 6189 | 事故賠償費 | 拠点別経費連携 | LocationMonthlyExpense | 同上 |

**按分ロジック**: 拠点の月額合計 ÷ その拠点の車両数 = 車両あたり金額。表示時は LocationMonthlyExpense を優先し、MonthlyRecord は参照しない。

---

### 2.5 その他経費科目（手動入力）

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 6139 | 業務給料 | 手動入力 | MonthlyRecord | CSVインポート / API一括 / 画面編集 |
| 6149 | 福利厚生費 | 手動入力 | MonthlyRecord | CSVインポート / API一括 / 画面編集 |
| 6141 | 人材派遣費 | 手動入力 | MonthlyRecord | CSVインポート / API一括 / 画面編集 |
| 6145 | 賞与 | 手動入力 | MonthlyRecord | 同上 |
| 6175 | 燃料費 | ITP連携＋計算 | VehicleMonthlyCost.fuelEfficiency × LocationCalculationParameter.fuelUnitPrice | イズミクラウド経由でITPから前月の燃費（L）を連携。表示月は前月データを使用 |
| 6176 | 道路使用料 | ITP連携＋計算 | VehicleMonthlyCost.roadUsageFee × LocationCalculationParameter.roadUsageDiscountRate | イズミクラウド経由でITPから前月の道路使用料を連携。表示月は前月データを使用 |
| 6190 | 車両修繕費 | 手動入力 | MonthlyRecord | 同上 |
| 6196 | メンテナンスリース | 手動入力 | MonthlyRecord | 同上 |
| 6197 | etc（常用外） | 手動入力 | MonthlyRecord | 同上 |
| SUBTOTAL_EXP | 自車原価計 | 集計 | 計算 | 経費科目の合計 |
| SUBTOTAL_GROSS | 自車粗利益 | 集計 | 計算 | 純売上高 − 自車原価計 |

**日次サマリーでの按分**: 経費科目は月次合計 ÷ 日数で均等割り。乗務員給料・通勤手当は単価×乗車回数で各日に配賦（乗車回数ベース、乗車回数0時は均等割り）。業務給料・福利厚生費は手動入力のため月次 ÷ 日数で均等割り。拠点別経費按分科目（6150〜6189）は LocationMonthlyExpense を車両数で按分した月次を日数で均等割り。

---

### 2.6 集計・サマリー科目

| 科目コード | 勘定科目名 | 取得方法 | 計算式 |
|------------|------------|----------|--------|
| SUBTOTAL_REV | 純売上高 | 集計 | 売上科目（revenue）の合計 |
| SUBTOTAL_EXP | 自車原価計 | 集計 | 経費科目（expense）の合計 |
| SUBTOTAL_GROSS | 自車粗利益 | 集計 | 純売上高 − 自車原価計 |
| SUMMARY_REV | 売　　上　　計 | 集計 | 純売上高と同値 |
| SUMMARY_EXP | 原　　価　　計 | 集計 | 自車原価計と同値 |
| SUMMARY_GROSS | 粗　　利　　益 | 集計 | 自車粗利益と同値 |

---

## 3. 取得方法サマリー（一覧表）

| 取得方法 | 対象科目 | 連携API / 入力手段 | 優先度・備考 |
|----------|----------|-------------------|--------------|
| **スプレッドシート参照** | 売上科目（山崎製パン〜関東運輸） | 各拠点スプレッドシート | getRevenueFromSpreadsheets で取得 |
| **拠点別経費連携** | 旅費交通費・消耗品・修繕費等（20種） | POST /api/location-monthly-expenses/sync | PCA からイズミクラウド経由で拠点ごとに月額連携。車両数で按分 |
| **手動入力** | その他経費（業務給料・福利厚生費・人材派遣費・賞与・車両修繕費等） | POST /api/import（CSV/Excel）、POST /api/income-statement/records/bulk、画面編集 | 経理データをイズミクラウド等から取得し登録 |
| **手入力のみ** | その他、不動産収入、人材派遣収入 | 画面編集のみ | CSV/API一括登録は不可。損益計算書画面から直接入力 |
| **イズミクラウド連携** | リース車償却、車両償却費、車両リース、損害保険料(自賠責)、賦課税 | POST /api/vehicle-monthly-costs/sync | 表示時は VehicleMonthlyCost を優先 |
| **ITP連携＋計算** | 燃料費、道路使用料 | vehicle-monthly-costs/sync（`fuelEfficiency`, `roadUsageFee`）＋ `PUT /api/location-calculation-parameters` | 表示は前月の生データ × 拠点別パラメータ（燃料単価・道路割引率）。詳細は 2.5 節 |
| **ドライバー配賦** | 乗務員給料、通勤手当、法定福利費 | POST /api/driver-monthly-amounts/sync、POST /api/driver-assignments/sync、POST /api/daily-operating/sync | 乗務員給料・通勤手当は乗車回数ベースで配賦し MonthlyRecord に保存。法定福利費は乗務日数按分 |
| **集計** | 純売上高、自車原価計、自車粗利益、売上計、原価計、粗利益 | システム内計算 | 他科目から自動算出 |

---

## 4. 補足

### 4.1 任意保険マスタ（ArbitraryInsuranceMaster）

- トン数別の月額保険料を設定するマスタ（2t, 4t, 8t, 10t）
- 損害保険料（任意保険）の計算に使用する想定
- 現時点では編集画面のみで、損益計算書への自動反映ロジックは未実装

### 4.2 拠点別計算パラメータ（LocationCalculationParameter）

- 燃料費・道路使用料の算出に使用する拠点別パラメータ
- `fuelUnitPrice`: 燃料単価（円/L）
- `roadUsageDiscountRate`: 使用料割引率（0〜1、例: 0.95 = 5%割引）
- 年月×拠点で管理。PUT /api/location-calculation-parameters で登録・更新

### 4.3 日次稼働・走行データ（DailyOperatingRecord）

- `POST /api/daily-operating/sync` でタイムシートから連携
- 売上科目（関東運輸まで）の日次按分（回数単価・月額単価）に使用
- その他・不動産収入・人材派遣収入は按分対象外
- 損益計算書の月次金額には直接影響しない（日次サマリー画面用）

### 4.4 勘定科目の有効期間

- `effectiveFrom` / `effectiveTo` で年月ごとの有効期間を管理
- 指定年月に有効な勘定科目のみが損益計算書に表示される

---

## 5. 関連ドキュメント

| ドキュメント | 内容 |
|--------------|------|
| [external-integration-spec.md](external-integration-spec.md) | 外部連携 API 仕様 |
| [driver-allocation-api.md](driver-allocation-api.md) | ドライバー配賦 API 詳細 |
| [db-schema.md](db-schema.md) | データベーススキーマ |
