## 日本語 / Japanese

### 親Issue
Parent: #956

### 説明
Izumi Cloud から VPL（vehicle-pl-system）へ同期するための**基盤**を実装する。HTTP クライアント、`POST /api/auth/login` による JWT 取得・キャッシュ・401 時の再ログイン、Artisan コマンド（例: `vpl:sync`）のエントリ、`--entity` / `--year-month` / `--dry-run` の骨格、日次ログ（`storage`、専用 channel 推奨）、HTTP エラー（400/401/403/500）と最小限のリトライ方針。

### 要件
- `docs/issues/Izumi_Issue-Requests-Repo/956/plan.md` Phase 0–1 に準拠
- Base URL は環境別（VPL は legacy `BASE_URL_PL` と混同しない）
- 秘密情報は `.env` のみ

### 技術詳細
- 実装リポジトリ: **izumi-cloud**（Laravel）
- 参照: `vehicle-pl-system/docs/external-integration-spec.md`（認証）

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格（クライアント・トークン周り）
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
なし（他子 Issue の前提）

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #956

### Mô tả
Triển khai **nền tảng** đồng bộ từ Izumi Cloud sang VPL: HTTP client, JWT qua `POST /api/auth/login`, cache token và login lại khi 401, Artisan command (`vpl:sync` hoặc tương đương), option `--entity` / `--year-month` / `--dry-run`, log theo ngày vào `storage` (nên dùng channel riêng), xử lý lỗi HTTP và retry tối thiểu.

### Yêu cầu
- Theo `plan.md` Phase 0–1
- Base URL theo môi trường; không nhầm VPL với `BASE_URL_PL`
- Secret chỉ trong `.env`

### Chi tiết kỹ thuật
- Code chính: **izumi-cloud** (Laravel)
- Tham chiếu: `external-integration-spec.md` (auth)

### Tiêu chí chấp nhận
- [ ] Hoàn thành triển khai
- [ ] Unit test (client, token)
- [ ] Tuân thủ quy ước dự án
- [ ] Không phá vỡ chức năng hiện có

### Phụ thuộc
Không
