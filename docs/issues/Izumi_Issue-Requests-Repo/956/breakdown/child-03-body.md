## 日本語 / Japanese

### 親Issue
Parent: #956

### 説明
`POST /api/vehicles/sync` と `POST /api/drivers/sync` を実装する。車両: `latestNumberPlateHistory` から `vehicleNo`、`departmentId` は LOC 規則、`courseExternalId` は未取得時 null 可。ドライバー: `employees`、多対多 department から主所属を決めて LOC へ。N+1 回避（500+ 台想定）。

### 要件
- `ic-sync-field-mapping.md` §3–4
- PHPUnit

### 技術詳細
- **izumi-cloud**
- 推奨順: courses 同期後に vehicles

### 受け入れ基準
- [ ] 実装完了
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
VPL 基盤。vehicles は courses 同期とデータ整合が望ましい

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #956

### Mô tả
`vehicles/sync` và `drivers/sync`. Xe: `vehicleNo` từ `latestNumberPlateHistory`, `departmentId` theo LOC, `courseExternalId` nullable. Driver: `employees`, chọn department chính từ quan hệ N-N → LOC. Tránh N+1.

### Yêu cầu
- `ic-sync-field-mapping.md` §3–4
- PHPUnit

### Chi tiết kỹ thuật
- **izumi-cloud**
- Nên sync courses trước vehicles

### Tiêu chí chấp nhận
- [ ] Hoàn thành
- [ ] Unit test
- [ ] Quy ước dự án
- [ ] Không breaking change

### Phụ thuộc
Nền tảng VPL; vehicles sau courses (khuyến nghị)
