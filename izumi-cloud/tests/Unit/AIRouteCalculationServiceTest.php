<?php

namespace Tests\Unit;

use App\Models\QuotationRoute;
use App\Models\QuotationRouteLocation;
use App\Services\AIRouteCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AIRouteCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AIRouteCalculationService;
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        QuotationRoute::where('route_code', 'LIKE', 'QR-%-TEST')->delete();
        parent::tearDown();
    }

    public function test_generate_route_code_format()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateRouteCode');
        $method->setAccessible(true);

        $routeCode = $method->invoke($this->service);

        $this->assertMatchesRegularExpression('/^QR-\d{8}-\d{3}$/', $routeCode);
        $this->assertStringStartsWith('QR-'.date('Ymd'), $routeCode);
    }

    public function test_generate_route_code_increments_sequence()
    {
        $existingRoute = QuotationRoute::create([
            'route_code' => 'QR-'.date('Ymd').'-TEST-001',
            'user_id' => 1,
            'pickup_location' => 'Test',
            'delivery_location' => 'Test',
            'return_location' => 'Test',
            'start_time' => '08:00',
            'status' => 'completed',
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateRouteCode');
        $method->setAccessible(true);

        $routeCode = $method->invoke($this->service);

        $this->assertStringStartsWith('QR-'.date('Ymd'), $routeCode);
        $this->assertMatchesRegularExpression('/^QR-\d{8}-\d{3}$/', $routeCode);

        $existingRoute->delete();
    }

    public function test_get_storage_directory_format()
    {
        $route = QuotationRoute::create([
            'route_code' => 'QR-20251212-001',
            'user_id' => 1,
            'pickup_location' => 'Test',
            'delivery_location' => 'Test',
            'return_location' => 'Test',
            'start_time' => '08:00',
            'status' => 'pending',
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getStorageDirectory');
        $method->setAccessible(true);

        $directory = $method->invoke($this->service, $route);

        $this->assertMatchesRegularExpression('/^ai_responses\/quotation_routes\/\d{4}\/\d{2}$/', $directory);
    }

    public function test_save_locations_creates_three_records()
    {
        $route = QuotationRoute::create([
            'route_code' => 'QR-20251212-001',
            'user_id' => 1,
            'pickup_location' => '東京都港区',
            'delivery_location' => '神奈川県横浜市',
            'return_location' => '東京都港区',
            'start_time' => '08:00',
            'status' => 'pending',
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('saveLocations');
        $method->setAccessible(true);

        $method->invoke($this->service, $route, []);

        $this->assertEquals(3, $route->locations()->count());

        $locations = $route->locations;
        $this->assertEquals('pickup', $locations[0]->location_type);
        $this->assertEquals('delivery', $locations[1]->location_type);
        $this->assertEquals('return', $locations[2]->location_type);

        $this->assertEquals(1, $locations[0]->sequence_order);
        $this->assertEquals(2, $locations[1]->sequence_order);
        $this->assertEquals(3, $locations[2]->sequence_order);
    }

    public function test_save_segments_creates_two_records()
    {
        $route = QuotationRoute::create([
            'route_code' => 'QR-20251212-001',
            'user_id' => 1,
            'pickup_location' => '東京都',
            'delivery_location' => '神奈川県',
            'return_location' => '東京都',
            'start_time' => '08:00',
            'status' => 'pending',
        ]);

        $loc1 = QuotationRouteLocation::create([
            'route_id' => $route->id,
            'sequence_order' => 1,
            'location_type' => 'pickup',
            'address' => '東京都',
        ]);

        $loc2 = QuotationRouteLocation::create([
            'route_id' => $route->id,
            'sequence_order' => 2,
            'location_type' => 'delivery',
            'address' => '神奈川県',
        ]);

        $loc3 = QuotationRouteLocation::create([
            'route_id' => $route->id,
            'sequence_order' => 3,
            'location_type' => 'return',
            'address' => '東京都',
        ]);

        $aiResponse = [
            'route_segments' => [
                [
                    'segment_order' => 1,
                    'distance_km' => 30.0,
                    'driving_time_minutes' => 45,
                    'toll_yen' => 800,
                    'route_description' => 'Test route 1',
                ],
                [
                    'segment_order' => 2,
                    'distance_km' => 30.0,
                    'driving_time_minutes' => 45,
                    'toll_yen' => 800,
                    'route_description' => 'Test route 2',
                ],
            ],
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('saveSegments');
        $method->setAccessible(true);

        $method->invoke($this->service, $route, $aiResponse);

        $this->assertEquals(2, $route->segments()->count());

        $segments = $route->segments;
        $this->assertEquals(1, $segments[0]->segment_order);
        $this->assertEquals(2, $segments[1]->segment_order);
        $this->assertEquals(30.0, $segments[0]->distance_km);
        $this->assertEquals(45, $segments[0]->driving_time_minutes);
        $this->assertEquals(800, $segments[0]->highway_fee);
    }

    public function test_parse_and_save_response_updates_route()
    {
        $route = QuotationRoute::create([
            'route_code' => 'QR-20251212-001',
            'user_id' => 1,
            'pickup_location' => '東京都',
            'delivery_location' => '神奈川県',
            'return_location' => '東京都',
            'start_time' => '08:00',
            'status' => 'pending',
        ]);

        $aiResponse = [
            'summary' => [
                'total_distance_km' => 60.5,
                'estimated_end_time' => '15:00',
                'date_change' => false,
            ],
            'time_breakdown' => [
                'total_duty_time_hours' => 7.0,
                'actual_working_hours' => 6.0,
                'total_break_time_minutes' => 60,
                'details' => [
                    'total_driving_time_minutes' => 120,
                    'total_handling_time_minutes' => 120,
                ],
            ],
            'cost_breakdown' => [
                'total_tolls_yen' => 2000,
            ],
            'compliance_info' => [
                'is_compliant' => true,
                'break_time_source' => 'Test rule',
            ],
            'route_segments' => [
                [
                    'segment_order' => 1,
                    'distance_km' => 30.0,
                    'driving_time_minutes' => 60,
                    'toll_yen' => 1000,
                    'route_description' => 'Route 1',
                ],
                [
                    'segment_order' => 2,
                    'distance_km' => 30.5,
                    'driving_time_minutes' => 60,
                    'toll_yen' => 1000,
                    'route_description' => 'Route 2',
                ],
            ],
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parseAndSaveResponse');
        $method->setAccessible(true);

        $method->invoke($this->service, $route, $aiResponse);

        $route->refresh();

        $this->assertEquals(60.5, $route->total_distance_km);
        $this->assertStringStartsWith('15:00', (string) $route->estimated_end_time);
        $this->assertEquals(false, $route->date_change);
        $this->assertEquals(7.0, $route->total_duty_time_hours);
        $this->assertEquals(6.0, $route->actual_working_hours);
        $this->assertEquals(120, $route->total_driving_time_minutes);
        $this->assertEquals(120, $route->total_handling_time_minutes);
        $this->assertEquals(60, $route->total_break_time_minutes);
        $this->assertEquals(2000, $route->highway_fee);
        $this->assertTrue($route->is_compliant);
        $this->assertEquals('Test rule', $route->applied_rule);

        $this->assertEquals(3, $route->locations()->count());
        $this->assertEquals(2, $route->segments()->count());
    }
}
