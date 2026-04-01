# Issue #560: Breakdown - Task Decomposition

## 概要 (Overview)

Issue #560 の実装タスクを Backend issue と Frontend issue に分解しました。

**戦略**: デフォルト戦略に従い、1つの BE issue + 1つの FE issue を作成（合計 2 issues）

**理由**:
- Backend: 7箇所の演算子変更（VehicleRepository.php）
- Frontend: 1箇所のフィールド名変更（VehicleMaster/index.vue）
- 各レイヤーで明確なオーナーシップ
- 並行開発が可能（BE 完了後、FE は独立して実装可能）

---

## Created Issues

### Issue #561: [BE] 廃車日ロジック修正: 翌日からグレーアウト / Sửa logic ngày hủy xe: Bôi xám từ ngày hôm sau

**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/561

**Type**: Backend (bug fix)

**Story Points**: 2 SP

**Estimated Time**: 1-2 hours

**Status**: ✅ 完了済み

**Scope**:
- VehicleRepository.php の7箇所を修正
  - paginate() メソッド: 1箇所
  - getAllVehicle() メソッド: 4箇所
  - getDashboardVehicle() メソッド: 4箇所

**Changes**:
- `<=` を `<` に変更（4箇所）: scrap_date_custom の計算ロジック
- `>` を `>=` に変更（3箇所）: hide_scrap_date フィルターロジック

**Dependencies**: None

**Acceptance Criteria**:
- [x] 7箇所の修正完了
- [x] テストケース全て合格:
  - scrap_date = 昨日 → グレーアウト
  - scrap_date = 今日 → 通常表示
  - scrap_date = 明日 → 通常表示
  - scrap_date = NULL → 通常表示
- [x] フィルター動作確認
- [x] 横浜800い1757 での確認

---

### Issue #600: [FE] 廃車日ロジック修正: scrap_date_custom使用 / Sửa logic ngày hủy xe: Sử dụng scrap_date_custom

**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/600

**Type**: Frontend (bug fix)

**Story Points**: 1 SP

**Estimated Time**: 0.25-0.5 hours

**Status**: ⏳ 未完了

**Scope**:
- VehicleMaster/index.vue の1箇所を修正
  - handleRenderCellClass() メソッド: 1箇所

**Changes**:
- `item['scrap_date']` → `item['scrap_date_custom']` に変更（1箇所）

**Dependencies**: Issue #561 (Backend 修正完了が必要)

**Acceptance Criteria**:
- [ ] 1箇所の修正完了
- [ ] 以下のテストケースが全て合格:
  - scrap_date = 昨日 → グレーアウト表示（scrap_date_custom が値を持つ）
  - scrap_date = 今日 → 通常表示（scrap_date_custom が NULL）
  - scrap_date = 明日 → 通常表示（scrap_date_custom が NULL）
  - scrap_date = NULL → 通常表示（scrap_date_custom が NULL）
- [ ] フォーム（edit/create）が正常動作
- [ ] 横浜800い1757 の車両で動作確認
- [ ] 既存機能への破壊的変更なし

**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/561

**Type**: Backend (bug fix)

**Story Points**: 2 SP

**Estimated Time**: 1-2 hours

**Scope**:
- VehicleRepository.php の7箇所を修正
  - paginate() メソッド: 1箇所
  - getAllVehicle() メソッド: 4箇所
  - getDashboardVehicle() メソッド: 4箇所

**Changes**:
- `<=` を `<` に変更（4箇所）: scrap_date_custom の計算ロジック
- `>` を `>=` に変更（3箇所）: hide_scrap_date フィルターロジック

**Dependencies**: None

**Acceptance Criteria**:
- [x] 7箇所の修正完了
- [x] テストケース全て合格:
  - scrap_date = 昨日 → グレーアウト
  - scrap_date = 今日 → 通常表示
  - scrap_date = 明日 → 通常表示
  - scrap_date = NULL → 通常表示
- [x] フィルター動作確認
- [x] 横浜800い1757 での確認

---

## Story Points Calculation

### Issue #561 (Backend): 2 SP

**Factors**:

1. **Code Volume**: Small (S)
   - 7箇所の演算子変更のみ
   - 新しいコードの追加なし

2. **Complexity**: Simple
   - 単純な演算子変更（`<=` → `<`, `>` → `>=`）
   - ロジックの理解が容易
   - エッジケースが少ない

3. **Testing**: Standard
   - 4つの基本テストケース
   - 1つの実車両での確認
   - フィルター機能のテスト

4. **Architecture Impact**: None
   - 既存の構造を変更しない
   - DB スキーマの変更なし
   - API インターフェースの変更なし

5. **Integration Dependencies**: None
   - Backend のみの修正
   - Frontend への影響なし

6. **Uncertainty**: Very Low
   - 要件が明確
   - 修正箇所が特定済み
   - 技術的な課題なし

**Time Breakdown**:
- Implementation: 0.5-1 hour (7箇所の修正)
- Unit Testing: 0.5-1 hour (テストケース実行)
- Total: 1-2 hours = **2 SP**

---

### Issue #600 (Frontend): 1 SP

**Factors**:

1. **Code Volume**: Very Small (XS)
   - 1箇所のフィールド名変更のみ
   - 1行のコード変更

2. **Complexity**: Very Simple
   - 単純なフィールド名変更（`scrap_date` → `scrap_date_custom`）
   - ロジックの変更なし
   - エッジケースなし

3. **Testing**: Minimal
   - 表示確認のみ
   - フォーム動作確認（影響なし）

4. **Architecture Impact**: None
   - 既存の構造を変更しない
   - API インターフェースの変更なし
   - コンポーネント構造の変更なし

5. **Integration Dependencies**: Medium
   - Backend の Issue #561 完了が必要
   - Backend が `scrap_date_custom` を返す必要がある

6. **Uncertainty**: Very Low
   - 要件が明確
   - 修正箇所が特定済み（1箇所のみ）
   - 技術的な課題なし

**Time Breakdown**:
- Implementation: 5-10 minutes (1箇所の修正)
- Testing: 10-20 minutes (表示確認)
- Code Review: 5 minutes
- Total: 0.25-0.5 hours = **1 SP**

---

## Task Distribution

### Backend: 1 issue (2 SP)

- Issue #561: 廃車日ロジック修正 ✅ 完了済み

### Frontend: 1 issue (1 SP)

- Issue #600: 廃車日ロジック修正（scrap_date_custom使用） ⏳ 未完了

### Total: 2 issues, 3 SP

---

## Implementation Strategy

### Phase 1: Backend Implementation (Issue #561) ✅ 完了済み

**Owner**: Backend Developer

**Tasks**:
1. VehicleRepository.php の修正 ✅
2. ローカルテスト実行 ✅
3. コードレビュー準備 ✅

**Deliverables**:
- 修正済み VehicleRepository.php ✅
- テスト結果レポート ✅

### Phase 2: Frontend Implementation (Issue #600) ⏳ 未完了

**Owner**: Frontend Developer

**Tasks**:
1. VehicleMaster/index.vue の修正
2. ローカルテスト実行
3. コードレビュー準備

**Deliverables**:
- 修正済み VehicleMaster/index.vue
- テスト結果レポート

**Dependencies**: Issue #561 完了が必要

### Phase 3: Integration Testing ⏳ 未完了

**Owner**: QA / Full Stack Developer

**Tasks**:
1. 車両マスター画面での動作確認
2. ダッシュボード画面での動作確認
3. フィルター機能の確認
4. 横浜800い1757 での実車確認
5. フォーム（edit/create）の動作確認

**Deliverables**:
- テスト結果ドキュメント
- スクリーンショット（必要に応じて）

**Dependencies**: Issue #561 と Issue #600 の両方が完了が必要

---

## Dependencies Graph

```
Issue #560 (Parent)
    ├── Issue #561 [BE] 廃車日ロジック修正 (2 SP) ✅ 完了
    │       └── No dependencies
    └── Issue #600 [FE] 廃車日ロジック修正 (1 SP) ⏳ 未完了
            └── Depends on: Issue #561 (Backend 完了が必要)
```

**Parallel Development**: 
- Backend (#561) は独立して開発可能 ✅ 完了済み
- Frontend (#600) は Backend 完了後に開発可能 ⏳ 待機中

**Critical Path**: Issue #561 → Issue #600 → Integration Testing → Deployment

---

## Risk Assessment

### Low Risk Factors:

✅ 修正箇所が明確（Backend 7箇所、Frontend 1箇所）
✅ 変更内容がシンプル（演算子のみ、フィールド名変更のみ）
✅ 既存データへの影響なし
✅ Frontend への影響が限定的（表示ロジックのみ）
✅ ロールバックが容易

### Mitigation Plan:

1. **コードレビュー**: 修正箇所を複数人で確認
2. **段階的テスト**: 
   - ローカル環境でテスト
   - ステージング環境でテスト
   - 本番環境で慎重にデプロイ
3. **ロールバック準備**: Git で変更を管理、いつでも戻せる状態を維持

---

## Success Metrics

### Definition of Done:

- [x] Issue #561 (Backend) が完了（全ての受け入れ基準を満たす） ✅
- [ ] Issue #600 (Frontend) が完了（全ての受け入れ基準を満たす） ⏳
- [x] Backend コードレビュー承認 ✅
- [ ] Frontend コードレビュー承認 ⏳
- [x] Backend 全テストケース合格 ✅
- [ ] Frontend 全テストケース合格 ⏳
- [ ] 統合テスト完了 ⏳
- [ ] 横浜800い1757 での動作確認完了 ⏳
- [ ] ステージング環境でのテスト完了 ⏳
- [ ] 本番環境へのデプロイ完了 ⏳
- [ ] 顧客への報告完了 ⏳

### Quality Criteria:

- コードの可読性が維持されている
- 既存機能への破壊的変更がない
- パフォーマンスへの影響がない
- ドキュメントが更新されている（必要に応じて）

---

## Notes

### Frontend について:

**重要**: Frontend の `handleRenderCellClass()` メソッドも修正が必要です。

**理由**:
- Backend は `scrap_date_custom` を計算して返す
- Frontend は現在 `item['scrap_date']` (元のフィールド) をチェックしている
- Frontend は `item['scrap_date_custom']` をチェックする必要がある
- Backend の修正だけでは不十分（Issue #600 が必要）

### 退職者ユーザーについて:

Issue タイトルに「退職者ユーザー」も含まれていますが、今回は車両廃車のロジックのみを修正します。退職者ユーザーについても同様の問題がある場合は、別途 issue を作成する必要があります。

### Story Points Registration:

**Status**: ⚠️ Manual registration required

GitHub Projects への SP 登録スクリプトが見つからないため、手動で登録する必要があります。

**Manual Registration Steps**:
1. GitHub Projects board を開く
2. Issue #561 を見つける → "Story Points" フィールドに `2` を入力
3. Issue #600 を見つける → "Story Points" フィールドに `1` を入力
4. 保存

**Alternative**: GitHub CLI を使用:
```bash
# Issue #561 (Backend)
gh project item-edit --field "Story Points" --project-id <PROJECT_ID> <ITEM_ID_561> --value 2

# Issue #600 (Frontend)
gh project item-edit --field "Story Points" --project-id <PROJECT_ID> <ITEM_ID_600> --value 1
```

---

## Timeline

**Estimated Duration**: 1.25-2.5 hours (Backend: 1-2h, Frontend: 0.25-0.5h)

**Breakdown**:
- Backend Implementation: 30-60 minutes ✅ 完了（35分）
- Backend Testing: 30-60 minutes ✅ 完了
- Frontend Implementation: 5-10 minutes ⏳
- Frontend Testing: 10-20 minutes ⏳
- Integration Testing: 15-30 minutes ⏳
- Code Review: 15 minutes (Backend ✅, Frontend ⏳)
- Deployment: 15 minutes ⏳

**Recommended Schedule**:
- Day 1 Morning: Backend Implementation + Testing ✅ 完了
- Day 1 Afternoon: Frontend Implementation + Testing ⏳
- Day 1 Evening: Integration Testing + Code Review ⏳
- Day 2 Morning: Deployment + Customer Confirmation ⏳

---

## Related Documents

- Parent Issue: #560
- Implementation Plan: `docs/issues/560/plan.md`
- Issue Document: `docs/issues/560/issue.md`
- Backend Issue: #561 ✅ 完了済み
- Frontend Issue: #600 ⏳ 未完了

---

## Changelog

- 2025-12-26: Initial breakdown created (Backend only)
- 2025-12-26: Issue #561 (Backend) created with 2 SP ✅
- 2025-12-26: Breakdown updated - Issue #600 (Frontend) added with 1 SP
- 2025-12-26: Total: 2 issues, 3 SP

