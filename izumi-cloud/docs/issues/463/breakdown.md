# Issue #463: Breakdown Summary

## 親 Issue

#463 - Add filtering and multi-select export for movie viewing data

## 作成された Issue (Created Issues)

### Frontend Issue

**Issue #465**: `[FE] 視聴者データのフィルタリングとマルチセレクトエクスポート機能 / Chức năng lọc và xuất dữ liệu người xem đa lựa chọn`

-   **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/465
-   **Story Points**: 8 SP (~8 hours)
-   **Labels**: frontend, enhancement
-   **Status**: Open

**含まれるタスク:**

1. 新しいモーダル「視聴者一括出力」を追加
2. モーダル UI 構造を実装
3. フィルタリング機能を実装（タイトル、日付範囲）
4. チェックボックス選択機能を実装（個別選択、全選択）
5. データ出力処理を実装
6. モーダルライフサイクル処理
7. フロントエンドユニットテスト

**依存関係:**

-   BE issue (#466) 完了後に統合テスト可能
-   単独で UI 実装とモック開発は可能

---

### Backend Issue

**Issue #466**: `[BE] 視聴者データのフィルタリングとマルチセレクトエクスポートAPI / API lọc và xuất dữ liệu người xem đa lựa chọn`

-   **URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/466
-   **Story Points**: 6 SP (~6 hours)
-   **Labels**: backend, enhancement
-   **Status**: Open

**含まれるタスク:**

1. MoviesController の `downloadAllWatchingMovie` メソッド修正
2. MoviesRepository の `downloadAllWatchingMovie` メソッド修正
3. ExportAllMovieWatching のエクスポートロジック確認
4. リクエストパラメータのバリデーション追加
5. バックエンドユニットテスト

**依存関係:**

-   なし（独立して開発可能）

---

## Summary

### 統計 (Statistics)

-   **Total Issues Created**: 2
-   **Frontend Issues**: 1 (8 SP)
-   **Backend Issues**: 1 (6 SP)
-   **Total Story Points**: 14 SP (~14 hours)

### 開発戦略 (Development Strategy)

-   **Parallel Development**: ✅ FE と BE は並行開発可能
-   **Dependencies**: FE (#465) は BE (#466) 完了後に統合テスト
-   **Team Assignment**:
    -   1 Frontend developer → Issue #465
    -   1 Backend developer → Issue #466

### 実装順序 (Implementation Order)

1. **Phase 1 (Parallel)**:

    - BE: API 修正、フィルタリング実装、バリデーション、ユニットテスト
    - FE: モーダル UI 実装、フィルタリング、チェックボックス、ユニットテスト（モック使用）

2. **Phase 2 (Integration)**:
    - FE と BE の統合テスト
    - エンドツーエンドテスト
    - バグ修正と調整

### 技術的ポイント (Technical Points)

-   **Frontend**: `resources/js/pages/VideoPlayer/index.vue` のみ変更
-   **Backend**: Controller, Repository, Export class の修正
-   **API Endpoint**: `/api/movies/download-all-watching-movie` (既存 API の拡張)
-   **New Parameters**: `movie_ids[]`, `title`, `start_date`, `end_date`

### 期待される成果 (Expected Outcome)

-   ユーザーが特定のムービーを選択して視聴データを出力可能
-   タイトルと日付でフィルタリング可能
-   UI が直感的で使いやすい
-   既存機能との互換性を維持

---

## Next Steps

1. `/dev 465` - FE 開発開始
2. `/dev 466` - BE 開発開始
3. 統合テスト実施
4. `/pr` - Pull Request 作成
