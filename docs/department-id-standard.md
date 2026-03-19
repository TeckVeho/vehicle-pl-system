# 部門 ID の共通化ルール

イズミクラウドなど他システムとの連携に合わせ、部門識別子の運用ルールを定義します。本仕様書では「部門（Department）」として統一し、識別子を **department id** とします。

---

## 1. 標準識別子: department id

**部門の共通識別子として department id を使用します。** 本システム内部では `Location.code` にマッピングされます。

| 項目 | 内容 |
|------|------|
| 識別子 | department id（イズミクラウドの部門IDと同一） |
| 形式 | 英数字。シードデータでは `LOC` + 3桁番号（例: LOC001, LOC002） |
| ユニーク | 本システム内で一意 |
| 変更 | 運用開始後は変更しない（他システムとの整合性のため） |

---

## 2. Location.id と department id の使い分け

| 用途 | 使用する識別子 | 備考 |
|------|----------------|------|
| **イズミクラウド等との連携** | department id | イズミクラウドと同一の department id を共有 |
| **本システム内の API** | Location.id または department id | 一覧・参照はどちらでも可。sync API は `departmentId` または `locationCode` で受け付け |
| **外部連携 API のパラメータ** | departmentId | Vehicle/Course/Driver の sync で `departmentId` を指定（実装では locationCode にマッピング） |

---

## 3. イズミクラウドとの整合手順

1. **初回構築時**: 本システムのシードで投入する department id（Location.code）の一覧をイズミクラウドと共有
2. **イズミクラウド側**: 同一の department id を部門識別子として採用
3. **運用中**: department id の追加・変更は両システムで同期して実施

---

## 4. シードデータの部門ID一覧

現在のシードで投入される department id（参考）:

- LOC001（本社）, LOC002（横浜第1）, LOC003（平塚）, LOC004（横浜第2）, LOC005（静岡）, LOC006（千葉）, LOC007（東京）, LOC008（八千代）, LOC009（古河）, LOC011（武蔵野）, LOC013（所沢）, LOC014（新潟）, LOC015（名古屋）, LOC016（安城）, LOC017（浜松）, LOC018（富山）, LOC019（大阪）, LOC020（神戸）, LOC022（横浜第3）, LOC023（不動産管理）, LOC024（米沢）, LOC025（管理本部）

---

## 5. 外部連携 API での使用例

### Vehicle sync（イズミクラウドから）

```json
{
  "vehicles": [
    {
      "departmentId": "LOC001",
      "vehicleNo": "001-001",
      "externalId": "ext-vehicle-123",
      "serviceType": "ＣＶＳ"
    }
  ]
}
```

※ API 実装では `departmentId` は `locationCode` パラメータとして受け付け

### Course sync（イズミクラウドから）

```json
{
  "courses": [
    {
      "departmentId": "LOC001",
      "code": "001-001",
      "name": "山崎製パンＡ便",
      "externalId": "ext-course-456"
    }
  ]
}
```
