## 日本語 / Japanese

### 親Issue
Parent: #956

### 説明
`POST /api/vehicle-monthly-costs/sync` を実装する。IC 側 DB（MaintenanceLease、InsuranceRate 等）からの集計と、**ITP API** からの `fuelEfficiency` / `roadUsageFee` をマージ。`yearMonth` パラメータ対応。

### 要件
- plan Phase 3、`ic-sync-field-mapping.md` §5
- PHPUnit（モック ITP を含む）

### 技術詳細
- **izumi-cloud**（ITP プロキシ／クライアント）
- 外部依存が大きいため不確実性にバッファ済み（SP: 13）

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
VPL 基盤、vehicles 同期（`vehicleExternalId` 整合）

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #956

### Mô tả
`vehicle-monthly-costs/sync`: tổng hợp từ bảng IC + gọi **ITP** cho `fuelEfficiency`, `roadUsageFee`; tham số `yearMonth`.

### Yêu cầu
- plan Phase 3, `ic-sync-field-mapping.md` §5
- PHPUnit (mock ITP)

### Chi tiết kỹ thuật
- **izumi-cloud**
- SP 13 do phụ thuộc ITP

### Tiêu chí chấp nhận
- [ ] Hoàn thành
- [ ] Unit test
- [ ] Quy ước
- [ ] Không breaking

### Phụ thuộc
Nền tảng + vehicles sync (khớp externalId)
