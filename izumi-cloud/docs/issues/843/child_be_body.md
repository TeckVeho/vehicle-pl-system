## 日本語 / Japanese

### 親Issue
Parent: #843

### 説明
コースマスタに「コース先住所」（course_address）フィールドを追加するバックエンド実装です。マイグレーションでカラム追加、Model・Repository・Request の修正、および既存 API で course_address を保存・取得できるようにします。既存データは nullable で互換性を保ちます。

### 要件
- courses テーブルに course_address（string, nullable）を追加するマイグレーションを作成・実行する。
- Course モデルの $fillable に course_address を追加する。
- app/constants.php の QUERY_COURSE_MUST_SELECT に courses.course_address を追加する。
- CourseRepository の update() で course_address を保存する（create は fillable で自動対応）。
- CourseRequest の store/update ルールに course_address: nullable|string|max:255 を追加し、attributes() にラベルを追加する。
- （任意）CourseFactory に course_address を追加する。
- 実装後、ユニットテストを作成・実行し、合格させる。

### 技術詳細
- **Migration:** `php artisan make:migration add_course_address_to_courses_table --table=courses`。up(): `$table->string('course_address')->nullable();`、down(): `$table->dropColumn('course_address');`
- **Model:** `app/Models/Course.php` の $fillable に `'course_address'` を追加。
- **Constants:** `app/constants.php` の QUERY_COURSE_MUST_SELECT 配列に `"courses.course_address"` を追加。
- **Repository:** `app/Repositories/CourseRepository.php` の update() で `$course->course_address = $attributes['course_address'] ?? null;` を追加。
- **Request:** `app/Http/Requests/CourseRequest.php` の getCustomRule() の store/update 両方に `'course_address' => 'nullable|string|max:255'`、attributes() に `'course_address' => 'コース先住所'`。
- 実装リポジトリ: izumi-cloud（backend_path: .）

### 受け入れ基準
- [ ] マイグレーション作成・実行完了
- [ ] Model / constants / Repository / Request の修正完了
- [ ] コース作成・更新 API で course_address が保存・返却されること
- [ ] ユニットテスト作成・合格
- [ ] プロジェクト規約に準拠
- [ ] 既存機能への破壊的変更なし

### 依存関係
なし（単体で開発可能）。FE は本 issue 完了後の統合テストで API を利用。

---

## Tiếng Việt / Vietnamese

### Issue cha
Parent: #843

### Mô tả
Triển khai backend thêm trường「コース先住所」(course_address) vào Course master: thêm cột bằng migration, chỉnh Model/Repository/Request, đảm bảo API hiện tại lưu và trả về course_address. Dữ liệu cũ tương thích (nullable).

### Yêu cầu
- Tạo và chạy migration thêm cột course_address (string, nullable) vào bảng courses.
- Thêm course_address vào $fillable của model Course.
- Thêm courses.course_address vào QUERY_COURSE_MUST_SELECT trong app/constants.php.
- Trong CourseRepository update() lưu course_address (create tự xử lý qua fillable).
- Trong CourseRequest thêm rule store/update: course_address: nullable|string|max:255 và thêm attribute trong attributes().
- (Tùy chọn) Thêm course_address vào CourseFactory.
- Sau khi triển khai, tạo và chạy unit test, đảm bảo pass.

### Chi tiết kỹ thuật
- **Migration:** `php artisan make:migration add_course_address_to_courses_table --table=courses`. up(): `$table->string('course_address')->nullable();`, down(): `$table->dropColumn('course_address');`
- **Model:** Thêm `'course_address'` vào $fillable trong `app/Models/Course.php`.
- **Constants:** Thêm `"courses.course_address"` vào mảng QUERY_COURSE_MUST_SELECT trong `app/constants.php`.
- **Repository:** Trong update() của `app/Repositories/CourseRepository.php` thêm `$course->course_address = $attributes['course_address'] ?? null;`.
- **Request:** Trong getCustomRule() (store và update) thêm `'course_address' => 'nullable|string|max:255'`, trong attributes() thêm `'course_address' => 'コース先住所'`.
- Repo triển khai: izumi-cloud (backend_path: .)

### Tiêu chí chấp nhận
- [ ] Hoàn thành tạo và chạy migration
- [ ] Hoàn thành sửa Model / constants / Repository / Request
- [ ] API tạo/cập nhật course lưu và trả về course_address
- [ ] Tạo và vượt qua unit tests
- [ ] Tuân thủ quy ước dự án
- [ ] Không có thay đổi phá vỡ chức năng hiện có

### Phụ thuộc
Không (có thể phát triển độc lập). FE sẽ dùng API này khi test tích hợp sau khi issue này hoàn thành.
