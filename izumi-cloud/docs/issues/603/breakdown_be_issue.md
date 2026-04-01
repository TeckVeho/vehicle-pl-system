## 日本語 / Japanese

### 親 Issue

#603 に関連

### 説明

ルート計算AIプロンプトを更新し、APIレスポンスに`thinking_process`（思考プロセス）情報を追加します。これにより、AIがどのようにルートを計算したかの詳細な説明を提供できます。

### 要件

1. **プロンプトファイルの更新**
   - `storage/app/prompts/route_calculation_prompt.txt`を更新
   - JSON出力形式に`thinking_process`オブジェクトを追加
   - 5つのキーを定義: `route_strategy`, `calculation_basis`, `workload_analysis`, `compliance_reasoning`, `schedule_summary`

2. **Controllerメソッドの追加・更新**
   - `getThinkingProcessFromResponse()`メソッドを追加
     - AIレスポンスファイルから`thinking_process`を読み取る
     - セキュリティ検証（path traversal攻撃対策）
     - JSON構造の検証
     - エラーハンドリングとログ記録
   - `calculate()`メソッドを更新して`thinking_process`をレスポンスに含める
   - `show()`メソッドを更新して`thinking_process`をレスポンスに含める

### 技術詳細

**変更するファイル:**
- `storage/app/prompts/route_calculation_prompt.txt`
- `app/Http/Controllers/Api/QuotationRouteController.php`

**実装のポイント:**
- `files`リレーションシップのeager loading
- ファイルパスのセキュリティ検証（`realpath()`を使用）
- `thinking_process`構造の検証（5つのキーの存在確認）
- 適切なエラーログ記録（`Log::warning`を使用）
- 後方互換性の確保（`thinking_process`が`null`の場合も対応）

**APIエンドポイント:**
- `POST /api/quotation/routes/calculate` - 計算結果に`thinking_process`を含める
- `GET /api/quotation/routes/{id}` - ルート詳細に`thinking_process`を含める

### 受け入れ基準

- [ ] プロンプトファイルに`thinking_process`オブジェクトが追加されている
- [ ] `getThinkingProcessFromResponse()`メソッドが実装されている
- [ ] セキュリティ検証（path traversal対策）が実装されている
- [ ] `calculate()`メソッドが`thinking_process`を返す
- [ ] `show()`メソッドが`thinking_process`を返す
- [ ] エラーハンドリングが適切に実装されている
- [ ] ユニットテストが作成され、合格している
- [ ] 既存機能への破壊的変更がない
- [ ] プロジェクト規約に準拠している

### 依存関係

なし（独立して開発可能）

---

## Tiếng Việt / Vietnamese

### Issue cha

Liên quan đến #603

### Mô tả

Cập nhật prompt tính toán route của AI và thêm thông tin `thinking_process` (quy trình suy nghĩ) vào API response. Điều này cho phép cung cấp giải thích chi tiết về cách AI tính toán route.

### Yêu cầu

1. **Cập nhật prompt file**
   - Cập nhật `storage/app/prompts/route_calculation_prompt.txt`
   - Thêm object `thinking_process` vào JSON output format
   - Định nghĩa 5 key: `route_strategy`, `calculation_basis`, `workload_analysis`, `compliance_reasoning`, `schedule_summary`

2. **Thêm và cập nhật methods trong Controller**
   - Thêm method `getThinkingProcessFromResponse()`
     - Đọc `thinking_process` từ AI response file
     - Validate bảo mật (chống path traversal attack)
     - Validate cấu trúc JSON
     - Xử lý lỗi và ghi log
   - Cập nhật method `calculate()` để trả về `thinking_process` trong response
   - Cập nhật method `show()` để trả về `thinking_process` trong response

### Chi tiết kỹ thuật

**Files cần thay đổi:**
- `storage/app/prompts/route_calculation_prompt.txt`
- `app/Http/Controllers/Api/QuotationRouteController.php`

**Điểm cần lưu ý khi implement:**
- Eager loading `files` relationship
- Validate bảo mật file path (sử dụng `realpath()`)
- Validate cấu trúc `thinking_process` (kiểm tra 5 key có đầy đủ không)
- Ghi log lỗi phù hợp (sử dụng `Log::warning`)
- Đảm bảo backward compatibility (xử lý trường hợp `thinking_process` là `null`)

**API endpoints:**
- `POST /api/quotation/routes/calculate` - Trả về `thinking_process` trong kết quả tính toán
- `GET /api/quotation/routes/{id}` - Trả về `thinking_process` trong chi tiết route

### Tiêu chí chấp nhận

- [ ] Prompt file đã được thêm object `thinking_process`
- [ ] Method `getThinkingProcessFromResponse()` đã được implement
- [ ] Security validation (chống path traversal) đã được implement
- [ ] Method `calculate()` trả về `thinking_process`
- [ ] Method `show()` trả về `thinking_process`
- [ ] Error handling được implement đúng cách
- [ ] Unit tests đã được tạo và pass
- [ ] Không có breaking changes với existing features
- [ ] Tuân thủ project conventions

### Phụ thuộc

Không có (có thể phát triển độc lập)
