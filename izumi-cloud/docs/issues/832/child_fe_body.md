## 日本語 / Japanese

### 親Issue
Parent: #832

### 説明
安否確認通知の Web 管理画面（WA）改善。新規作成フォームにメッセージのデフォルト文言を設定し、「一覧に戻る」でリストに戻った際に最新データを再取得して表示する。実装は izumi-web-app リポジトリで行う想定。

### 要件
1. **デフォルト本文**: 安否確認通知の作成フォームで、本文（content/message）の初期値を「皆様の安全を確認するため、安否報告をお願いします。」に設定する。ユーザーは編集可能で、そのまま送信すれば素早く送れる。
2. **一覧の再読込**: 送信成功後に「一覧に戻る」でリスト画面に戻ったとき、リストを自動で再取得（refetch）し、直前に送信した通知がすぐ表示されるようにする（F5 不要）。

### 技術詳細
- フォームコンポーネント（create notice）で content/message の data 初期値または placeholder に上記デフォルト文言を設定。
- リスト画面で、activated()（Vue 2）または onActivated（Vue 3）、または mounted() で API を呼び直し、リスト表示を更新。ルート進入時または「一覧に戻る」ナビゲーション時に refetch が実行されるようにする。
- 実装・単体テストは izumi-web-app 内で実施。Scope: Implementation and unit tests only.

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
統合テストでは BE の子 issue（プッシュ・タイトル変更）に依存。FE 単体の実装は並行可能。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #832

### Mô tả
Cải thiện màn Web Admin (WA) cho thông báo an toàn. Set nội dung mặc định cho form tạo mới và khi bấm 「一覧に戻る」 thì list tự động tải lại dữ liệu mới. Triển khai dự kiến tại repo izumi-web-app.

### Yêu cầu
1. **Nội dung mặc định**: Trong form tạo thông báo an toàn, set giá trị ban đầu cho trường nội dung (content/message) là 「皆様の安全を確認するため、安否報告をお願いします。」. User có thể sửa; nếu không sửa có thể gửi nhanh.
2. **Tải lại list**: Sau khi gửi thành công, khi bấm 「一覧に戻る」 về màn list thì tự động gọi lại API lấy list để thông báo vừa gửi hiển thị ngay (không cần F5).

### Chi tiết kỹ thuật
- Trong component form tạo notice, khởi tạo content/message với chuỗi mặc định trên (data default hoặc placeholder).
- Ở màn list: gọi API trong activated() (Vue 2) hoặc onActivated (Vue 3) hoặc mounted() để refetch; đảm bảo khi vào route hoặc navigate từ 「一覧に戻る」 thì refetch chạy.
- Triển khai và unit test trong izumi-web-app. Phạm vi: Implementation and unit tests only.

### Tiêu chí chấp nhận
- [ ] Hoàn thành việc triển khai
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Khi kiểm thử tích hợp phụ thuộc vào child issue BE (push, đổi title). Có thể triển khai FE song song với BE.
