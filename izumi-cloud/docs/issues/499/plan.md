# Issue #499: AQ_AI calculation - Implementation Plan

## 概要 (Overview)

**現状 (Current State):**
- Hệ thống quotation hiện tại có các bảng: `quotations`, `quotation_master_data`, `quotation_staff`
- User phải nhập thủ công các thông tin về khoảng cách, thời gian, phí cao tốc
- Không có tính năng tự động tính toán route dựa trên AI

**改善後 (Improved State):**
- Thêm tính năng AI calculation cho route planning
- User chỉ cần nhập: 積地 (pickup), 届け地 (delivery), 帰社地 (return), thời gian xuất phát
- AI tự động tính toán:
  - Khoảng cách quãng đường (total_distance_km)
  - Thời gian di chuyển (driving_time)
  - Thời gian làm việc và nghỉ ngơi (tuân thủ luật lao động Nhật Bản 2024)
  - Phí cao tốc (highway_fee)
  - Chi tiết từng đoạn đường
- Lưu kết quả vào database (normalized tables) và file JSON
- Hiển thị kết quả lên màn hình quotation

---

## BE (Backend)

### 1. Database Migration Files

#### 1.1. File: `database/migrations/2025_12_12_100000_create_quotation_routes_table.php`

##### 1.1.1. Tạo bảng `quotation_routes` (Bảng chính)

Tạo migration cho bảng lưu trữ kết quả tính toán route từ AI.

**変更内容:**

```php
Schema::create('quotation_routes', function (Blueprint $table) {
    $table->id();
    
    // Mã định danh
    $table->string('route_code', 50)->unique()->comment('QR-YYYYMMDD-XXX');
    $table->unsignedBigInteger('user_id')->comment('User thực hiện tính toán');
    $table->unsignedBigInteger('quotation_id')->nullable()->comment('FK đến bảng quotations');
    
    // Input từ user
    $table->string('title', 500)->nullable()->comment('Tiêu đề');
    $table->string('pickup_location', 500)->comment('積地 - Điểm bốc hàng');
    $table->string('delivery_location', 500)->comment('届け地 - Điểm giao hàng');
    $table->string('return_location', 500)->comment('帰社地 - Điểm về');
    $table->time('start_time')->comment('運行開始時間');
    $table->string('vehicle_type', 50)->default('4t')->comment('車両区分');
    $table->integer('loading_time_minutes')->default(60)->comment('積み込み作業時間');
    $table->integer('unloading_time_minutes')->default(60)->comment('荷下ろし作業時間');
    $table->integer('user_break_time_minutes')->nullable()->comment('休憩時間指定');
    
    // Output từ AI
    $table->decimal('total_distance_km', 10, 2)->nullable()->comment('総距離');
    $table->time('estimated_end_time')->nullable()->comment('終了予定時刻');
    $table->boolean('date_change')->default(false)->comment('日付変更フラグ');
    
    // Time breakdown
    $table->decimal('total_duty_time_hours', 5, 2)->nullable()->comment('拘束時間');
    $table->decimal('actual_working_hours', 5, 2)->nullable()->comment('実労働時間');
    $table->integer('total_driving_time_minutes')->nullable()->comment('総運転時間');
    $table->integer('total_handling_time_minutes')->nullable()->comment('荷役時間合計');
    $table->integer('total_break_time_minutes')->nullable()->comment('休憩時間合計');
    
    // Cost
    $table->decimal('highway_fee', 12, 2)->default(0)->comment('高速道路料金合計');
    $table->decimal('fuel_cost', 12, 2)->default(0)->comment('燃料費');
    $table->decimal('estimated_total_cost', 12, 2)->default(0)->comment('総費用見積');
    
    // Compliance
    $table->boolean('is_compliant')->default(true)->comment('法令基準を満たすか');
    $table->text('applied_rule')->nullable()->comment('適用したルールの説明');
    
    // Metadata
    $table->string('ai_model_used', 50)->nullable()->comment('AI model');
    $table->integer('calculation_duration_seconds')->nullable()->comment('計算時間');
    $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
    $table->text('error_message')->nullable()->comment('エラーメッセージ');
    $table->text('notes')->nullable()->comment('備考');
    
    $table->timestamps();
    
    // Indexes
    $table->index(['user_id', 'created_at']);
    $table->index('route_code');
    $table->index('status');
    $table->index('quotation_id');
});
```

#### 1.2. File: `database/migrations/2025_12_12_100001_create_quotation_route_locations_table.php`

##### 1.2.1. Tạo bảng `quotation_route_locations` (Các điểm dừng)

Tạo migration cho bảng lưu chi tiết các điểm dừng trong route.

**変更内容:**

```php
Schema::create('quotation_route_locations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('route_id')->comment('FK đến quotation_routes');
    
    // Thứ tự và loại
    $table->tinyInteger('sequence_order')->unsigned()->comment('Thứ tự: 1, 2, 3...');
    $table->enum('location_type', ['pickup', 'delivery', 'return', 'waypoint']);
    
    // Địa chỉ
    $table->string('location_name', 200)->nullable()->comment('Tên địa điểm');
    $table->string('address', 500)->comment('Địa chỉ đầy đủ');
    $table->string('prefecture', 50)->nullable()->comment('都道府県');
    $table->string('city', 100)->nullable()->comment('市区町村');
    $table->decimal('latitude', 10, 8)->nullable()->comment('緯度');
    $table->decimal('longitude', 11, 8)->nullable()->comment('経度');
    
    // Thời gian
    $table->time('arrival_time')->nullable()->comment('到着時刻');
    $table->time('departure_time')->nullable()->comment('出発時刻');
    $table->integer('stay_duration_minutes')->nullable()->comment('滞在時間');
    
    // Khoảng cách từ điểm trước
    $table->decimal('distance_from_previous_km', 10, 2)->nullable();
    $table->integer('travel_time_from_previous_min')->nullable();
    
    // Thông tin liên hệ
    $table->string('contact_name', 100)->nullable()->comment('担当者名');
    $table->string('contact_phone', 20)->nullable()->comment('連絡先電話番号');
    $table->text('notes')->nullable()->comment('備考');
    
    $table->timestamp('created_at')->useCurrent();
    
    // Foreign Key
    $table->foreign('route_id')->references('id')->on('quotation_routes')->onDelete('cascade');
    
    // Indexes
    $table->index(['route_id', 'sequence_order']);
    $table->index('location_type');
});
```

#### 1.3. File: `database/migrations/2025_12_12_100002_create_quotation_route_segments_table.php`

##### 1.3.1. Tạo bảng `quotation_route_segments` (Chi tiết đoạn đường)

Tạo migration cho bảng lưu chi tiết từng đoạn đường giữa 2 điểm.

**変更内容:**

```php
Schema::create('quotation_route_segments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('route_id')->comment('FK đến quotation_routes');
    $table->unsignedBigInteger('from_location_id')->comment('FK đến quotation_route_locations');
    $table->unsignedBigInteger('to_location_id')->comment('FK đến quotation_route_locations');
    
    $table->tinyInteger('segment_order')->unsigned()->comment('Thứ tự đoạn');
    
    // Thông tin đoạn đường
    $table->decimal('distance_km', 10, 2)->comment('距離');
    $table->integer('driving_time_minutes')->comment('運転時間');
    
    // Chi phí
    $table->decimal('highway_fee', 10, 2)->default(0)->comment('高速料金');
    $table->decimal('fuel_cost', 10, 2)->default(0)->comment('燃料費');
    
    // Thông tin đường
    $table->enum('road_type', ['highway', 'national', 'prefectural', 'local'])->nullable();
    $table->string('highway_name', 200)->nullable()->comment('高速道路名');
    $table->text('route_description')->nullable()->comment('ルート概要');
    
    // Điều kiện
    $table->string('traffic_condition', 50)->nullable()->comment('交通状況');
    $table->string('weather_condition', 50)->nullable()->comment('天候');
    $table->text('notes')->nullable()->comment('備考');
    
    $table->timestamp('created_at')->useCurrent();
    
    // Foreign Keys
    $table->foreign('route_id')->references('id')->on('quotation_routes')->onDelete('cascade');
    $table->foreign('from_location_id')->references('id')->on('quotation_route_locations')->onDelete('cascade');
    $table->foreign('to_location_id')->references('id')->on('quotation_route_locations')->onDelete('cascade');
    
    // Indexes
    $table->index(['route_id', 'segment_order']);
});
```

#### 1.4. File: `database/migrations/2025_12_12_100003_create_quotation_route_files_table.php`

##### 1.4.1. Tạo bảng `quotation_route_files` (Lưu path file JSON)

Tạo migration cho bảng lưu đường dẫn đến file JSON request/response từ AI.

**変更内容:**

```php
Schema::create('quotation_route_files', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('route_id')->comment('FK đến quotation_routes');
    
    $table->enum('file_type', ['request', 'response'])->comment('Loại file');
    $table->string('file_path', 500)->comment('Đường dẫn file');
    $table->string('file_name', 255)->comment('Tên file');
    $table->unsignedBigInteger('file_size')->nullable()->comment('Kích thước (bytes)');
    $table->string('mime_type', 50)->default('application/json');
    
    $table->string('storage_disk', 50)->default('local')->comment('local, s3, etc');
    $table->boolean('is_archived')->default(false)->comment('Đã archive chưa');
    $table->timestamp('archived_at')->nullable()->comment('Thời gian archive');
    
    $table->timestamp('created_at')->useCurrent();
    
    // Foreign Key
    $table->foreign('route_id')->references('id')->on('quotation_routes')->onDelete('cascade');
    
    // Indexes
    $table->index(['route_id', 'file_type']);
    $table->index('created_at');
    $table->index(['is_archived', 'created_at']);
});
```

---

### 2. Model Files

#### 2.1. File: `app/Models/QuotationRoute.php` (NEW)

##### 2.1.1. Tạo Model QuotationRoute

Tạo Eloquent model cho bảng `quotation_routes`.

**変更内容:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationRoute extends Model
{
    use HasFactory;

    protected $table = 'quotation_routes';

    protected $fillable = [
        'route_code',
        'user_id',
        'quotation_id',
        'title',
        'pickup_location',
        'delivery_location',
        'return_location',
        'start_time',
        'vehicle_type',
        'loading_time_minutes',
        'unloading_time_minutes',
        'user_break_time_minutes',
        'total_distance_km',
        'estimated_end_time',
        'date_change',
        'total_duty_time_hours',
        'actual_working_hours',
        'total_driving_time_minutes',
        'total_handling_time_minutes',
        'total_break_time_minutes',
        'highway_fee',
        'fuel_cost',
        'estimated_total_cost',
        'is_compliant',
        'applied_rule',
        'ai_model_used',
        'calculation_duration_seconds',
        'status',
        'error_message',
        'notes',
    ];

    protected $casts = [
        'date_change' => 'boolean',
        'is_compliant' => 'boolean',
        'total_distance_km' => 'decimal:2',
        'total_duty_time_hours' => 'decimal:2',
        'actual_working_hours' => 'decimal:2',
        'highway_fee' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function locations()
    {
        return $this->hasMany(QuotationRouteLocation::class, 'route_id')->orderBy('sequence_order');
    }

    public function segments()
    {
        return $this->hasMany(QuotationRouteSegment::class, 'route_id')->orderBy('segment_order');
    }

    public function files()
    {
        return $this->hasMany(QuotationRouteFile::class, 'route_id');
    }
}
```

#### 2.2. File: `app/Models/QuotationRouteLocation.php` (NEW)

##### 2.2.1. Tạo Model QuotationRouteLocation

**変更内容:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRouteLocation extends Model
{
    protected $table = 'quotation_route_locations';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'sequence_order',
        'location_type',
        'location_name',
        'address',
        'prefecture',
        'city',
        'latitude',
        'longitude',
        'arrival_time',
        'departure_time',
        'stay_duration_minutes',
        'distance_from_previous_km',
        'travel_time_from_previous_min',
        'contact_name',
        'contact_phone',
        'notes',
    ];

    public function route()
    {
        return $this->belongsTo(QuotationRoute::class, 'route_id');
    }
}
```

#### 2.3. File: `app/Models/QuotationRouteSegment.php` (NEW)

##### 2.3.1. Tạo Model QuotationRouteSegment

**変更内容:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRouteSegment extends Model
{
    protected $table = 'quotation_route_segments';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'from_location_id',
        'to_location_id',
        'segment_order',
        'distance_km',
        'driving_time_minutes',
        'highway_fee',
        'fuel_cost',
        'road_type',
        'highway_name',
        'route_description',
        'traffic_condition',
        'weather_condition',
        'notes',
    ];

    public function route()
    {
        return $this->belongsTo(QuotationRoute::class, 'route_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(QuotationRouteLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(QuotationRouteLocation::class, 'to_location_id');
    }
}
```

#### 2.4. File: `app/Models/QuotationRouteFile.php` (NEW)

##### 2.4.1. Tạo Model QuotationRouteFile

**変更内容:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRouteFile extends Model
{
    protected $table = 'quotation_route_files';

    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'file_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'storage_disk',
        'is_archived',
        'archived_at',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(QuotationRoute::class, 'route_id');
    }
}
```

---

### 3. Service Layer

#### 3.1. File: `app/Services/AIRouteCalculationService.php` (NEW)

##### 3.1.1. Tạo Service xử lý AI calculation

Service chính để xử lý logic gọi AI API và parse response.

**変更内容:**

```php
<?php

namespace App\Services;

use App\Models\QuotationRoute;
use App\Models\QuotationRouteLocation;
use App\Models\QuotationRouteSegment;
use App\Models\QuotationRouteFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AIRouteCalculationService
{
    protected $aiApiUrl;
    protected $aiApiKey;
    protected $aiModel;

    public function __construct()
    {
        $this->aiApiUrl = config('services.openai.api_url', 'https://api.openai.com/v1/chat/completions');
        $this->aiApiKey = config('services.openai.api_key');
        $this->aiModel = config('services.openai.model', 'gpt-4-turbo-preview');
    }

    public function calculate(array $input, int $userId)
    {
        $startTime = microtime(true);
        
        // Generate route code
        $routeCode = $this->generateRouteCode();
        
        // Create pending record
        $route = QuotationRoute::create([
            'route_code' => $routeCode,
            'user_id' => $userId,
            'title' => $input['title'] ?? null,
            'pickup_location' => $input['pickup_location'],
            'delivery_location' => $input['delivery_location'],
            'return_location' => $input['return_location'],
            'start_time' => $input['start_time'],
            'vehicle_type' => $input['vehicle_type'] ?? '4t',
            'loading_time_minutes' => $input['loading_time'] ?? 60,
            'unloading_time_minutes' => $input['unloading_time'] ?? 60,
            'user_break_time_minutes' => $input['break_time'] ?? null,
            'status' => 'pending',
            'ai_model_used' => $this->aiModel,
        ]);

        try {
            // Build prompt
            $prompt = $this->buildPrompt($input);
            
            // Save request JSON
            $this->saveRequestFile($route, $prompt);
            
            // Call AI API
            $aiResponse = $this->callAI($prompt);
            
            // Save response JSON
            $this->saveResponseFile($route, $aiResponse);
            
            // Parse and save to DB
            $this->parseAndSaveResponse($route, $aiResponse);
            
            // Update calculation duration
            $duration = round(microtime(true) - $startTime);
            $route->update([
                'status' => 'completed',
                'calculation_duration_seconds' => $duration,
            ]);
            
            return $route->fresh()->load(['locations', 'segments']);
            
        } catch (\Exception $e) {
            Log::error('AI Route Calculation Error: ' . $e->getMessage(), [
                'route_code' => $routeCode,
                'error' => $e->getTraceAsString(),
            ]);
            
            $route->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    protected function generateRouteCode(): string
    {
        $date = Carbon::now()->format('Ymd');
        $lastRoute = QuotationRoute::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastRoute ? (int)substr($lastRoute->route_code, -3) + 1 : 1;
        
        return sprintf('QR-%s-%03d', $date, $sequence);
    }

    protected function buildPrompt(array $input): array
    {
        $promptTemplate = Storage::get('prompts/route_calculation_prompt.txt');
        
        $userMessage = str_replace([
            '{pickup_location}',
            '{delivery_location}',
            '{return_location}',
            '{start_time}',
            '{vehicle_type}',
            '{loading_time}',
            '{unloading_time}',
            '{break_time}',
        ], [
            $input['pickup_location'],
            $input['delivery_location'],
            $input['return_location'],
            $input['start_time'],
            $input['vehicle_type'] ?? '中型車(4t)',
            $input['loading_time'] ?? 60,
            $input['unloading_time'] ?? 60,
            $input['break_time'] ?? 'Auto',
        ], $promptTemplate);

        return [
            'model' => $this->aiModel,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a logistics and route planning AI expert.'],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object'],
        ];
    }

    protected function callAI(array $payload): array
    {
        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->aiApiUrl, $payload);

        if (!$response->successful()) {
            throw new \Exception('AI API Error: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;
        
        if (!$content) {
            throw new \Exception('AI response is empty');
        }

        return json_decode($content, true);
    }

    protected function saveRequestFile(QuotationRoute $route, array $payload): void
    {
        $directory = $this->getStorageDirectory($route);
        $fileName = $route->route_code . '-request.json';
        $filePath = $directory . '/' . $fileName;
        
        Storage::put($filePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        QuotationRouteFile::create([
            'route_id' => $route->id,
            'file_type' => 'request',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => Storage::size($filePath),
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);
    }

    protected function saveResponseFile(QuotationRoute $route, array $response): void
    {
        $directory = $this->getStorageDirectory($route);
        $fileName = $route->route_code . '-response.json';
        $filePath = $directory . '/' . $fileName;
        
        Storage::put($filePath, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        QuotationRouteFile::create([
            'route_id' => $route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => Storage::size($filePath),
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);
    }

    protected function getStorageDirectory(QuotationRoute $route): string
    {
        $date = Carbon::parse($route->created_at);
        return 'ai_responses/quotation_routes/' . $date->format('Y/m');
    }

    protected function parseAndSaveResponse(QuotationRoute $route, array $response): void
    {
        DB::transaction(function () use ($route, $response) {
            // Update route with summary data
            $summary = $response['summary'] ?? [];
            $timeBreakdown = $response['time_breakdown'] ?? [];
            $costBreakdown = $response['cost_breakdown'] ?? [];
            $complianceCheck = $summary['compliance_check'] ?? [];
            
            $route->update([
                'total_distance_km' => $summary['total_distance_km'] ?? null,
                'estimated_end_time' => $summary['estimated_end_time'] ?? null,
                'date_change' => $summary['date_change'] ?? false,
                'total_duty_time_hours' => $timeBreakdown['total_duty_time_hours'] ?? null,
                'actual_working_hours' => $timeBreakdown['actual_working_hours'] ?? null,
                'total_driving_time_minutes' => $timeBreakdown['total_driving_time_minutes'] ?? null,
                'total_handling_time_minutes' => $timeBreakdown['total_handling_time_minutes'] ?? null,
                'total_break_time_minutes' => $timeBreakdown['total_break_time_minutes'] ?? null,
                'highway_fee' => $costBreakdown['estimated_total_tolls'] ?? 0,
                'is_compliant' => $complianceCheck['is_compliant'] ?? true,
                'applied_rule' => $complianceCheck['applied_rule'] ?? null,
            ]);
            
            // Save locations
            $this->saveLocations($route, $response);
            
            // Save segments
            $this->saveSegments($route, $response);
        });
    }

    protected function saveLocations(QuotationRoute $route, array $response): void
    {
        $locations = [
            [
                'sequence_order' => 1,
                'location_type' => 'pickup',
                'address' => $route->pickup_location,
            ],
            [
                'sequence_order' => 2,
                'location_type' => 'delivery',
                'address' => $route->delivery_location,
            ],
            [
                'sequence_order' => 3,
                'location_type' => 'return',
                'address' => $route->return_location,
            ],
        ];
        
        foreach ($locations as $locationData) {
            QuotationRouteLocation::create(array_merge([
                'route_id' => $route->id,
            ], $locationData));
        }
    }

    protected function saveSegments(QuotationRoute $route, array $response): void
    {
        $routeDetails = $response['route_details'] ?? [];
        $locations = $route->locations;
        
        if (isset($routeDetails['section_1_pickup_to_delivery'])) {
            $section1 = $routeDetails['section_1_pickup_to_delivery'];
            QuotationRouteSegment::create([
                'route_id' => $route->id,
                'from_location_id' => $locations[0]->id,
                'to_location_id' => $locations[1]->id,
                'segment_order' => 1,
                'distance_km' => $section1['distance_km'] ?? 0,
                'driving_time_minutes' => $section1['driving_time_minutes'] ?? 0,
                'highway_fee' => $section1['toll_yen'] ?? 0,
                'route_description' => $section1['route_description'] ?? null,
            ]);
        }
        
        if (isset($routeDetails['section_2_delivery_to_return'])) {
            $section2 = $routeDetails['section_2_delivery_to_return'];
            QuotationRouteSegment::create([
                'route_id' => $route->id,
                'from_location_id' => $locations[1]->id,
                'to_location_id' => $locations[2]->id,
                'segment_order' => 2,
                'distance_km' => $section2['distance_km'] ?? 0,
                'driving_time_minutes' => $section2['driving_time_minutes'] ?? 0,
                'highway_fee' => $section2['toll_yen'] ?? 0,
                'route_description' => $section2['route_description'] ?? null,
            ]);
        }
    }
}
```

---

### 4. Request Validation

#### 4.1. File: `app/Http/Requests/CalculateRouteRequest.php` (NEW)

##### 4.1.1. Tạo FormRequest cho validation

**変更内容:**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateRouteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'nullable|string|max:500',
            'pickup_location' => 'required|string|max:500',
            'delivery_location' => 'required|string|max:500',
            'return_location' => 'required|string|max:500',
            'start_time' => 'required|date_format:H:i',
            'vehicle_type' => 'nullable|string|max:50',
            'loading_time' => 'nullable|integer|min:0|max:480',
            'unloading_time' => 'nullable|integer|min:0|max:480',
            'break_time' => 'nullable|integer|min:0|max:480',
        ];
    }

    public function messages()
    {
        return [
            'pickup_location.required' => '積地を入力してください',
            'delivery_location.required' => '届け地を入力してください',
            'return_location.required' => '帰社地を入力してください',
            'start_time.required' => '運行開始時間を入力してください',
            'start_time.date_format' => '時間形式が正しくありません（HH:MM）',
        ];
    }
}
```

---

### 5. Controller

#### 5.1. File: `app/Http/Controllers/Api/QuotationRouteController.php` (NEW)

##### 5.1.1. Tạo Controller xử lý API endpoints

**変更内容:**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalculateRouteRequest;
use App\Models\QuotationRoute;
use App\Services\AIRouteCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuotationRouteController extends Controller
{
    protected $aiService;

    public function __construct(AIRouteCalculationService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * @OA\Post(
     *   path="/api/quotation/routes/calculate",
     *   tags={"Quotation Route"},
     *   summary="Calculate route using AI",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"pickup_location","delivery_location","return_location","start_time"},
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="pickup_location", type="string"),
     *       @OA\Property(property="delivery_location", type="string"),
     *       @OA\Property(property="return_location", type="string"),
     *       @OA\Property(property="start_time", type="string", format="time"),
     *       @OA\Property(property="vehicle_type", type="string"),
     *       @OA\Property(property="loading_time", type="integer"),
     *       @OA\Property(property="unloading_time", type="integer"),
     *       @OA\Property(property="break_time", type="integer")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=422, description="Validation Error"),
     *   @OA\Response(response=500, description="Server Error")
     * )
     */
    public function calculate(CalculateRouteRequest $request)
    {
        try {
            $userId = auth()->id() ?? 1;
            
            $route = $this->aiService->calculate($request->validated(), $userId);
            
            return $this->responseJson(Response::HTTP_OK, [
                'route_id' => $route->id,
                'route_code' => $route->route_code,
                'summary' => [
                    'total_distance_km' => $route->total_distance_km,
                    'estimated_end_time' => $route->estimated_end_time,
                    'highway_fee' => $route->highway_fee,
                    'is_compliant' => $route->is_compliant,
                    'applied_rule' => $route->applied_rule,
                ],
                'time_breakdown' => [
                    'total_duty_time_hours' => $route->total_duty_time_hours,
                    'actual_working_hours' => $route->actual_working_hours,
                    'total_driving_time_minutes' => $route->total_driving_time_minutes,
                    'total_handling_time_minutes' => $route->total_handling_time_minutes,
                    'total_break_time_minutes' => $route->total_break_time_minutes,
                ],
                'locations' => $route->locations,
                'segments' => $route->segments,
            ], 'Route calculated successfully');
            
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/quotation/routes",
     *   tags={"Quotation Route"},
     *   summary="Get list of route calculations",
     *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $userId = auth()->id() ?? null;
        
        $query = QuotationRoute::with(['locations', 'segments'])
            ->orderBy('created_at', 'desc');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $routes = $query->paginate($perPage);
        
        return $this->responseJson(Response::HTTP_OK, $routes);
    }

    /**
     * @OA\Get(
     *   path="/api/quotation/routes/{id}",
     *   tags={"Quotation Route"},
     *   summary="Get route calculation detail",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show($id)
    {
        $route = QuotationRoute::with(['locations', 'segments', 'files'])->find($id);
        
        if (!$route) {
            return $this->responseJsonError(Response::HTTP_NOT_FOUND, 'Route not found');
        }
        
        return $this->responseJson(Response::HTTP_OK, $route);
    }

    /**
     * @OA\Get(
     *   path="/api/quotation/routes/{id}/ai-response",
     *   tags={"Quotation Route"},
     *   summary="Download AI response JSON file",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Success"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function downloadAIResponse($id)
    {
        $route = QuotationRoute::with('files')->find($id);
        
        if (!$route) {
            return $this->responseJsonError(Response::HTTP_NOT_FOUND, 'Route not found');
        }
        
        $responseFile = $route->files()->where('file_type', 'response')->first();
        
        if (!$responseFile) {
            return $this->responseJsonError(Response::HTTP_NOT_FOUND, 'Response file not found');
        }
        
        return response()->download(
            storage_path('app/' . $responseFile->file_path),
            $responseFile->file_name
        );
    }
}
```

---

### 6. Routes

#### 6.1. File: `routes/api.php`

##### 6.1.1. Thêm routes cho Quotation Route API

**既存コード** (line 52-73):
```php
Route::group(['middleware' => 'auth:api'], function () {
    // ... existing routes
});
```

**変更内容:**

Thêm routes mới vào trong group `auth:api`:

```php
// Quotation Route AI Calculation
Route::prefix('quotation')->group(function () {
    Route::post('routes/calculate', 'QuotationRouteController@calculate');
    Route::get('routes', 'QuotationRouteController@index');
    Route::get('routes/{id}', 'QuotationRouteController@show');
    Route::get('routes/{id}/ai-response', 'QuotationRouteController@downloadAIResponse');
});
```

---

### 7. Configuration

#### 7.1. File: `config/services.php`

##### 7.1.1. Thêm OpenAI configuration

**変更内容:**

Thêm vào cuối file:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
    'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
],
```

#### 7.2. File: `.env`

##### 7.2.1. Thêm environment variables

**変更内容:**

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_API_URL=https://api.openai.com/v1/chat/completions
OPENAI_MODEL=gpt-4-turbo-preview
```

---

### 8. Prompt Template

#### 8.1. File: `storage/app/prompts/route_calculation_prompt.txt` (NEW)

##### 8.1.1. Tạo prompt template cho AI

**変更内容:**

Copy toàn bộ prompt tiếng Nhật từ yêu cầu (đã có sẵn trong issue).

---

### 9. Console Command (Cleanup)

#### 9.1. File: `app/Console/Commands/CleanupOldRouteFiles.php` (NEW)

##### 9.1.1. Tạo command để xóa file JSON cũ

**変更内容:**

```php
<?php

namespace App\Console\Commands;

use App\Models\QuotationRouteFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupOldRouteFiles extends Command
{
    protected $signature = 'quotation:cleanup-old-files {--days=30}';
    protected $description = 'Cleanup old AI response files';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $files = QuotationRouteFile::where('created_at', '<', $cutoffDate)
            ->where('is_archived', false)
            ->get();
        
        $count = 0;
        foreach ($files as $file) {
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
                $file->update(['is_archived' => true, 'archived_at' => now()]);
                $count++;
            }
        }
        
        $this->info("Cleaned up {$count} old files.");
    }
}
```

---

## FE (Frontend)

### 1. Files need to edit:

**Note:** Frontend implementation sẽ được thực hiện sau khi Backend hoàn thành và test xong API.

#### 1.1. File: `resources/js/pages/Quotation/QuotationForm.vue` (hoặc tương tự)

##### 1.1.1. Thêm nút "AI計算" và xử lý call API

**変更内容:**

- Thêm button "AI計算" vào form
- Implement function `handleAICalculation()` để call API `/api/quotation/routes/calculate`
- Nhận response và fill vào các field:
  - `total_distance_km` → 走行距離
  - `estimated_end_time` → 終了時間
  - `highway_fee` → 高速料金
- Hiển thị loading state khi đang tính toán
- Hiển thị error message nếu có lỗi

---

## 実装順序 (Implementation Order)

### Phase 1: Database & Models (Độc lập)
1. Tạo 4 migration files
2. Run migrations
3. Tạo 4 model files
4. Test relationships

**Estimated:** 2-3 giờ

### Phase 2: Service Layer & AI Integration (Phụ thuộc Phase 1)
1. Tạo `AIRouteCalculationService`
2. Implement prompt building
3. Implement AI API call
4. Implement file storage logic
5. Implement parse response logic
6. Test với mock data

**Estimated:** 4-6 giờ

### Phase 3: API Layer (Phụ thuộc Phase 2)
1. Tạo `CalculateRouteRequest` validation
2. Tạo `QuotationRouteController`
3. Thêm routes
4. Thêm config
5. Test API endpoints với Postman

**Estimated:** 2-3 giờ

### Phase 4: Cleanup & Utilities (Độc lập)
1. Tạo cleanup command
2. Tạo prompt template file
3. Test cleanup command

**Estimated:** 1-2 giờ

### Phase 5: Frontend Integration (Phụ thuộc Phase 3)
1. Thêm UI button "AI計算"
2. Implement API call
3. Handle response và fill data
4. Handle loading & error states

**Estimated:** 3-4 giờ

### Phase 6: Testing & Bug Fixes
1. Unit tests cho Service
2. Feature tests cho API
3. Manual testing
4. Bug fixes

**Estimated:** 3-4 giờ

---

## 見積もり工数 (Estimated Effort)

### Backend: 12-18 giờ
- Database & Models: 2-3 giờ
- Service Layer: 4-6 giờ
- API Layer: 2-3 giờ
- Cleanup & Utilities: 1-2 giờ
- Testing & Bug Fixes: 3-4 giờ

### Frontend: 3-4 giờ
- UI Implementation: 2-3 giờ
- Testing: 1 giờ

### **合計: 15-22 giờ (2-3 ngày làm việc)**

---

## 技術的な注意事項 (Technical Notes)

### 1. パフォーマンス考慮:
- AI API call có thể mất 10-30 giây → Cần hiển thị loading state rõ ràng
- Sử dụng queue job nếu cần xử lý async (optional)
- Cache kết quả nếu input giống nhau (optional)

### 2. セキュリティ考慮:
- API key phải lưu trong `.env`, không commit lên Git
- Validate input kỹ để tránh injection
- Rate limiting cho API endpoint (tránh spam)

### 3. データ整合性:
- Sử dụng DB transaction khi lưu nhiều bảng
- Foreign key constraints để đảm bảo data integrity
- Soft delete nếu cần giữ lịch sử

### 4. エラーハンドリング:
- AI API timeout → Retry 1 lần, sau đó báo lỗi
- AI response format sai → Validate JSON structure
- File storage error → Log và báo lỗi cho user

### 5. スケーラビリティ:
- File JSON có thể move sang S3 sau này
- Database có thể partition theo năm/tháng
- Có thể thêm cache layer (Redis) nếu cần

### 6. モニタリング:
- Log mọi AI API call (request + response)
- Track calculation duration
- Monitor file storage size
- Alert nếu AI API fail rate > 10%

