# Issue #463: Add filtering and multi-select export for movie viewing data - Implementation Plan

## 概要 (Overview)

視聴者データ出力機能を強化し、配信記録ポップアップからチェックボックス選択、タイトルと日付によるフィルタリング機能を追加する。

現状：「視聴者一括出力」ボタンは、日付範囲のみでフィルタリングし、全てのムービーの視聴データを一括出力する。
改善後：モーダルを表示し、特定のムービーを選択、タイトルと日付でフィルタリングして出力可能にする。

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/VideoPlayer/index.vue`

##### 1.1.1. 新しいモーダル「視聴者一括出力」を追加

現在の `handleDownloadDeliveryRecord()` 関数を変更し、直接ダウンロードするのではなく、モーダルを表示するように修正する。

**既存コード** (line 98-101):

-   `視聴者一括出力` ボタンをクリックすると `handleDownloadDeliveryRecord()` を呼び出す
-   この関数は直接 API を呼び出してダウンロードする

**変更内容:**

-   新しいデータプロパティを追加:
    -   `modal_bulk_export: false` - モーダルの表示状態
    -   `bulk_export_movies: []` - エクスポート用のムービーリスト（チェックボックス付き）
    -   `bulk_export_filter: { title: '', start_date: '', end_date: '' }` - フィルター条件
    -   `bulk_export_selected_movies: []` - 選択されたムービー ID 配列

##### 1.1.2. モーダル UI 構造を実装

モーダル内に以下の要素を配置:

-   **検索フィルター部分:**

    -   タイトル検索入力フィールド (`b-form-input`)
    -   開始日付ピッカー (`date-picker`)
    -   終了日付ピッカー (`date-picker`)
    -   検索ボタン

-   **ムービーリスト部分:**

    -   全選択チェックボックス
    -   ムービーごとのチェックボックス付きテーブル:
        -   チェックボックス
        -   ムービータイトル
        -   日付
        -   閲覧数

-   **フッター部分:**
    -   「キャンセル」ボタン
    -   「出力」ボタン

##### 1.1.3. フィルタリング機能を実装

-   タイトルでフィルタリング:

    -   `bulk_export_filter.title` でムービーリストをリアルタイムフィルタリング
    -   `computed` プロパティで `filteredBulkExportMovies` を作成

-   日付範囲でフィルタリング:
    -   `bulk_export_filter.start_date` と `bulk_export_filter.end_date` で配信記録をフィルタリング

##### 1.1.4. チェックボックス選択機能を実装

-   個別選択:

    -   各ムービーのチェックボックスをクリックすると `bulk_export_selected_movies` 配列に追加/削除

-   全選択機能:
    -   ヘッダーの全選択チェックボックスをクリックすると、フィルタリングされた全てのムービーを選択/解除

##### 1.1.5. データ出力処理を実装

新しい関数 `handleBulkExportMovies()` を作成:

-   選択されたムービー ID 配列をバリデーション（最低 1 つ選択）
-   フィルター条件と選択されたムービー ID を API に送信
-   Excel ファイルをダウンロード
-   モーダルを閉じる

##### 1.1.6. モーダルライフサイクル処理

-   `handleShowBulkExportModal()` 関数:

    -   モーダルを開く前に配信記録データを取得
    -   `bulk_export_movies` にデータをセット
    -   フィルター条件をリセット

-   `handleCloseBulkExportModal()` 関数:
    -   選択状態をクリア
    -   フィルター条件をリセット

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: `app/Http/Controllers/Api/MoviesController.php`

##### 1.1.1. `downloadAllWatchingMovie` メソッドを修正

**現在の実装** (line 1099-1109):

-   `start_date` と `end_date` のみを受け取る
-   全てのムービーのデータをエクスポート

**変更内容:**

-   リクエストパラメータを追加:

    -   `movie_ids` (array): 選択されたムービー ID の配列
    -   `title` (string): タイトルフィルター
    -   `start_date` (string): 開始日付
    -   `end_date` (string): 終了日付

-   バリデーションを追加:
    -   `movie_ids` が配列であることを確認
    -   日付形式のバリデーション

#### 1.2. File: `app/Repositories/MoviesRepository.php`

##### 1.2.1. `downloadAllWatchingMovie` メソッドを修正

**現在の実装** (line 656-676):

-   `start_date` と `end_date` でフィルタリング
-   全てのムービーを取得

**変更内容:**

-   ムービー ID フィルタリングを追加:

    -   `movie_ids` パラメータが存在する場合、`whereIn('id', $movie_ids)` を追加

-   タイトルフィルタリングを追加:

    -   `title` パラメータが存在する場合、`where('title', 'like', "%{$title}%")` を追加

-   クエリ構造:

```php
$query = Movies::select('id', 'title');

if ($movieIds = Arr::get($params, 'movie_ids')) {
    $query->whereIn('id', $movieIds);
}

if ($title = Arr::get($params, 'title')) {
    $query->where('title', 'like', "%{$title}%");
}

$query->with(['movieWatching' => function ($subQuery) use ($params) {
    // 既存の日付フィルタリングロジック
}]);

return $query->get();
```

#### 1.3. File: `app/Exports/ExportAllMovieWatching.php`

##### 1.3.1. エクスポートロジックの確認と調整

-   データ構造が新しいフィルタリング条件に対応しているか確認
-   必要に応じてヘッダーやフォーマットを調整
-   空データの場合のハンドリングを追加

#### 1.4. File: `routes/api.php` (確認のみ)

##### 1.4.1. ルート定義の確認

**現在のルート** (line 134):

```php
Route::get('movies/download-all-watching-movie', "MoviesController@downloadAllWatchingMovie");
```

-   GET メソッドのままで良いか確認
-   パラメータが多い場合、POST メソッドへの変更を検討

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (依存関係なし、並行開発可能)

    - 1.1. MoviesController の修正
    - 1.2. MoviesRepository の修正
    - 1.3. ExportAllMovieWatching の確認

2. **Frontend 実装** (Backend 完了後)

    - 1.1.1. モーダルデータプロパティ追加
    - 1.1.2. モーダル UI 構造実装
    - 1.1.3. フィルタリング機能実装
    - 1.1.4. チェックボックス選択機能実装
    - 1.1.5. データ出力処理実装
    - 1.1.6. モーダルライフサイクル処理

3. **統合テスト**
    - Frontend と Backend の統合確認
    - 各フィルター条件のテスト
    - エッジケースのテスト

---

## 見積もり工数 (Estimated Effort)

-   **Backend**: 3-4 時間

    -   Controller 修正: 1h
    -   Repository 修正: 1.5h
    -   Export クラス確認: 0.5h
    -   テスト: 1h

-   **Frontend**: 5-6 時間
    -   モーダル UI 実装: 2h
    -   フィルタリング機能: 1.5h
    -   チェックボックス機能: 1h
    -   API 統合: 1h
    -   テスト: 0.5h

**合計**: 8-10 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**

    - ムービーリストが大量にある場合のページネーション検討
    - フィルタリングのデバウンス処理

2. **UX 考慮:**

    - ローディング状態の表示
    - エラーハンドリングとユーザーフィードバック
    - 選択数の表示

3. **データ整合性:**

    - 選択されたムービー ID のバリデーション
    - 削除されたムービーのハンドリング

4. **既存機能との互換性:**
    - 既存の「配信記録」モーダルとの UI 統一
    - 既存のフィルター機能との整合性
