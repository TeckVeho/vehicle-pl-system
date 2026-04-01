# Issue #468: Add option to exclude movies from auto-loop delivery - Implementation Plan

## 概要 (Overview)

**現状 (Current State):**
- ムービー配信システムでは、すべてのムービーが日次自動的に順序にループ送信される
- 配信スケジュールは `movie_schedules` テーブルで管理され、`AutoStoreMovieSchedule` コマンドで毎日自動生成される
- 現在、特定のムービーをループから除外する機能がない

**改善後 (Improved State):**
- ムービー一覧画面で各ムービーにトグルボタンを追加
- トグルボタンでムービーのループ配信対象/対象外を設定可能
- 自動配信スケジュール生成時に、対象外フラグを持つムービーをスキップ
- デフォルトはON（ループ対象）

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: database/migrations/YYYY_MM_DD_HHMMSS_add_is_loop_enabled_to_movies_table.php

##### 1.1.1. 新しいマイグレーションファイルを作成

新しいカラム `is_loop_enabled` を `movies` テーブルに追加するマイグレーションを作成します。

**変更内容:**
- マイグレーションファイルを作成: `php artisan make:migration add_is_loop_enabled_to_movies_table --table=movies`
- `up()` メソッドで `is_loop_enabled` カラムを追加
- `down()` メソッドでロールバック処理を定義

**実装例:**
```php
public function up()
{
    Schema::table('movies', function (Blueprint $table) {
        $table->boolean('is_loop_enabled')->default(true)->after('position')->comment('Loop delivery enabled flag: true=enabled, false=disabled');
    });
}

public function down()
{
    Schema::table('movies', function (Blueprint $table) {
        $table->dropColumn('is_loop_enabled');
    });
}
```

---

#### 1.2. File: app/Models/Movies.php

##### 1.2.1. Movieモデルに新しいカラムを追加

**既存コード** (line 21-30):
```php
protected $fillable = [
    'id',
    'file_id',
    'thumbnail_file_id',
    'title',
    'content',
    'position',
    'tag',
    'file_length'
];
```

**変更内容:**
- `$fillable` 配列に `is_loop_enabled` を追加

```php
protected $fillable = [
    'id',
    'file_id',
    'thumbnail_file_id',
    'title',
    'content',
    'position',
    'tag',
    'file_length',
    'is_loop_enabled'
];
```

##### 1.2.2. キャストを追加（オプション）

**変更内容:**
- `$casts` 配列に `is_loop_enabled` を boolean として追加

```php
protected $casts = [
    'data' => 'array',
    'is_loop_enabled' => 'boolean'
];
```

---

#### 1.3. File: app/Console/Commands/AutoStoreMovieSchedule.php

##### 1.3.1. ループ対象外のムービーを除外するロジックを追加

**現在の実装** (line 54):
```php
$movie = Movies::query()->where('id', '>', $subMovieSchedules->movie_id)->first();
```

**変更内容:**
- `is_loop_enabled = true` のムービーのみを取得するように条件を追加
- ループの最初に戻る処理でも同様の条件を追加

**実装例:**
```php
$movie = Movies::query()
    ->where('id', '>', $subMovieSchedules->movie_id)
    ->where('is_loop_enabled', true)
    ->orderBy('id', 'ASC')
    ->first();
```

##### 1.3.2. ループの最初に戻る処理を更新

**現在の実装** (line 64):
```php
$movieFirst = Movies::query()->first();
```

**変更内容:**
- ループ対象のムービーのみを取得

```php
$movieFirst = Movies::query()
    ->where('is_loop_enabled', true)
    ->orderBy('id', 'ASC')
    ->first();
```

##### 1.3.3. 初期スケジュール作成処理を更新

**現在の実装** (line 77):
```php
$movieFirst = Movies::query()->first();
```

**変更内容:**
- 同様に `is_loop_enabled = true` の条件を追加

```php
$movieFirst = Movies::query()
    ->where('is_loop_enabled', true)
    ->orderBy('id', 'ASC')
    ->first();
```

---

#### 1.4. File: app/Http/Controllers/Api/MoviesController.php

##### 1.4.1. 新しいAPIエンドポイントを追加: ループフラグ更新

**変更内容:**
- `updateLoopEnabled` メソッドを追加して、ムービーのループフラグを更新

**実装例:**
```php
/**
 * @OA\Put(
 *   path="/api/movies/{id}/loop-enabled",
 *   tags={"Movies"},
 *   summary="Update movie loop enabled flag",
 *   operationId="movies_update_loop_enabled",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     @OA\Schema(type="integer"),
 *   ),
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       @OA\Property(property="is_loop_enabled", type="boolean", example=true)
 *     )
 *   ),
 *   @OA\Response(response=200, description="Success"),
 *   @OA\Response(response=404, description="Movie not found"),
 *   security={{"auth": {}}},
 * )
 */
public function updateLoopEnabled(MoviesRequest $request, $id)
{
    try {
        $data = $this->repository->updateLoopEnabled($id, $request->input('is_loop_enabled'));
        return $this->responseJson(200, new BaseResource($data));
    } catch (\Exception $e) {
        return $this->responseJson(404, null, $e->getMessage());
    }
}
```

---

#### 1.5. File: app/Repositories/MoviesRepository.php

##### 1.5.1. ループフラグ更新メソッドを追加

**変更内容:**
- `updateLoopEnabled` メソッドを実装

**実装例:**
```php
public function updateLoopEnabled($id, $isLoopEnabled)
{
    $movie = $this->model->find($id);
    
    if (!$movie) {
        throw new Exception("Movie not found");
    }
    
    $movie->is_loop_enabled = $isLoopEnabled;
    $movie->save();
    
    return $movie;
}
```

##### 1.5.2. listMovies メソッドを更新してループフラグを含める

**既存コード** (line 59-67):
```php
$movieList = $this->model->with([
    'movieFile' => function($query) {
        $query->select('id', 'file_url');
    },
    'thumbnail' => function($query) {
        $query->select('id', 'file_url');
    },
]);
```

**変更内容:**
- 特に変更不要（`is_loop_enabled` は自動的に含まれる）
- ただし、明示的に選択する場合は select に追加

---

#### 1.6. File: app/Repositories/Contracts/MoviesRepositoryInterface.php

##### 1.6.1. インターフェースに新しいメソッドを追加

**変更内容:**
- `updateLoopEnabled` メソッドのシグネチャを追加

```php
public function updateLoopEnabled($id, $isLoopEnabled);
```

---

#### 1.7. File: routes/api.php

##### 1.7.1. 新しいルートを追加

**既存コード** (line 148-150):
```php
Route::put('movies/update-position', "MoviesController@updatePosition");

Route::apiResource('movies', "MoviesController");
```

**変更内容:**
- ループフラグ更新用のルートを追加（apiResource の前に配置）

```php
Route::put('movies/{id}/loop-enabled', "MoviesController@updateLoopEnabled");
Route::put('movies/update-position', "MoviesController@updatePosition");

Route::apiResource('movies', "MoviesController");
```

---

#### 1.8. File: app/Http/Requests/MoviesRequest.php

##### 1.8.1. バリデーションルールを追加（必要に応じて）

**変更内容:**
- `is_loop_enabled` フィールドのバリデーションルールを追加

```php
public function rules()
{
    $rules = [
        // 既存のルール
        'is_loop_enabled' => 'sometimes|boolean',
    ];
    
    return $rules;
}
```

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: resources/js/pages/VideoPlayer/index.vue

##### 1.1.1. トグルボタンをムービーアイテムに追加

**既存コード** (line 136-173):
```vue
<div v-for="(item, index) in items" :key="index" class="d-flex flex-row drag-cat item position-relative">
    <div class="d-flex align-items-center p-2" style="margin-right: 20px;">
        <i class="fas fa-bars icon-drag handle" />
    </div>

    <div v-if="item.thumbnail" class="thumbnail">
        <img :src="item.thumbnail.file_url" alt="item-thumbnail" class="thumbnail-img">
        <span class="video-length">{{ item.file_length }}</span>
    </div>

    <div v-else class="thumbnail">
        <b-skeleton class="skeleton-cover" />
        <span class="video-length">{{ item.file_length }}</span>
    </div>

    <div class="video-info">
        <div class="d-flex flex-column">
            <span class="title">{{ item.title }}</span>
            <span class="description">{{ item.content }}</span>
        </div>

        <div class="d-flex flex-row flex-wrap">
            <div v-for="(tag_item, tag_index) in item.tag" :key="`item-${index}-tag-${tag_index}`" class="tag-pill">
                <span>{{ returnTextFromOption(tag_item) }}</span>
            </div>
        </div>
    </div>

    <div class="function-button position-absolute">
        <b-button variant="warning" @click="handleOpenModalEditVideo(item.id)">
            <i class="fas fa-pen" />
        </b-button>

        <b-button class="ml-3" variant="danger" @click="handleOpenModalConfirmDelete(item.id)">
            <i class="fas fa-trash-alt" />
        </b-button>
    </div>
</div>
```

**変更内容:**
- サムネイルの下にトグルボタンを追加
- トグルボタンのON/OFF状態を `item.is_loop_enabled` にバインド
- トグルボタンのクリックイベントハンドラーを追加

**実装例:**
```vue
<div v-for="(item, index) in items" :key="index" class="d-flex flex-row drag-cat item position-relative">
    <div class="d-flex align-items-center p-2" style="margin-right: 20px;">
        <i class="fas fa-bars icon-drag handle" />
    </div>

    <div class="thumbnail-container">
        <div v-if="item.thumbnail" class="thumbnail">
            <img :src="item.thumbnail.file_url" alt="item-thumbnail" class="thumbnail-img">
            <span class="video-length">{{ item.file_length }}</span>
        </div>

        <div v-else class="thumbnail">
            <b-skeleton class="skeleton-cover" />
            <span class="video-length">{{ item.file_length }}</span>
        </div>

        <!-- トグルボタン追加 -->
        <div class="loop-toggle-container">
            <b-form-checkbox
                v-model="item.is_loop_enabled"
                switch
                size="sm"
                @change="handleToggleLoopEnabled(item.id, $event)"
            >
                <span class="toggle-label">ループ配信</span>
            </b-form-checkbox>
        </div>
    </div>

    <div class="video-info">
        <div class="d-flex flex-column">
            <span class="title">{{ item.title }}</span>
            <span class="description">{{ item.content }}</span>
        </div>

        <div class="d-flex flex-row flex-wrap">
            <div v-for="(tag_item, tag_index) in item.tag" :key="`item-${index}-tag-${tag_index}`" class="tag-pill">
                <span>{{ returnTextFromOption(tag_item) }}</span>
            </div>
        </div>
    </div>

    <div class="function-button position-absolute">
        <b-button variant="warning" @click="handleOpenModalEditVideo(item.id)">
            <i class="fas fa-pen" />
        </b-button>

        <b-button class="ml-3" variant="danger" @click="handleOpenModalConfirmDelete(item.id)">
            <i class="fas fa-trash-alt" />
        </b-button>
    </div>
</div>
```

##### 1.1.2. トグルボタンのイベントハンドラーを追加

**変更内容:**
- `methods` セクションに `handleToggleLoopEnabled` メソッドを追加
- APIを呼び出してループフラグを更新

**実装例:**
```javascript
async handleToggleLoopEnabled(movieId, isEnabled) {
    try {
        this.overlay.show = true;

        const url = `${url_api_list.apiGetListVideo}/${movieId}/loop-enabled`;
        const payload = {
            is_loop_enabled: isEnabled
        };

        const response = await axios.put(url, payload);

        if (response.data.code === 200) {
            this.$bvToast.toast('ループ配信設定を更新しました', {
                title: '成功',
                variant: 'success',
                solid: true,
                autoHideDelay: 3000
            });
        }
    } catch (error) {
        console.error('Error updating loop enabled:', error);
        
        this.$bvToast.toast('ループ配信設定の更新に失敗しました', {
            title: 'エラー',
            variant: 'danger',
            solid: true,
            autoHideDelay: 5000
        });

        await this.handleGetListVideo();
    } finally {
        this.overlay.show = false;
    }
}
```

##### 1.1.3. スタイルを追加

**変更内容:**
- `<style>` セクションにトグルボタン用のスタイルを追加

**実装例:**
```scss
.thumbnail-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.loop-toggle-container {
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;

    .toggle-label {
        font-size: 12px;
        color: #333;
        margin-left: 4px;
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }
}
```

---

#### 1.2. File: resources/js/api/url_api_list.js (または該当するAPI設定ファイル)

##### 1.2.1. API URLを確認

**変更内容:**
- `apiGetListVideo` が正しく設定されていることを確認
- 必要に応じて、新しいエンドポイント用のURLを追加

**確認例:**
```javascript
export const url_api_list = {
    apiGetListVideo: '/api/movies',
    // 他のURL...
};
```

---

## 実装順序 (Implementation Order)

### 1. Backend 実装 (優先度: 高)

**Phase 1: Database & Model (依存関係なし)**
1. マイグレーションファイルを作成 (BE 1.1)
2. マイグレーションを実行: `php artisan migrate`
3. Movies モデルを更新 (BE 1.2)

**Phase 2: Repository & Controller (Phase 1に依存)**
4. MoviesRepositoryInterface にメソッドを追加 (BE 1.6)
5. MoviesRepository に実装を追加 (BE 1.5)
6. MoviesController に新しいエンドポイントを追加 (BE 1.4)
7. MoviesRequest にバリデーションを追加 (BE 1.8)
8. routes/api.php にルートを追加 (BE 1.7)

**Phase 3: Auto Schedule Logic (Phase 1に依存)**
9. AutoStoreMovieSchedule コマンドを更新 (BE 1.3)

### 2. Frontend 実装 (Backend Phase 2完了後)

**Phase 4: UI Implementation (Backend Phase 2に依存)**
10. VideoPlayer/index.vue にトグルボタンを追加 (FE 1.1.1)
11. イベントハンドラーを実装 (FE 1.1.2)
12. スタイルを追加 (FE 1.1.3)
13. API URL設定を確認 (FE 1.2)

### 3. 統合テスト (全実装完了後)

**Phase 5: Testing**
14. トグルボタンの動作確認
15. APIエンドポイントのテスト
16. 自動配信スケジュール生成のテスト
17. デフォルト値（ON）の確認
18. 既存データの互換性確認

---

## 見積もり工数 (Estimated Effort)

### Backend: 4-6 時間

- **Database Migration (BE 1.1)**: 0.5時間
  - マイグレーションファイル作成と実行
  
- **Model Update (BE 1.2)**: 0.5時間
  - Movies モデルの更新
  
- **Repository & Interface (BE 1.5, 1.6)**: 1時間
  - Repository メソッド実装とインターフェース更新
  
- **Controller & Routes (BE 1.4, 1.7)**: 1.5時間
  - 新しいエンドポイント実装とルート設定
  
- **Request Validation (BE 1.8)**: 0.5時間
  - バリデーションルール追加
  
- **Auto Schedule Logic (BE 1.3)**: 1-2時間
  - AutoStoreMovieSchedule コマンドの更新とテスト

### Frontend: 3-4 時間

- **UI Implementation (FE 1.1.1)**: 1時間
  - トグルボタンのマークアップ追加
  
- **Event Handler (FE 1.1.2)**: 1.5時間
  - APIコール実装とエラーハンドリング
  
- **Styling (FE 1.1.3)**: 0.5-1時間
  - トグルボタンのスタイリング
  
- **API Configuration (FE 1.2)**: 0.5時間
  - API URL確認と設定

### Testing & Integration: 2-3 時間

- **Unit Testing**: 1時間
- **Integration Testing**: 1-2時間
- **Manual Testing**: 0.5-1時間

**合計**: 9-13 時間

---

## 技術的な注意事項 (Technical Notes)

### 1. パフォーマンス考慮

**Database Indexing:**
- `is_loop_enabled` カラムにインデックスを追加することを検討
- 自動配信スケジュール生成クエリが頻繁に実行されるため

```php
$table->index('is_loop_enabled');
```

**Query Optimization:**
- `AutoStoreMovieSchedule` コマンドで複数のクエリを実行するため、N+1問題に注意
- 必要に応じて Eager Loading を使用

### 2. UX 考慮

**即時フィードバック:**
- トグルボタンをクリックした際、即座にUIを更新
- API呼び出しが失敗した場合は元の状態に戻す

**視覚的な明確さ:**
- ON状態は緑色、OFF状態はグレーで表示
- ラベル「ループ配信」を表示して機能を明確化

**ローディング状態:**
- API呼び出し中はトグルボタンを無効化
- オーバーレイまたはスピナーで処理中を表示

### 3. データ整合性

**既存データの処理:**
- マイグレーション実行時、既存のすべてのムービーは `is_loop_enabled = true` がデフォルト
- 既存の配信スケジュールには影響なし

**トランザクション:**
- トグルボタン更新時のエラーハンドリングを適切に実装
- データベース更新が失敗した場合はロールバック

**配信スケジュールへの影響:**
- 既に作成された配信スケジュールには影響しない
- 次回の自動生成時から新しいロジックが適用される

### 4. 既存機能との互換性

**AutoStoreMovieSchedule コマンド:**
- ループ対象のムービーが0件の場合の処理を追加
- ログ出力を追加して、スキップされたムービーを記録

**API互換性:**
- 既存のムービー一覧取得APIは変更なし（新しいフィールドが追加されるだけ）
- 既存のフロントエンドコードには影響なし

**マイグレーション:**
- ロールバック可能な設計
- 本番環境でのマイグレーション実行前にバックアップを推奨

### 5. セキュリティ考慮

**権限チェック:**
- ループフラグ更新APIは認証済みユーザーのみアクセス可能
- 必要に応じて、管理者権限チェックを追加

**入力検証:**
- `is_loop_enabled` は boolean 値のみ受け付ける
- MoviesRequest でバリデーションを実施

### 6. ログとモニタリング

**ログ記録:**
- ループフラグ変更時にログを記録
- 自動配信スケジュール生成時、スキップされたムービーをログに記録

**モニタリング:**
- ループ対象のムービー数をモニタリング
- すべてのムービーがOFFになった場合のアラート検討

---

## リスクと対策 (Risks & Mitigation)

### リスク1: すべてのムービーがループ対象外になる

**対策:**
- AutoStoreMovieSchedule コマンドで、ループ対象のムービーが0件の場合は警告ログを出力
- 管理画面で少なくとも1つのムービーがONであることを確認するUI追加を検討

### リスク2: 既存の配信スケジュールへの影響

**対策:**
- 既存のスケジュールには影響しない設計
- 次回の自動生成時から新しいロジックが適用されることをドキュメント化

### リスク3: マイグレーション失敗

**対策:**
- 本番環境でのマイグレーション前にステージング環境でテスト
- データベースバックアップを取得
- ロールバック手順を準備

---

**Generated by:** Cursor AI Agent  
**Generated at:** 2025-11-28  
**Branch:** 468-feat-movie-auto-loop-exclusion

