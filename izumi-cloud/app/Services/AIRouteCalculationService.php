<?php

namespace App\Services;

use App\Models\QuotationRoute;
use App\Models\QuotationRouteLocation;
use App\Models\QuotationRouteSegment;
use App\Models\QuotationRouteFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use OpenAI;

class AIRouteCalculationService
{
    protected $client;
    protected $aiModel;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key');
        $this->client = $apiKey ? OpenAI::client($apiKey) : null;
        $this->aiModel = config('services.openai.model', 'gpt-4-turbo-preview');
    }

    public function calculate(array $input, int $userId)
    {
        $startTime = microtime(true);

        $routeCode = $this->generateRouteCode();

        $route = QuotationRoute::create([
            'route_code' => $routeCode,
            'user_id' => $userId,
            'title' => $input['title'] ?? null,
            'start_location' => $input['start_location'] ?? null,
            'pickup_location' => $input['pickup_location'],
            'delivery_location' => $input['delivery_location'] ?? null,
            'delivery_locations' => $input['delivery_locations'] ?? null,
            'return_location' => $input['return_location'],
            'start_time' => $input['start_time'],
            'vehicle_type' => $input['vehicle_type'] ?? '4t',
            'loading_time_minutes' => $input['loading_time'] ?? 60,
            'unloading_time_minutes' => $input['unloading_time'] ?? 30,
            'user_break_time_minutes' => $input['break_time'] ?? null,
            'status' => 'pending',
            'ai_model_used' => $this->aiModel,
        ]);

        try {
            $userMessage = $this->buildPrompt($input);

            $requestPayload = [
                'model' => $this->aiModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a logistics and route planning AI expert for Japan.'],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ];

            $this->saveRequestFile($route, $requestPayload);

            $aiResponse = $this->callAI($userMessage);

            Log::info('AI Response received', ['response' => $aiResponse]);

            $this->saveResponseFile($route, $aiResponse);

            $this->parseAndSaveResponse($route, $aiResponse);

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

    protected function buildPrompt(array $input): string
    {
        $promptPath = storage_path('app/prompts/route_calculation_prompt.txt');

        if (!file_exists($promptPath)) {
            throw new \Exception('Prompt template not found: ' . $promptPath);
        }

        $promptTemplate = file_get_contents($promptPath);

        $deliveryLocations = $input['delivery_locations'] ?? [];
        if (is_array($deliveryLocations)) {
            $deliveryLocationsStr = implode('、', array_filter($deliveryLocations));
        } else {
            $deliveryLocationsStr = $deliveryLocations;
        }

        if (empty($deliveryLocationsStr) && !empty($input['delivery_location'])) {
            $deliveryLocationsStr = $input['delivery_location'];
        }

        $userMessage = str_replace([
            '{start_location}',
            '{pickup_location}',
            '{delivery_locations}',
            '{return_location}',
            '{start_time}',
            '{vehicle_type}',
            '{loading_time}',
            '{unloading_time}',
            '{break_time}',
        ], [
            $input['start_location'] ?? $input['departure_location'] ?? '',
            $input['pickup_location'] ?? $input['loading_location'] ?? '',
            $deliveryLocationsStr,
            $input['return_location'] ?? '',
            $input['start_time'] ?? '09:00',
            $input['vehicle_type'] ?? '中型車(4t)',
            $input['loading_time'] ?? 60,
            $input['unloading_time'] ?? 30,
            $input['break_time'] ?? 'Auto',
        ], $promptTemplate);

        return $userMessage;
    }

    protected function callAI(string $userMessage): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $this->aiModel,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a logistics and route planning AI expert for Japan. You must respond in JSON format.'],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content ?? null;

            Log::info('AI Raw Response', ['content' => $content]);

            if (!$content) {
                throw new \Exception('AI response is empty');
            }

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', ['error' => json_last_error_msg(), 'content' => $content]);
                throw new \Exception('Failed to decode AI response: ' . json_last_error_msg());
            }

            if (empty($decoded)) {
                Log::error('Decoded response is empty', ['content' => $content]);
                throw new \Exception('Decoded AI response is empty');
            }

            return $decoded;

        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw new \Exception('AI API Error: ' . $e->getMessage());
        }
    }

    protected function saveRequestFile(QuotationRoute $route, array $payload): void
    {
        $directory = $this->getStorageDirectory($route);
        $fileName = $route->route_code . '-request.json';
        $filePath = $directory . '/' . $fileName;

        $fullDirectoryPath = storage_path('app/' . $directory);
        if (!file_exists($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        $fullFilePath = storage_path('app/' . $filePath);
        $jsonContent = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $bytesWritten = file_put_contents($fullFilePath, $jsonContent);

        if ($bytesWritten === false) {
            throw new \Exception('Failed to write request file: ' . $fullFilePath);
        }

        QuotationRouteFile::create([
            'route_id' => $route->id,
            'file_type' => 'request',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $bytesWritten,
            'mime_type' => 'application/json',
            'storage_disk' => 'local',
        ]);
    }

    protected function saveResponseFile(QuotationRoute $route, array $response): void
    {
        $directory = $this->getStorageDirectory($route);
        $fileName = $route->route_code . '-response.json';
        $filePath = $directory . '/' . $fileName;

        $fullDirectoryPath = storage_path('app/' . $directory);
        if (!file_exists($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        $fullFilePath = storage_path('app/' . $filePath);
        $jsonContent = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $bytesWritten = file_put_contents($fullFilePath, $jsonContent);

        if ($bytesWritten === false) {
            throw new \Exception('Failed to write response file: ' . $fullFilePath);
        }

        QuotationRouteFile::create([
            'route_id' => $route->id,
            'file_type' => 'response',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $bytesWritten,
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
            $summary = $response['summary'] ?? [];
            $timeBreakdown = $response['time_breakdown'] ?? [];
            $timeDetails = $timeBreakdown['details'] ?? [];
            $costBreakdown = $response['cost_breakdown'] ?? [];
            $complianceInfo = $response['compliance_info'] ?? [];
            $thinkingProcess = $response['thinking_process'] ?? null;
            $thinkingProcessValue = null;
            if (is_string($thinkingProcess) && $thinkingProcess !== '') {
                $thinkingProcessValue = $thinkingProcess;
            } elseif (is_array($thinkingProcess) && !empty($thinkingProcess)) {
                $thinkingProcessValue = implode(' ', array_filter($thinkingProcess));
            }
            $route->update([
                'total_distance_km' => $summary['total_distance_km'] ?? null,
                'estimated_end_time' => $summary['estimated_end_time'] ?? null,
                'date_change' => $summary['date_change'] ?? false,
                'total_duty_time_hours' => $timeBreakdown['total_duty_time_hours'] ?? null,
                'actual_working_hours' => $timeBreakdown['actual_working_hours'] ?? null,
                'total_break_time_minutes' => $timeBreakdown['total_break_time_minutes'] ?? null,
                'total_driving_time_minutes' => $timeDetails['total_driving_time_minutes'] ?? null,
                'total_handling_time_minutes' => $timeDetails['total_handling_time_minutes'] ?? null,
                'highway_fee' => $costBreakdown['total_tolls_yen'] ?? 0,
                'is_compliant' => $complianceInfo['is_compliant'] ?? true,
                'applied_rule' => $complianceInfo['break_time_source'] ?? null,
                'compliance_note' => $complianceInfo['note'] ?? null,
                'thinking_process' => $thinkingProcessValue,
            ]);

            $this->saveLocations($route, $response);

            $this->saveSegments($route, $response);
        });
    }

    protected function saveLocations(QuotationRoute $route, array $response): void
    {
        $locations = [];

        if (!empty($route->start_location)) {
            $locations[] = [
                'sequence_order' => 1,
                'location_type' => 'start',
                'address' => $route->start_location,
            ];
        }

        $sequenceOrder = !empty($route->start_location) ? 2 : 1;

        $locations[] = [
            'sequence_order' => $sequenceOrder++,
            'location_type' => 'pickup',
            'address' => $route->pickup_location,
        ];

        $deliveryLocations = $route->delivery_locations ?? [];
        if (is_string($deliveryLocations)) {
            $deliveryLocations = json_decode($deliveryLocations, true) ?? [];
        }

        if (!empty($deliveryLocations) && is_array($deliveryLocations)) {
            foreach ($deliveryLocations as $deliveryLocation) {
                if (!empty($deliveryLocation)) {
                    $locations[] = [
                        'sequence_order' => $sequenceOrder++,
                        'location_type' => 'delivery',
                        'address' => $deliveryLocation,
                    ];
                }
            }
        } elseif (!empty($route->delivery_location)) {
            $locations[] = [
                'sequence_order' => $sequenceOrder++,
                'location_type' => 'delivery',
                'address' => $route->delivery_location,
            ];
        }

        $locations[] = [
            'sequence_order' => $sequenceOrder,
            'location_type' => 'return',
            'address' => $route->return_location,
        ];

        foreach ($locations as $locationData) {
            QuotationRouteLocation::create(array_merge([
                'route_id' => $route->id,
            ], $locationData));
        }
    }

    protected function saveSegments(QuotationRoute $route, array $response): void
    {
        $routeSegments = $response['route_segments'] ?? [];

        if (empty($routeSegments)) {
            Log::warning('No route_segments in AI response', ['route_id' => $route->id]);
            return;
        }

        $locations = $route->locations()->orderBy('sequence_order')->get();

        if ($locations->isEmpty()) {
            Log::error('No locations found for route', ['route_id' => $route->id]);
            return;
        }

        foreach ($routeSegments as $segment) {
            $segmentOrder = $segment['segment_order'] ?? null;

            if ($segmentOrder === null) {
                Log::warning('Segment missing segment_order', ['segment' => $segment]);
                continue;
            }

            $fromLocationIndex = $segmentOrder - 1;
            $toLocationIndex = $segmentOrder;

            $fromLocation = $locations[$fromLocationIndex] ?? null;
            $toLocation = $locations[$toLocationIndex] ?? null;

            if (!$fromLocation || !$toLocation) {
                Log::warning('Location not found for segment', [
                    'segment_order' => $segmentOrder,
                    'from_index' => $fromLocationIndex,
                    'to_index' => $toLocationIndex,
                    'total_locations' => $locations->count(),
                ]);
                continue;
            }

            QuotationRouteSegment::create([
                'route_id' => $route->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'segment_order' => $segmentOrder,
                'segment_type' => $segment['type'] ?? null,
                'distance_km' => $segment['distance_km'] ?? 0,
                'driving_time_minutes' => $segment['driving_time_minutes'] ?? 0,
                'highway_fee' => $segment['toll_yen'] ?? 0,
                'route_description' => $segment['route_description'] ?? null,
            ]);
        }
    }
}