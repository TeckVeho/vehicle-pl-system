# Issue #870: Employee master DB_Display pdf on the detail screen - Implementation Plan

## 概要 (Overview)

- **要件**: 従業員マスタDB詳細画面（基本情報）で、健康診断受診日欄をクリックすると受診履歴の日付一覧を表示し、任意の日付を選択すると該当PDFを表示する。また、健康診断結果通知書タブで誤アップロードしたPDFを履歴から削除できるようにする。
- **現状**: (1) 詳細画面の「健康診断受診日」は `b-form-input` の disabled 表示のみで、クリックしても何も起きない。(2) 健康診断タブ（HealthExam.vue）では PDF 一覧・表示・ダウンロードはあるが削除機能がない。API は `addHealthExaminationResults` のみで、ファイル履歴の削除 API は存在しない。
- **改善後**: (1) 詳細画面で健康診断受診日をクリック → モーダルで受診日一覧（最大10件・5年分）表示 → 日付選択で PDF 表示。(2) 健康診断タブの履歴テーブルに削除ボタンを追加し、DELETE API で該当ファイル履歴を削除可能にする。

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: resources/js/pages/EmployeeMaster/detail.vue

##### 1.1.1. 健康診断受診日クリックでモーダル表示（Clickable medical examination date → modal with date list）

- 現状: 141–153行付近で「健康診断受診日」が `b-form-input` で `employee.medical_examination_date` を disabled 表示しているだけ。
- 変更: この欄をクリック可能にする（リンクまたはボタン風のスタイル）。クリック時に、受診履歴の日付一覧を表示するモーダルを開く。履歴は既に `getEmployeeDetailData` で取得している `EMPLOYEE_DATA.health_examination_results[0].file_history` を利用する（API は既に返却済み）。データを保持するため、`getEmployeeDetailData` 内で `health_examination_results` をコンポーネントの data に保存する（例: `this.healthExaminationFileHistory = EMPLOYEE_DATA.health_examination_results?.[0]?.file_history ?? []`）。

**既存コード** (141-153):

- `label` + `b-form-input` で `employee.medical_examination_date` を disabled 表示。

**変更内容:**

- 同じ label の下で、入力欄を「クリック可能な見た目」に変更（例: `b-form-input` を `readonly` のままにして `@click` でモーダルを開く、または span/button で日付を表示してクリックでモーダルを開く）。
- data に `showModalHealthExamDates: false`, `healthExaminationFileHistory: []` を追加。
- `getEmployeeDetailData` の 1351–1352 行付近で、`employee.medical_examination_date` をセットしたあと、`this.healthExaminationFileHistory = EMPLOYEE_DATA.health_examination_results?.[0]?.file_history ?? []` を代入。

##### 1.1.2. 受診日一覧・PDF表示モーダルの追加（Modal: date list + PDF viewer）

- 受診履歴の日付一覧を表示するモーダルを追加する。一覧は `healthExaminationFileHistory` を日付（`date_of_visit` または file の `created_at`）で表示。ユーザーが一行を選択すると、該当 PDF を表示する（同一モーダル内の iframe、または既存の `ModalViewPDF` のようなコンポーネントを流用）。
- 仕様: 保存期間5年・最大10回分。一覧は既存 API の返却順（DESC）で表示すればよい。

**変更内容:**

- `b-modal` を追加（例: `id="modal-health-exam-dates"`）。モーダル内: タイトル「受診履歴」、テーブルまたはリストで `healthExaminationFileHistory` の各要素の日付（`date_of_visit`）を表示。行クリックで「選択した日付の PDF URL」をセットし、下段に iframe または `ModalViewPDF` で PDF を表示。
- 既存の `ModalViewPDF` があれば、同じコンポーネントをインポートして「選択された file の file_url」を渡して表示する。
- データが空のときは「履歴がありません」等のメッセージを表示。

---

#### 1.2. File: resources/js/pages/EmployeeMaster/tabs/HealthExam.vue

##### 1.2.1. 履歴テーブルに削除ボタン追加（Delete button in file history table）

- 現状: 116–136 行の `b-table` の `#cell(operation)` で「表示」「ダウンロード」ボタンのみ。
- 変更: 操作列に「削除」ボタンを追加。クリック時に確認ダイアログ（`this.$bvModal.msgBoxConfirm` 等）を表示し、OK なら削除 API を呼ぶ。

**既存コード** (127-136):

- `operation` 列に表示・ダウンロードボタン。

**変更内容:**

- `#cell(operation)` 内に削除用ボタン（例: ゴミ箱アイコン）を追加。`@click` で `handleDeleteFileHistory(scope.item)` を呼ぶ。
- `scope.item` にファイル履歴の `id` が必要。現在 `updateItemsFromData` で `file_history` を map する際、`id` を渡していないため、map 時に `id: item?.id`（または `item` が file_history の行そのものなら `item.id`）を追加する。バックエンドで `fileHistory` の select に `id` を含める必要あり（BE で対応）。

##### 1.2.2. 削除API呼び出しと再取得（Call delete API and refresh）

- 削除用メソッド `handleDeleteFileHistory(item)` を追加。引数は履歴1件（`id` を持つ）。確認後、`DELETE /employee/health-examination-results/file-history/{id}` を呼ぶ（API は BE で新規追加）。成功時は `this.$emit('update-success')` で親に再取得させるか、親から渡された refetch を実行する。

**変更内容:**

- `employeeMaster.js` に `deleteHealthExaminationFileHistory(id)` のような関数を追加（DELETE リクエスト）。
- HealthExam.vue の methods に `handleDeleteFileHistory(item)` を追加。確認 → API 呼び出し → 成功で toast と `$emit('update-success')`。

---

#### 1.3. File: resources/js/api/modules/employeeMaster.js

##### 1.3.1. 健康診断ファイル履歴削除API（Delete health examination file history API client）

- 既存の `deletePDF` は別用途（employee pdf 用）。健康診断ファイル履歴用の DELETE を追加する。

**変更内容:**

- 例: `export function deleteHealthExaminationFileHistory(url) { return RequestApi.deleteOne(url); }` を追加。呼び出し側で `deleteHealthExaminationFileHistory(\`/employee/health-examination-results/file-history/${id}\`)` のように使用。

---

## BE (Backend)

### 1. Files need to edit:

#### 1.1. File: app/Http/Controllers/Api/EmployeeController.php

##### 1.1.1. 健康診断ファイル履歴削除エンドポイントの追加（DELETE file history endpoint）

- 新規: `DELETE /api/employee/health-examination-results/file-history/{id}`。`id` は `health_examination_results_file_history.id`。
- 実装: 対象レコードが存在し、かつその `employee_health_examination_results` が現在の従業員（または権限のある対象）に属することを確認してから削除。Repository に `deleteHealthExaminationFileHistory($id)` を追加し、Controller から呼ぶ。

**変更内容:**

- ルートに `Route::delete('employee/health-examination-results/file-history/{id}', "EmployeeController@deleteHealthExaminationFileHistory");` を追加（api.php）。
- Controller に `deleteHealthExaminationFileHistory($id)` メソッドを追加。Request は ID のみ。Repository の `deleteHealthExaminationFileHistory($id)` を呼び、成功時 200、失敗時 400 を返す。

##### 1.1.2. 既存 addHealthExaminationResults の変更は不要

- 追加・更新は既存のまま。必要に応じて「最大10件」のバリデーションを Repository 側で検討可能（仕様上は5年・年2回で10回まで）。

---

#### 1.2. File: app/Repositories/EmployeeRepository.php

##### 1.2.1. fileHistory の select に id を追加（Include id in fileHistory for delete）

- 現在の実装 (306-307行): `"healthExaminationResults.fileHistory" => function ($query) { $query->select('employee_health_examination_results_id', 'file_id', 'user_id', 'date_of_visit')->orderBy('created_at', 'DESC'); }` では主キー `id` が含まれていない可能性がある。フロントで削除対象を指定するため、`id` を select に含める。

**現在の実装** (306-307):

- `$query->select('employee_health_examination_results_id', 'file_id', 'user_id', 'date_of_visit')`

**変更内容:**

- `$query->select('id', 'employee_health_examination_results_id', 'file_id', 'user_id', 'date_of_visit')` に変更。

##### 1.2.2. deleteHealthExaminationFileHistory メソッドの追加（Delete file history record）

- `HealthExaminationResultsFileHistory` の `id` でレコードを取得。紐づく `employee_health_examination_results` から `employee_id` を取得し、権限チェック用に利用する（必要なら呼び出し元で employee_id と照合）。その後、該当の `HealthExaminationResultsFileHistory` を `delete()`。ファイル実体の削除は既存ポリシーに合わせる（他タブと同様、履歴レコードのみ削除でも可）。

**変更内容:**

- `public function deleteHealthExaminationFileHistory($id)` を追加。`HealthExaminationResultsFileHistory::query()->findOrFail($id)` で取得し、`$record->delete()`。返り値は削除したモデルまたは true。存在しない id の場合は findOrFail で 404。

---

#### 1.3. File: routes/api.php

##### 1.3.1. DELETE ルート追加（Route for delete file history）

- `Route::delete('employee/health-examination-results/file-history/{id}', "EmployeeController@deleteHealthExaminationFileHistory");` を employee 関連のグループ内に追加。

**変更内容:**

- 上記ルートを 120 行付近（health-examination-results の近く）に追加。

---

## 実装順序 (Implementation Order)

1. **Backend 実装**（FE が API に依存するため先に実施）
   - EmployeeRepository: fileHistory の select に `id` 追加（1.2.1）
   - EmployeeRepository: `deleteHealthExaminationFileHistory($id)` 追加（1.2.2）
   - routes/api.php: DELETE ルート追加（1.3.1）
   - EmployeeController: `deleteHealthExaminationFileHistory($id)` 追加（1.1.1）

2. **Frontend 実装**（BE 完了後）
   - employeeMaster.js: `deleteHealthExaminationFileHistory` 追加（1.3.1）
   - HealthExam.vue: items に `id` を入れ、操作列に削除ボタンと `handleDeleteFileHistory`（1.2.1, 1.2.2）
   - detail.vue: 健康診断受診日をクリック可能にし、data に `healthExaminationFileHistory` を保存（1.1.1）
   - detail.vue: 受診日一覧・PDF表示モーダルを追加（1.1.2）

3. **統合テスト**
   - 詳細画面で健康診断受診日クリック → モーダルで日付一覧表示 → 日付選択で PDF 表示されること。
   - 健康診断タブで削除 → 確認後、該当履歴が消え、一覧が再取得されること。
   - 保存期間5年・最大10件の表示が仕様通りであることを確認。

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 1.5–2 時間
  - Repository select 修正・delete メソッド: 0.5h
  - Controller ・ルート追加: 0.5h
  - 動作確認・権限確認: 0.5–1h

- **Frontend**: 2.5–3.5 時間
  - detail.vue クリック対応・data・モーダル（日付一覧＋PDF表示）: 1.5–2h
  - HealthExam.vue 削除ボタン・API 呼び出し・再取得: 0.5–1h
  - API モジュール追加: 0.25h
  - 結合・表示確認: 0.5h

**合計**: 4–5.5 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮**
   - 詳細 API は既に `healthExaminationResults.fileHistory` を eager load しているため、モーダル用の追加リクエストは不要。一覧は最大10件のため負荷は小さい。

2. **UX 考慮**
   - 受診日欄は「クリックできる」ことが分かるよう、カーソルや下線などで示す。
   - 削除時は必ず確認ダイアログを表示し、誤削除を防ぐ。

3. **データ整合性**
   - ファイル履歴を削除しても、既存の `files` レコードは残す方針でよい（他機能で参照される可能性）。必要なら後から物理削除ポリシーを検討。

4. **既存機能との互換性**
   - 詳細画面は「表示専用」のため、既存の編集フローには影響しない。HealthExam タブの表示・ダウンロード・アップロードは既存のまま、削除のみ追加。

5. **5年・10件の制限**
   - 表示は既存の file_history をそのまま使用。追加時（addHealthExaminationResults）で「最大10件」をチェックするかは任意（仕様書では保存期間5年・年2回で10回とあるため、必要なら Repository で件数制限を実装可能）。
