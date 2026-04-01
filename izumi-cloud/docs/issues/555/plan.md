# Issue #555: 拠点マスタの項目追加 - Implementation Plan

## 概要 (Overview)

### 現状 (Current State)
拠点マスタ詳細画面（`DepartmentMaster/edit.vue`）には以下のセクションが存在します：
- **基本情報**: 拠点名、都道府県、郵便番号、住所、電話番号
- **採用関連情報**: 面接住所、面接住所URL、面接住所までの経路、採用担当者、Line Worksアカウント名

### 改善後 (Improved State)
運輸支局に届けが必要な項目を管理するため、新しいセクション「運輸支局届出情報」を追加します：
- **大分類**: ドロップダウン選択（A列）
- **小分類**: ドロップダウン選択（B列、大分類に紐づく）
- **お客様入力値**: テキスト入力フィールド（C列）

### 要件
- 添付Excelファイル（事務所・車庫等運輸支局届出情報）の内容に基づいて項目を追加
- 大分類と小分類の階層関係を正しく実装
- ユーザーが入力した値を保存・表示できるようにする
- 既存のUI/UXパターンに従う

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/DepartmentMaster/edit.vue`

##### 1.1.1. 新しいセクション「運輸支局届出情報」の追加

**既存コード** (line 47-105):

- 採用関連情報セクションの後に新しいセクションを追加する必要があります
- 既存のセクション構造（`v-b-toggle`、`b-collapse`）を参考に実装

**変更内容:**

- Line 105の後に新しいセクション「運輸支局届出情報」を追加
- セクションタイトル: "運輸支局届出情報"
- コラプシブル（折りたたみ可能）なセクションとして実装
- 既存のセクションと同じスタイルとレイアウトパターンを使用

```vue
<div v-b-toggle="'transportation-bureau-notification'" class="d-flex flex-row align-items-center w-100 mt-3">
    <span class="label-line" />
    <span style="text-wrap: nowrap;" class="label-text">運輸支局届出情報</span>
    <span class="label-line" />
</div>

<b-collapse id="transportation-bureau-notification" v-model="transportation_bureau_dropdown" class="mt-2">
    <!-- フィールドをここに追加 -->
</b-collapse>
```

##### 1.1.2. 大分類（Major Category）フィールドの追加

**変更内容:**

- 大分類用のドロップダウン/セレクトフィールドを追加
- `b-form-select`コンポーネントを使用（既存パターンに従う）
- ラベル: "大分類"
- プレースホルダー: "選択してください"
- 選択値が変更されたときに小分類のオプションを更新するロジックを実装

```vue
<label for="major-category-input" class="mt-3">大分類</label>
<b-form-select
    id="major-category-input"
    v-model="major_category"
    :options="major_category_options"
    @change="handleMajorCategoryChange"
>
    <template #first>
        <b-form-select-option :value="null" disabled>選択してください</b-form-select-option>
    </template>
</b-form-select>
```

##### 1.1.3. 小分類（Minor Category）フィールドの追加

**変更内容:**

- 小分類用のドロップダウン/セレクトフィールドを追加
- 大分類が選択されていない場合は無効化（disabled）
- 大分類の選択値に基づいてオプションを動的にフィルタリング
- ラベル: "小分類"
- 大分類が変更されたときに小分類をリセット

```vue
<label for="minor-category-input" class="mt-3">小分類</label>
<b-form-select
    id="minor-category-input"
    v-model="minor_category"
    :options="filtered_minor_category_options"
    :disabled="!major_category"
    @change="handleMinorCategoryChange"
>
    <template #first>
        <b-form-select-option :value="null" disabled>選択してください</b-form-select-option>
    </template>
</b-form-select>
```

##### 1.1.4. お客様入力値（Customer Input Value）フィールドの追加

**変更内容:**

- お客様が入力する値用のテキスト入力フィールドを追加
- `b-form-input`または`b-form-textarea`を使用（値の長さに応じて選択）
- ラベル: "お客様入力値"
- プレースホルダー: "入力してください"
- バリデーション: 必要に応じて最大文字数制限を追加

```vue
<label for="customer-input-value" class="mt-3">お客様入力値</label>
<b-form-input
    id="customer-input-value"
    v-model="customer_input_value"
    placeholder="入力してください"
    class="customer-input-value"
/>
```

##### 1.1.5. Data propertiesの追加

**既存コード** (line 144-171):

- `data()`メソッド内に新しいデータプロパティを追加

**変更内容:**

- `major_category`: 選択された大分類の値（nullまたは選択値）
- `minor_category`: 選択された小分類の値（nullまたは選択値）
- `customer_input_value`: お客様入力値（文字列）
- `major_category_options`: 大分類のオプションリスト（配列）
- `minor_category_options`: 全小分類のオプションリスト（配列、大分類IDでフィルタリング可能）
- `filtered_minor_category_options`: 現在選択されている大分類に基づいてフィルタリングされた小分類オプション（computed property）
- `transportation_bureau_dropdown`: セクションの開閉状態（boolean、デフォルト: true）

```javascript
data() {
    return {
        // ... existing properties ...
        
        // 運輸支局届出情報
        major_category: null,
        minor_category: null,
        customer_input_value: '',
        major_category_options: [],
        minor_category_options: [],
        transportation_bureau_dropdown: true,
    };
}
```

##### 1.1.6. 大分類・小分類のオプションデータ構造の定義

**変更内容:**

- 添付Excelファイルの内容に基づいて、大分類と小分類のマスタデータを定義
- データ構造例:
  ```javascript
  major_category_options: [
      { value: 1, text: '大分類1' },
      { value: 2, text: '大分類2' },
      // ... ExcelファイルのA列の内容に基づいて定義
  ],
  minor_category_options: [
      { value: 1, major_category_id: 1, text: '小分類1-1' },
      { value: 2, major_category_id: 1, text: '小分類1-2' },
      { value: 3, major_category_id: 2, text: '小分類2-1' },
      // ... ExcelファイルのB列の内容に基づいて定義（major_category_idで紐づけ）
  ]
  ```
- 注意: 実際のExcelファイルの内容を確認して、正確なデータを定義する必要があります

##### 1.1.7. Computed property: filtered_minor_category_options

**変更内容:**

- 現在選択されている大分類に基づいて小分類オプションをフィルタリングするcomputed propertyを追加
- 大分類が選択されていない場合は空配列を返す
- 大分類が選択されている場合は、該当する`major_category_id`を持つ小分類のみを返す

```javascript
computed: {
    filtered_minor_category_options() {
        if (!this.major_category) {
            return [];
        }
        return this.minor_category_options.filter(
            option => option.major_category_id === this.major_category
        );
    },
}
```

##### 1.1.8. メソッド: handleMajorCategoryChange

**変更内容:**

- 大分類が変更されたときに呼び出されるメソッド
- 小分類をリセット（nullに設定）
- 必要に応じて追加のロジックを実装

```javascript
handleMajorCategoryChange() {
    // 大分類が変更されたら小分類をリセット
    this.minor_category = null;
}
```

##### 1.1.9. メソッド: handleMinorCategoryChange

**変更内容:**

- 小分類が変更されたときに呼び出されるメソッド
- 必要に応じて追加のロジックを実装（現時点では空でも可）

```javascript
handleMinorCategoryChange() {
    // 必要に応じて追加のロジックを実装
}
```

##### 1.1.10. メソッド: handleGetDepartmentInfo の更新

**既存コード** (line 228-281):

- 既存の`handleGetDepartmentInfo`メソッドを更新して、新しいフィールドのデータを取得・設定

**変更内容:**

- APIレスポンスから新しいフィールドの値を取得
- `major_category`, `minor_category`, `customer_input_value`を設定
- APIレスポンスの構造が不明な場合は、バックエンドチームと確認が必要（FE専用issueのため、仮のデータ構造を想定）

```javascript
// Line 242-269のDATA処理部分に追加
this.major_category = DATA['major_category'] || null;
this.minor_category = DATA['minor_category'] || null;
this.customer_input_value = DATA['customer_input_value'] || '';
```

##### 1.1.11. メソッド: handleSaveDepartmentInfo の更新

**既存コード** (line 285-336):

- 既存の`handleSaveDepartmentInfo`メソッドを更新して、新しいフィールドのデータを保存

**変更内容:**

- `DATA`オブジェクトに新しいフィールドを追加
- Line 303-312のDATAオブジェクト構築部分に追加

```javascript
const DATA = {
    // ... existing fields ...
    major_category: this.major_category,
    minor_category: this.minor_category,
    customer_input_value: this.customer_input_value,
};
```

##### 1.1.12. バリデーション: handleVailidateFormData の更新（オプション）

**既存コード** (line 337-365):

- 必要に応じて新しいフィールドのバリデーションを追加
- 要件に基づいて、大分類・小分類の必須チェックや入力値の文字数制限を実装

**変更内容:**

- 大分類が必須の場合: 大分類が選択されているかチェック
- 小分類が必須の場合: 大分類が選択されている場合のみ小分類が選択されているかチェック
- お客様入力値: 文字数制限がある場合はチェック

```javascript
// 例: 大分類が必須の場合
if (this.major_category === null) {
    this.$toast.warning({
        content: '大分類を選択してください',
    });
    return false;
}
```

---

## BE (Backend)

⚠️ **注意:** このissueは**FE専用**として処理されます。Backendタスクは別issueで対応してください。

### 想定されるBackend実装（参考情報）

以下の実装が必要になる可能性がありますが、このissueの範囲外です：

1. **データベーススキーマの更新**
   - `departments`テーブルに以下のカラムを追加:
     - `major_category` (integer, nullable)
     - `minor_category` (integer, nullable)
     - `customer_input_value` (string, nullable)

2. **APIエンドポイントの更新**
   - `GET /department/{id}`: 新しいフィールドを含むレスポンスを返す
   - `PUT /department/{id}`: 新しいフィールドを受け取り、保存する

3. **バリデーション**
   - 大分類と小分類の関係性のバリデーション
   - お客様入力値の文字数制限

---

## 実装順序 (Implementation Order)

1. **Frontend 実装** (独立実装可能)

   - Task 1.1.1: 新しいセクション「運輸支局届出情報」の追加
   - Task 1.1.5: Data propertiesの追加
   - Task 1.1.6: 大分類・小分類のオプションデータ構造の定義
   - Task 1.1.7: Computed property: filtered_minor_category_options
   - Task 1.1.2: 大分類フィールドの追加
   - Task 1.1.3: 小分類フィールドの追加
   - Task 1.1.4: お客様入力値フィールドの追加
   - Task 1.1.8: メソッド: handleMajorCategoryChange
   - Task 1.1.9: メソッド: handleMinorCategoryChange
   - Task 1.1.10: handleGetDepartmentInfo の更新（APIレスポンス構造が確定後）
   - Task 1.1.11: handleSaveDepartmentInfo の更新（APIリクエスト構造が確定後）
   - Task 1.1.12: バリデーションの追加（要件確認後）

2. **統合テスト**
   - 大分類選択時に小分類が正しくフィルタリングされることを確認
   - 大分類変更時に小分類がリセットされることを確認
   - フォーム送信時に新しいフィールドの値が正しく送信されることを確認
   - UI/UXが既存のセクションと一貫性があることを確認

---

## 見積もり工数 (Estimated Effort)

- **Frontend**: 6-8 時間
  - セクション追加とUI実装: 1-2時間
  - データ構造定義とオプション設定: 1-2時間
  - 依存ドロップダウンロジック実装: 1-2時間
  - API統合（データ取得・保存）: 1-2時間
  - バリデーション実装: 0.5-1時間
  - テストとデバッグ: 1.5-2時間

- **Backend**: 別issueで対応（このissueの範囲外）

**合計**: 6-8 時間（FEのみ）

---

## 技術的な注意事項 (Technical Notes)

1. **パフォーマンス考慮:**
   - 大分類・小分類のオプションリストが大きい場合、computed propertyによるフィルタリングは効率的
   - 必要に応じて、オプションデータをAPIから取得する方式も検討可能（現時点では静的なデータ構造を想定）

2. **UX 考慮:**
   - 大分類が選択されていない場合、小分類を無効化することで、ユーザーに明確な操作順序を示す
   - 大分類変更時に小分類を自動リセットすることで、データの整合性を保つ
   - 既存のセクションと同じスタイルとレイアウトを使用することで、一貫性のあるUIを提供

3. **データ整合性:**
   - 大分類と小分類の関係性をフロントエンドで検証（バックエンドでも検証が必要だが、FE専用issueのためフロントエンドでの検証を実装）
   - お客様入力値の文字数制限を実装（要件確認が必要）

4. **既存機能との互換性:**
   - 既存のセクション構造（`v-b-toggle`、`b-collapse`）を踏襲
   - 既存のフォーム送信ロジック（`handleSaveDepartmentInfo`）に統合
   - 既存のデータ取得ロジック（`handleGetDepartmentInfo`）に統合
   - 既存のバリデーションロジック（`handleVailidateFormData`）に統合

5. **Excelファイルの確認:**
   - 実装前に添付Excelファイル（事務所・車庫等運輸支局届出情報.xlsx）の内容を確認し、正確な大分類・小分類のデータを定義する必要があります
   - ExcelファイルのA列（大分類）とB列（小分類）の関係性を正確に把握する必要があります

6. **API統合の注意:**
   - このissueはFE専用のため、バックエンドAPIの実装状況を確認する必要があります
   - APIレスポンス/リクエストの構造が確定していない場合は、仮のデータ構造で実装し、後で調整する必要があります
   - バックエンドチームと連携して、API仕様を確認することを推奨します







