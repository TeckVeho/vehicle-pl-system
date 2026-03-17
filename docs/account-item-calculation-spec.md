# 勘定科目ごとの取得・計算方法

車両損益計算書（VPL）における各勘定科目のデータ取得方法をまとめた資料です。クライアントとのすり合わせ用です。

---

## 1. 取得方法の分類

| 分類 | 説明 | 主な連携元 |
|------|------|------------|
| **手動入力** | CSVインポート、API一括登録、画面からの手動編集 | イズミクラウド等（経理データ） |
| **イズミクラウド連携** | 車両月次費用 sync API で連携 | イズミクラウド |
| **ドライバー配賦** | タイムシート連携後、自動で車両別に按分計算 | タイムシート（DriverMonthlyAmount） |
| **集計・計算** | 他科目の合計・差額から算出 | システム内計算 |

---

## 2. 勘定科目一覧（取得方法別）

### 2.1 売上科目（revenue）

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 5010 | 山崎製パン | 手動入力 | MonthlyRecord | CSVインポート / API一括 / 画面編集 |
| 5010 | ヤマザキ物流 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | サンロジスティックス | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 末広製菓 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 富士エコー | 手動入力 | MonthlyRecord | 同上 |
| 5010 | パスコ | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 日立物流 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 菱倉運輸 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | ロジスティクス・ネットワーク | 手動入力 | MonthlyRecord | 同上 |
| 5010 | ダイセーロジスティクス | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 関東運輸 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | その他 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 不動産収入 | 手動入力 | MonthlyRecord | 同上 |
| 5010 | 人材派遣収入 | 手動入力 | MonthlyRecord | 同上 |
| SUBTOTAL_REV | 純売上高 | 集計 | 計算 | 売上科目の合計 |

**日次サマリーでの按分**: 売上科目は DailyOperatingRecord（日次稼働・走行）があれば、勘定科目の `revenuePricingType` に応じて日次按分されます。

| revenuePricingType | 按分ロジック |
|-------------------|--------------|
| per_run（回数単価） | 月次合計 ÷ 月間走行回数 × その日の runCount |
| monthly（月額単価） | 月次合計 ÷ 稼働日数（isOperating=true の日数）を稼働日のみに配賦 |
| null（未設定） | 月次合計 ÷ 日数で均等割り |

---

### 2.2 ドライバー配賦科目（isDriverRelated: true）

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 6138 | 乗務員給料 | ドライバー配賦 | DriverMonthlyAmount → MonthlyRecord | タイムシート連携後、自動按分 |
| 6139 | 業務給料 | ドライバー配賦 | DriverMonthlyAmount → MonthlyRecord | 同上 |
| 6147 | 通勤手当 | ドライバー配賦 | DriverMonthlyAmount → MonthlyRecord | 同上 |
| 6148 | 法定福利費 | ドライバー配賦 | DriverMonthlyAmount → MonthlyRecord | 同上 |
| 6149 | 福利厚生費 | ドライバー配賦 | DriverMonthlyAmount → MonthlyRecord | 同上 |

**配賦ロジック**:
1. タイムシートから `POST /api/driver-monthly-amounts/sync` でドライバー別月次金額を連携
2. タイムシートから `POST /api/driver-assignments/sync` で日次乗務記録を連携
3. 乗務日数按分: 1日複数車両に乗務した場合は、その日のウェイトを 1/車両数 で按分
4. 配賦結果を MonthlyRecord に書き込み

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

### 2.4 その他経費科目（手動入力）

| 科目コード | 勘定科目名 | 取得方法 | データソース | 備考 |
|------------|------------|----------|--------------|------|
| 6141 | 人材派遣費 | 手動入力 | MonthlyRecord | CSVインポート / API一括 / 画面編集 |
| 6145 | 賞与 | 手動入力 | MonthlyRecord | 同上 |
| 6150 | 旅費交通地 | 手動入力 | MonthlyRecord | 同上 |
| 6151 | 消耗品 | 手動入力 | MonthlyRecord | 同上 |
| 6154 | 修繕費 | 手動入力 | MonthlyRecord | 同上 |
| 6156 | 通信費 | 手動入力 | MonthlyRecord | 同上 |
| 6159 | 水道光熱費 | 手動入力 | MonthlyRecord | 同上 |
| 6160 | 保険料 | 手動入力 | MonthlyRecord | 同上（任意保険マスタは別途、計算ロジックは未実装） |
| 6162 | 租税公課 | 手動入力 | MonthlyRecord | 同上 |
| 6164 | 他手数料 | 手動入力 | MonthlyRecord | 同上 |
| 6165 | 交際接待費 | 手動入力 | MonthlyRecord | 同上 |
| 6166 | 会議費 | 手動入力 | MonthlyRecord | 同上 |
| 6167 | 広告宣伝費 | 手動入力 | MonthlyRecord | 同上 |
| 6168 | 諸会費 | 手動入力 | MonthlyRecord | 同上 |
| 6171 | 地代家賃 | 手動入力 | MonthlyRecord | 同上 |
| 6172 | 借家料 | 手動入力 | MonthlyRecord | 同上 |
| 6173 | 賃借料 | 手動入力 | MonthlyRecord | 同上 |
| 6174 | 保守料 | 手動入力 | MonthlyRecord | 同上 |
| 6175 | 燃料費 | 手動入力 | MonthlyRecord | 同上 |
| 6176 | 道路使用料 | 手動入力 | MonthlyRecord | 同上 |
| 6177 | 図書研修費 | 手動入力 | MonthlyRecord | 同上 |
| 6178 | 減価償却費 | 手動入力 | MonthlyRecord | 同上 |
| 6188 | 雑費 | 手動入力 | MonthlyRecord | 同上 |
| 6189 | 事故賠償費 | 手動入力 | MonthlyRecord | 同上 |
| 6190 | 車両修繕費 | 手動入力 | MonthlyRecord | 同上 |
| 6196 | メンテナンスリース | 手動入力 | MonthlyRecord | 同上 |
| 6197 | etc（常用外） | 手動入力 | MonthlyRecord | 同上 |
| SUBTOTAL_EXP | 自車原価計 | 集計 | 計算 | 経費科目の合計 |
| SUBTOTAL_GROSS | 自車粗利益 | 集計 | 計算 | 純売上高 − 自車原価計 |

**日次サマリーでの按分**: 経費科目は月次合計 ÷ 日数で均等割り。

---

### 2.5 集計・サマリー科目

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
| **手動入力** | 売上科目（14種）、その他経費（27種） | POST /api/import（CSV/Excel）、POST /api/income-statement/records/bulk、画面編集 | 経理データをイズミクラウド等から取得し登録 |
| **イズミクラウド連携** | リース車償却、車両償却費、車両リース、損害保険料、賦課税 | POST /api/vehicle-monthly-costs/sync | 表示時は VehicleMonthlyCost を優先 |
| **ドライバー配賦** | 乗務員給料、業務給料、通勤手当、法定福利費、福利厚生費 | POST /api/driver-monthly-amounts/sync、POST /api/driver-assignments/sync | 連携後に自動で車両別に按分 |
| **集計** | 純売上高、自車原価計、自車粗利益、売上計、原価計、粗利益 | システム内計算 | 他科目から自動算出 |

---

## 4. 補足

### 4.1 任意保険マスタ（ArbitraryInsuranceMaster）

- トン数別の月額保険料を設定するマスタ（2t, 4t, 8t, 10t）
- 損害保険料（任意保険）の計算に使用する想定
- 現時点では編集画面のみで、損益計算書への自動反映ロジックは未実装

### 4.2 日次稼働・走行データ（DailyOperatingRecord）

- `POST /api/daily-operating/sync` でタイムシートから連携
- 売上科目の日次按分（回数単価・月額単価）に使用
- 損益計算書の月次金額には直接影響しない（日次サマリー画面用）

### 4.3 勘定科目の有効期間

- `effectiveFrom` / `effectiveTo` で年月ごとの有効期間を管理
- 指定年月に有効な勘定科目のみが損益計算書に表示される

---

## 5. 関連ドキュメント

| ドキュメント | 内容 |
|--------------|------|
| [external-integration-spec.md](external-integration-spec.md) | 外部連携 API 仕様 |
| [driver-allocation-api.md](driver-allocation-api.md) | ドライバー配賦 API 詳細 |
| [db-schema.md](db-schema.md) | データベーススキーマ |
