## 日本語 / Japanese

### 親Issue
Parent: #956

### 説明
`POST /api/location-monthly-expenses/sync` を実装する。PCA データ（例: `pl_pca_data`）から `yearMonth`、`departmentId`（LOC 規則）、`accountItemCode`（PCA → VPL 20 科目 6150–6189）、`amount` をマッピング。

### 要件
- plan Phase 3、`ic-sync-field-mapping.md` §6
- PHPUnit

### 技術詳細
- **izumi-cloud**
- BA/経理による PCA コード対応表の確定が前提

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
VPL 基盤

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #956

### Mô tả
`location-monthly-expenses/sync`: PCA → payload với `yearMonth`, `departmentId` (LOC), map `accountItemCode` PCA → 20 mã VPL, `amount`.

### Yêu cầu
- plan Phase 3, `ic-sync-field-mapping.md` §6
- PHPUnit

### Chi tiết kỹ thuật
- **izumi-cloud**
- Cần bảng map PCA do BA/kế toán

### Tiêu chí chấp nhận
- [ ] Hoàn thành
- [ ] Unit test
- [ ] Quy ước
- [ ] Không breaking

### Phụ thuộc
Nền tảng VPL
