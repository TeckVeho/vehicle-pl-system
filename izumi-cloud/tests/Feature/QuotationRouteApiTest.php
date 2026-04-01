<?php

namespace Tests\Feature;

use App\Models\QuotationRoute;
use App\Models\QuotationRouteLocation;
use App\Models\User;
use App\Services\AIRouteCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * @note Thiết kế hiện tại: /api/quotation/routes* không dùng middleware auth:api (cố ý, không bắt buộc JWT).
 *       Controller: calculate dùng auth()->id() ?? 1; index dùng auth()->id() ?? null — test khớp hành vi này.
 */
class QuotationRouteApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_calculate_route_without_token_is_not_unauthorized(): void
    {
        $response = $this->postJson('/api/quotation/routes/calculate', []);

        $this->assertNotEquals(401, $response->getStatusCode());
        $response->assertStatus(422);
    }

    public function test_calculate_route_validates_required_fields(): void
    {
        $response = $this->postJson('/api/quotation/routes/calculate', []);

        $response->assertStatus(422);
        $response->assertJsonPath('code', 422);
        $internal = $response->json('message_internal');
        $this->assertIsArray($internal);
        $this->assertArrayHasKey('pickup_location', $internal);
        $this->assertArrayHasKey('return_location', $internal);
        $this->assertArrayHasKey('start_time', $internal);
    }

    public function test_calculate_route_validates_time_format(): void
    {
        $response = $this->postJson('/api/quotation/routes/calculate', [
            'pickup_location' => '東京都',
            'delivery_location' => '神奈川県',
            'return_location' => '東京都',
            'start_time' => 'invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('code', 422);
        $this->assertArrayHasKey('start_time', $response->json('message_internal') ?? []);
    }

    public function test_calculate_route_with_mock_ai_service(): void
    {
        $mockService = Mockery::mock(AIRouteCalculationService::class);
        $this->app->instance(AIRouteCalculationService::class, $mockService);

        $mockRoute = QuotationRoute::factory()->create([
            'route_code' => 'QR-TEST-MOCK-001',
            'user_id' => $this->user->id,
            'pickup_location' => '東京都港区',
            'delivery_location' => '神奈川県横浜市',
            'return_location' => '東京都港区',
            'start_time' => '08:00',
            'vehicle_type' => '4t',
            'loading_time_minutes' => 60,
            'unloading_time_minutes' => 60,
            'total_distance_km' => 50.0,
            'estimated_end_time' => '14:00',
            'date_change' => false,
            'total_duty_time_hours' => 8.0,
            'actual_working_hours' => 7.5,
            'total_driving_time_minutes' => 90,
            'total_handling_time_minutes' => 120,
            'total_break_time_minutes' => 45,
            'highway_fee' => 1500,
            'is_compliant' => true,
            'applied_rule' => 'Test rule',
            'compliance_note' => null,
            'status' => 'completed',
        ]);

        QuotationRouteLocation::query()->create([
            'route_id' => $mockRoute->id,
            'sequence_order' => 1,
            'location_type' => 'pickup',
            'address' => '東京都港区',
        ]);

        QuotationRouteLocation::query()->create([
            'route_id' => $mockRoute->id,
            'sequence_order' => 2,
            'location_type' => 'delivery',
            'address' => '神奈川県横浜市',
        ]);

        QuotationRouteLocation::query()->create([
            'route_id' => $mockRoute->id,
            'sequence_order' => 3,
            'location_type' => 'return',
            'address' => '東京都港区',
        ]);

        $mockService->shouldReceive('calculate')
            ->once()
            ->andReturn($mockRoute->load(['locations', 'segments']));

        $response = $this->postJson('/api/quotation/routes/calculate', [
            'pickup_location' => '東京都港区',
            'delivery_location' => '神奈川県横浜市',
            'return_location' => '東京都港区',
            'start_time' => '08:00',
            'vehicle_type' => '4t',
        ]);

        $response->assertOk();
        $response->assertJsonPath('code', 200);
        $response->assertJsonStructure([
            'code',
            'data' => [
                'route_id',
                'route_code',
                'summary' => [
                    'start_time',
                    'estimated_end_time',
                    'total_distance_km',
                    'date_change',
                ],
                'time_breakdown' => [
                    'total_duty_time_hours',
                    'actual_working_hours',
                    'total_break_time_minutes',
                    'details' => [
                        'total_driving_time_minutes',
                        'total_handling_time_minutes',
                    ],
                ],
                'cost_breakdown' => [
                    'total_tolls_yen',
                ],
                'compliance_info' => [
                    'is_compliant',
                    'break_time_source',
                    'note',
                ],
                'thinking_process',
                'locations',
                'segments',
            ],
        ]);
    }

    public function test_get_routes_list_without_token_returns_ok(): void
    {
        QuotationRoute::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/quotation/routes?per_page=3');

        $response->assertOk();
        $response->assertJsonStructure([
            'code',
            'data' => [
                'data',
                'current_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_get_route_detail_without_token_returns_route_when_exists(): void
    {
        $route = QuotationRoute::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/quotation/routes/{$route->id}");

        $response->assertOk();
        $response->assertJsonPath('code', 200);
    }

    public function test_get_route_detail_returns_full_data(): void
    {
        $route = QuotationRoute::factory()->create([
            'user_id' => $this->user->id,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            QuotationRouteLocation::query()->create([
                'route_id' => $route->id,
                'sequence_order' => $i,
                'location_type' => 'pickup',
                'address' => '住所 '.$i,
            ]);
        }

        $response = $this->getJson("/api/quotation/routes/{$route->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'code',
            'data' => [
                'id',
                'route_code',
                'pickup_location',
                'delivery_location',
                'return_location',
                'total_distance_km',
                'locations',
                'segments',
                'files',
            ],
        ]);
    }

    public function test_get_route_detail_returns_thinking_process_when_available(): void
    {
        $route = QuotationRoute::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $thinkingProcess = '出発地から積地への回送を含めた全3区間のルートで計算しました。主要経路として東名高速（東京IC～静岡IC）を選択し、中型車料金を適用しています。';

        $responseData = [
            'summary' => ['total_distance_km' => 100],
            'thinking_process' => $thinkingProcess,
        ];

        $filePath = 'ai_responses/quotation_routes/2026/01/'.$route->route_code.'-response.json';
        $fullPath = storage_path('app/'.$filePath);
        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        try {
            file_put_contents($fullPath, json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            \App\Models\QuotationRouteFile::query()->create([
                'route_id' => $route->id,
                'file_type' => 'response',
                'file_path' => $filePath,
                'file_name' => $route->route_code.'-response.json',
                'file_size' => 100,
                'mime_type' => 'application/json',
                'storage_disk' => 'local',
            ]);

            $response = $this->getJson("/api/quotation/routes/{$route->id}");

            $response->assertOk();
            $data = $response->json('data');
            $this->assertNotNull($data['thinking_process']);
            $this->assertIsString($data['thinking_process']);
            $this->assertEquals($thinkingProcess, $data['thinking_process']);
        } finally {
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    public function test_get_route_detail_returns_null_thinking_process_when_not_available(): void
    {
        $route = QuotationRoute::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/quotation/routes/{$route->id}");

        $response->assertOk();
        $data = $response->json('data');

        if (isset($data['thinking_process'])) {
            $this->assertNull($data['thinking_process']);
        }
    }

    public function test_get_route_detail_returns_404_for_invalid_id(): void
    {
        $response = $this->getJson('/api/quotation/routes/99999');

        $response->assertStatus(404);
    }
}
