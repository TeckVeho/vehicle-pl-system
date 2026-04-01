# Issue #843 — Add course address to Course master

## Context / Codebase Paths (from pre-questions)

```yaml
frontend_path: ./frontend
backend_path: .
migrations_path: ./database/migrations
api_docs_path:   # optional
tests_path:      # optional
workspace_root: .
```

---

## Metadata

| Field | Value |
|-------|--------|
| **URL** | https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/843 |
| **Title** | Add course address to Course master |
| **State** | OPEN |
| **Assignees** | maitue |
| **Labels** | — |
| **Created** | 2026-03-09T08:45:44Z |
| **Updated** | 2026-03-09T08:50:42Z |

---

## Body (Overview)

### 概要 (JP) / Tổng quan (VN)

- **背景:** コースマスタにコース先住所のデータがない → Thêm địa chỉ đến của khóa học vào Course master.
- **達成目標:** コースマスタに「コース先住所」を追加し、保存・表示・更新できるようにする。

### Definition of Done

- [ ] コースマスタにコース先住所フィールドが追加されていること
- [ ] コース先住所が正しく保存され、表示されること
- [ ] 既存のデータに対してもコース先住所が適用されること
- [ ] ユーザーがコース先住所を更新できること
- [ ] テストが完了し、すべての要件が満たされていること

### 機能要件 (Functional Requirements)

- コースマスタに新フィールド「コース先住所」を追加する。
- 入力したコース先住所を正しく保存する。
- 表示時は正しいフォーマットで表示する。
- 更新時は変更を即座に反映する。
- 既存コースデータにもコース先住所を適用できるようにする。

### タスクタイプ / Loại tác vụ

要件 (Yêu cầu)

### 起票者 / Người khởi tạo

Đào Thị Thư

---

## Implementation checklist

- [ ] Backend: migration / model にコース先住所フィールド追加
- [ ] Backend: 保存・更新・取得でコース先住所を扱う
- [ ] Frontend: コースマスタ画面にコース先住所の入力・表示・編集
- [ ] 既存データへの対応（nullable またはデフォルト値）
- [ ] テスト追加・実施

## Implementation Tasks (Child issues)

- [ ] https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/848 (SP: 2)
- [ ] https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/849 (SP: 3)

---

## Notes / Review

- Các lệnh `/plan`, `/breakdown`, `/dev` đọc **Context / Codebase Paths** ở đầu file để biết đường dẫn frontend, backend, migrations.
