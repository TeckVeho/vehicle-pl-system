## 日本語 / Japanese

### 親Issue
Parent: #832

### 説明
安否確認（災害確認）通知機能の改善。システムが通知を送信したタイミングでプッシュ通知を即時送信し、通知タイトルを「安否確認」に統一する。実装は izumi-web-app リポジトリで行う想定。

### 要件
1. **プッシュの即時送信**: ユーザーがアプリを開いていなくても、バックエンドが安否確認通知を送信した時点で FCM/APNs によりプッシュを送信する。
2. **タイトル変更**: プッシュのタイトルを「絵文字+災害通知+絵文字」から「安否確認」に変更する（安否確認タイプの通知のみ対象）。
3. **（任意）デフォルト本文**: リクエストの content が空かつ notice タイプが disaster confirmation の場合、本文に「皆様の安全を確認するため、安否報告をお願いします。」をデフォルト設定する。
4. GET list notices が作成直後に新規レコードを返すことを保証し、FE の refetch が正しく動作するようにする。

### 技術詳細
- 通知送信フロー（POST 作成または「送信」アクション）の箇所で FCM/APNs を呼び出す。
- プッシュ payload の title（例: notification.title）を「安否確認」に設定。cron/queue/API の全ての送信経路で統一。
- 実装・単体テストは izumi-web-app 内で実施。Scope: Implementation and unit tests only.

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
なし（単体で開発可能）。FE は統合テスト時に本 issue に依存。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #832

### Mô tả
Cải thiện chức năng thông báo an toàn (安否確認). Gửi push notification ngay tại thời điểm hệ thống gửi thông báo, thống nhất title push là 「安否確認」. Triển khai dự kiến tại repo izumi-web-app.

### Yêu cầu
1. **Gửi push ngay**: Khi backend gửi thông báo an toàn, gửi push qua FCM/APNs ngay lập tức, kể cả khi user chưa mở app.
2. **Đổi title**: Đổi title push từ 「絵文字+災害通知+絵文字」 sang 「安否確認」（chỉ áp dụng cho loại thông báo an toàn）。
3. **（Tùy chọn）Nội dung mặc định**: Nếu request content rỗng và loại notice là disaster confirmation, set nội dung mặc định 「皆様の安全を確認するため、安否報告をお願いします。」.
4. Đảm bảo GET list notices trả về bản ghi mới ngay sau khi tạo để FE refetch hoạt động đúng.

### Chi tiết kỹ thuật
- Tại luồng gửi thông báo (POST tạo hoặc action "gửi"), gọi FCM/APNs.
- Set trường title trong payload push (ví dụ notification.title) thành 「安否確認」; kiểm tra mọi đường gửi (cron, queue, API) để thống nhất.
- Triển khai và unit test trong izumi-web-app. Phạm vi: Implementation and unit tests only.

### Tiêu chí chấp nhận
- [ ] Hoàn thành việc triển khai
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Không (có thể phát triển độc lập). FE phụ thuộc issue này khi kiểm thử tích hợp.
