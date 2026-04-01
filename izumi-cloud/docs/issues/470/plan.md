# Issue #470: [FE] ムービー自動ループ配信除外オプション: トグルボタンUI実装 - Implementation Plan

## 概要 (Overview)

**Current State:**
ムービー一覧画面には各ムービーのサムネール、タイトル、説明、タグ、および編集・削除ボタンが表示されています。ユーザーがムービーを自動ループ配信から除外するオプションはありません。

**Improved State:**
各ムービーサムネールの下にトグルボタンを追加し、ユーザーがムービーを自動ループ配信から除外できるようにします。トグルボタンの状態はBackend API（Issue #469で実装済み）に保存され、ON（緑色）/OFF（グレー）の視覚的フィードバックを提供します。

**Parent Issue:** #468  
**Backend Dependency:** Issue #469 (完了済み)  
**Scope:** Frontend Only

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/VideoPlayer/index.vue`

##### 1.1.1. Template - トグルボタンUIの追加

各ムービーアイテムのサムネールの下にトグルボタンを追加します。

**既存コード** (line 136-173):

現在、ムービーアイテムは以下の構造になっています：
- ドラッグハンドル（左側）
- サムネール画像 + 動画長
- ビデオ情報（タイトル、説明、タグ）
- 機能ボタン（編集・削除）

**変更内容:**

サムネール画像の下（`<div class="thumbnail">` の直後）にトグルボタンコンテナを追加：

```vue
<div class="thumbnail">
  <img :src="item.thumbnail.file_url" alt="item-thumbnail" class="thumbnail-img">
  <span class="video-length">{{ item.file_length }}</span>
</div>

<div class="toggle-loop-container">
  <b-form-checkbox
    v-model="item.is_loop_enabled"
    switch
    size="sm"
    @change="handleToggleLoopEnabled(item)"
  >
    ループ配信
  </b-form-checkbox>
</div>
```

**詳細:**
- `v-model="item.is_loop_enabled"`: データバインディング（default: true）
- `switch`: Bootstrap Vue のスイッチスタイル
- `size="sm"`: 小さいサイズ
- `@change`: トグル時のイベントハンドラー
- ラベル「ループ配信」を表示

**挿入位置:** Line 149 の後（`</div>` の後、`<div class="video-info">` の前）

##### 1.1.2. Script - API Endpoint定義の追加

`url_api_list` オブジェクトに新しいAPIエンドポイントを追加します。

**既存コード** (line 821-834):

```javascript
const url_api_list = {
    apiPostVideo: '/movies',
    apiEditVideo: '/movies',
    apiDeleteVideo: '/movies',
    apiGetListVideo: '/movies',
    apiGetVideoDetail: '/movies',
    apiPostFile: '/movies/upload-file',
    apiChangeOrder: '/movies/update-position',
    apiGetAssignMovieOnDates: '/movies/schedule',
    apiExportFileCSV: '/movies/dowload-user-watching',
    apiAssignMovieOnDates: '/movies/store-movie-schedule',
    apiGetDeliveryRecord: '/movies/show-user-watch-movie',
    apiDownloadDeliveryRecord: '/movies/download-all-watching-movie',
};
```

**変更内容:**

新しいエンドポイントを追加：

```javascript
const url_api_list = {
    apiPostVideo: '/movies',
    apiEditVideo: '/movies',
    apiDeleteVideo: '/movies',
    apiGetListVideo: '/movies',
    apiGetVideoDetail: '/movies',
    apiPostFile: '/movies/upload-file',
    apiChangeOrder: '/movies/update-position',
    apiGetAssignMovieOnDates: '/movies/schedule',
    apiExportFileCSV: '/movies/dowload-user-watching',
    apiAssignMovieOnDates: '/movies/store-movie-schedule',
    apiGetDeliveryRecord: '/movies/show-user-watch-movie',
    apiDownloadDeliveryRecord: '/movies/download-all-watching-movie',
    apiUpdateLoopEnabled: '/movies',
};
```

**挿入位置:** Line 833 の後

##### 1.1.3. Script - Import文の追加

`updateMovieLoopEnabled` 関数をimportします。

**既存コード** (line 808-819):

```javascript
import {
    postFile,
    postVideo,
    editVideo,
    deleteVideo,
    getVideoDetail,
    getMovieOnDates,
    changeVideoOrder,
    getDeliveryRecord,
    getListVideoPlayer,
    assignMovieOnDates,
} from '@/api/modules/videoPlayer';
```

**変更内容:**

新しい関数を追加：

```javascript
import {
    postFile,
    postVideo,
    editVideo,
    deleteVideo,
    getVideoDetail,
    getMovieOnDates,
    changeVideoOrder,
    getDeliveryRecord,
    getListVideoPlayer,
    assignMovieOnDates,
    updateMovieLoopEnabled,
} from '@/api/modules/videoPlayer';
```

##### 1.1.4. Script - Methods セクションに `handleToggleLoopEnabled` メソッドの追加

トグルボタンのイベントハンドラーを実装します。

**既存コード** (line 1059-):

`methods` セクションに新しいメソッドを追加します。

**変更内容:**

`handleToggleLoopEnabled` メソッドを追加（適切な位置、例えば `handleGetListVideo` の後）：

```javascript
async handleToggleLoopEnabled(item) {
    const previousState = item.is_loop_enabled;
    
    try {
        this.overlay.show = true;
        
        const url = `${url_api_list.apiUpdateLoopEnabled}/${item.id}/loop-enabled`;
        
        const data = {
            is_loop_enabled: item.is_loop_enabled,
        };
        
        const response = await updateMovieLoopEnabled(url, data);
        
        if (response.code === 200) {
            MakeToast({
                variant: 'success',
                title: '成功',
                content: 'ループ配信設定を更新しました',
            });
        }
    } catch (error) {
        console.log(error);
        
        item.is_loop_enabled = previousState;
        
        MakeToast({
            variant: 'danger',
            title: 'エラー',
            content: 'ループ配信設定の更新に失敗しました',
        });
        
        await this.handleGetListVideo();
    }
    
    this.overlay.show = false;
},
```

**実装詳細:**
- Optimistic Update: UI を即座に更新
- エラー時のロールバック: 前の状態に戻す
- Toast通知: 成功/エラーメッセージを表示
- Overlay表示: API呼び出し中のローディング状態

**挿入位置:** Line 1152 の後（`handleGetListVideo` メソッドの後）

##### 1.1.5. Style - トグルボタン用SCSSの追加

トグルボタンのスタイルを定義します。

**既存コード** (line 2200-):

Styleセクションの末尾に新しいスタイルを追加します。

**変更内容:**

```scss
.toggle-loop-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
  padding: 4px;
  
  .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #28a745;
    border-color: #28a745;
  }
  
  .custom-control-input:not(:checked) ~ .custom-control-label::before {
    background-color: #6c757d;
    border-color: #6c757d;
  }
  
  .custom-control-label {
    font-size: 12px;
    color: #3e3e3e;
    user-select: none;
    white-space: nowrap;
  }
  
  .custom-switch {
    padding-left: 2.25rem;
  }
}

@media (max-width: 768px) {
  .toggle-loop-container {
    margin-top: 6px;
    
    .custom-control-label {
      font-size: 11px;
    }
  }
}
```

**スタイル詳細:**
- ON状態: 緑色 (`#28a745`)
- OFF状態: グレー (`#6c757d`)
- フォントサイズ: 12px（レスポンシブ: 11px on mobile）
- レイアウト: 中央揃え
- ユーザー選択不可: テキスト選択を防止

**挿入位置:** Line 3124 の後（styleセクションの末尾）

#### 1.2. File: `resources/js/api/modules/videoPlayer.js`

##### 1.2.1. API関数 `updateMovieLoopEnabled` の追加

Backend APIを呼び出す関数を実装します。

**変更内容:**

ファイルの末尾に新しいexport関数を追加：

```javascript
export const updateMovieLoopEnabled = (url, data) => {
    return new Promise((resolve, reject) => {
        axios.put(url, data)
            .then((response) => {
                resolve(response.data);
            })
            .catch((error) => {
                reject(error);
            });
    });
};
```

**実装詳細:**
- Method: `PUT`
- URL: `/api/movies/{id}/loop-enabled`
- Request Body: `{ is_loop_enabled: boolean }`
- Response: `{ code: 200, data: {...} }`

**挿入位置:** ファイル末尾（他のexport関数の後）

---

## 実装順序 (Implementation Order)

### Frontend 実装 (1-2時間)

**Phase 1: API層の準備 (15分)**
1. Task 1.2.1: `resources/js/api/modules/videoPlayer.js` に `updateMovieLoopEnabled` 関数を追加

**Phase 2: Component実装 (45分)**
2. Task 1.1.3: Import文を追加（`updateMovieLoopEnabled`）
3. Task 1.1.2: `url_api_list` にAPIエンドポイントを追加
4. Task 1.1.4: `handleToggleLoopEnabled` メソッドを実装
5. Task 1.1.1: Templateにトグルボタンを追加
6. Task 1.1.5: SCSSスタイルを追加

**Phase 3: テストと調整 (30分)**
7. 手動テスト: トグルボタンの動作確認
8. UI/UXの調整
9. レスポンシブデザインの確認

---

## 見積もり工数 (Estimated Effort)

### Frontend: 1-2 時間

- **API関数実装**: 15分
  - `updateMovieLoopEnabled` 関数作成
  
- **Component実装**: 45分
  - Import文追加: 5分
  - API endpoint定義: 5分
  - `handleToggleLoopEnabled` メソッド実装: 20分
  - Template修正: 10分
  - SCSS追加: 5分

- **テスト・調整**: 30分
  - 手動テスト: 15分
  - UI/UX調整: 10分
  - レスポンシブ確認: 5分

**合計**: 1-2 時間

---

## 技術的な注意事項 (Technical Notes)

### 1. パフォーマンス考慮:

**Optimistic Update:**
- UI を即座に更新してユーザー体験を向上
- API呼び出しは非同期でバックグラウンド実行
- ネットワーク遅延を感じさせない

**Error Handling:**
- API失敗時は前の状態にロールバック
- データ整合性を保つため、エラー時にリスト全体を再取得

### 2. UX 考慮:

**視覚的フィードバック:**
- ON状態: 緑色（`#28a745`）- 明確な「有効」の意味
- OFF状態: グレー（`#6c757d`）- 明確な「無効」の意味
- スムーズなアニメーション（Bootstrap Vueデフォルト）

**ローディング状態:**
- `b-overlay` を使用してAPI呼び出し中を表示
- ユーザーは処理中であることを理解できる

**Toast通知:**
- 成功: 「ループ配信設定を更新しました」（緑色）
- エラー: 「ループ配信設定の更新に失敗しました」（赤色）

### 3. データ整合性:

**Default値:**
- 新しいムービーはデフォルトで `is_loop_enabled: true`（Backend側で設定）
- Frontendは受け取った値を表示

**状態管理:**
- `v-model` を使用してリアルタイムでデータバインディング
- `item.is_loop_enabled` を直接操作（Vue reactivity system）

**Rollback戦略:**
- API失敗時は `previousState` に戻す
- さらに `handleGetListVideo()` を呼び出してサーバー状態と同期

### 4. 既存機能との互換性:

**非破壊的変更:**
- 既存のムービーアイテムレイアウトを維持
- サムネールの下に追加するだけ
- 既存の編集・削除機能に影響なし

**ドラッグ＆ドロップ:**
- `draggable` コンポーネントとの干渉なし
- トグルボタンは `.drag-cat` の内部にあるため、ドラッグ可能

**レスポンシブデザイン:**
- モバイル対応のメディアクエリを追加
- 小さい画面でも適切に表示

### 5. Backend統合:

**API Contract:**
- Endpoint: `PUT /api/movies/{id}/loop-enabled`
- Request: `{ "is_loop_enabled": true/false }`
- Response: `{ "code": 200, "data": {...} }`
- Backend Issue #469で既に実装済み

**Validation:**
- Backend側でバリデーション済み
- Frontend はシンプルにboolean値を送信

---

## Testing Checklist

### Unit Testing
- [ ] `handleToggleLoopEnabled` がAPIを正しく呼び出すか
- [ ] エラー時に状態がロールバックされるか
- [ ] Toast通知が適切に表示されるか

### Integration Testing
- [ ] Backend API（Issue #469）との統合テスト
- [ ] APIレスポンスの処理
- [ ] エラーハンドリング

### UI/UX Testing
- [ ] トグルボタンが正しい位置に表示されるか
- [ ] ON/OFF状態の色が正しいか（緑/グレー）
- [ ] レスポンシブデザインが機能するか
- [ ] Overlayが表示されるか

### Regression Testing
- [ ] 既存のムービー一覧機能が正常動作するか
- [ ] ドラッグ＆ドロップが正常動作するか
- [ ] 編集・削除ボタンが正常動作するか
- [ ] フィルター機能が正常動作するか

---

## Dependencies

- **Backend Issue #469**: 完了済み
  - `PUT /api/movies/{id}/loop-enabled` endpoint
  - `is_loop_enabled` フィールドがAPIレスポンスに含まれる

- **Libraries**:
  - Bootstrap Vue: `b-form-checkbox` component
  - Axios: HTTP client
  - Vue.js 2.x: Reactivity system

---

## Acceptance Criteria Review

✅ **Requirements from Issue #470:**

1. ✅ トグルボタンUI追加
   - `b-form-checkbox` with `switch` prop
   - ラベル「ループ配信」表示
   
2. ✅ 状態管理
   - `v-model="item.is_loop_enabled"`
   - デフォルト値: ON (true)
   
3. ✅ API統合
   - `handleToggleLoopEnabled` メソッド実装
   - `PUT /api/movies/{id}/loop-enabled` 呼び出し
   - Success/Error toast messages
   
4. ✅ スタイリング
   - ON: 緑色 (`#28a745`)
   - OFF: グレー (`#6c757d`)
   - レスポンシブデザイン
   
5. ✅ ユーザー体験
   - Overlay表示
   - Optimistic update
   - Error時のrollback

---

## Risk Analysis

### Low Risk:
- シンプルなUI追加
- Backend API既に実装済み
- 既存コードへの影響最小限

### Potential Issues:
1. **Default値の不一致**: Backend が `is_loop_enabled` を返さない場合
   - **Mitigation**: Backend Issue #469のレビューで確認

2. **Network Error**: API呼び出し失敗
   - **Mitigation**: Rollback + データ再取得で対応済み

3. **UI Layout**: サムネール下のスペース不足
   - **Mitigation**: レスポンシブデザインで調整

---

## Review Checklist

Before marking as complete, verify:

- [ ] すべてのコード変更が実装されている
- [ ] スタイルが適切に適用されている
- [ ] API統合が正常動作する
- [ ] エラーハンドリングが機能する
- [ ] Toast通知が表示される
- [ ] Overlayが表示される
- [ ] レスポンシブデザインが機能する
- [ ] 既存機能に破壊的変更がない
- [ ] コードがプロジェクト規約に準拠している
- [ ] テストが完了している

---

**Plan Created:** 2025-12-03  
**Issue:** #470  
**Type:** Frontend Enhancement  
**Estimated Effort:** 1-2 hours  
**Status:** Ready for Development

