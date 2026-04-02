## 日本語 / Japanese

### 親Issue
Parent: #956

### 説明
`POST /api/users/sync` と `POST /api/courses/sync` の ETL・呼び出しを実装する。`departmentId` は `LOC` + `departments.id` を 3 桁ゼロ埋め。コース `name` は IC に列がないため plan / `ic-sync-field-mapping.md` に従い生成。`externalId` は IC の `id`。Spatie role → VPL `VALID_ROLES` のマッピング。

### 要件
- `ic-sync-field-mapping.md` §1–2
- PHPUnit（サービス／トランスフォーマ）

### 技術詳細
- リポジトリ: **izumi-cloud**
- 依存: 子 Issue「VPL 基盤」（JWT・クライアント）

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
子 Issue #（VPL 基盤）マージ後に結合テスト可能

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #956

### Mô tả
ETL và gọi `POST /api/users/sync`, `POST /api/courses/sync`. `departmentId` = `LOC` + pad 3 `departments.id`. Tạo `name` cho course theo mapping doc. `externalId` = id IC. Map role Spatie → `VALID_ROLES` VPL.

### Yêu cầu
- `ic-sync-field-mapping.md` §1–2
- PHPUnit

### Chi tiết kỹ thuật
- **izumi-cloud**
- Phụ thuộc: issue con nền tảng (client + JWT)

### Tiêu chí chấp nhận
- [ ] Hoàn thành triển khai
- [ ] Unit test đạt
- [ ] Tuân thủ quy ước
- [ ] Không breaking change

### Phụ thuộc
Sau khi có client/JWT từ issue nền tảng
