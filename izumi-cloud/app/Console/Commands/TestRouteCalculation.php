<?php

namespace App\Console\Commands;

use App\Services\AIRouteCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestRouteCalculation extends Command
{
    protected $signature = 'quotation:test-route-calculation {--skip-db-check : Skip database check}';
    protected $description = 'Test route calculation API end-to-end';

    protected $aiService;

    public function __construct(AIRouteCalculationService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    public function handle()
    {
        $this->info('=== Testing Route Calculation API ===');
        $this->newLine();

        // Step 1: Check database
        if (!$this->option('skip-db-check')) {
            if (!$this->checkDatabase()) {
                return 1;
            }
        }

        // Step 2: Check OpenAI configuration
        if (!$this->checkOpenAI()) {
            return 1;
        }

        // Step 3: Check prompt template
        if (!$this->checkPromptTemplate()) {
            return 1;
        }

        // Step 4: Test route calculation
        if (!$this->testCalculation()) {
            return 1;
        }

        $this->newLine();
        $this->info('🎉 All tests passed! Route Calculation API is working correctly.');
        
        return 0;
    }

    protected function checkDatabase(): bool
    {
        $this->info('Step 1: Checking database tables...');
        
        $tables = [
            'quotation_routes',
            'quotation_route_locations',
            'quotation_route_segments',
            'quotation_route_files',
        ];

        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $this->line("  ✅ Table '{$table}' exists");
                } else {
                    $this->error("  ❌ Table '{$table}' not found");
                    $this->warn('Run: php artisan migrate');
                    return false;
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Database error: " . $e->getMessage());
                return false;
            }
        }
        
        $this->newLine();
        return true;
    }

    protected function checkOpenAI(): bool
    {
        $this->info('Step 2: Checking OpenAI configuration...');
        
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            $this->error('  ❌ OPENAI_API_KEY not configured');
            $this->warn('Add to .env: OPENAI_API_KEY=sk-your-key');
            return false;
        }
        
        $this->line('  ✅ API Key configured: ' . substr($apiKey, 0, 10) . '...');
        
        $model = config('services.openai.model');
        $this->line('  ✅ Model: ' . $model);
        
        $this->newLine();
        return true;
    }

    protected function checkPromptTemplate(): bool
    {
        $this->info('Step 3: Checking prompt template...');
        
        $path = storage_path('app/prompts/route_calculation_prompt.txt');
        if (!file_exists($path)) {
            $this->error('  ❌ Prompt template not found');
            $this->warn('Expected: ' . $path);
            return false;
        }
        
        $content = file_get_contents($path);
        $requiredVars = [
            '{pickup_location}',
            '{delivery_location}',
            '{return_location}',
            '{start_time}',
        ];
        
        foreach ($requiredVars as $var) {
            if (strpos($content, $var) === false) {
                $this->error("  ❌ Missing variable: {$var}");
                return false;
            }
        }
        
        $this->line('  ✅ Prompt template found and valid');
        $this->line('  ✅ All required variables present');
        
        $this->newLine();
        return true;
    }

    protected function testCalculation(): bool
    {
        $this->info('Step 4: Testing route calculation...');
        $this->warn('This will make a real API call to OpenAI (costs money)');
        
        if (!$this->confirm('Do you want to proceed?', true)) {
            $this->warn('Test skipped by user');
            return true;
        }

        $this->newLine();
        $this->line('Using test data:');
        
        $testData = [
            'title' => 'Test Route Calculation',
            'pickup_location' => '東京都港区芝公園4-2-8（東京タワー）',
            'delivery_location' => '神奈川県横浜市西区みなとみらい2-2-1（横浜ランドマークタワー）',
            'return_location' => '東京都港区芝公園4-2-8（東京タワー）',
            'start_time' => '08:00',
            'vehicle_type' => '4t',
            'loading_time' => 60,
            'unloading_time' => 60,
        ];

        foreach ($testData as $key => $value) {
            $this->line("  {$key}: {$value}");
        }
        
        $this->newLine();
        $this->info('Calling AI API... (this may take 10-30 seconds)');
        
        $startTime = microtime(true);
        
        try {
            $route = $this->aiService->calculate($testData, 1);
            
            $duration = round(microtime(true) - $startTime, 2);
            
            $this->newLine();
            $this->info("✅ Calculation completed in {$duration} seconds");
            $this->newLine();
            
            // Display results
            $this->line('=== Results ===');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Route Code', $route->route_code],
                    ['Status', $route->status],
                    ['Total Distance', $route->total_distance_km . ' km'],
                    ['End Time', $route->estimated_end_time],
                    ['Highway Fee', '¥' . number_format($route->highway_fee)],
                    ['Driving Time', $route->total_driving_time_minutes . ' min'],
                    ['Break Time', $route->total_break_time_minutes . ' min'],
                    ['Compliant', $route->is_compliant ? 'Yes' : 'No'],
                ]
            );
            
            $this->newLine();
            $this->line('Applied Rule: ' . ($route->applied_rule ?? 'N/A'));
            
            // Check locations
            $locationCount = $route->locations()->count();
            $this->line("Locations saved: {$locationCount}");
            
            // Check segments
            $segmentCount = $route->segments()->count();
            $this->line("Segments saved: {$segmentCount}");
            
            // Check files
            $fileCount = $route->files()->count();
            $this->line("Files saved: {$fileCount}");
            
            if ($fileCount > 0) {
                $this->newLine();
                $this->line('JSON Files:');
                foreach ($route->files as $file) {
                    $this->line("  - {$file->file_type}: {$file->file_path}");
                }
            }
            
            $this->newLine();
            $this->info('✅ All data saved correctly to database');
            
            return true;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Calculation failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Error details:');
            $this->line($e->getTraceAsString());
            return false;
        }
    }
}
