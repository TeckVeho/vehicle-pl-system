## 日本語 / Japanese

### 親Issue

#499 に関連

### 説明

見積もり画面にAI経路計算機能のUIを統合します。ユーザーが「AI計算」ボタンをクリックすると、バックエンドAPIを呼び出し、計算結果（距離、時間、高速料金）を画面に自動入力します。

### 要件

#### 1. UI コンポーネント
- 「AI計算」ボタンの追加（見積もりフォーム内）
- ローディング状態の表示（計算中: 10-30秒）
- エラーメッセージ表示（API失敗時）
- 成功メッセージ表示（計算完了時）

#### 2. API統合
- `POST /api/quotation/routes/calculate` エンドポイント呼び出し
- Request payload構築:
  - pickup_location（積地）
  - delivery_location（届け地）
  - return_location（帰社地）
  - start_time（運行開始時間）
  - vehicle_type（車両区分）
  - loading_time（積み込み時間）
  - unloading_time（荷下ろし時間）

#### 3. レスポンス処理
- 計算結果を以下のフィールドに自動入力:
  - `total_distance_km` → 走行距離入力フィールド
  - `estimated_end_time` → 終了時間フィールド
  - `highway_fee` → 高速料金フィールド
- 詳細情報の表示（オプション）:
  - 拘束時間（total_duty_time_hours）
  - 実労働時間（actual_working_hours）
  - 休憩時間（total_break_time_minutes）
  - 法令遵守状態（is_compliant）

#### 4. エラーハンドリング
- API呼び出し失敗時のエラー表示
- タイムアウト処理（120秒）
- バリデーションエラー表示
- ネットワークエラー処理

#### 5. UX改善
- ボタン無効化（計算中）
- プログレスインジケーター表示
- 計算完了後のスムーズなフィールド更新
- 既存データの上書き確認（オプション）

### 技術詳細

**使用技術:**
- Vue.js / Nuxt.js（フロントエンドフレームワーク）
- Axios / Fetch API（HTTP client）
- Vuex / Pinia（状態管理 - オプション）

**実装場所:**
- 見積もりフォームコンポーネント（QuotationForm.vue または類似）
- API client module（api/quotation.js または類似）

**API Request例:**
```javascript
const response = await axios.post('/api/quotation/routes/calculate', {
  pickup_location: '東京都千代田区...',
  delivery_location: '神奈川県横浜市...',
  return_location: '埼玉県さいたま市...',
  start_time: '01:00',
  vehicle_type: '4t',
  loading_time: 60,
  unloading_time: 60
});

// レスポンス処理
if (response.data.success) {
  form.distance = response.data.data.summary.total_distance_km;
  form.end_time = response.data.data.summary.estimated_end_time;
  form.highway_fee = response.data.data.summary.highway_fee;
}
```

**状態管理:**
```javascript
data() {
  return {
    isCalculating: false,
    calculationError: null,
    calculationResult: null
  }
}
```

### 受け入れ基準

- [ ] 「AI計算」ボタンUI実装完了
- [ ] API呼び出し機能実装完了
- [ ] ローディング状態表示実装完了
- [ ] 計算結果の自動入力実装完了
- [ ] エラーハンドリング実装完了
- [ ] タイムアウト処理実装完了
- [ ] ユーザビリティテスト完了
- [ ] レスポンシブデザイン対応
- [ ] ブラウザ互換性確認（Chrome, Firefox, Safari, Edge）
- [ ] フロントエンドユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係

**ブロッキング:**
- #501（Backend issue）が完了し、APIエンドポイントが利用可能になる必要があります

**統合テスト:**
- Backend APIが正常に動作していることを確認後、統合テストを実施

### 実装ガイド

#### ステップ1: API Client作成
```javascript
// api/quotation.js
export const calculateRoute = (data) => {
  return axios.post('/api/quotation/routes/calculate', data);
};
```

#### ステップ2: コンポーネントにボタン追加
```vue
<template>
  <button 
    @click="handleAICalculation"
    :disabled="isCalculating"
    class="btn btn-primary"
  >
    <span v-if="isCalculating">計算中...</span>
    <span v-else>AI計算</span>
  </button>
</template>
```

#### ステップ3: ハンドラー実装
```javascript
async handleAICalculation() {
  this.isCalculating = true;
  this.calculationError = null;
  
  try {
    const response = await calculateRoute({
      pickup_location: this.form.pickup_location,
      delivery_location: this.form.delivery_location,
      return_location: this.form.return_location,
      start_time: this.form.start_time,
      vehicle_type: this.form.vehicle_type || '4t',
      loading_time: this.form.loading_time || 60,
      unloading_time: this.form.unloading_time || 60
    });
    
    // 結果を自動入力
    this.form.distance = response.data.data.summary.total_distance_km;
    this.form.end_time = response.data.data.summary.estimated_end_time;
    this.form.highway_fee = response.data.data.summary.highway_fee;
    
    this.$message.success('計算が完了しました');
    
  } catch (error) {
    this.calculationError = error.response?.data?.message || 'エラーが発生しました';
    this.$message.error(this.calculationError);
  } finally {
    this.isCalculating = false;
  }
}
```

---

## Tiếng Việt / Vietnamese

### Issue cha

Liên quan đến #499

### Mô tả

Tích hợp chức năng tính toán lộ trình AI vào màn hình báo giá. Khi người dùng click nút "AI計算" (Tính toán AI), hệ thống sẽ gọi Backend API và tự động điền kết quả (khoảng cách, thời gian, phí cao tốc) vào form.

### Yêu cầu

#### 1. UI Components
- Thêm nút "AI計算" vào form báo giá
- Hiển thị trạng thái loading (đang tính toán: 10-30 giây)
- Hiển thị thông báo lỗi (khi API fail)
- Hiển thị thông báo thành công (khi tính toán xong)

#### 2. Tích hợp API
- Gọi endpoint `POST /api/quotation/routes/calculate`
- Xây dựng request payload:
  - pickup_location (điểm lấy hàng)
  - delivery_location (điểm giao hàng)
  - return_location (điểm về)
  - start_time (thời gian xuất phát)
  - vehicle_type (loại xe)
  - loading_time (thời gian bốc hàng)
  - unloading_time (thời gian dỡ hàng)

#### 3. Xử lý Response
- Tự động điền kết quả vào các trường:
  - `total_distance_km` → Trường nhập khoảng cách
  - `estimated_end_time` → Trường thời gian kết thúc
  - `highway_fee` → Trường phí cao tốc
- Hiển thị thông tin chi tiết (tùy chọn):
  - Tổng thời gian làm việc (total_duty_time_hours)
  - Thời gian làm việc thực tế (actual_working_hours)
  - Thời gian nghỉ ngơi (total_break_time_minutes)
  - Tuân thủ luật (is_compliant)

#### 4. Xử lý Lỗi
- Hiển thị lỗi khi API call fail
- Xử lý timeout (120 giây)
- Hiển thị lỗi validation
- Xử lý lỗi network

#### 5. Cải thiện UX
- Disable nút khi đang tính toán
- Hiển thị progress indicator
- Cập nhật field mượt mà sau khi tính toán xong
- Xác nhận ghi đè dữ liệu cũ (tùy chọn)

### Chi tiết kỹ thuật

**Công nghệ sử dụng:**
- Vue.js / Nuxt.js (Frontend framework)
- Axios / Fetch API (HTTP client)
- Vuex / Pinia (State management - tùy chọn)

**Vị trí implement:**
- Component form báo giá (QuotationForm.vue hoặc tương tự)
- API client module (api/quotation.js hoặc tương tự)

**Ví dụ API Request:**
```javascript
const response = await axios.post('/api/quotation/routes/calculate', {
  pickup_location: '東京都千代田区...',
  delivery_location: '神奈川県横浜市...',
  return_location: '埼玉県さいたま市...',
  start_time: '01:00',
  vehicle_type: '4t',
  loading_time: 60,
  unloading_time: 60
});

// Xử lý response
if (response.data.success) {
  form.distance = response.data.data.summary.total_distance_km;
  form.end_time = response.data.data.summary.estimated_end_time;
  form.highway_fee = response.data.data.summary.highway_fee;
}
```

**Quản lý state:**
```javascript
data() {
  return {
    isCalculating: false,
    calculationError: null,
    calculationResult: null
  }
}
```

### Tiêu chí chấp nhận

- [ ] Implement xong UI nút "AI計算"
- [ ] Implement xong chức năng gọi API
- [ ] Implement xong hiển thị loading state
- [ ] Implement xong tự động điền kết quả
- [ ] Implement xong xử lý lỗi
- [ ] Implement xong xử lý timeout
- [ ] Hoàn thành usability test
- [ ] Responsive design hoàn chỉnh
- [ ] Kiểm tra tương thích browser (Chrome, Firefox, Safari, Edge)
- [ ] Tạo và pass Frontend unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc

**Blocking:**
- #501 (Backend issue) phải hoàn thành và API endpoint phải sẵn sàng

**Integration Testing:**
- Sau khi Backend API hoạt động bình thường, thực hiện integration test

### Hướng dẫn Implementation

#### Bước 1: Tạo API Client
```javascript
// api/quotation.js
export const calculateRoute = (data) => {
  return axios.post('/api/quotation/routes/calculate', data);
};
```

#### Bước 2: Thêm Button vào Component
```vue
<template>
  <button 
    @click="handleAICalculation"
    :disabled="isCalculating"
    class="btn btn-primary"
  >
    <span v-if="isCalculating">Đang tính toán...</span>
    <span v-else>AI計算</span>
  </button>
</template>
```

#### Bước 3: Implement Handler
```javascript
async handleAICalculation() {
  this.isCalculating = true;
  this.calculationError = null;
  
  try {
    const response = await calculateRoute({
      pickup_location: this.form.pickup_location,
      delivery_location: this.form.delivery_location,
      return_location: this.form.return_location,
      start_time: this.form.start_time,
      vehicle_type: this.form.vehicle_type || '4t',
      loading_time: this.form.loading_time || 60,
      unloading_time: this.form.unloading_time || 60
    });
    
    // Tự động điền kết quả
    this.form.distance = response.data.data.summary.total_distance_km;
    this.form.end_time = response.data.data.summary.estimated_end_time;
    this.form.highway_fee = response.data.data.summary.highway_fee;
    
    this.$message.success('Tính toán hoàn tất');
    
  } catch (error) {
    this.calculationError = error.response?.data?.message || 'Đã xảy ra lỗi';
    this.$message.error(this.calculationError);
  } finally {
    this.isCalculating = false;
  }
}
```

