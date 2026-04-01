# Issue #799: Add PDF upload section to the employee master detail page - Implementation Plan

## 概要 (Overview)

- **要件**: 従業員台帳（Employee Master）の詳細ページに、PDFを一時アップロードするセクションを追加する。アップロードしたPDFは一覧表示し、後から「振り分け」で運転免許証・運転記録証明書・適性診断票・健康診断結果通知書のいずれかのタブに分類できるようにする。関連: Issue #515。
- **現状**: 従業員台帳ドキュメントページ（`employee-master-document/:id`）では、各タブ（運転免許証・運転記録証明書・適性診断票・健康診断結果通知書）で個別にPDFをアップロードする運用。分類前のPDFをまとめてアップロードし、後で振り分ける機能はない。
- **改善後**: 同一ページに「PDF保管データ」セクションを設け、まずPDFをアップロード → 一覧で確認・閲覧・削除 → 振り分けモーダルで種別・受診日を指定して該当タブに移動。振り分け後は当該タブの履歴に表示される。

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: resources/js/pages/EmployeeMaster/document.vue

##### 1.1.1. PDF保管セクションの追加（Upload PDF section）

- タブ（従業員マスタ・運転免許証・…）の**上**に、新セクション「PDF保管データ」（または「アップロードPDF」）を追加する。
- セクション構成: タイトル、アップロードボタン、一覧テーブル（ファイル名、アップロード日時、操作: 表示 / 振り分け / 削除）。一覧はアップロード日時の降順（新しい→古い）。
- 既存コード: 17–46行目が `employee-master-list` と tabs / content。ここに `employee-master-list__pdf-upload-section` のようなブロックを挿入する。

**変更内容:**

- `<div class="employee-master-list__tabs">` の直前に、PDF保管用のブロックを追加。
- 子コンポーネント `PdfUploadSection`（または同等の名前）に `employee-id` と `employee` を渡し、一覧取得・アップロード・振り分け・削除のイベントをハンドルする。
- 振り分け成功時は `@update-success` 等でタブ側のデータを再取得（既存の `handleRefreshData` と同様）。

##### 1.1.2. 振り分けモーダル用の分類オプション・i18n

- 振り分け先: 運転免許証 / 運転記録証明書 / 適性診断票 / 健康診断結果通知書。
- 適性診断票選択時: 種別（既存 `ListType`）と受診日を必須入力。
- 健康診断結果通知書選択時: 受診日を必須入力（種別は既存仕様に合わせる）。
- 既存の `ja.js` / `en.js` 等のキーを流用しつつ、不足分は「PDF保管」「振り分け」「分類先」等のラベルを追加。

**変更内容:**

- モーダル内で分類先セレクト、条件に応じて種別・受診日フィールドを表示。送信時にバリデーション（必須項目）を行う。

#### 1.2. File: resources/js/pages/EmployeeMaster/components/PdfUploadSection.vue（新規）

##### 1.2.1. PDF保管セクションコンポーネント

- 表示: タイトル、アップロードボタン、テーブル（ファイル名、アップロード日時、表示 / 振り分け / 削除）。
- アップロード: 既存の `/employee/upload-file` を利用し、成功後に「この従業員のPDF保管一覧に追加」するAPI（例: `POST /employee/{id}/pdf-uploads` で `file_id` を送る）を呼ぶ。
- 一覧取得: `GET /employee/{id}/pdf-uploads` で取得し、降順表示。
- 表示: 既存のPDF表示（別タブ or モーダル）の仕組みを流用（file URL取得方法は既存タブと同様）。
- 振り分け: モーダルで分類先・種別・受診日を選択し、`POST /employee/pdf-uploads/{id}/classify` を呼ぶ。成功後は親でタブデータ再取得し、当該PDFは一覧から削除される。
- 削除: 確認モーダル後に `DELETE /employee/pdf-uploads/{id}` を呼び、一覧から削除。

**変更内容:**

- 新規コンポーネントとして上記ロジックとテンプレートを実装。既存の `AptitudeTest.vue` / `HealthExam.vue` のアップロードUI・日付入力・PDF表示を参考にする。

#### 1.3. File: resources/js/api/modules/employeeMaster.js（または該当APIモジュール）

##### 1.3.1. PDF保管用APIクライアント

- `getEmployeePdfUploads(employeeId)` → GET list.
- `addEmployeePdfUpload(employeeId, fileId)` → POST 保管レコード作成（アップロードは既存 upload-file で行い、返却された file_id を渡す）。
- `classifyEmployeePdfUpload(pdfUploadId, payload)` → POST classify（category, type, date_of_visit 等）。
- `deleteEmployeePdfUpload(pdfUploadId)` → DELETE.

**変更内容:**

- 上記4メソッドを追加。既存の `getDetailEmployee` 等と同じベースURL・認証を利用する。

#### 1.4. File: resources/js/lang/subs/ja.js（および en.js / vi.js 等）

##### 1.4.1. 文言追加

- 「PDF保管データ」「振り分け」「分類先」「アップロード日時」「削除してよろしいですか」等、新セクション・モーダル用のキーを追加。

**変更内容:**

- 該当キーを追加し、他言語ファイルにも対応する。

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: database/migrations/xxxx_create_employee_pdf_uploads_table.php（新規）

##### 1.1.1. employee_pdf_uploads テーブル作成

- カラム: `id`, `employee_id`, `file_id`, `user_id`, `created_at`, `updated_at`.
- `employee_id`, `file_id` は外部キー制約またはインデックスを検討。既存の `files` テーブルと `employees` を参照。

**変更内容:**

- マイグレーションを新規作成し、`employee_pdf_uploads` を作成する。

#### 1.2. File: app/Models/EmployeePdfUpload.php（新規）

##### 1.2.1. モデル定義

- `EmployeePdfUpload` を定義。`employee_id`, `file_id`, `user_id` を fillable に。`employee`, `file` のリレーションを定義。

**変更内容:**

- 新規モデル。既存の `EmployeeAptitudeAssessmentForms` 等を参考にする。

#### 1.3. File: app/Repositories/EmployeeRepository.php

##### 1.3.1. PDF保管の一覧・登録・削除

- `getEmployeePdfUploads(employeeId)`: `employee_pdf_uploads` から該当 employee の一覧を `created_at` 降順で取得。file の URL 等を join またはリレーションで付与。
- `addEmployeePdfUpload(employeeId, fileId)`: レコード作成。既存の `saveFile` はそのまま利用し、アップロード済み `file_id` を渡す。
- `deleteEmployeePdfUpload(id)`: 指定IDのレコードを削除（ファイル実体の削除は既存ポリシーに合わせる）。

**現在の実装** (saveFile 840–858行付近):

- ファイル保存は `saveFile` で行い、`File` モデルでレコード作成。PDF保管は「既にアップロードされた file_id を従業員に紐付ける」形でよい。

**変更内容:**

- 上記3メソッドを Repository に追加。必要なら Interface にも追加。

##### 1.3.2. 振り分け（classify）処理

- `classifyEmployeePdfUpload(id, attributes)`: `employee_pdf_uploads` から1件取得し、`category` に応じて既存の `addDrivingRecordCertificate` / `addAptitudeAssessmentForm` / `addHealthExaminationResults` を呼ぶ。運転免許証の場合は既存の `addDriverLicense` は surface/back のため、仕様に合わせて「片面のみ」や新エンドポイントを検討する。
- 振り分け成功後に `employee_pdf_uploads` の当該レコードを削除する。

**変更内容:**

- category を enum または文字列で受け、既存の addXxx を流用。driver_license は既存APIが表面・裏面の2ファイルのため、単一PDFの振り分けの場合は「表面のみ」で登録するか、別途仕様を決める。

#### 1.4. File: app/Http/Controllers/Api/EmployeeController.php

##### 1.4.1. PDF保管用エンドポイント

- `GET /api/employee/{id}/pdf-uploads`: 一覧。Repository の `getEmployeePdfUploads` を呼ぶ。
- `POST /api/employee/{id}/pdf-uploads`: body に `file_id`。Repository の `addEmployeePdfUpload` を呼ぶ。
- `POST /api/employee/pdf-uploads/{id}/classify`: body に `category`, `type`（適性時）, `date_of_visit`（適性・健康診断時）。Repository の `classifyEmployeePdfUpload` を呼ぶ。
- `DELETE /api/employee/pdf-uploads/{id}`: Repository の `deleteEmployeePdfUpload` を呼ぶ。

**変更内容:**

- 上記4アクションを追加。認証・認可は既存の employee API と同様。Request クラスで `file_id` / `category` / `type` / `date_of_visit` をバリデーション。

#### 1.5. File: routes/api.php

##### 1.5.1. ルート追加

- `Route::get('employee/{id}/pdf-uploads', ...)`  
- `Route::post('employee/{id}/pdf-uploads', ...)`  
- `Route::post('employee/pdf-uploads/{id}/classify', ...)`  
- `Route::delete('employee/pdf-uploads/{id}', ...)`  

**変更内容:**

- 既存の `employee` グループ内に上記を追加。ミドルウェアは既存と同一でよい。

---

## 実装順序 (Implementation Order)

1. **Backend 実装** (FE の依存なしで先行可能)
   - Migration → Model → Repository（一覧・追加・削除・classify）→ Controller → Routes
   - 振り分け時の「運転免許証」は、既存が表面・裏面の2ファイルのため、単一PDFの扱いを決めてから実装（例: 表面のみで登録する等）。

2. **Frontend 実装** (BE の API が利用可能になった後)
   - API モジュール追加 → PdfUploadSection.vue 作成 → document.vue にセクション組み込み → i18n 追加。
   - 振り分けモーダルで適性診断票は種別・受診日、健康診断結果通知書は受診日を送信。

3. **統合テスト**
   - 従業員台帳ドキュメントページで、PDFアップロード → 一覧表示 → 表示/振り分け/削除が期待どおり動作することを確認。
   - 振り分け後、該当タブ（運転記録証明書・適性診断票・健康診断結果通知書）に履歴が表示されることを確認。

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 4–6 時間
  - Migration / Model: 0.5h
  - Repository（一覧・追加・削除・classify）: 2–2.5h
  - Controller・Request・Routes: 1–1.5h
  - 運転免許証の単一PDF扱いの検討・実装: 0.5–1h

- **Frontend**: 5–7 時間
  - API モジュール: 0.5h
  - PdfUploadSection.vue（一覧・アップロード・表示・振り分け・削除）: 3–4h
  - document.vue への組み込み・イベント連携: 0.5–1h
  - i18n・UI調整: 0.5–1h

**合計**: 9–13 時間

※ 子 Issue #800–#804 で SP が既に振られている場合は、上記は親 Issue #799 全体の目安として利用する。

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**
   - 一覧取得は employee 単位で件数が多くならない想定。必要なら pagination を検討。
   - ファイルURLは既存の File モデル・Storage URL の仕組みを流用する。

2. **UX 考慮:**
   - 振り分け後は「このPDFは〇〇タブに移動しました」などのメッセージを表示するとよい。
   - 削除は確認モーダル必須。表示は既存のモーダルまたは新タブで。

3. **データ整合性:**
   - 振り分け時に既存の addXxx を利用するため、既存の履歴テーブル（aptitude_assessment_forms_file_history 等）に正しくレコードが作られるようにする。
   - employee_pdf_uploads のレコードは振り分け成功後に削除し、二重登録を防ぐ。

4. **既存機能との互換性:**
   - 既存の「各タブから直接アップロード」はそのまま残す。PDF保管セクションは「一時アップロード→振り分け」用の追加機能とする。
   - 退職後3年経過等の既存制限は、addXxx 内で既にチェックされているため、振り分け時もそのまま適用される。
