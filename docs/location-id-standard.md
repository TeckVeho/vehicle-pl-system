# 拠点 ID の共通化ルール（非推奨）

> **注意**: 本ドキュメントは後方互換のため残しています。新規は [department-id-standard.md](department-id-standard.md)（部門 ID）を参照してください。

他システム（人事、経理など）との連携計画に合わせ、拠点識別子の運用ルールを定義します。

---

## 1. 標準識別子: Location.code（→ department id）

**拠点の共通識別子として `Location.code` を使用します。** 仕様書では「部門（Department）」として **department id** に統一しています。

| 項目 | 内容 |
|------|------|
| 識別子 | `Location.code`（例: LOC001, LOC002） |
| 形式 | 英数字。シードデータでは `LOC` + 3桁番号 |
| ユニーク | 本システム内で一意 |
| 変更 | 運用開始後は変更しない（他システムとの整合性のため） |

---

## 2. Location.id と Location.code の使い分け

| 用途 | 使用する識別子 | 備考 |
|------|----------------|------|
| **他システムとの連携** | `Location.code` | 人事、経理など他システムと同一の code を共有 |
| **本システム内の API** | `Location.id` または `Location.code` | 一覧・参照はどちらでも可。sync API は `locationCode` で受け付け |
| **外部連携 API のパラメータ** | `locationCode` | Vehicle/Course の sync で `locationCode` を指定 |

---

## 3. 他システムとの整合手順

1. **初回構築時**: 本システムのシードで投入する `Location.code` の一覧を他システムと共有
2. **他システム側**: 人事・経理などで同一の code を拠点識別子として採用
3. **運用中**: code の追加・変更は両システムで同期して実施

---

## 4. シードデータの拠点コード一覧

現在のシードで投入される code（参考）:

- LOC001 ～ LOC022（横浜第1, 横浜第2, …, 米沢, 本社, 管理本部, 不動産管理 など）

---

## 5. 外部連携 API での使用例

### Vehicle sync

```json
{
  "vehicles": [
    {
      "locationCode": "LOC001",
      "vehicleNo": "001-001",
      "externalId": "ext-vehicle-123",
      "serviceType": "ＣＶＳ"
    }
  ]
}
```

### Course sync

```json
{
  "courses": [
    {
      "locationCode": "LOC001",
      "code": "001-001",
      "name": "山崎製パンＡ便",
      "externalId": "ext-course-456"
    }
  ]
}
```
