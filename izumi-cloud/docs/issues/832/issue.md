# Issue #832: Disaster confirmation noti function feefback

## Thông tin Issue

- **Issue Number:** 832
- **Title:** Disaster confirmation noti function feefback
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/832
- **Related:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/328
- **Status:** OPEN
- **Created At:** 2026-03-06T02:47:38Z
- **Updated At:** 2026-03-06T02:47:38Z
- **Labels:** (không có)
- **Assignees:** Hà Thái Việt (hathaiviet411)

## Mô tả (Body)

Related to https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/328

**・アプリ通知 (App notification)**  
① アプリを開いた時に通知が来るので、アプリを開いていなくても、システムから送信したタイミングで通知するように変更  
② 通知タイトルが「絵文字絵文字+災害通知+絵文字絵文字」となっているが、テキスト部分を　安否確認　へ変更  

**・システム新規作成 (System new creation)**  
① メッセージ内容にデフォルトでテキストを入れておくことが可能か？（変更したい場合は変更でき、素早く送信したい場合はそのまま送信できるように）  
「皆様の安全を確認するため、安否報告をお願いします。」  
② 新規内容送信後、「一覧に戻る」を選択し一覧に戻ると、今送った内容が反映されておらず、更新すると表示されるので、一覧戻った時に最新情報を読み込んで表示して欲しい  

**・Phía IA (App side)**  
① Hiện tại thông báo được gửi khi đã mở app, họ muốn chưa mở app thì noti cũng được gửi khi hệ thống gửi thông báo.  
② Hiện tại title đang là 「Emoji+災害通知+Emoji」, họ muốn thay đổi phần text thành "安否確認"  

**・Phía WA (Web Admin)**  
① Set nội dung text mặc định khi tạo thông báo để gửi. Nếu muốn thay đổi nội dung thì vẫn thay đổi được, còn trong trường hợp gấp muốn gửi được nhanh thất thì có thể cứ thế gửi đi luôn ko cần mất thời gian nhập nội dung.  
Nội dung mặc định: 「皆様の安全を確認するため、安否報告をお願いします。」  
② Sau khi gửi thông báo, bấm 「一覧に戻る」 để quay lại màn hình list, thông báo vừa gửi đi đang không được hiển thị ở list mà phải reload thì mới hiển thị, họ muốn khi quay lại màn list sau khi tạo xong thì nó phải được hiển thị luôn.  

---

## Context / Codebase Paths

- **frontend_path:** ./resources/js (Laravel + Vue trong repo izumi-cloud)
- **backend_path:** ./app (Laravel)
- **migrations_path:** ./database/migrations
- **workspace_root:** .

**Lưu ý phạm vi:** Chức năng thông báo an toàn (安否確認 / disaster confirmation) và màn tạo/xem thông báo có thể nằm chủ yếu ở **izumi-web-app** (API `api/notices`). Repo **izumi-cloud** hiện chỉ gọi API ngoài từ DriverRecorder (Create/Edit) tới izumi-web-app.

---

## Implementation Checklist

- [ ] [App] Gửi push notification ngay khi hệ thống gửi (không chờ mở app)
- [ ] [App] Đổi title thông báo từ 「絵文字+災害通知+絵文字」 → 「安否確認」
- [ ] [WA] Set nội dung mặc định khi tạo thông báo: 「皆様の安全を確認するため、安否報告をお願いします。」
- [ ] [WA] Khi bấm 「一覧に戻る」 sau khi gửi, load lại list để hiển thị thông báo vừa gửi

---

## Implementation Tasks (injected from /breakdown)

- [ ] [BE #846](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/846) (SP: 4)
- [ ] [FE #847](https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/847) (SP: 2)

---

## Notes / Review

- Cần xác nhận repo chứa màn tạo thông báo an toàn và logic push: izumi-web-app vs izumi-cloud.
- Issue liên quan: #328.
