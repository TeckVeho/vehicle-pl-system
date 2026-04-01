# Issue #470: Breakdown Report - ムービー自動ループ配信除外オプション: トグルボタンUI実装

## Breakdown Summary

**Parent Issue:** #470  
**Related to:** #468 (親issue)  
**Backend Dependency:** #469 (完了済み)  
**Scope:** Frontend Only  
**Strategy:** Single FE Issue (Default Strategy)  
**Total Issues to Create:** 1 issue  
**Total Story Points:** 2 SP (~2 hours)

---

## 分解戦略 (Breakdown Strategy)

### ✅ Recommended: 1 Frontend Issue (Default Strategy)

**理由 / Rationale:**
- Issue #470 は Frontend Only の小規模な機能追加
- すべてのタスクが密接に関連している（1つのコンポーネント内の変更）
- 1人のFE開発者が一括で実装可能
- 分割する必要性がない（Total SP: 2 SP < 8 SP threshold）
- シンプルな依存関係管理（Backend Issue #469に依存）

**Benefits:**
- ✅ 単一オーナーシップ（1人のFE開発者が担当）
- ✅ タスク間の調整不要
- ✅ 実装の一貫性を保証
- ✅ 進捗管理がシンプル
- ✅ コミュニケーションオーバーヘッドなし

---

## Issue Details (予定)

### Issue #470: [FE] ムービー自動ループ配信除外オプション: トグルボタンUI実装 / Loại trừ video khỏi phát sóng tự động: Triển khai UI nút chuyển đổi

**Type:** Frontend Enhancement  
**Labels:** `frontend`, `enhancement`  
**Story Points:** 2 SP (~2 hours)  
**Dependency:** Backend Issue #469 (完了済み)

---

## 日本語 / Japanese

### 親Issue

#468 に関連

### 説明

ムービー一覧画面に各ムービーのサムネールの下にトグルボタンを追加し、ユーザーがムービーを自動ループ配信から除外できるようにします。トグルボタンの状態はBackend API（Issue #469で実装済み）に保存され、ON（緑色）/OFF（グレー）の視覚的フィードバックを提供します。

### 実装タスク

この issue には以下のすべてのタスクが含まれます：

#### 1. API層の実装 (15分)

**File:** `resources/js/api/modules/videoPlayer.js`

- API関数 `updateMovieLoopEnabled` の追加
- Method: `PUT`
- Endpoint: `/api/movies/{id}/loop-enabled`
- Request Body: `{ is_loop_enabled: boolean }`
- Response: `{ code: 200, data: {...} }`

#### 2. Component実装 (45分)

**File:** `resources/js/pages/VideoPlayer/index.vue`

##### 2.1. Import文の追加 (5分)
- `updateMovieLoopEnabled` 関数をimport
- Line 808-819付近

##### 2.2. API Endpoint定義の追加 (5分)
- `url_api_list` に `apiUpdateLoopEnabled: '/movies'` を追加
- Line 821-834付近

##### 2.3. Event Handler実装 (20分)
- `handleToggleLoopEnabled(item)` メソッドを追加
- Optimistic Update実装
- Error時のRollback処理
- Toast通知（成功/エラー）
- Overlay表示
- Line 1152付近（`handleGetListVideo` の後）

##### 2.4. Template修正 (10分)
- `<b-form-checkbox>` switchコンポーネントを追加
- サムネールの下に配置
- `v-model="item.is_loop_enabled"` でバインド
- `@change` イベントハンドラー接続
- ラベル「ループ配信」表示
- Line 149付近

##### 2.5. SCSS追加 (5分)
- `.toggle-loop-container` スタイル定義
- ON状態: 緑色 (`#28a745`)
- OFF状態: グレー (`#6c757d`)
- レスポンシブデザイン対応
- Line 3124付近（styleセクション末尾）

#### 3. テスト・調整 (30分)

- 手動テスト: トグルボタンの動作確認
- UI/UX調整: 色、配置、サイズ
- レスポンシブ確認: モバイル表示チェック
- Backend統合テスト: API呼び出し確認
- エラーハンドリング確認
- 既存機能への影響確認（ドラッグ＆ドロップ、編集・削除）

### 技術詳細

**API Endpoint:**
```
PUT /api/movies/{id}/loop-enabled
Body: { "is_loop_enabled": true/false }
Response: { "code": 200, "data": {...} }
```

**使用コンポーネント:**
- `b-form-checkbox` with `switch` prop (Bootstrap Vue)
- `b-toast` for notifications
- `b-overlay` for loading state

**実装パターン:**
- Optimistic Update: UI即時更新
- Error Rollback: API失敗時に前の状態に戻す
- Toast Notifications: 成功/エラーメッセージ表示

### 受け入れ基準

- [ ] トグルボタンが各ムービーサムネールの下に表示されること
- [ ] トグルボタンのデフォルト状態がON（緑色）であること
- [ ] ユーザーがトグルボタンをクリックしてON/OFFを切り替えられること
- [ ] トグルボタンの状態変更がAPIを通じてバックエンドに保存されること
- [ ] 成功時に「ループ配信設定を更新しました」というトーストメッセージが表示されること
- [ ] エラー時に「ループ配信設定の更新に失敗しました」というトーストメッセージが表示されること
- [ ] API呼び出し中はオーバーレイが表示されること
- [ ] トグルボタンのスタイルが適切に適用されること（ON=緑、OFF=グレー）
- [ ] レスポンシブデザインが機能すること（モバイル対応）
- [ ] プロジェクト規約に準拠すること
- [ ] 既存機能への破壊的変更がないこと（ドラッグ＆ドロップ、編集・削除）

### 依存関係

- **Backend Issue #469**: 完了済み
  - `PUT /api/movies/{id}/loop-enabled` endpoint
  - `is_loop_enabled` フィールドがAPIレスポンスに含まれる

### 見積もり工数

**Total: 2 SP (~2 hours)**

- API関数実装: 15分 (0.25 SP)
- Component実装: 45分 (0.75 SP)
  - Import文追加: 5分
  - API endpoint定義: 5分
  - Event handler実装: 20分
  - Template修正: 10分
  - SCSS追加: 5分
- テスト・調整: 30分 (0.5 SP)
  - 手動テスト: 15分
  - UI/UX調整: 10分
  - レスポンシブ確認: 5分
- バッファー: 30分 (0.5 SP)

---

## Tiếng Việt / Vietnamese

### Issue cha

Liên quan đến #468

### Mô tả

Thêm nút chuyển đổi (toggle button) dưới thumbnail của mỗi video trong màn hình danh sách video, cho phép người dùng loại trừ video khỏi phát sóng tự động lặp. Lưu trạng thái ON/OFF của nút chuyển đổi qua Backend API (đã triển khai ở Issue #469) và cung cấp phản hồi trực quan với màu ON (xanh lá) / OFF (xám).

### Các task triển khai

Issue này bao gồm tất cả các task sau:

#### 1. Triển khai API layer (15 phút)

**File:** `resources/js/api/modules/videoPlayer.js`

- Thêm API function `updateMovieLoopEnabled`
- Method: `PUT`
- Endpoint: `/api/movies/{id}/loop-enabled`
- Request Body: `{ is_loop_enabled: boolean }`
- Response: `{ code: 200, data: {...} }`

#### 2. Triển khai Component (45 phút)

**File:** `resources/js/pages/VideoPlayer/index.vue`

##### 2.1. Thêm Import statement (5 phút)
- Import function `updateMovieLoopEnabled`
- Line 808-819 khu vực

##### 2.2. Thêm định nghĩa API Endpoint (5 phút)
- Thêm `apiUpdateLoopEnabled: '/movies'` vào `url_api_list`
- Line 821-834 khu vực

##### 2.3. Triển khai Event Handler (20 phút)
- Thêm method `handleToggleLoopEnabled(item)`
- Triển khai Optimistic Update
- Xử lý Rollback khi lỗi
- Toast notification (thành công/lỗi)
- Hiển thị Overlay
- Line 1152 khu vực (sau `handleGetListVideo`)

##### 2.4. Sửa Template (10 phút)
- Thêm component `<b-form-checkbox>` switch
- Đặt dưới thumbnail
- Bind với `v-model="item.is_loop_enabled"`
- Kết nối `@change` event handler
- Hiển thị label "ループ配信" (Phát sóng lặp)
- Line 149 khu vực

##### 2.5. Thêm SCSS (5 phút)
- Định nghĩa style `.toggle-loop-container`
- Trạng thái ON: màu xanh lá (`#28a745`)
- Trạng thái OFF: màu xám (`#6c757d`)
- Hỗ trợ responsive design
- Line 3124 khu vực (cuối section style)

#### 3. Testing và điều chỉnh (30 phút)

- Manual test: Kiểm tra hoạt động của toggle button
- Điều chỉnh UI/UX: màu sắc, vị trí, kích thước
- Kiểm tra responsive: hiển thị mobile
- Integration test với Backend: xác nhận API call
- Kiểm tra error handling
- Xác nhận không ảnh hưởng chức năng hiện có (drag & drop, edit/delete)

### Chi tiết kỹ thuật

**API Endpoint:**
```
PUT /api/movies/{id}/loop-enabled
Body: { "is_loop_enabled": true/false }
Response: { "code": 200, "data": {...} }
```

**Component sử dụng:**
- `b-form-checkbox` với prop `switch` (Bootstrap Vue)
- `b-toast` cho thông báo
- `b-overlay` cho trạng thái loading

**Pattern triển khai:**
- Optimistic Update: Cập nhật UI ngay lập tức
- Error Rollback: Khôi phục trạng thái trước khi có lỗi API
- Toast Notifications: Hiển thị message thành công/lỗi

### Tiêu chí chấp nhận

- [ ] Toggle button hiển thị dưới thumbnail của mỗi video
- [ ] Trạng thái mặc định của toggle button là ON (màu xanh lá)
- [ ] Người dùng có thể click toggle button để chuyển đổi ON/OFF
- [ ] Thay đổi trạng thái toggle button được lưu vào backend qua API
- [ ] Hiển thị toast message "ループ配信設定を更新しました" khi thành công
- [ ] Hiển thị toast message "ループ配信設定の更新に失敗しました" khi lỗi
- [ ] Hiển thị overlay trong khi gọi API
- [ ] Style của toggle button được áp dụng đúng (ON=xanh, OFF=xám)
- [ ] Responsive design hoạt động (hỗ trợ mobile)
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có (drag & drop, edit/delete)

### Phụ thuộc

- **Backend Issue #469**: Đã hoàn thành
  - Endpoint `PUT /api/movies/{id}/loop-enabled`
  - Field `is_loop_enabled` có trong API response

### Ước tính công sức

**Tổng: 2 SP (~2 giờ)**

- Triển khai API function: 15 phút (0.25 SP)
- Triển khai Component: 45 phút (0.75 SP)
  - Thêm import statement: 5 phút
  - Định nghĩa API endpoint: 5 phút
  - Triển khai event handler: 20 phút
  - Sửa template: 10 phút
  - Thêm SCSS: 5 phút
- Testing và điều chỉnh: 30 phút (0.5 SP)
  - Manual test: 15 phút
  - Điều chỉnh UI/UX: 10 phút
  - Kiểm tra responsive: 5 phút
- Buffer: 30 phút (0.5 SP)

---

## Story Points Calculation

### Factors Considered

1. **Code Volume:** Small (S)
   - 1 API function (~10 lines)
   - 1 event handler method (~25 lines)
   - 1 template addition (~15 lines)
   - 1 SCSS block (~30 lines)
   - Total: ~80 lines of code

2. **Complexity:** Simple
   - Straightforward UI component addition
   - Standard API integration pattern
   - No complex business logic
   - No algorithm complexity

3. **Testing:** Minimal
   - Manual testing only
   - No unit test creation required for this task
   - Integration testing with existing backend

4. **Architecture Impact:** None
   - No new patterns introduced
   - No migrations needed
   - No breaking changes
   - Uses existing Bootstrap Vue components

5. **Integration Dependencies:** Low
   - Backend API already implemented (Issue #469)
   - Uses existing API client infrastructure
   - No external service integration

6. **Uncertainty:** Very Low
   - Well-defined requirements
   - Similar pattern already exists in codebase
   - No research needed
   - Clear implementation path

### SP Calculation Result

Based on the factors above:

**Total SP: 2 SP (~2 hours)**

Breakdown:
- API function: 0.25 SP (15 min) - Simple axios wrapper
- Component implementation: 0.75 SP (45 min) - Standard Vue component modification
- Testing & adjustment: 0.5 SP (30 min) - Manual testing and UI tweaks
- Buffer: 0.5 SP (30 min) - Unexpected issues, code review feedback

**Complexity Level:** Simple  
**Risk Level:** Low  
**Confidence Level:** High

---

## Implementation Order

### Recommended Development Sequence

```
Phase 1: API Layer (15 min)
  └─ Task 1: Add updateMovieLoopEnabled function to videoPlayer.js

Phase 2: Component Layer (45 min)
  ├─ Task 2: Add import statement
  ├─ Task 3: Add API endpoint to url_api_list
  ├─ Task 4: Implement handleToggleLoopEnabled method
  ├─ Task 5: Add b-form-checkbox to template
  └─ Task 6: Add SCSS styles

Phase 3: Testing & Refinement (30 min)
  ├─ Manual testing
  ├─ UI/UX adjustment
  ├─ Responsive design verification
  └─ Regression testing (existing features)
```

### Dependencies Flow

```
Backend Issue #469 (完了済み)
        ↓
     Issue #470 (FE)
        ↓
  Integration Testing
        ↓
    Ready for PR
```

---

## Files to Modify

### 1. `resources/js/api/modules/videoPlayer.js`
**Lines to add:** ~10 lines  
**Location:** End of file  
**Purpose:** Add `updateMovieLoopEnabled` API function

### 2. `resources/js/pages/VideoPlayer/index.vue`
**Lines to modify/add:** ~70 lines  
**Locations:**
- Line ~819: Add import statement (1 line)
- Line ~833: Add API endpoint definition (1 line)
- Line ~149: Add template toggle button (~15 lines)
- Line ~1152: Add event handler method (~25 lines)
- Line ~3124: Add SCSS styles (~30 lines)

**Total files:** 2 files  
**Total lines:** ~80 lines

---

## Risk Analysis

### Low Risk ✅
- Simple UI addition with existing component
- Backend API already implemented and tested
- No database changes required
- No breaking changes to existing features
- Uses familiar patterns already in codebase

### Potential Issues (Mitigation Planned)

1. **UI Layout Issues** (Low probability)
   - Risk: Toggle button might not fit well below thumbnail
   - Mitigation: Responsive design with media queries

2. **Backend API Response Format** (Very Low probability)
   - Risk: Backend might not return `is_loop_enabled` field
   - Mitigation: Backend Issue #469 already complete and tested

3. **Browser Compatibility** (Very Low probability)
   - Risk: Bootstrap Vue switch might not work in old browsers
   - Mitigation: Bootstrap Vue supports modern browsers (project requirement)

---

## Technical Notes

### Optimistic Update Pattern
```javascript
async handleToggleLoopEnabled(item) {
    const previousState = item.is_loop_enabled;
    
    try {
        this.overlay.show = true;
        const response = await updateMovieLoopEnabled(url, data);
        
        if (response.code === 200) {
            MakeToast({ variant: 'success', ... });
        }
    } catch (error) {
        item.is_loop_enabled = previousState;
        MakeToast({ variant: 'danger', ... });
        await this.handleGetListVideo();
    }
    
    this.overlay.show = false;
}
```

### Style Guidelines
- ON state: `#28a745` (success green)
- OFF state: `#6c757d` (secondary gray)
- Font size: 12px (desktop), 11px (mobile)
- Layout: Flexbox with center alignment

### Accessibility
- Switch component is keyboard accessible
- Clear visual feedback (color + position)
- Toast notifications for screen readers

---

## Issue Creation Commands (NOT EXECUTED - DOCUMENTATION ONLY)

**Note:** Per user request, these commands are **NOT executed**. This is documentation only.

### Frontend Issue (Hypothetical)

```bash
gh issue create \
  --repo TeckVeho/Izumi_Issue-Requests-Repo \
  --title "[FE] ムービー自動ループ配信除外オプション: トグルボタンUI実装 / Loại trừ video khỏi phát sóng tự động: Triển khai UI nút chuyển đổi" \
  --body "$(cat issue_body.md)" \
  --label "frontend" \
  --label "enhancement"
```

### SP Registration Command (Hypothetical)

```bash
bash docs/AI_driven_dedelopment/cursor/script/setsp.ps <issue_url> 2
```

**SP Value:** 2 SP (not a range, single concrete number for GitHub Projects)

---

## Verification Checklist

Before marking this breakdown as complete:

- [x] Plan.md analyzed and all tasks identified
- [x] Tasks grouped by layer (Frontend only in this case)
- [x] Story Points calculated (2 SP)
- [x] Dependencies identified (Backend Issue #469)
- [x] Risk analysis completed
- [x] Implementation order defined
- [x] Files to modify listed
- [x] Bilingual content prepared (Japanese/Vietnamese)
- [x] Acceptance criteria defined
- [x] Technical details documented

---

## Summary

**Issue #470 Breakdown Result:**

| Metric | Value |
|--------|-------|
| Total Issues | 1 (Frontend only) |
| Total Story Points | 2 SP (~2 hours) |
| Files to Modify | 2 files |
| Lines of Code | ~80 lines |
| Complexity | Simple |
| Risk Level | Low |
| Dependencies | 1 (Backend #469 - completed) |

**Recommended Strategy:** Single Frontend Issue  
**Ready for Development:** ✅ Yes  
**Backend Dependency:** ✅ Completed (Issue #469)

---

**Breakdown Created:** 2025-12-03  
**Parent Issue:** #470  
**Status:** Documentation Complete (Issues NOT created per user request)  
**Next Step:** `/dev 470` when ready to implement

