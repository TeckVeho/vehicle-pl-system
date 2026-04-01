Related to https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/515

> 運転者台帳（PDF保管データの追加）（検討レベル）
> 今後、様々な従業員台帳関連の保管必要資料が増えていくことが予想され、その他項目のようなもので一時的にアップロード→項目追加振り分けなどができないか

- Tại page employee master detail, thêm section cho phép upload file PDF
- Mục đích: KH có nhiều file PDF muốn lưu trữ, trong đó có một số file sau này sẽ phân loại vào các tab 運転免許証, 運転記録証明書, 適性診断票, 健康診断結果通知書 sau.
- Link UI: https://v0.app/chat/employee-master-ui-u6adbHPRgFA
- Chức năng: hiển thị list file upload, thời gian upload, view file, phân loại file, xóa file
- Flow:
1. User bấm button upload để upload file PDF => PDF hiển thị tại section upload PDF tại employee detail page
2. Bấm button 振り分け để phân loại file.
2.1. Hiển thị modal để chọn phân loại
2.1.1. Nếu chọn phân loại 適性診断票 thì sẽ phải chọn tiếp 種別 và 受診日
2.1.2. Nếu chọn phân loại 健康診断結果通知書 thì sẽ phải chọn tiếp 種別 và 受診日
2.2. Sau khi phân loại, các file PDF sẽ: 
2.2.1. Không còn được hiển thị ở section upload PDF ở bên employee detail nữa
2.2.2. Hiển thị ở tab đã chọn ở bước phân loại.
2.2.3. Lịch sử PDF đó cũng sẽ được lưu tại tab tương ứng.
3. User có thể bấm view để xem file (tại section mới thêm)
4. User có thể bấm xóa file, khi bấm xóa file sẽ hiển thị modal xác nhận xóa (tại section mới thêm)
5. List hiển thị theo thứ tự mặc định thời gian upload mới nhất => cũ nhất từ trên xuống.

## Implementation Tasks
- [ ] https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/806 (SP: 5)
- [ ] https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/807 (SP: 5)
