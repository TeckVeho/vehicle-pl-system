## 日本語 / Japanese

### 親Issue
Parent: #956

### 説明
経理連携を **どちらか一方** 先に実装: `POST /api/import`（multipart）**または** `POST /api/income-statement/records/bulk`（JSON）。IC 側データソース（ファイル vs DB）と **EDIT_PL** ユーザーを確定。最小 E2E（local）と `dev.md` 更新、known limitations 記載。

### 要件
- plan Phase 4–5
- ユニットテスト＋手動 E2E 観点

### 技術詳細
- **izumi-cloud**
- VPL 側 API は既存仕様に準拠

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
VPL 基盤。master 同期後のデータがあると検証しやすい

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #956

### Mô tả
Triển khai **một** trong hai: `import` (multipart) **hoặc** `income-statement/records/bulk`. Chốt nguồn IC và user **EDIT_PL**. E2E tối thiểu, cập nhật `dev.md`, ghi hạn chế.

### Yêu cầu
- plan Phase 4–5
- Unit test + kịch bản E2E thủ công

### Chi tiết kỹ thuật
- **izumi-cloud**

### Tiêu chí chấp nhận
- [ ] Hoàn thành
- [ ] Unit test
- [ ] Quy ước
- [ ] Không breaking

### Phụ thuộc
Nền tảng; có master sync thì test dễ hơn
