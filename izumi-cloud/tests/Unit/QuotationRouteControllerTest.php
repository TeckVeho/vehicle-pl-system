<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\QuotationRouteController;
use App\Models\QuotationRoute;
use App\Models\QuotationRouteFile;
use App\Services\AIRouteCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class QuotationRouteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected QuotationRouteController $controller;

    protected QuotationRoute $route;

    protected function setUp(): void
    {
        parent::setUp();

        $mockService = $this->createMock(AIRouteCalculationService::class);
        $this->controller = new QuotationRouteController($mockService);

        $this->route = QuotationRoute::factory()->create([
            'route_code' => 'QR-TEST-001',
        ]);
    }

    protected function tearDown(): void
    {
        QuotationRoute::where('route_code', 'LIKE', '%TEST%')->delete();
        parent::tearDown();
    }

    /**
     * Controller đọc file qua storage_path('app/...'), không qua Storage facade — cần ghi file thật.
     */
    protected function writeStorageAppFile(string $relativePath, string $contents): void
    {
        $full = storage_path('app/'.ltrim($relativePath, '/'));
        $dir = dirname($full);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($full, $contents);
    }

    /**
     * Test getThinkingProcessFromResponse with valid response file containing thinking_process as string
     */
    public function test_get_thinking_process_from_response_with_valid_file(): void
    {
        $thinkingProcess = '出発地から積地への回送を含めた全3区間のルートで計算しました。';

        $responseData = [
            'summary' => ['total_distance_km' => 100],
            'thinking_process' => $thinkingProcess,
        ];

        $filePath = 'ai_responses/quotation_routes/2026/01/QR-TEST-001-response.json';
        $this->writeStorageAppFile($filePath, json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => 'QR-TEST-001-response.json',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertEquals($thinkingProcess, $result);
    }

    /**
     * Test getThinkingProcessFromResponse with response file containing thinking_process as array (backward compat: imploded to string)
     */
    public function test_get_thinking_process_from_response_with_array_returns_imploded_string(): void
    {
        $thinkingProcess = [
            'route_strategy' => 'Test route strategy',
            'calculation_basis' => 'Test calculation basis',
        ];

        $responseData = [
            'summary' => ['total_distance_km' => 100],
            'thinking_process' => $thinkingProcess,
        ];

        $filePath = 'ai_responses/quotation_routes/2026/01/QR-TEST-002-response.json';
        $this->writeStorageAppFile($filePath, json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => 'QR-TEST-002-response.json',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertStringContainsString('Test route strategy', $result);
        $this->assertStringContainsString('Test calculation basis', $result);
    }

    /**
     * Test getThinkingProcessFromResponse when response file does not exist
     */
    public function test_get_thinking_process_from_response_file_not_exists(): void
    {
        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => 'ai_responses/quotation_routes/2026/01/non-existent.json',
            'file_name' => 'non-existent.json',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNull($result);
    }

    /**
     * Test getThinkingProcessFromResponse when route has no response file
     */
    public function test_get_thinking_process_from_response_no_file(): void
    {
        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNull($result);
    }

    /**
     * Test getThinkingProcessFromResponse with invalid JSON
     */
    public function test_get_thinking_process_from_response_invalid_json(): void
    {
        $filePath = 'ai_responses/quotation_routes/2026/01/QR-TEST-003-response.json';
        $this->writeStorageAppFile($filePath, 'invalid json content {');

        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => 'QR-TEST-003-response.json',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNull($result);
    }

    /**
     * Test getThinkingProcessFromResponse with response file without thinking_process
     */
    public function test_get_thinking_process_from_response_no_thinking_process(): void
    {
        $responseData = [
            'summary' => ['total_distance_km' => 100],
            'route_segments' => [],
        ];

        $filePath = 'ai_responses/quotation_routes/2026/01/QR-TEST-004-response.json';
        $this->writeStorageAppFile($filePath, json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => 'QR-TEST-004-response.json',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNull($result);
    }

    /**
     * Test getThinkingProcessFromResponse with path traversal attack attempt
     */
    public function test_get_thinking_process_from_response_path_traversal_protection(): void
    {
        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => '../../../etc/passwd',
            'file_name' => 'passwd',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->route->load('files');

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNull($result);
    }

    /**
     * Test getThinkingProcessFromResponse loads files relationship if not loaded
     */
    public function test_get_thinking_process_from_response_loads_files_relationship(): void
    {
        $thinkingProcess = 'Test thinking process string.';

        $responseData = [
            'summary' => ['total_distance_km' => 100],
            'thinking_process' => $thinkingProcess,
        ];

        $filePath = 'ai_responses/quotation_routes/2026/01/QR-TEST-005-response.json';
        $this->writeStorageAppFile($filePath, json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        QuotationRouteFile::create([
            'route_id' => $this->route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => 'QR-TEST-005-response.json',
            'file_size' => 100,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);

        $this->assertFalse($this->route->relationLoaded('files'));

        $method = $this->getProtectedMethod('getThinkingProcessFromResponse');
        $result = $method->invoke($this->controller, $this->route);

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertEquals($thinkingProcess, $result);
        $this->assertTrue($this->route->relationLoaded('files'));
    }

    protected function getProtectedMethod(string $methodName): ReflectionMethod
    {
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
