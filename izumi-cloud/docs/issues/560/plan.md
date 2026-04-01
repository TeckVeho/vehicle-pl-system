# Issue #560: Retired user/ vehicle grey out logic error - Implementation Plan

## 概要 (Overview)

**現在の状態 (Current State):**
- 廃車日を本日の日付で登録すると、即座に車両がグレーアウトされる
- Backend ロジック: `scrap_date <= today` を使用しているため、当日も廃車扱いになる
- Frontend ロジック: `item['scrap_date']` (元のフィールド) をチェックしているため、Backend の修正だけでは不十分

**改善後の状態 (Improved State):**
- 廃車日を登録した日の翌日からグレーアウトされる
- Backend ロジック: `scrap_date < today` に変更し、廃車日の翌日から廃車扱いにする
- Frontend ロジック: `item['scrap_date_custom']` (計算済みフィールド) をチェックするように変更

**影響範囲 (Impact Scope):**
- Backend: VehicleRepository.php の3つのメソッド（7箇所）
- Frontend: VehicleMaster/index.vue の1箇所（1箇所）

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/VehicleMaster/index.vue`

##### 1.1.1. 修正: handleRenderCellClass() メソッドの scrap_date チェックロジック

**目的:** グレーアウト表示判定ロジックの修正

**既存コード** (line 1358-1364):

```javascript
handleRenderCellClass(value, key, item) {
    if (item['scrap_date'] !== null) {
        return 'text-center darker-bg-td';
    } else {
        return 'text-center';
    }
}
```

**問題点:**
- `item['scrap_date']` はデータベースから取得した元のフィールド
- Backend は `scrap_date_custom` を計算しているが、Frontend は元のフィールドをチェックしている
- そのため、Backend の修正だけでは不十分で、Frontend も修正が必要

**変更内容:**

```javascript
handleRenderCellClass(value, key, item) {
    if (item['scrap_date_custom'] !== null) {
        return 'text-center darker-bg-td';
    } else {
        return 'text-center';
    }
}
```

**変更ポイント:**
- `item['scrap_date']` → `item['scrap_date_custom']` に変更
- Backend が計算した `scrap_date_custom` を使用することで、正しいロジックが適用される

**影響範囲:**
- 車両マスター一覧画面の表示のみに影響
- フォーム（edit/create）には影響なし（`scrap_date` 元フィールドはそのまま使用）

**技術的詳細:**
- Backend は `vehicles.*` で `scrap_date` 元フィールドも返している
- Backend は `scrap_date_custom` を計算して追加で返している
- Frontend は表示判定に `scrap_date_custom` を使用すべき

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `app/Repositories/VehicleRepository.php`

##### 1.1.1. 修正: paginate() メソッドの hide_scrap_date フィルターロジック

**目的:** 廃車を非表示にするフィルター機能の修正

**現在の実装** (line 79-84):

```php
if (Arr::get($filter, 'hide_scrap_date', false)) {
    $this->model = $this->model
        ->where(function ($query) use ($today) {
            $query->whereNull('vehicles.scrap_date')
                ->orWhere('vehicles.scrap_date', '>=', $today);
        });
}
```

**変更内容:**
- 既に修正済み: `>` → `>=` に変更済み
- 今日の廃車日の車両も表示されるようにする

**変更理由:**
- `scrap_date < today` の逆条件は `scrap_date >= today`
- 今日登録した廃車日の車両は翌日まで表示されるべき

---

##### 1.1.2. 修正: getAllVehicle() メソッドの scrap_date_custom 計算ロジック (number_plate 検索時)

**目的:** 車両一覧表示時のグレーアウト判定ロジックの修正

**現在の実装** (line 560-564):

```php
if ($number_plate || isset($sort_by) == 'number_plate') {
    $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
    $this->model = $this->model->where('vehicle_no_number_plate_history.no_number_plate', 'like', '%' . $number_plate . '%');
    $this->model = $this->model->addSelect(
        'vehicles.*',
        'departments.name as department_name',
        DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
    );
}
```

**変更内容:**
- 既に修正済み: `<=` → `<` に変更済み
- 廃車日が今日より前の場合のみグレーアウト

**変更ポイント:**
- `<=` を `<` に変更
- これにより、scrap_date が今日より前の場合のみグレーアウトされる

---

##### 1.1.3. 修正: getAllVehicle() メソッドの scrap_date_custom 計算ロジック (通常検索時)

**目的:** 車両一覧表示時のグレーアウト判定ロジックの修正（number_plate なしの場合）

**現在の実装** (line 565-571):

```php
} else {
    $this->model = $this->model->addSelect(
        'vehicles.*',
        'departments.name as department_name',
        DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
    );
}
```

**変更内容:**
- 既に修正済み: `<=` → `<` に変更済み

**変更ポイント:**
- `<=` を `<` に変更

---

##### 1.1.4. 修正: getAllVehicle() メソッドの hide_scrap_date フィルターロジック

**目的:** 廃車を非表示にするフィルター機能の修正

**現在の実装** (line 521-527):

```php
if (Arr::get($request, 'hide_scrap_date', false)) {
    $this->model = $this->model
        ->where(function ($query) use ($today) {
            $query->whereNull('vehicles.scrap_date')
                ->orWhere('vehicles.scrap_date', '>=', $today);
        });
}
```

**変更内容:**
- 既に修正済み: `>` → `>=` に変更済み
- 今日の廃車日の車両も表示されるようにする

**変更ポイント:**
- `>` を `>=` に変更

---

##### 1.1.5. 修正: getDashboardVehicle() メソッドの scrap_date_custom 計算ロジック (number_plate 検索時)

**目的:** ダッシュボード表示時のグレーアウト判定ロジックの修正

**現在の実装** (line 624-631):

```php
if ($number_plate) {
    $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
    $this->model = $this->model->where('vehicle_no_number_plate_history.no_number_plate', 'like', '%' . $number_plate . '%');
    $this->model = $this->model->addSelect(
        'vehicles.*',
        'departments.name as department_name',
        DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
    );
}
```

**変更内容:**
- 既に修正済み: `<=` → `<` に変更済み

**変更ポイント:**
- `<=` を `<` に変更

---

##### 1.1.6. 修正: getDashboardVehicle() メソッドの scrap_date_custom 計算ロジック (通常検索時)

**目的:** ダッシュボード表示時のグレーアウト判定ロジックの修正（number_plate なしの場合）

**現在の実装** (line 632-638):

```php
} else {
    $this->model = $this->model->addSelect(
        'vehicles.*',
        'departments.name as department_name',
        DB::raw("CASE WHEN vehicles.scrap_date < '{$today}' THEN vehicles.scrap_date ELSE NULL END AS scrap_date_custom")
    );
}
```

**変更内容:**
- 既に修正済み: `<=` → `<` に変更済み

**変更ポイント:**
- `<=` を `<` に変更

---

##### 1.1.7. 修正: getDashboardVehicle() メソッドの hide_scrap_date フィルターロジック

**目的:** ダッシュボードの廃車を非表示にするフィルター機能の修正

**現在の実装** (line 609-615):

```php
if (Arr::get($request, 'hide_scrap_date', false)) {
    $this->model = $this->model
        ->where(function ($query) use ($today) {
            $query->whereNull('vehicles.scrap_date')
                ->orWhere('vehicles.scrap_date', '>=', $today);
        });
}
```

**変更内容:**
- 既に修正済み: `>` → `>=` に変更済み
- 今日の廃車日の車両も表示されるようにする

**変更ポイント:**
- `>` を `>=` に変更

---

## 実装順序 (Implementation Order)

1. **Backend 実装** ✅ 完了済み
   - Task 1.1.1: paginate() の hide_scrap_date フィルター修正 ✅
   - Task 1.1.2: getAllVehicle() の scrap_date_custom 修正 (number_plate検索) ✅
   - Task 1.1.3: getAllVehicle() の scrap_date_custom 修正 (通常検索) ✅
   - Task 1.1.4: getAllVehicle() の hide_scrap_date フィルター修正 ✅
   - Task 1.1.5: getDashboardVehicle() の scrap_date_custom 修正 (number_plate検索) ✅
   - Task 1.1.6: getDashboardVehicle() の scrap_date_custom 修正 (通常検索) ✅
   - Task 1.1.7: getDashboardVehicle() の hide_scrap_date フィルター修正 ✅

2. **Frontend 実装** ⏳ 未完了
   - Task 1.1.1: handleRenderCellClass() の修正
   - **依存関係**: Backend の修正完了後に実装可能

3. **統合テスト**
   - 車両マスター一覧画面でのグレーアウト表示確認
   - ダッシュボード画面での表示確認
   - 「廃車を非表示にする」フィルターの動作確認
   - 横浜800い1757 の車両での動作確認
   - フォーム（edit/create）の動作確認

---

## 見積もり工数 (Estimated Effort)

### Backend: 0.5-1 時間 ✅ 完了済み

- Task 1.1.1: paginate() 修正 - 5分 ✅
- Task 1.1.2: getAllVehicle() 修正 (number_plate) - 5分 ✅
- Task 1.1.3: getAllVehicle() 修正 (通常) - 5分 ✅
- Task 1.1.4: getAllVehicle() フィルター修正 - 5分 ✅
- Task 1.1.5: getDashboardVehicle() 修正 (number_plate) - 5分 ✅
- Task 1.1.6: getDashboardVehicle() 修正 (通常) - 5分 ✅
- Task 1.1.7: getDashboardVehicle() フィルター修正 - 5分 ✅
- コードレビュー - 10分 ✅

**実績**: 35分で完了（予定より早い）

### Frontend: 0.25-0.5 時間 ⏳ 未完了

- Task 1.1.1: handleRenderCellClass() 修正 - 10分
- コードレビュー - 5分

**合計**: 0.75-1.5 時間（Backend 完了済み、Frontend 残り 0.25-0.5 時間）

---

## 技術的な注意事項 (Technical Notes)

### 1. パフォーマンス考慮:

- SQL の CASE 文の条件変更のみなので、パフォーマンスへの影響はなし
- インデックスの使用状況も変わらない
- Frontend の変更は単純なフィールド名変更のみ

### 2. UX 考慮:

- **重要**: ユーザーが廃車日を登録した当日は、まだ車両が通常表示される
- 翌日から自動的にグレーアウトされる
- これは仕様通りの動作だが、ユーザーに説明が必要な場合がある

### 3. データ整合性:

- 既存データへの影響なし
- DB スキーマの変更なし
- 表示ロジックの変更のみ
- `scrap_date` 元フィールドはそのまま保持（フォームで使用）

### 4. 既存機能との互換性:

- **廃車を非表示にする機能**: フィルターロジックも修正するため、一貫性が保たれる
- **ソート機能**: 影響なし
- **検索機能**: 影響なし
- **エクスポート機能**: Backend のデータ構造は変わらないため影響なし
- **フォーム（edit/create）**: `scrap_date` 元フィールドを使用するため影響なし

### 5. Backend と Frontend の連携:

**データフロー:**
1. Backend は `vehicles.*` で `scrap_date` 元フィールドを返す
2. Backend は `scrap_date_custom` を計算して追加で返す
3. Frontend は表示判定に `scrap_date_custom` を使用
4. Frontend はフォーム（edit/create）で `scrap_date` 元フィールドを使用

**重要なポイント:**
- Backend の修正だけでは不十分
- Frontend も修正が必要（`scrap_date` → `scrap_date_custom`）
- 両方修正しないと、正しい動作にならない

### 6. テストケース:

| テストケース | scrap_date | 今日の日付 | Backend scrap_date_custom | Frontend 表示 | 期待結果 |
|------------|-----------|----------|-------------------------|-------------|---------|
| Case 1 | 2025-12-25 | 2025-12-26 | '2025-12-25' | グレーアウト | ✅ 正しい |
| Case 2 | 2025-12-26 | 2025-12-26 | NULL | 通常表示 | ✅ 正しい |
| Case 3 | 2025-12-27 | 2025-12-26 | NULL | 通常表示 | ✅ 正しい |
| Case 4 | NULL | 2025-12-26 | NULL | 通常表示 | ✅ 正しい |
| Case 5 (Filter ON) | 2025-12-26 | 2025-12-26 | NULL | 表示される | ✅ 正しい |
| Case 6 (Filter ON) | 2025-12-25 | 2025-12-26 | '2025-12-25' | 非表示 | ✅ 正しい |

### 7. 退職者ユーザーについて:

Issue タイトルに「退職者ユーザー」も含まれていますが、今回は車両廃車のロジックのみを修正します。退職者ユーザーについても同様のロジックがある場合は、別途 issue を作成する必要があります。

**確認事項:**
- Employee/User マスターに同様の退職日ロジックがあるか？
- ある場合、同じ修正が必要か？

---

## リスク管理 (Risk Management)

### Low Risk Factors:

✅ 修正箇所が明確（Backend 7箇所、Frontend 1箇所）
✅ 変更内容がシンプル（演算子のみ、フィールド名変更のみ）
✅ 既存データへの影響なし
✅ Frontend への影響が限定的（表示ロジックのみ）
✅ ロールバックが容易

### 注意点:

- **重要**: Backend と Frontend の両方を修正する必要がある
- Backend だけ修正しても、Frontend が元のフィールドをチェックしているため動作しない
- 本番環境での動作確認が必須
- 横浜800い1757 の車両で実際に確認する
- 他の車両でも複数パターンテストする

### ロールバック計画:

- 修正が単純なため、元の演算子/フィールド名に戻すだけでロールバック可能
- Git で変更を管理しているため、いつでも元に戻せる

---

## 実装状況 (Implementation Status)

### Backend: ✅ 完了済み

- [x] paginate() メソッドの修正
- [x] getAllVehicle() メソッドの修正（2箇所）
- [x] getAllVehicle() メソッドのフィルター修正
- [x] getDashboardVehicle() メソッドの修正（2箇所）
- [x] getDashboardVehicle() メソッドのフィルター修正
- [x] コードレビュー完了

### Frontend: ⏳ 未完了

- [ ] handleRenderCellClass() メソッドの修正
- [ ] コードレビュー

### Testing: ⏳ 未完了

- [ ] 統合テスト
- [ ] 横浜800い1757 での確認

---

## 次のステップ (Next Steps)

1. **Frontend 実装** (優先度: 高)
   - handleRenderCellClass() メソッドの修正
   - コードレビュー

2. **統合テスト** (優先度: 高)
   - 車両マスター一覧画面での動作確認
   - ダッシュボード画面での動作確認
   - フィルター機能の確認
   - 横浜800い1757 での実車確認
   - フォーム（edit/create）の動作確認

3. **本番環境へのデプロイ** (優先度: 中)
   - ステージング環境でのテスト完了後
   - 顧客への報告

---

## 関連ドキュメント (Related Documents)

- Issue Document: `docs/issues/560/issue.md`
- Development Log: `docs/issues/561/dev.md` (Backend 実装ログ)
- Test Report: `docs/issues/561/test.md` (Backend テストレポート)