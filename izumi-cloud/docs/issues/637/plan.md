# Issue #637: Fix sorting not working correctly (vehicle master) - Implementation Plan

## 概要 (Overview)

Vehicle Master のソート機能に問題があります。特に `no_number_plate` (車両No) カラムのソートが正しく動作していません。

**現在の状態:**
- `no_number_plate` カラムをクリックしてもソートが機能しない
- 一部のカラムでソート結果が正しくない
- Frontend テンプレートで `no_number_plate` カラムが表示されない（field key の不一致）

**改善後の状態:**
- `no_number_plate` カラムのソートが正常に動作する
- 最新の `no_number_plate` 値でソートされる
- Frontend で `no_number_plate` カラムが正しく表示される
- すべてのソート可能なカラムが正しく動作する

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/VehicleMaster/index.vue`

##### 1.1.1. Fix field key mismatch in template

**問題:**
- Backend から送信される field key は `'no_number_plate'` ですが、テンプレートでは `'number_plate'` をチェックしています
- このため、`no_number_plate` カラムのテンプレートがレンダリングされず、データが表示されません

**既存コード** (line 316):

```vue
<template v-else-if="field.key === 'number_plate'">
    {{ getDepartmentName(item.plate_history[0] ? item.plate_history[0].no_number_plate : '') }}
</template>
```

**変更内容:**

- Line 316 の `'number_plate'` を `'no_number_plate'` に変更
- Backend の constant 定義 (`app/constants.php`) と一致させる
- これにより、Backend から送信される field key と一致し、テンプレートが正しくレンダリングされる

**変更後のコード:**

```vue
<template v-else-if="field.key === 'no_number_plate'">
    {{ getDepartmentName(item.plate_history[0] ? item.plate_history[0].no_number_plate : '') }}
</template>
```

**影響範囲:**
- Vehicle Master リスト画面の `no_number_plate` カラム表示
- ソート機能の動作確認（カラムが表示されないとソートも確認できない）

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `app/Repositories/VehicleRepository.php`

##### 1.1.1. Fix logic error in `paginate()` method - JOIN condition

**問題:**
- Line 126 で `isset($sort['sort_by']) == 'number_plate'` という誤ったロジックを使用
- `isset()` は boolean を返すため、文字列 `'number_plate'` と比較しても常に false になる
- さらに、Frontend は `'no_number_plate'` を送信するが、Backend は `'number_plate'` をチェックしている
- このため、`no_number_plate` でソートする際に必要な JOIN が実行されない

**現在の実装** (line 126):

```php
if ($filter['number_plate'] || isset($sort['sort_by']) == 'number_plate') {
    $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
    // ...
}
```

**変更内容:**

- Line 126 の条件を修正:
  - `isset($sort['sort_by']) == 'number_plate'` → `isset($sort['sort_by']) && $sort['sort_by'] == 'no_number_plate'`
- 正しいロジック演算子 (`&&`) を使用
- Frontend が送信する field key (`'no_number_plate'`) と一致させる

**変更後のコード:**

```php
if ($filter['number_plate'] || (isset($sort['sort_by']) && $sort['sort_by'] == 'no_number_plate')) {
    $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
    // ...
}
```

##### 1.1.2. Add special handling for `no_number_plate` sorting in `paginate()` method

**問題:**
- `no_number_plate` でソートする際、`vehicle_no_number_plate_history` テーブルから最新のレコードを取得する必要がある
- 現在の実装では、JOIN が実行されても `orderBy($sort['sort_by'], ...)` で `vehicles.no_number_plate` を参照しようとするが、このカラムは存在しない
- 各 vehicle には複数の `no_number_plate` 履歴があるため、最新のレコード（`date DESC`）でソートする必要がある

**現在の実装** (lines 142-151):

```php
if (isset($sort['sort_by']) && isset($sort['sort_type'])) {
    if($sort['sort_by'] == 'leasing_period') {
        $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
        $this->model = $this->model->orderBy('maintenance_leases.leasing_period', $sort['sort_type']);
    } else if($sort['sort_by'] == 'department_name') {
        $this->model = $this->model->orderBy('departments.position', $sort['sort_type']);
    } else {
        $this->model = $this->model->orderBy($sort['sort_by'], $sort['sort_type']);
    }
}
```

**変更内容:**

- `no_number_plate` のソートケースを追加
- サブクエリを使用して各 vehicle の最新の `no_number_plate` レコードを取得
- その最新レコードの `no_number_plate` 値でソート

**変更後のコード:**

```php
if (isset($sort['sort_by']) && isset($sort['sort_type'])) {
    if($sort['sort_by'] == 'leasing_period') {
        $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
        $this->model = $this->model->orderBy('maintenance_leases.leasing_period', $sort['sort_type']);
    } else if($sort['sort_by'] == 'department_name') {
        $this->model = $this->model->orderBy('departments.position', $sort['sort_type']);
    } else if($sort['sort_by'] == 'no_number_plate') {
        // Get latest no_number_plate for each vehicle
        $subquery = DB::table('vehicle_no_number_plate_history')
            ->select('vehicle_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('vehicle_id');
        
        $this->model = $this->model
            ->leftJoin('vehicle_no_number_plate_history', function($join) use ($subquery) {
                $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'vehicles.id')
                     ->joinSub($subquery, 'latest_plate', function($join) {
                         $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'latest_plate.vehicle_id')
                              ->on('vehicle_no_number_plate_history.date', '=', 'latest_plate.max_date');
                     });
            })
            ->orderBy('vehicle_no_number_plate_history.no_number_plate', $sort['sort_type']);
    } else {
        $this->model = $this->model->orderBy($sort['sort_by'], $sort['sort_type']);
    }
}
```

**注意事項:**
- `leftJoin` を使用して、`no_number_plate` 履歴がない vehicle も含める
- サブクエリで各 vehicle の最新レコード（`MAX(date)`）を取得
- JOIN 条件で最新レコードのみを結合

##### 1.1.3. Fix logic error in `getAllVehicle()` method - JOIN condition

**問題:**
- Line 561 で `paginate()` メソッドと同様のロジックエラーが存在
- `isset($sort_by) == 'number_plate'` という誤ったロジックを使用
- Frontend は `'no_number_plate'` を送信するが、Backend は `'number_plate'` をチェックしている

**現在の実装** (line 561):

```php
if ($number_plate || isset($sort_by) == 'number_plate') {
    $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
    // ...
}
```

**変更内容:**

- Line 561 の条件を修正:
  - `isset($sort_by) == 'number_plate'` → `isset($sort_by) && $sort_by == 'no_number_plate'`
- `paginate()` メソッドと同じ修正を適用

**変更後のコード:**

```php
if ($number_plate || (isset($sort_by) && $sort_by == 'no_number_plate')) {
    $this->model = $this->model->join('vehicle_no_number_plate_history', 'vehicle_no_number_plate_history.vehicle_id', 'vehicles.id');
    // ...
}
```

##### 1.1.4. Add special handling for `no_number_plate` sorting in `getAllVehicle()` method

**問題:**
- `getAllVehicle()` メソッドでも `no_number_plate` のソート処理が必要
- 現在は `orderBy($sort_by, ...)` で直接ソートしようとするが、`vehicles.no_number_plate` カラムは存在しない
- このメソッドは既に `orderBy('departments.position', 'ASC')` をデフォルトで使用しているため、それを維持しつつ `no_number_plate` ソートを追加する必要がある

**現在の実装** (lines 577-585):

```php
if (isset($sort_by) && isset($sort_type)) {
    if($sort_by == 'leasing_period') {
        $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
        $this->model = $this->model->orderBy('departments.position', 'ASC')
            ->orderBy('maintenance_leases.leasing_period', $sort_type);
    } else {
        $this->model = $this->model->orderBy('departments.position', 'ASC')
            ->orderBy($sort_by, $sort_type);
    }
} else {
    $this->model = $this->model->orderBy('departments.position', 'ASC');
}
```

**変更内容:**

- `no_number_plate` のソートケースを追加
- `paginate()` メソッドと同じアプローチを使用（サブクエリで最新レコードを取得）
- `departments.position` のソートを維持しつつ、`no_number_plate` でソート

**変更後のコード:**

```php
if (isset($sort_by) && isset($sort_type)) {
    if($sort_by == 'leasing_period') {
        $this->model = $this->model->join('maintenance_leases', 'maintenance_leases.vehicle_id', '=', 'vehicles.id');
        $this->model = $this->model->orderBy('departments.position', 'ASC')
            ->orderBy('maintenance_leases.leasing_period', $sort_type);
    } else if($sort_by == 'no_number_plate') {
        // Get latest no_number_plate for each vehicle
        $subquery = DB::table('vehicle_no_number_plate_history')
            ->select('vehicle_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('vehicle_id');
        
        $this->model = $this->model
            ->leftJoin('vehicle_no_number_plate_history', function($join) use ($subquery) {
                $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'vehicles.id')
                     ->joinSub($subquery, 'latest_plate', function($join) {
                         $join->on('vehicle_no_number_plate_history.vehicle_id', '=', 'latest_plate.vehicle_id')
                              ->on('vehicle_no_number_plate_history.date', '=', 'latest_plate.max_date');
                     });
            })
            ->orderBy('departments.position', 'ASC')
            ->orderBy('vehicle_no_number_plate_history.no_number_plate', $sort_type);
    } else {
        $this->model = $this->model->orderBy('departments.position', 'ASC')
            ->orderBy($sort_by, $sort_type);
    }
} else {
    $this->model = $this->model->orderBy('departments.position', 'ASC');
}
```

**注意事項:**
- `departments.position` のソートを最初に維持（既存の動作を保持）
- その後、`no_number_plate` でソート
- CSV エクスポート機能でも使用されるメソッドのため、既存の動作を壊さないように注意

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (Frontend に依存しない)

    - Task 1.1.1: Fix logic error in `paginate()` method - JOIN condition
    - Task 1.1.2: Add special handling for `no_number_plate` sorting in `paginate()` method
    - Task 1.1.3: Fix logic error in `getAllVehicle()` method - JOIN condition
    - Task 1.1.4: Add special handling for `no_number_plate` sorting in `getAllVehicle()` method

2. **Frontend 実装** (Backend 実装後にテスト可能)

    - Task 1.1.1: Fix field key mismatch in template

3. **統合テスト**
    - Backend API で `no_number_plate` ソートが正しく動作することを確認
    - Frontend で `no_number_plate` カラムが表示されることを確認
    - Frontend でソート機能が動作することを確認
    - 他のソート可能なカラムが影響を受けないことを確認

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 2-3 時間

    - Task 1.1.1: Fix logic error in `paginate()` - 15分
    - Task 1.1.2: Add special handling in `paginate()` - 1時間（サブクエリ実装とテスト）
    - Task 1.1.3: Fix logic error in `getAllVehicle()` - 15分
    - Task 1.1.4: Add special handling in `getAllVehicle()` - 1時間（サブクエリ実装とテスト）
    - テストとデバッグ: 30分

- **Frontend**: 15-30 分

    - Task 1.1.1: Fix field key mismatch - 5分
    - テストと確認: 10-25分

**合計**: 2.5-3.5 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

    - サブクエリを使用するため、大量のデータがある場合のパフォーマンスに注意
    - `vehicle_no_number_plate_history` テーブルにインデックスが適切に設定されているか確認
    - 必要に応じて、クエリの実行計画を確認し、最適化を検討

2. **UX 考慮:**

    - ソート機能が正常に動作することで、ユーザーがデータを効率的に検索・整理できるようになる
    - `no_number_plate` カラムが正しく表示されることで、ユーザー体験が向上

3. **データ整合性:**

    - `vehicle_no_number_plate_history` テーブルから最新のレコードを取得する際、`MAX(date)` を使用
    - 同じ日付に複数のレコードがある場合の動作を確認（通常は `id` が最大のレコードを取得）
    - `no_number_plate` 履歴がない vehicle も含めるため、`leftJoin` を使用

4. **既存機能との互換性:**

    - `department_name` と `leasing_period` のソート処理は既存のまま維持
    - `getAllVehicle()` メソッドの `departments.position` ソートを維持
    - 他のソート可能なカラムへの影響がないことを確認
    - CSV エクスポート機能への影響を確認

5. **データベース構造:**

    - `vehicle_no_number_plate_history` テーブル:
      - `id`: Primary key
      - `vehicle_id`: Foreign key to `vehicles.id`
      - `date`: DateTime (インデックスあり)
      - `no_number_plate`: String (インデックスあり)
    - 各 vehicle は複数の `no_number_plate` 履歴を持つ可能性がある
    - Frontend は `plate_history[0].no_number_plate`（最新レコード）を表示

6. **テストケース:**

    - `no_number_plate` で昇順ソート
    - `no_number_plate` で降順ソート
    - `no_number_plate` 履歴がない vehicle が含まれる場合の動作
    - フィルターと組み合わせたソート
    - 他のカラムとのソート比較
    - 大量データでのパフォーマンステスト
