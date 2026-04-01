<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalculateRouteRequest;
use App\Models\QuotationRoute;
use App\Services\AIRouteCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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
            
            // Load files relationship to access response file
            $route->load('files');
            
            // Get thinking_process from AI response file
            $thinkingProcess = $this->getThinkingProcessFromResponse($route);
            
            return $this->responseJson(Response::HTTP_OK, [
                'route_id' => $route->id,
                'route_code' => $route->route_code,
                'summary' => [
                    'start_time' => $route->start_time,
                    'estimated_end_time' => $route->estimated_end_time,
                    'total_distance_km' => $route->total_distance_km,
                    'date_change' => $route->date_change,
                ],
                'time_breakdown' => [
                    'total_duty_time_hours' => $route->total_duty_time_hours,
                    'actual_working_hours' => $route->actual_working_hours,
                    'total_break_time_minutes' => $route->total_break_time_minutes,
                    'details' => [
                        'total_driving_time_minutes' => $route->total_driving_time_minutes,
                        'total_handling_time_minutes' => $route->total_handling_time_minutes,
                    ],
                ],
                'cost_breakdown' => [
                    'total_tolls_yen' => $route->highway_fee,
                ],
                'compliance_info' => [
                    'is_compliant' => $route->is_compliant,
                    'break_time_source' => $route->applied_rule ?? 'Auto_Calculation',
                    'note' => $route->compliance_note,
                ],
                'thinking_process' => $route->thinking_process ?? null,
                'locations' => $route->locations,
                'segments' => $route->segments,
            ], 'Route calculated successfully');
            
        } catch (\Exception $e) {
            return $this->responseJsonEx($e);
        }
    }
    
    /**
     * Get thinking_process from AI response file (string or null).
     */
    protected function getThinkingProcessFromResponse(QuotationRoute $route): ?string
    {
        try {
            if (!$route->relationLoaded('files')) {
                $route->load('files');
            }

            $responseFile = $route->files()->where('file_type', 'response')->first();

            if (!$responseFile) {
                return null;
            }

            $filePath = storage_path('app/' . $responseFile->file_path);

            $realPath = realpath($filePath);
            $storagePath = realpath(storage_path('app'));

            if (!$realPath || strpos($realPath, $storagePath) !== 0) {
                Log::warning('Invalid file path detected', [
                    'route_id' => $route->id,
                    'file_path' => $responseFile->file_path,
                ]);
                return null;
            }

            if (!file_exists($filePath)) {
                return null;
            }

            $content = file_get_contents($filePath);
            $response = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Failed to decode JSON from response file', [
                    'route_id' => $route->id,
                    'json_error' => json_last_error_msg(),
                ]);
                return null;
            }

            $thinkingProcess = $response['thinking_process'] ?? null;

            if (is_string($thinkingProcess) && $thinkingProcess !== '') {
                return $thinkingProcess;
            }
            if (is_array($thinkingProcess) && !empty($thinkingProcess)) {
                return implode(' ', array_filter($thinkingProcess));
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to get thinking_process from response file', [
                'route_id' => $route->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
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
        
        // Get thinking_process from AI response file
        $thinkingProcess = $this->getThinkingProcessFromResponse($route);
        
        $routeData = $route->toArray();
        if ($thinkingProcess) {
            $routeData['thinking_process'] = $thinkingProcess;
        }
        
        return $this->responseJson(Response::HTTP_OK, $routeData);
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