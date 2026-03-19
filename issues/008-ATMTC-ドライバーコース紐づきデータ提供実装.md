# ATMTC: ドライバー・コース紐づきデータの提供実装

**対象:** ATMTC 側エンジニア

## 1. 概要

ドライバーとコース（または車両）の紐づきをイズミクラウドが取得できる形式で提供する実装。データは ATMTC → イズミクラウド → IZUMI の流れで連携され、drivers sync や courses sync に反映される。

### 達成目標

* [ ] ドライバーとコース（または車両）の紐づきをイズミクラウドが取得できる形式で提供
* [ ] イズミクラウドのドライバー・コースの externalId と整合する識別子を使用していること

---
## 2. 実装仕様

### 紐づきデータの提供

| 項目 | 仕様 |
|------|------|
| 提供形式 | API、ファイル、DB 連携のいずれか |
| データ内容 | どのドライバーがどのコース（または車両）に紐づくか |
| 連携先 | イズミクラウドが取得し、drivers sync や courses sync に反映して IZUMI に送信 |

### 識別子の整合

| 項目 | 仕様 |
|------|------|
| ドライバー識別子 | イズミクラウドのドライバー externalId と整合 |
| コース識別子 | イズミクラウドのコース externalId と整合 |
| 一貫性 | IZUMI の drivers sync や courses sync で使用される externalId と一貫性を保つ |

---
## 3. 参考資料

- [external-system-implementation-checklist.md](../docs/external-system-implementation-checklist.md) - 5. ATMTC
- [external-integration-spec.md](../docs/external-integration-spec.md) - 5.6 ドライバー一括同期

---
## 4. Implementation Tasks

- [ ] ドライバー・コース（または車両）紐づきのエクスポート機能実装（API/ファイル/DB のいずれか）
- [ ] イズミクラウドの externalId との識別子整合
- [ ] イズミクラウドとのデータ形式・取得方式のすり合わせ
