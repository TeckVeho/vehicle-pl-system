<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Repository\GoogleSpreadsheetService;
use Throwable;

class GoogleSpreadsheetSyncVehicleOperationScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $backoff = 60;

    public $timeout = 300;

    protected string $departmentName;

    public function __construct(string $departmentName)
    {
        $this->departmentName = $departmentName;
    }

    public function handle()
    {
        Log::info("Bắt đầu cập nhật vehicle operation schedule cho department: {$this->departmentName}");

        try {
            $googleSpreadsheetService = new GoogleSpreadsheetService();

            $googleSpreadsheetService->loadAccessTokenFromFile();

            if (!$googleSpreadsheetService->isAuthenticated()) {
                throw new \Exception('Không thể xác thực với Google API');
            }

            $result = $googleSpreadsheetService->updateVehicleOperationData($this->departmentName);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            Log::info("✅ Cập nhật thành công department {$this->departmentName}: {$result['url']}");

        } catch (\Exception $e) {
            Log::error("❌ Lỗi cập nhật department {$this->departmentName}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error("Job thất bại cho department {$this->departmentName}: " . $exception->getMessage());
    }

    public function retryUntil()
    {
        return now()->addMinutes(10);
    }
}
