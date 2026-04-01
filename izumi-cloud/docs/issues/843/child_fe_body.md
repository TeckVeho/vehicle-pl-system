## 日本語 / Japanese

### 親Issue
Parent: #843

### 説明
コースマスタに「コース先住所」（course_address）を入力・表示・編集するフロントエンド実装です。create.vue / edit.vue / detail.vue にフィールドを追加し、i18n（ja.js / en.js）にラベルを追加します。一覧でテーブル表示がある場合は列追加も行います。実装リポジトリは izumi-cloud、フロントは resources/js を編集します。

### 要件
- create.vue: isForm に course_address を追加し、入力欄と登録ペイロードに course_address を含める。
- edit.vue: isForm に course_address を追加し、API 取得データのマッピング・入力欄・更新ペイロードに含める。
- detail.vue: isForm に course_address を追加し、API データのマッピングと表示（disabled）を追加する。
- ja.js / en.js: COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS を追加（日本語「コース先住所」、英語「Course address」等）。
- index.vue: 一覧がテーブルで course を表示している場合のみ、course_address 列を追加する。
- 実装後、ユニットテストを作成・実行し、合格させる。

### 技術詳細
- **パス:** フロントは `resources/js`（Laravel プロジェクト内）。issue.md の frontend_path は別参照用。
- **create.vue:** data().isForm に course_address: ''。テンプレートに b-form-input 等で v-model="isForm.course_address"。postCourse ペイロードに course_address: this.isForm.course_address || null。
- **edit.vue:** isForm に course_address。DATA マッピングで this.isForm.course_address = DATA.course_address ?? ''。テンプレートに入力欄。更新ペイロードに course_address。
- **detail.vue:** isForm に course_address。DATA マッピングで this.isForm.course_address = DATA.course_address ?? ''。テンプレートにラベルと表示（disabled）。
- **i18n:** resources/js/lang/subs/ja.js と en.js に COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS を追加。
- API は親 issue の BE 実装（#843 の BE 子 issue）が完了していることを前提とする。

### 受け入れ基準
- [ ] create / edit / detail にコース先住所の入力・表示・編集が実装されていること
- [ ] i18n ラベルが ja / en に追加されていること
- [ ] 一覧でテーブルがある場合、course_address 列が追加されていること
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
統合テスト時は Backend 子 issue（コース先住所の API 実装）が完了している必要があります。並行開発は可能ですが、結合は BE 完了後。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #843

### Mô tả
Triển khai frontend cho trường「コース先住所」(course_address): thêm nhập, hiển thị, chỉnh sửa trên create.vue / edit.vue / detail.vue, thêm label i18n (ja.js / en.js). Nếu màn hình danh sách có bảng thì thêm cột. Repo triển khai: izumi-cloud, chỉnh sửa trong resources/js.

### Yêu cầu
- create.vue: Thêm course_address vào isForm, thêm ô nhập và đưa course_address vào payload khi đăng ký.
- edit.vue: Thêm course_address vào isForm, mapping dữ liệu từ API, ô nhập và payload cập nhật.
- detail.vue: Thêm course_address vào isForm, mapping từ API và hiển thị (disabled).
- ja.js / en.js: Thêm COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS (ja: 「コース先住所」, en: "Course address").
- index.vue: Chỉ khi danh sách hiển thị course dạng bảng thì thêm cột course_address.
- Sau khi triển khai, tạo và chạy unit test, đảm bảo pass.

### Chi tiết kỹ thuật
- **Đường dẫn:** Frontend nằm trong `resources/js` (Laravel). frontend_path trong issue.md dùng cho tham chiếu khác.
- **create.vue:** data().isForm thêm course_address: ''. Template thêm b-form-input với v-model="isForm.course_address". Payload postCourse thêm course_address: this.isForm.course_address || null.
- **edit.vue:** isForm có course_address. Mapping DATA: this.isForm.course_address = DATA.course_address ?? ''. Template thêm ô nhập. Payload cập nhật có course_address.
- **detail.vue:** isForm có course_address. Mapping: this.isForm.course_address = DATA.course_address ?? ''. Template thêm label và hiển thị (disabled).
- **i18n:** Thêm COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS vào resources/js/lang/subs/ja.js và en.js.
- API giả định Backend (issue con BE của #843) đã hoàn thành.

### Tiêu chí chấp nhận
- [ ] create / edit / detail đã có nhập, hiển thị, chỉnh sửaコース先住所
- [ ] Đã thêm label i18n cho ja / en
- [ ] Nếu có bảng danh sách thì đã thêm cột course_address
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Khi test tích hợp cần issue con Backend (API コース先住所) đã hoàn thành. Có thể phát triển song song nhưng tích hợp sau khi BE xong.
