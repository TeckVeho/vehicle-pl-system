# Issue #507: [FE] 所定労働時間の設定: UI表示・入力・統合 - Implementation Plan

## 概要 (Overview)

**現在の状態:**
- EmployeeMasterのDetail ViewとEdit Viewには勤務情報モーダルがあり、所定労働時間表（時・分）や深夜労働時間（時・分）などのフィールドが表示・編集可能
- 所定労働時間の開始時刻と終了時刻を表示・編集する機能がない

**実装後の状態:**
- Detail Modalに所定労働時間（始）と（終）が表示される（読み取り専用）
- Edit Modalに所定労働時間（始）と（終）の入力フィールドがあり、`b-form-timepicker`で1分単位の時刻選択が可能
- 24時間形式で表示され、日本語ロケールが適用される
- データはAPIから取得・送信され、NULL値は適切に処理される（placeholder: "--:--"）

**関連Issue:**
- Parent: #504
- Backend dependency: #506 (統合テスト前に完了必須)

---

## FE (Frontend)

### 1. Files need to edit:

#### 1.1. File: `resources/js/pages/EmployeeMaster/detail.vue`

このファイルはEmployeeMasterのDetail画面で、従業員の基本情報、勤務情報、デバイス情報、備品情報を表示します。

##### 1.1.1. Detail Modal - 所定労働時間フィールドの追加

Detail Modalに所定労働時間（始）と（終）の表示フィールドを追加します。

**既存コード** (line 404-424):
```vue
<b-row class="mt-3">
  <b-col cols="6">
    <label for="scheduled-labor-table-hour">{{ $t('SCHEDULED_LABOR_TABLE') }} (時)</label>
    <b-form-input
      id="scheduled-labor-table-hour"
      :value="detailModalData.scheduled_labor_table_hour"
      :placeholder="'(時)'"
      disabled
    />
  </b-col>

  <b-col cols="6">
    <label for="scheduled-labor-table-minute">{{ $t('SCHEDULED_LABOR_TABLE') }} (分)</label>
    <b-form-input
      id="scheduled-labor-table-minute"
      :value="detailModalData.scheduled_labor_table_minute"
      :placeholder="'(分)'"
      disabled
    />
  </b-col>
</b-row>
```

**変更内容:**
- Line 424の後に新しい`<b-row>`を追加して、所定労働時間（始）と（終）のフィールドを追加
- 形式: `HH:mm` (24時間形式)
- Placeholder: `"--:--"` (値がNULLの場合)
- 読み取り専用フィールド（disabled）

**追加するコード:**
```vue
<b-row class="mt-3">
  <b-col cols="6">
    <label for="scheduled-work-start-time">所定労働時間（始）</label>
    <b-form-input
      id="scheduled-work-start-time"
      :value="detailModalData.scheduled_work_start_time || '--:--'"
      :placeholder="'--:--'"
      disabled
    />
  </b-col>

  <b-col cols="6">
    <label for="scheduled-work-end-time">所定労働時間（終）</label>
    <b-form-input
      id="scheduled-work-end-time"
      :value="detailModalData.scheduled_work_end_time || '--:--'"
      :placeholder="'--:--'"
      disabled
    />
  </b-col>
</b-row>
```

##### 1.1.2. Data Structure - detailModalData初期化

`detailModalData`オブジェクトに新しいフィールドを追加します。

**現在の実装** (line 1041-1059):
```javascript
detailModalData: {
  department_working_id: '',
  department_working_name: '',
  employee_grade: '',
  driveable_route: [],
  support_end_date: '',
  employee_grade_2: '',
  support_start_date: '',
  boarding_employee_grade: '',
  boarding_employee_grade_2: '',
  midnight_working_time_hour: '',
  scheduled_labor_table_hour: '',
  transportation_compensation: '',
  midnight_working_time_minute: '',
  scheduled_labor_table_minute: '',
  daily_transportation_compensation: '',
  support_date_list: [],
  temp_wage: null,
},
```

**変更内容:**
- `scheduled_work_start_time: ''` を追加
- `scheduled_work_end_time: ''` を追加
- 初期値は空文字列

**更新後のコード:**
```javascript
detailModalData: {
  department_working_id: '',
  department_working_name: '',
  employee_grade: '',
  driveable_route: [],
  support_end_date: '',
  employee_grade_2: '',
  support_start_date: '',
  boarding_employee_grade: '',
  boarding_employee_grade_2: '',
  midnight_working_time_hour: '',
  scheduled_labor_table_hour: '',
  transportation_compensation: '',
  midnight_working_time_minute: '',
  scheduled_labor_table_minute: '',
  daily_transportation_compensation: '',
  support_date_list: [],
  temp_wage: null,
  scheduled_work_start_time: '',
  scheduled_work_end_time: '',
},
```

##### 1.1.3. API Data Mapping - handleGetDepartmentWorkingDetail()更新

APIからデータを取得する際に新しいフィールドをマッピングします。

**現在の実装** (line 1405-1444):
```javascript
async handleGetDepartmentWorkingDetail(ID) {
  try {
    const URL = `${urlAPIs.apiGetDepartmentWorking}`;
    
    const PARAMS = {
      employee_id: this.$route.params.id,
      department_working_id: ID,
    };
    
    const response = await getDepartmentWorking(URL, PARAMS);
    
    if (response.code === 200) {
      const EMPLOYEE_DATA_DETAIL = response.data.employee_data;
      
      if (EMPLOYEE_DATA_DETAIL) {
        this.detailModalData.employee_grade = EMPLOYEE_DATA_DETAIL.grade || '';
        this.detailModalData.employee_grade_2 = EMPLOYEE_DATA_DETAIL.employee_grade_2 || '';
        // ... 他のフィールド
        this.detailModalData.scheduled_labor_table_hour = EMPLOYEE_DATA_DETAIL.scheduled_labor_hour || 0;
        this.detailModalData.scheduled_labor_table_minute = EMPLOYEE_DATA_DETAIL.scheduled_labor_minutes || 0;
        
        this.detailModalData.driveable_route = EMPLOYEE_ROUTES_DETAIL || [];
        this.detailModalData.support_date_list = EMPLOYEE_WORKING_DEPARTMENT_DETAIL || [];
        this.detailModalData.temp_wage = parseInt(EMPLOYEE_DATA_DETAIL.temp_wage) || null;
      }
    }
  } catch (error) {
    console.log(error);
  }
}
```

**変更内容:**
- Line 1433の後（`scheduled_labor_minutes`の割り当て後）に新しいフィールドのマッピングを追加
- APIから`scheduled_work_start_time`と`scheduled_work_end_time`を取得
- NULL値の場合は空文字列を設定

**追加するコード (line 1433の後):**
```javascript
this.detailModalData.scheduled_work_start_time = EMPLOYEE_DATA_DETAIL.scheduled_work_start_time || '';
this.detailModalData.scheduled_work_end_time = EMPLOYEE_DATA_DETAIL.scheduled_work_end_time || '';
```

##### 1.1.4. Modal Reset - handleCloseModalDetail()更新

Detail Modalを閉じる際にデータをリセットします。

**現在の実装** (line 1672-1689):
```javascript
handleCloseModalDetail() {
  this.detailModalData = {
    employee_grade: '',
    driveable_route: [],
    support_end_date: '',
    employee_grade_2: '',
    support_start_date: '',
    boarding_employee_grade: '',
    boarding_employee_grade_2: '',
    midnight_working_time_hour: '',
    scheduled_labor_table_hour: '',
    transportation_compensation: '',
    midnight_working_time_minute: '',
    scheduled_labor_table_minute: '',
    daily_transportation_compensation: '',
    support_date_list: [],
    temp_wage: null,
  };
},
```

**変更内容:**
- 新しいフィールドをリセット対象に追加

**更新後のコード:**
```javascript
handleCloseModalDetail() {
  this.detailModalData = {
    employee_grade: '',
    driveable_route: [],
    support_end_date: '',
    employee_grade_2: '',
    support_start_date: '',
    boarding_employee_grade: '',
    boarding_employee_grade_2: '',
    midnight_working_time_hour: '',
    scheduled_labor_table_hour: '',
    transportation_compensation: '',
    midnight_working_time_minute: '',
    scheduled_labor_table_minute: '',
    daily_transportation_compensation: '',
    support_date_list: [],
    temp_wage: null,
    scheduled_work_start_time: '',
    scheduled_work_end_time: '',
  };
},
```

---

#### 1.2. File: `resources/js/pages/EmployeeMaster/edit.vue`

このファイルはEmployeeMasterの編集画面で、従業員情報の編集と勤務情報の更新を行います。

##### 1.2.1. Detail Modal - 所定労働時間フィールドの追加（読み取り専用）

Detail Modalに所定労働時間（始）と（終）の表示フィールドを追加します（detail.vueと同様）。

**既存コード** (line 396-416):
```vue
<b-row class="mt-3">
  <b-col cols="6">
    <label for="scheduled-labor-table-hour">{{ $t('SCHEDULED_LABOR_TABLE') }} (時)</label>
    <b-form-input
      id="scheduled-labor-table-hour"
      :value="detailModalData.scheduled_labor_table_hour"
      :placeholder="'(時)'"
      disabled
    />
  </b-col>

  <b-col cols="6">
    <label for="scheduled-labor-table-minute">{{ $t('SCHEDULED_LABOR_TABLE') }} (分)</label>
    <b-form-input
      id="scheduled-labor-table-minute"
      :value="detailModalData.scheduled_labor_table_minute"
      :placeholder="'(分)'"
      disabled
    />
  </b-col>
</b-row>
```

**変更内容:**
- Line 416の後に新しい`<b-row>`を追加（detail.vueと同じ形式）
- 読み取り専用フィールド

**追加するコード:**
```vue
<b-row class="mt-3">
  <b-col cols="6">
    <label for="scheduled-work-start-time">所定労働時間（始）</label>
    <b-form-input
      id="scheduled-work-start-time"
      :value="detailModalData.scheduled_work_start_time || '--:--'"
      :placeholder="'--:--'"
      disabled
    />
  </b-col>

  <b-col cols="6">
    <label for="scheduled-work-end-time">所定労働時間（終）</label>
    <b-form-input
      id="scheduled-work-end-time"
      :value="detailModalData.scheduled_work_end_time || '--:--'"
      :placeholder="'--:--'"
      disabled
    />
  </b-col>
</b-row>
```

##### 1.2.2. Edit Modal - Time Pickerフィールドの追加

Edit Modalに所定労働時間（始）と（終）のtime pickerを追加します。

**既存コード** (line 667-697):
```vue
<b-row class="mt-3">
  <b-col cols="6">
    <label for="select-scheduled-labor-table-hour">{{ $t('SCHEDULED_LABOR_TABLE') }} (時)</label>
    
    <b-input-group append="時">
      <b-form-select
        id="select-scheduled-labor-table-hour"
        v-model="editModalData.scheduled_labor_table_hour"
        :options="listTimeHour"
        placeholder="選択してください"
      />
    </b-input-group>
  </b-col>

  <b-col cols="6">
    <label for="select-scheduled-labor-table-minute">{{ $t('SCHEDULED_LABOR_TABLE') }} (分)</label>
    
    <b-input-group append="分">
      <b-form-select
        id="select-scheduled-labor-table-minute"
        v-model="editModalData.scheduled_labor_table_minute"
        :options="listTimeMinute"
        placeholder="選択してください"
      />
    </b-input-group>
  </b-col>
</b-row>
```

**変更内容:**
- Line 697の後に新しい`<b-row>`を追加
- `b-form-timepicker`を使用（1分単位の選択が可能）
- 24時間形式（`:hour12="false"`）
- 日本語ロケール（`:locale="lang"`）
- Placeholder: "選択してください"
- 秒は非表示だが内部的に0秒に設定

**追加するコード:**
```vue
<b-row class="mt-3">
  <b-col cols="6">
    <label for="scheduled-work-start-time">所定労働時間（始）</label>
    <b-form-timepicker
      id="scheduled-work-start-time"
      v-model="editModalData.scheduled_work_start_time"
      :locale="lang"
      :hour12="false"
      show-seconds
      :seconds="0"
      placeholder="選択してください"
    />
  </b-col>

  <b-col cols="6">
    <label for="scheduled-work-end-time">所定労働時間（終）</label>
    <b-form-timepicker
      id="scheduled-work-end-time"
      v-model="editModalData.scheduled_work_end_time"
      :locale="lang"
      :hour12="false"
      show-seconds
      :seconds="0"
      placeholder="選択してください"
    />
  </b-col>
</b-row>
```

##### 1.2.3. Data Structure - detailModalData初期化

`detailModalData`オブジェクトに新しいフィールドを追加します（detail.vueと同様）。

**現在の実装** (line 980-998):
```javascript
detailModalData: {
  department_working_id: '',
  department_working_name: '',
  employee_grade: '',
  driveable_route: [],
  support_end_date: '',
  employee_grade_2: '',
  support_start_date: '',
  boarding_employee_grade: '',
  boarding_employee_grade_2: '',
  midnight_working_time_hour: '',
  scheduled_labor_table_hour: '',
  transportation_compensation: '',
  midnight_working_time_minute: '',
  scheduled_labor_table_minute: '',
  daily_transportation_compensation: '',
  support_date_list: [],
  temp_wage: null,
},
```

**変更内容:**
- 新しいフィールドを追加

**更新後のコード:**
```javascript
detailModalData: {
  department_working_id: '',
  department_working_name: '',
  employee_grade: '',
  driveable_route: [],
  support_end_date: '',
  employee_grade_2: '',
  support_start_date: '',
  boarding_employee_grade: '',
  boarding_employee_grade_2: '',
  midnight_working_time_hour: '',
  scheduled_labor_table_hour: '',
  transportation_compensation: '',
  midnight_working_time_minute: '',
  scheduled_labor_table_minute: '',
  daily_transportation_compensation: '',
  support_date_list: [],
  temp_wage: null,
  scheduled_work_start_time: '',
  scheduled_work_end_time: '',
},
```

##### 1.2.4. Data Structure - editModalData初期化

`editModalData`オブジェクトに新しいフィールドを追加します。

**現在の実装** (line 1000-1018):
```javascript
editModalData: {
  department_working_id: '',
  department_working_name: '',
  employee_grade: '',
  driveable_route: [],
  support_end_date: '',
  employee_grade_2: '',
  support_start_date: '',
  boarding_employee_grade: '',
  boarding_employee_grade_2: '',
  midnight_working_time_hour: '',
  scheduled_labor_table_hour: '',
  transportation_compensation: '',
  midnight_working_time_minute: '',
  scheduled_labor_table_minute: '',
  daily_transportation_compensation: '',
  support_date_list: [],
  temp_wage: null,
},
```

**変更内容:**
- 新しいフィールドを追加

**更新後のコード:**
```javascript
editModalData: {
  department_working_id: '',
  department_working_name: '',
  employee_grade: '',
  driveable_route: [],
  support_end_date: '',
  employee_grade_2: '',
  support_start_date: '',
  boarding_employee_grade: '',
  boarding_employee_grade_2: '',
  midnight_working_time_hour: '',
  scheduled_labor_table_hour: '',
  transportation_compensation: '',
  midnight_working_time_minute: '',
  scheduled_labor_table_minute: '',
  daily_transportation_compensation: '',
  support_date_list: [],
  temp_wage: null,
  scheduled_work_start_time: '',
  scheduled_work_end_time: '',
},
```

##### 1.2.5. API Data Mapping (Detail) - handleGetDepartmentWorkingDetail()更新

Detail ModalでAPIからデータを取得する際のマッピング。

**現在の実装** (line 1338-1378):
```javascript
async handleGetDepartmentWorkingDetail(ID) {
  try {
    const URL = `${urlAPIs.apiGetDepartmentWorking}`;
    
    const PARAMS = {
      employee_id: this.$route.params.id,
      department_working_id: ID,
    };
    
    const response = await getDepartmentWorking(URL, PARAMS);
    
    if (response.code === 200) {
      const EMPLOYEE_DATA_DETAIL = response.data.employee_data;
      
      if (EMPLOYEE_DATA_DETAIL) {
        this.detailModalData.employee_grade = EMPLOYEE_DATA_DETAIL.grade || '';
        // ... 他のフィールド
        this.detailModalData.scheduled_labor_table_hour = EMPLOYEE_DATA_DETAIL.scheduled_labor_hour || 0;
        this.detailModalData.scheduled_labor_table_minute = EMPLOYEE_DATA_DETAIL.scheduled_labor_minutes || 0;
        
        this.detailModalData.driveable_route = EMPLOYEE_ROUTES_DETAIL || [];
        this.detailModalData.support_date_list = EMPLOYEE_WORKING_DEPARTMENT_DETAIL || [];
        this.detailModalData.temp_wage = parseInt(EMPLOYEE_DATA_DETAIL.temp_wage) || null;
      }
    }
  } catch (error) {
    console.log(error);
  }
}
```

**変更内容:**
- Line 1366の後（`scheduled_labor_minutes`の後）に新しいフィールドを追加

**追加するコード (line 1366の後):**
```javascript
this.detailModalData.scheduled_work_start_time = EMPLOYEE_DATA_DETAIL.scheduled_work_start_time || '';
this.detailModalData.scheduled_work_end_time = EMPLOYEE_DATA_DETAIL.scheduled_work_end_time || '';
```

##### 1.2.6. API Data Mapping (Edit) - handleGetDepartmentWorkingEdit()更新

Edit ModalでAPIからデータを取得する際のマッピング。

**現在の実装** (line 1401-1456):
```javascript
async handleGetDepartmentWorkingEdit(ID) {
  try {
    const URL = `${urlAPIs.apiGetDepartmentWorking}`;
    
    const PARAMS = {
      employee_id: this.$route.params.id,
      department_working_id: ID,
    };
    
    const response = await getDepartmentWorking(URL, PARAMS);
    
    if (response.code === 200) {
      const EMPLOYEE_DATA = response.data.employee_data;
      
      if (EMPLOYEE_DATA) {
        this.editModalData.employee_grade = EMPLOYEE_DATA.grade;
        // ... 他のフィールド
        this.editModalData.scheduled_labor_table_hour = EMPLOYEE_DATA.scheduled_labor_hour || 0;
        this.editModalData.scheduled_labor_table_minute = EMPLOYEE_DATA.scheduled_labor_minutes || 0;
        this.editModalData.temp_wage = parseInt(EMPLOYEE_DATA.temp_wage) || null;
      }
      
      // listRouteMaster, listSelectedの処理...
    }
  } catch (error) {
    console.log(error);
  }
}
```

**変更内容:**
- Line 1430の後（`scheduled_labor_minutes`の後、`temp_wage`の前）に新しいフィールドを追加

**追加するコード (line 1430の後):**
```javascript
this.editModalData.scheduled_work_start_time = EMPLOYEE_DATA.scheduled_work_start_time || '';
this.editModalData.scheduled_work_end_time = EMPLOYEE_DATA.scheduled_work_end_time || '';
```

##### 1.2.7. API Data Submit - handleSaveButtonClicked()更新

Edit Modalで保存する際にAPIに新しいデータを送信します。

**現在の実装** (line 1495-1549):
```javascript
async handleSaveButtonClicked() {
  const URL = `${urlAPIs.apiPostEmployee}/${this.$route.params.id}`;
  
  const UPDATE_DATA = {
    department_working_id: this.editModalData.department_working_id,
    grade: this.editModalData.employee_grade,
    employee_grade_2: this.editModalData.employee_grade_2,
    boarding_employee_grade: this.editModalData.boarding_employee_grade,
    boarding_employee_grade_2: this.editModalData.boarding_employee_grade_2,
    transportation_compensation: this.editModalData.transportation_compensation || 0,
    daily_transportation_cp: this.editModalData.daily_transportation_compensation || 0,
    midnight_worktime_hour: this.editModalData.midnight_working_time_hour || 0,
    midnight_worktime_minutes: this.editModalData.midnight_working_time_minute || 0,
    scheduled_labor_hour: this.editModalData.scheduled_labor_table_hour || 0,
    scheduled_labor_minutes: this.editModalData.scheduled_labor_table_minute || 0,
    working_date: this.listSupportDate,
    employee_courses: this.listSelected,
    temp_wage: parseInt(this.editModalData.temp_wage) || null,
  };
  
  const validate = this.handleValidateEdit(UPDATE_DATA);
  
  if (validate === null) {
    try {
      const response = await postEmployee(URL, UPDATE_DATA);
      // ... 成功処理
    } catch (error) {
      // ... エラー処理
    }
  }
}
```

**変更内容:**
- Line 1509の後（`scheduled_labor_minutes`の後）に新しいフィールドを追加
- NULL値の場合は空文字列またはnullを送信

**追加するコード (line 1509の後):**
```javascript
scheduled_work_start_time: this.editModalData.scheduled_work_start_time || null,
scheduled_work_end_time: this.editModalData.scheduled_work_end_time || null,
```

##### 1.2.8. Modal Reset (Detail) - handleCloseModalDetail()更新

Detail Modalを閉じる際のリセット処理。

**現在の実装** (line 1605-1623):
```javascript
handleCloseModalDetail() {
  this.detailModalData = {
    employee_grade: '',
    driveable_route: [],
    support_end_date: '',
    employee_grade_2: '',
    support_start_date: '',
    boarding_employee_grade: '',
    boarding_employee_grade_2: '',
    midnight_working_time_hour: '',
    scheduled_labor_table_hour: '',
    transportation_compensation: '',
    midnight_working_time_minute: '',
    scheduled_labor_table_minute: '',
    daily_transportation_compensation: '',
    support_date_list: [],
    temp_wage: null,
  };
},
```

**変更内容:**
- 新しいフィールドをリセット対象に追加

**更新後のコード:**
```javascript
handleCloseModalDetail() {
  this.detailModalData = {
    employee_grade: '',
    driveable_route: [],
    support_end_date: '',
    employee_grade_2: '',
    support_start_date: '',
    boarding_employee_grade: '',
    boarding_employee_grade_2: '',
    midnight_working_time_hour: '',
    scheduled_labor_table_hour: '',
    transportation_compensation: '',
    midnight_working_time_minute: '',
    scheduled_labor_table_minute: '',
    daily_transportation_compensation: '',
    support_date_list: [],
    temp_wage: null,
    scheduled_work_start_time: '',
    scheduled_work_end_time: '',
  };
},
```

##### 1.2.9. Modal Reset (Edit) - handleCloseModalEdit()更新

Edit Modalを閉じる際のリセット処理。

**現在の実装** (line 1577-1604):
```javascript
handleCloseModalEdit() {
  this.editModalData = {
    department_working_id: '',
    department_working_name: '',
    employee_grade: '',
    driveable_route: [],
    support_end_date: '',
    employee_grade_2: '',
    support_start_date: '',
    boarding_employee_grade: '',
    boarding_employee_grade_2: '',
    midnight_working_time_hour: '',
    scheduled_labor_table_hour: '',
    transportation_compensation: '',
    midnight_working_time_minute: '',
    scheduled_labor_table_minute: '',
    daily_transportation_compensation: '',
    support_date_list: [],
    temp_wage: null,
  };
  
  this.support_start_date = '';
  this.support_end_date = '';
  
  this.listSelected = [];
  this.listSupportDate = [];
  this.listRouteMaster = [];
},
```

**変更内容:**
- 新しいフィールドをリセット対象に追加

**更新後のコード:**
```javascript
handleCloseModalEdit() {
  this.editModalData = {
    department_working_id: '',
    department_working_name: '',
    employee_grade: '',
    driveable_route: [],
    support_end_date: '',
    employee_grade_2: '',
    support_start_date: '',
    boarding_employee_grade: '',
    boarding_employee_grade_2: '',
    midnight_working_time_hour: '',
    scheduled_labor_table_hour: '',
    transportation_compensation: '',
    midnight_working_time_minute: '',
    scheduled_labor_table_minute: '',
    daily_transportation_compensation: '',
    support_date_list: [],
    temp_wage: null,
    scheduled_work_start_time: '',
    scheduled_work_end_time: '',
  };
  
  this.support_start_date = '';
  this.support_end_date = '';
  
  this.listSelected = [];
  this.listSupportDate = [];
  this.listRouteMaster = [];
},
```

---

## BE (Backend)

### ⚠️ Backend Dependency Notice

このFrontend実装は**Backend Issue #506**に依存しています。

Backend実装が完了している必要がある項目:
1. **Database Migration**: `scheduled_work_start_time`と`scheduled_work_end_time`カラムの追加
2. **API Response**: GET `/api/employee/dp-working` が新しいフィールドを返す
3. **API Request**: PUT `/api/employee/{id}` が新しいフィールドを受け取る
4. **Data Validation**: 時刻形式のバリデーション（HH:mm:ss形式）

**統合テスト前の確認事項:**
- Backend APIが正常に新しいフィールドを返すか確認
- Backend APIが正常に新しいフィールドを保存するか確認
- NULL値の処理が適切か確認

---

## 実装順序 (Implementation Order)

### Phase 1: detail.vue の実装 (Backend未完了でも可能)

1. **Data Structure 準備**
   - `detailModalData`に`scheduled_work_start_time`と`scheduled_work_end_time`を追加
   - `handleCloseModalDetail()`にリセット処理を追加

2. **UI実装**
   - Detail Modalに表示フィールドを追加（line 424の後）
   - Placeholder "--:--"の設定

3. **API Integration準備**
   - `handleGetDepartmentWorkingDetail()`にマッピング処理を追加（Backend完了待ち）

### Phase 2: edit.vue の実装 (Backend未完了でも可能)

1. **Data Structure 準備**
   - `detailModalData`と`editModalData`に新しいフィールドを追加
   - `handleCloseModalDetail()`と`handleCloseModalEdit()`にリセット処理を追加

2. **UI実装 - Detail Modal**
   - Detail Modalに表示フィールドを追加（line 416の後）

3. **UI実装 - Edit Modal**
   - Edit Modalに`b-form-timepicker`を追加（line 697の後）
   - 24時間形式、日本語ロケール設定

4. **API Integration準備**
   - `handleGetDepartmentWorkingDetail()`にマッピング処理を追加
   - `handleGetDepartmentWorkingEdit()`にマッピング処理を追加
   - `handleSaveButtonClicked()`にsubmit処理を追加

### Phase 3: 統合テスト (Backend #506完了後)

**前提条件:**
- Backend Issue #506が完了していること
- APIが新しいフィールドを返す・受け取ることができること

**テスト項目:**

1. **Detail Modal表示テスト**
   - detail.vueとedit.vueの両方でDetail Modalを開く
   - 所定労働時間（始）と（終）が正しく表示されるか確認
   - NULL値の場合に"--:--"が表示されるか確認
   - 既存の値がある場合に正しい形式（HH:mm）で表示されるか確認

2. **Edit Modal入力テスト**
   - edit.vueでEdit Modalを開く
   - Time pickerで時刻を選択できるか確認
   - 1分単位で選択できるか確認
   - 24時間形式で表示されるか確認
   - 日本語ロケールが適用されるか確認

3. **データ保存テスト**
   - 新しい時刻を入力して保存
   - APIに正しいデータが送信されるか確認（Network tab）
   - 保存後にDetail Modalで正しく表示されるか確認
   - NULL値（未入力）で保存できるか確認

4. **Responsive Design テスト**
   - Mobile view (< 768px)
   - Tablet view (768px - 1024px)
   - Desktop view (> 1024px)

5. **Cross-browser テスト**
   - Chrome (最新版)
   - Firefox (最新版)
   - Safari (最新版)

6. **Regression テスト**
   - 既存の勤務情報フィールドが正常に動作するか確認
   - 他のモーダル機能が正常に動作するか確認
   - データの保存・読み込みが正常に動作するか確認

---

## 見積もり工数 (Estimated Effort)

### Frontend実装

**detail.vue: 1.5-2時間**
- Data structure準備: 0.5時間
- UI実装（Detail Modal表示フィールド）: 0.5時間
- API integration: 0.3時間
- テスト・調整: 0.2-0.7時間

**edit.vue: 2.5-3時間**
- Data structure準備: 0.5時間
- UI実装（Detail Modal表示フィールド）: 0.3時間
- UI実装（Edit Modal time picker）: 1時間
- API integration: 0.5時間
- テスト・調整: 0.2-0.7時間

**統合テスト: 2-3時間** (Backend完了後)
- Detail Modal表示テスト: 0.5時間
- Edit Modal入力・保存テスト: 0.5時間
- Responsive design テスト: 0.5時間
- Cross-browser テスト: 0.5時間
- Regression テスト: 0.5-1時間
- Bug修正: 0-0.5時間

**合計: 6-8時間**

---

## 技術的な注意事項 (Technical Notes)

### 1. Time Picker設定

**Bootstrap Vue's b-form-timepicker:**
- 分単位の選択が可能（1分刻み）
- `show-seconds`プロパティでUIに秒を表示
- `:seconds="0"`で秒を常に0に設定
- `:hour12="false"`で24時間形式
- `:locale="lang"`で日本語ロケール適用

**データ形式:**
- APIから受け取る形式: `"HH:mm:ss"` または `"HH:mm"` (Backend仕様による)
- Time pickerの形式: `"HH:mm:ss"` (Bootstrap Vueのデフォルト)
- 表示形式: `"HH:mm"` (秒は非表示だが内部的に保持)

### 2. NULL値の処理

**Display (Detail Modal):**
```vue
:value="detailModalData.scheduled_work_start_time || '--:--'"
```

**Edit Modal:**
- Time pickerはv-modelでバインド
- 空の値はnullとして扱う
- Placeholder: "選択してください"

**API Submit:**
```javascript
scheduled_work_start_time: this.editModalData.scheduled_work_start_time || null,
```

### 3. データ整合性

**Validation考慮事項:**
- 開始時刻と終了時刻の前後関係チェックは不要（業務要件により）
- 24時間を超える場合の処理は不要（Time pickerが0-23時に制限）
- 必須フィールドではない（NULLを許可）

**Backend連携:**
- Backend Issue #506が実装する validation に依存
- Frontend側では基本的な形式チェックのみ

### 4. UX考慮

**ユーザビリティ:**
- 所定労働時間表（時・分）の下に配置（論理的な順序）
- Detail ModalとEdit Modalで一貫したUI
- Placeholderで入力方法を明示

**Accessibility:**
- labelとinputの紐付け（for/id属性）
- キーボード操作可能（Time pickerのデフォルト機能）
- スクリーンリーダー対応（Bootstrap Vueのデフォルト機能）

### 5. 既存機能との互換性

**影響を受けない機能:**
- 既存の勤務情報フィールド（所定労働時間表、深夜労働時間など）
- サポート期間の入力・編集
- ルートマスターの選択
- 備品情報の編集

**注意点:**
- Modal内のスクロール位置が変わる可能性あり
- 既存のデータ構造に新しいフィールドを追加するだけなので、既存機能への影響は最小限

### 6. パフォーマンス考慮

**最適化ポイント:**
- 新しいフィールドは軽量（文字列2つのみ）
- APIレスポンスサイズの増加は最小限
- Time pickerの初期化はBootstrap Vueが最適化済み

**レンダリング:**
- v-ifやv-showは不要（常に表示）
- computed propertyは不要（シンプルなデータバインディング）

---

**Generated**: 2025-12-18
**Author**: AI Agent
**Status**: Ready for Implementation
