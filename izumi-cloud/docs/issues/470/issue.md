# Issue #470: [FE] ムービー自動ループ配信除外オプション: トグルボタンUI実装

## Metadata

- **Issue Number:** 470
- **Title:** [FE] ムービー自動ループ配信除外オプション: トグルボタンUI実装 / Loại trừ video khỏi phát sóng tự động: Triển khai UI nút chuyển đổi
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/470
- **State:** OPEN
- **Created:** 2025-11-28T10:43:43Z
- **Updated:** 2025-12-03T07:45:35Z
- **Labels:** enhancement, frontend
- **Assignee:** hathaiviet411 (Hà Thái Việt)
- **Parent Issue:** #468
- **Dependencies:** Backend Issue #469 (phải hoàn thành trước)

---

## 日本語 / Japanese

### 親Issue

#468 に関連

### 説明

ムービー一覧画面に各ムービーのサムネールの下にトグルボタンを追加し、ユーザーがムービーを自動ループ配信から除外できるようにします。トグルボタンのON/OFF状態をAPIで保存し、UIに反映させます。

### 要件

1. **トグルボタンUI追加**
   - ムービー一覧画面（`VideoPlayer/index.vue`）の各ムービーサムネールの下にトグルボタンを追加
   - Bootstrap Vue の `b-form-checkbox` コンポーネント（switch スタイル）を使用
   - ラベル「ループ配信」を表示

2. **状態管理**
   - トグルボタンの状態を `item.is_loop_enabled` にバインド
   - デフォルト値は ON（true）

3. **API統合**
   - トグルボタンのクリックイベントハンドラー `handleToggleLoopEnabled` を実装
   - `PUT /api/movies/{id}/loop-enabled` エンドポイントを呼び出し
   - 成功時にトーストメッセージを表示
   - エラー時にトーストメッセージを表示し、データを再取得

4. **スタイリング**
   - トグルボタンコンテナのスタイルを追加
   - ON状態は緑色、OFF状態はグレーで表示
   - レスポンシブデザインに対応

5. **ユーザー体験**
   - API呼び出し中はオーバーレイを表示
   - 即座にUIを更新（楽観的更新）
   - エラー時は元の状態に戻す

### 技術詳細

**変更するファイル:**
- `resources/js/pages/VideoPlayer/index.vue`
- `resources/js/api/url_api_list.js` (確認のみ)

**API Endpoint:**
```
PUT /api/movies/{id}/loop-enabled
Body: { "is_loop_enabled": true/false }
Response: { "code": 200, "data": {...} }
```

**実装箇所:**
- Template: line 136-173 付近（ムービーアイテムのレンダリング部分）
- Script: methods セクションに `handleToggleLoopEnabled` メソッドを追加
- Style: トグルボタン用のSCSSスタイルを追加

**使用コンポーネント:**
- `b-form-checkbox` with `switch` prop
- `b-toast` for notifications
- `b-overlay` for loading state

### 受け入れ基準

- [ ] ムービー一覧画面の各ムービーサムネールの下にトグルボタンが表示されること
- [ ] トグルボタンのデフォルト状態がON（緑色）であること
- [ ] ユーザーがトグルボタンをクリックしてON/OFFを切り替えられること
- [ ] トグルボタンの状態変更がAPIを通じてバックエンドに保存されること
- [ ] 成功時に「ループ配信設定を更新しました」というトーストメッセージが表示されること
- [ ] エラー時に「ループ配信設定の更新に失敗しました」というトーストメッセージが表示されること
- [ ] API呼び出し中はオーバーレイが表示されること
- [ ] トグルボタンのスタイルが適切に適用されること（ON=緑、OFF=グレー）
- [ ] フロントエンドユニットテストを作成し、すべて合格すること
- [ ] プロジェクト規約に準拠すること
- [ ] 既存機能への破壊的変更がないこと

### 依存関係

- Backend Issue #469 が完了していること（統合テストのため）

---

## Tiếng Việt / Vietnamese

### Issue cha

Liên quan đến #468

### Mô tả

Thêm nút chuyển đổi (toggle button) dưới thumbnail của mỗi video trong màn hình danh sách video, cho phép người dùng loại trừ video khỏi phát sóng tự động lặp. Lưu trạng thái ON/OFF của nút chuyển đổi qua API và phản ánh trên UI.

### Yêu cầu

1. **Thêm UI nút chuyển đổi**
   - Thêm nút chuyển đổi dưới thumbnail của mỗi video trong màn hình danh sách (`VideoPlayer/index.vue`)
   - Sử dụng component `b-form-checkbox` của Bootstrap Vue (kiểu switch)
   - Hiển thị label "ループ配信" (Phát sóng lặp)

2. **Quản lý trạng thái**
   - Bind trạng thái nút chuyển đổi với `item.is_loop_enabled`
   - Giá trị mặc định là ON (true)

3. **Tích hợp API**
   - Triển khai event handler `handleToggleLoopEnabled` cho sự kiện click
   - Gọi endpoint `PUT /api/movies/{id}/loop-enabled`
   - Hiển thị toast message khi thành công
   - Hiển thị toast message khi lỗi và tải lại dữ liệu

4. **Styling**
   - Thêm style cho container nút chuyển đổi
   - Trạng thái ON màu xanh lá, trạng thái OFF màu xám
   - Hỗ trợ responsive design

5. **Trải nghiệm người dùng**
   - Hiển thị overlay trong khi gọi API
   - Cập nhật UI ngay lập tức (optimistic update)
   - Khôi phục trạng thái cũ khi có lỗi

### Chi tiết kỹ thuật

**File cần thay đổi:**
- `resources/js/pages/VideoPlayer/index.vue`
- `resources/js/api/url_api_list.js` (chỉ kiểm tra)

**API Endpoint:**
```
PUT /api/movies/{id}/loop-enabled
Body: { "is_loop_enabled": true/false }
Response: { "code": 200, "data": {...} }
```

**Vị trí triển khai:**
- Template: khoảng line 136-173 (phần render movie item)
- Script: thêm method `handleToggleLoopEnabled` vào section methods
- Style: thêm SCSS style cho nút chuyển đổi

**Component sử dụng:**
- `b-form-checkbox` với prop `switch`
- `b-toast` cho thông báo
- `b-overlay` cho trạng thái loading

### Tiêu chí chấp nhận

- [ ] Nút chuyển đổi hiển thị dưới thumbnail của mỗi video trong danh sách
- [ ] Trạng thái mặc định của nút chuyển đổi là ON (màu xanh lá)
- [ ] Người dùng có thể click nút chuyển đổi để bật/tắt
- [ ] Thay đổi trạng thái nút chuyển đổi được lưu vào backend qua API
- [ ] Hiển thị toast message "ループ配信設定を更新しました" khi thành công
- [ ] Hiển thị toast message "ループ配信設定の更新に失敗しました" khi lỗi
- [ ] Hiển thị overlay trong khi gọi API
- [ ] Style của nút chuyển đổi được áp dụng đúng (ON=xanh, OFF=xám)
- [ ] Tạo và vượt qua frontend unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc

- Backend Issue #469 phải hoàn thành (để integration testing)

---

## Implementation Checklist

### Phase 1: Analysis & Planning
- [ ] Đọc và hiểu yêu cầu issue
- [ ] Xác nhận Backend Issue #469 đã hoàn thành
- [ ] Review file `VideoPlayer/index.vue` hiện tại
- [ ] Xác định vị trí thêm toggle button trong template
- [ ] Kiểm tra API endpoint có sẵn chưa

### Phase 2: Implementation
- [ ] Thêm `b-form-checkbox` switch component vào template (line 136-173)
- [ ] Bind `v-model` với `item.is_loop_enabled`
- [ ] Implement method `handleToggleLoopEnabled` trong methods section
- [ ] Tích hợp API call `PUT /api/movies/{id}/loop-enabled`
- [ ] Thêm toast notifications (success/error)
- [ ] Thêm overlay loading state
- [ ] Implement optimistic update và error rollback
- [ ] Thêm SCSS styles cho toggle button

### Phase 3: Testing
- [ ] Test toggle button hiển thị đúng vị trí
- [ ] Test default state là ON (true)
- [ ] Test switch between ON/OFF
- [ ] Test API integration
- [ ] Test toast messages
- [ ] Test loading overlay
- [ ] Test error handling
- [ ] Test responsive design
- [ ] Viết frontend unit tests
- [ ] Test không có breaking changes

### Phase 4: Documentation
- [ ] Document code changes
- [ ] Update dev.md với implementation details
- [ ] Prepare for review

---

## Notes

- **Branch hiện tại:** `issuie-469-be` (KHÔNG checkout sang branch mới theo yêu cầu)
- **File chính cần sửa:** `resources/js/pages/VideoPlayer/index.vue` (3126 lines)
- **Bootstrap Vue components:** Sử dụng `b-form-checkbox`, `b-toast`, `b-overlay`
- **API:** Backend endpoint phải sẵn sàng từ issue #469

---

## Review Notes

_Để trống, sẽ cập nhật trong quá trình review_

