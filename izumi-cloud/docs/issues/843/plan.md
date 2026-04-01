# Issue #843: Add course address to Course master — Implementation Plan

## 概要 (Overview)

- **現状:** コースマスタ（Course master）にコース先住所（course address / コース先住所）のデータがない。
- **目標:** コースマスタに「コース先住所」フィールドを追加し、作成・表示・編集・一覧で扱えるようにする。既存データは nullable で対応する。
- **Scope:** Backend（migration, model, repository, request, constants）, Frontend（create / edit / detail のフォーム、必要なら index）。

---

## FE (Frontend)

**注:** 本プロジェクトではフロントは `resources/js` にあります（issue.md の `frontend_path: ./frontend` は別モノ。実装は `resources/js` を編集）。

### 1. Files need to edit

#### 1.1. File: `resources/js/pages/CourseMaster/create.vue`

##### 1.1.1. フォームに「コース先住所」フィールドを追加

- **既存コード:** `isForm`（line 446–464）に `course_id`, `start_date`, `course_flag`, `gate`, `wing`, `tonnage`, `shipper`, `delivery_store` 等がある。同様のパターンで `course_address` を追加する。
- **変更内容:**
  - `data().isForm` に `course_address: ''` を追加する。
  - 基本情報エリア内（例: コースID の後、または配送開始日の前）に、`b-row` + `b-col` + `zone-form` でラベル「コース先住所」と `b-form-input`（または `b-form-textarea`）を追加し、`v-model="isForm.course_address"` でバインドする。
  - 登録時ペイロード（postCourse 用オブジェクト、line 700–713 付近）に `course_address: this.isForm.course_address || null` を追加する。

##### 1.1.2. 多言語ラベルの追加

- **変更内容:**
  - ラベルは `$t('COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS')` のように i18n キーを使う。対応するキーを `resources/js/lang/subs/ja.js` と `en.js` に追加する（後述）。

---

#### 1.2. File: `resources/js/pages/CourseMaster/edit.vue`

##### 1.2.1. 編集フォームにコース先住所を追加

- **既存コード:** `isForm` の構造は create.vue と同様。API から取得した DATA を `isForm` にマッピングしている（line 503–517 付近）。
- **変更内容:**
  - `isForm` に `course_address: ''` を追加する。
  - DATA マッピングで `this.isForm.course_address = DATA.course_address ?? ''` を追加する。
  - テンプレートに create と同様の「コース先住所」入力欄を追加し、`v-model="isForm.course_address"` でバインドする。
  - 更新 API に送るペイロード（line 731–744 付近）に `course_address: this.isForm.course_address || null` を追加する。

---

#### 1.3. File: `resources/js/pages/CourseMaster/detail.vue`

##### 1.3.1. 詳細画面でコース先住所を表示

- **既存コード:** 他フィールドと同様、`isForm` に保持し、disabled の `b-form-input` 等で表示している（例: course_id line 48）。
- **変更内容:**
  - `isForm` に `course_address: ''` を追加する。
  - API から取得した DATA のマッピング（line 519–533 付近）に `this.isForm.course_address = DATA.course_address ?? ''` を追加する。
  - テンプレートに「コース先住所」のラベルと表示用の `b-form-input`（disabled）またはテキスト表示を追加する。

---

#### 1.4. File: `resources/js/lang/subs/ja.js` および `resources/js/lang/subs/en.js`

##### 1.4.1. コース先住所のラベルを追加

- **変更内容:**
  - `ja.js`: コースマスタ関連のブロックに `COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS: 'コース先住所'` を追加する。
  - `en.js`: 同キーで `COURSE_MASTER_FORM_LABLE_COURSE_ADDRESS: 'Course address'`（または 'Course destination address'）を追加する。

---

#### 1.5. File: `resources/js/pages/CourseMaster/index.vue`（必要に応じて）

##### 1.5.1. 一覧でコース先住所を表示する場合

- **変更内容:**
  - 一覧がテーブルで course を表示している場合は、列を 1 つ追加して `course_address` を表示する。一覧がカレンダーや別コンポーネントのみの場合は、このタスクはスキップ可能。

---

## BE (Backend)

### 1. Files need to edit

#### 1.1. File: `database/migrations/` — 新規マイグレーション

##### 1.1.1. マイグレーション作成

- **変更内容:**
  - `php artisan make:migration add_course_address_to_courses_table --table=courses` で新規ファイルを作成する。
  - `up()` で `Schema::table('courses', function (Blueprint $table) { $table->string('course_address')->nullable(); });`
  - `down()` で `$table->dropColumn('course_address');`
  - 既存データはそのまま残し、新カラムは nullable とする。

---

#### 1.2. File: `app/Models/Course.php`

##### 1.2.1. fillable に course_address を追加

- **現在の実装:** `$fillable`（line 24–38）に `course_code`, `start_date`, `end_date`, `course_type`, ... `course_flag` がある。
- **変更内容:**
  - `$fillable` に `'course_address'` を追加する（例: `course_flag` の次）。

---

#### 1.3. File: `app/constants.php`

##### 1.3.1. QUERY_COURSE_MUST_SELECT に course_address を追加

- **現在の実装:** line 283–299 で `QUERY_COURSE_MUST_SELECT` に `courses.course_code`, `courses.start_date`, ... `courses.course_flag` が定義されている。
- **変更内容:**
  - 配列に `"courses.course_address"` を 1 件追加する（例: `courses.course_flag` の次）。これにより `CourseRepository::find()` や `get(QUERY_COURSE_MUST_SELECT)` で course_address が含まれる。

---

#### 1.4. File: `app/Repositories/CourseRepository.php`

##### 1.4.1. create での course_address 扱い

- **現在の実装:** `create()`（line 67–85）は `$this->model->create($attributes)` で一括代入しており、Model の `$fillable` に含めれば `course_address` は自動で保存される。
- **変更内容:**
  - 特になし（Model の fillable 追加で対応）。

##### 1.4.2. update で course_address を保存

- **現在の実装:** `update()`（line 156–185）で各フィールドを個別に代入している（`course_code`, `start_date`, ... `course_flag`）。
- **変更内容:**
  - `$course->course_flag = $attributes['course_flag'];` の直後に `$course->course_address = $attributes['course_address'] ?? null;` を追加する。存在しないキー対策のため `?? null` を推奨。

---

#### 1.5. File: `app/Http/Requests/CourseRequest.php`

##### 1.5.1. バリデーションと attributes に course_address を追加

- **現在の実装:** `getCustomRule()` で store / update 用の rules を返している（line 42–79）。`attributes()`（line 89–108）で日本語ラベルを定義している。
- **変更内容:**
  - store 用 rules に `'course_address' => 'nullable|string|max:255'`（または必要な max）を追加する。
  - update 用 rules にも同様に `'course_address' => 'nullable|string|max:255'` を追加する。
  - `attributes()` に `'course_address' => 'コース先住所'` を追加する。

---

#### 1.6. File: `app/Http/Resources/CourseResource.php`

##### 1.6.1. レスポンスに course_address を含める

- **現在の実装:** `toArray()` は `return parent::toArray($request);` のみ。BaseResource は Model の属性をそのまま返すため、Model に `course_address` が存在すれば API レスポンスに含まれる。
- **変更内容:**
  - 特になし（Model と select に含めれば自動で出る）。必要なら `CourseResource::toArray` で明示的にキーを並べてもよい。

---

#### 1.7. File: `database/factories/CourseFactory.php`（任意）

##### 1.7.1. テスト用ファクトリで course_address を定義

- **変更内容:**
  - `definition()` の return 配列に `'course_address' => $this->faker->optional(0.7)->address()` などを追加すると、テスト・シードで扱いやすい。

---

## 実装順序 (Implementation Order)

1. **Backend 実装**（Frontend が API を叩く前提のため先に実施）
   - 1.1 マイグレーション作成・実行
   - 1.2 Model `Course` の fillable
   - 1.3 constants の QUERY_COURSE_MUST_SELECT
   - 1.4 Repository の update
   - 1.5 CourseRequest の rules / attributes
   - 1.6 CourseResource は変更なしで可
   - 1.7 Factory は任意

2. **Frontend 実装**
   - 1.4 多言語（ja.js / en.js）
   - 1.1 create.vue
   - 1.2 edit.vue
   - 1.3 detail.vue
   - 1.5 index.vue（一覧に列がある場合のみ）

3. **統合テスト**
   - コース作成時に course_address を送信 → DB に保存されること
   - コース詳細 GET で course_address が返ること
   - コース更新で course_address を変更できること
   - 既存コース（course_address = null）が一覧・詳細・編集で問題なく表示・更新できること

---

## 見積もり工数 (Estimated Effort)

- **Backend**: 約 1–1.5 時間
  - マイグレーション: 0.25h
  - Model / constants / Repository / Request: 0.5h
  - 動作確認: 0.25–0.5h

- **Frontend**: 約 1.5–2 時間
  - create / edit / detail フォーム + i18n: 1–1.25h
  - 一覧対応（必要な場合）: 0.25h
  - 動作確認: 0.25–0.5h

**合計**: 約 2.5–3.5 時間

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮**
   - 追加カラムは 1 つの string のみで、インデックスは不要。既存の `QUERY_COURSE_MUST_SELECT` に 1 カラム増えるだけなので影響は小さい。

2. **UX 考慮**
   - コース先住所は任意（nullable）のため、必須ラベルにせず「任意」であることが分かるようにするか、プレースホルダで補足するとよい。

3. **データ整合性**
   - 既存レコードは `course_address = null` のまま。新規・更新時のみ値を設定する。バリデーションは `nullable|string|max:255` で十分。

4. **既存機能との互換性**
   - API の request/response に 1 フィールド増えるだけなので、既存クライアントは無視すればよい。Frontend は新フィールドを送信・表示するだけで既存動作を壌さない。
