# Issue #555: 拠点マスタの項目追加

## Issue Metadata

- **Issue Number:** 555
- **Title:** 拠点マスタの項目追加
- **URL:** https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/555
- **State:** OPEN
- **Created At:** 2025-12-25T10:24:45Z
- **Updated At:** 2025-12-29T02:38:18Z
- **Assignee:** DongVietLong (@DongVietLong)
- **Labels:** (none)
- **Task Type:** 要件 (Requirement)

---

## Issue Description

### 日本語版

#### 1. 概要 (Overview)

##### 背景 (Background)

* **現状の課題:** 拠点マスタの項目追加  
運輸支局に届けが必要な項目を管理しておきたい

* **ビジネス要求:** 添付ファイルに記載されている項目を拠点マスタ詳細画面に追加すること。

* **ユーザーストーリー:** ユーザーとして、私は運輸支局に届けが必要な項目を管理したい。

##### 達成目標 (Goal)

* **あるべき姿:** 添付ファイルに記載されている項目を、拠点マスタ詳細画面に追加してください。  
  - A 列：大分類  
  - B 列：小分類（A 列に紐づく）  
  - C 列：お客様が入力する必要のある値

* **完了条件 (Definition of Done):**
    * [ ] 拠点マスタ詳細画面に大分類の項目が追加されていること。
    * [ ] 小分類が大分類に正しく紐づいていること。
    * [ ] お客様が入力する必要のある値が表示されていること。
    * [ ] 追加された項目が運輸支局に届けが必要な項目として管理されていること。

#### 2. 仕様 (Specification)

##### 機能要件 (Functional Requirements)

* 拠点マスタ詳細画面に新しい項目を追加する際、システムは添付ファイルに基づいて大分類、小分類、お客様が入力する必要のある値を正しく表示すること。
* ユーザーが拠点マスタ詳細画面を開いた際、追加された項目が表示されることを保証する。
* システムは、ユーザーが入力した値を保存し、後で参照できるようにすること。
* 大分類と小分類の関係が正しく設定されていることを確認するためのバリデーションを実施すること。

##### UI/UX

* **デザイン:** (未指定)
* **コンポーネント:** (未指定)

##### 添付ファイル

* 参考資料: [事務所・車庫等運輸支局届出情報(2025年12月3日現在).xlsx](https://github.com/user-attachments/files/24337808/2025.12.3.xlsx)

##### 起票者

Đào Thị Thư

---

### Vietnamese Version

#### 1. Tổng quan (Overview)

##### Bối cảnh (Background)

* **Vấn đề hiện tại:** Thêm mục vào cơ sở dữ liệu chi nhánh  
Cần quản lý các mục cần thông báo đến **運輸支局 (Cục vận tải)**

* **Yêu cầu kinh doanh:** Thêm các mục được ghi trong tệp đính kèm vào màn hình chi tiết cơ sở dữ liệu chi nhánh.

* **Câu chuyện người dùng:** Là người dùng, tôi muốn quản lý các mục cần thông báo đến **運輸支局 (Cục vận tải)**.

##### Mục tiêu đạt được (Goal)

* **Hình ảnh lý tưởng:** Vui lòng thêm các mục được ghi trong tệp đính kèm vào màn hình chi tiết cơ sở dữ liệu chi nhánh.  
  - Cột A: Phân loại lớn  
  - Cột B: Phân loại nhỏ (liên kết với cột A)  
  - Cột C: Giá trị mà khách hàng cần nhập

* **Điều kiện hoàn thành (Definition of Done):**
    * [ ] Màn hình chi tiết cơ sở dữ liệu chi nhánh đã được thêm mục phân loại lớn.
    * [ ] Phân loại nhỏ đã liên kết đúng với phân loại lớn.
    * [ ] Giá trị mà khách hàng cần nhập được hiển thị.
    * [ ] Các mục đã thêm được quản lý như là các mục cần thông báo đến **運輸支局 (Cục vận tải)**.

#### 2. Thông số kỹ thuật (Specification)

##### Yêu cầu chức năng (Functional Requirements)

* Khi thêm mục mới vào màn hình chi tiết cơ sở dữ liệu chi nhánh, hệ thống phải hiển thị đúng phân loại lớn, phân loại nhỏ và giá trị mà khách hàng cần nhập dựa trên tệp đính kèm.
* Đảm bảo rằng các mục đã thêm được hiển thị khi người dùng mở màn hình chi tiết cơ sở dữ liệu chi nhánh.
* Hệ thống phải lưu giá trị mà người dùng đã nhập và cho phép tham khảo sau này.
* Thực hiện xác thực để đảm bảo mối quan hệ giữa phân loại lớn và phân loại nhỏ được thiết lập đúng.

##### UI/UX (nếu có)

* **Thiết kế:** (chưa chỉ định)
* **Thành phần:** (chưa chỉ định)

##### Tệp đính kèm

* Tài liệu tham khảo: [事務所・車庫等運輸支局届出情報(2025年12月3日現在).xlsx](https://github.com/user-attachments/files/24337808/2025.12.3.xlsx)

##### Người khởi tạo

Đào Thị Thư

---

## Implementation Checklist

### Frontend Tasks (FE Only)

- [ ] 拠点マスタ詳細画面のコンポーネントを特定
- [ ] 大分類フィールドの追加
- [ ] 小分類フィールドの追加（大分類に紐づく）
- [ ] お客様入力値フィールドの追加
- [ ] 大分類と小分類の連動ロジック実装
- [ ] フォームバリデーション実装
- [ ] UI/UXデザインの実装
- [ ] レスポンシブ対応
- [ ] エラーハンドリング
- [ ] ユニットテスト作成

### Backend Tasks

⚠️ **注意:** このissueは**FE専用**として処理されます。Backendタスクは別issueで対応してください。

---

## Notes / Review Section

### 重要な注意事項

1. **FE専用実装:** このissueはFrontendのみの実装として扱います。
2. **添付ファイル確認:** 実装前に添付ファイル（Excel）の内容を確認し、必要な項目を特定してください。
3. **既存画面の確認:** 拠点マスタ詳細画面の既存実装を確認し、適切な場所に項目を追加してください。
4. **データ構造:** 大分類と小分類の階層関係を正しく実装してください。

### 技術スタック

- **Frontend:** Nuxt.js, Vue.js, TypeScript
- **テスト:** Jest
- **スタイリング:** (プロジェクトの既存スタイルに従う)

### 参考資料

- 添付Excelファイル: [事務所・車庫等運輸支局届出情報(2025年12月3日現在).xlsx](https://github.com/user-attachments/files/24337808/2025.12.3.xlsx)

---

## Branch Information

- **Branch Name:** `555-feat-branch-master-items`
- **Created From:** `515_UI_driver_dev`
- **Created At:** 2025-12-29

---

## Next Steps

1. `/plan 555` - 実装計画の作成
2. `/breakdown 555` - FE/BE issue分解（このissueはFE専用のため、FE issueのみ作成）
3. `/dev {fe_issue_number}` - 開発実行







