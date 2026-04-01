<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Repository\GoogleSpreadsheetService;
use Illuminate\Support\Facades\Log;
use App\Jobs\GoogleSpreadsheetSyncVehicleOperationScheduleJob;
use App\Models\Department;

class GoogleSpreadsheetVehicleOperationScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:update-operation-schedule
                            {--department= : Tên department cụ thể để cập nhật}
                            {--all : Cập nhật tất cả departments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật vehicle operation schedule cho Google Spreadsheet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu cập nhật vehicle operation schedule...');

        try {
            $googleSpreadsheetService = new GoogleSpreadsheetService();

            // Load access token
            $googleSpreadsheetService->loadAccessTokenFromFile();

            if (!$googleSpreadsheetService->isAuthenticated()) {
                $this->error('Không thể xác thực với Google API. Vui lòng kiểm tra access token.');
                return 1;
            }

            $this->info('Đã xác thực thành công với Google API.');

            if ($this->option('all')) {
                $this->updateAllDepartments($googleSpreadsheetService);
            } elseif ($this->option('department')) {
                $this->updateSpecificDepartment($googleSpreadsheetService, $this->option('department'));
            } else {
                $this->error('Vui lòng chỉ định --department=<tên> hoặc --all');
                return 1;
            }

            $this->info('Hoàn thành cập nhật vehicle operation schedule!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            Log::error('GoogleSpreadsheetVehicleOperationScheduleCommand error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Cập nhật tất cả departments
     */
    private function updateAllDepartments(GoogleSpreadsheetService $service)
    {
        $this->info('Dispatch jobs cho tất cả departments...');

        $departments = Department::query()->orderBy('position')->get();
        $dispatchedCount = 0;
        $delaySeconds = 0;

        foreach ($departments as $department) {
            GoogleSpreadsheetSyncVehicleOperationScheduleJob::dispatch($department->name)
                ->delay(now()->addSeconds($delaySeconds));

            $this->info("✅ Đã dispatch job cho department: {$department->name} (delay: {$delaySeconds}s)");

            $dispatchedCount++;
            $delaySeconds += 20;
        }

        $this->info("Tổng cộng đã dispatch {$dispatchedCount} jobs.");
        $this->info("Các jobs sẽ chạy tuần tự với khoảng cách 20 giây để tránh vượt quota.");
        $this->info("Mỗi job thất bại sẽ tự động retry sau 60 giây, tối đa 1 lần.");
    }

    /**
     * Cập nhật department cụ thể
     */
    private function updateSpecificDepartment(GoogleSpreadsheetService $service, string $departmentName)
    {
        $this->info("Dispatch job cho department: {$departmentName}");

        GoogleSpreadsheetSyncVehicleOperationScheduleJob::dispatch($departmentName);

        $this->info("✅ Đã dispatch job cho department: {$departmentName}");
        $this->info("Job sẽ chạy ngay lập tức.");
        $this->info("Nếu thất bại, job sẽ tự động retry sau 60 giây, tối đa 1 lần.");
        $this->info("Kiểm tra log để xem kết quả: storage/logs/laravel.log");
    }
}
