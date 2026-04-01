<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\InspectionNotificationRecipientRepositoryInterface;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SendNotiOfVehicleInspectionDeadline extends Command
{
    /** @var InspectionNotificationRecipientRepositoryInterface */
    protected $inspectionNotificationRecipientRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-noti-of-vehicle-inspection-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo về xe sắp hết hạn kiểm định (14 ngày trước)';

    public function __construct(InspectionNotificationRecipientRepositoryInterface $inspectionNotificationRecipientRepository)
    {
        parent::__construct();
        $this->inspectionNotificationRecipientRepository = $inspectionNotificationRecipientRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Gửi thông báo xe sắp hết hạn kiểm định (14 ngày trước)");
        
        $baseUrl = BASE_URL_WEB_APP;
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_WEB_APP_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_WEB_APP_PRODUCTION;
        }
        $url = $baseUrl . '/api/cloud/noti-send-vehicle-due-date';

        $now = Carbon::now()->addDays(14);
        $targetDate = $now->format('Y-m-d');
        
        $vehicles = Vehicle::query()
            ->with(['department:id', 'plate_history' => function ($query) {
                $query->orderBy('date', 'DESC')
                    ->select(['id', 'vehicle_id', 'date', 'no_number_plate'])
                    ->limit(1);
            }])
            ->where('inspection_expiration_date', $targetDate)
            ->get();
            
        $datas = [];
        $vehiclesByDepartment = $vehicles->groupBy('department_id');
        
        foreach ($vehiclesByDepartment as $departmentId => $departmentVehicles) {
            $vehicleNumbers = $departmentVehicles->map(function($vehicle) {
                return $vehicle->plate_history->first()->no_number_plate ?? null;
            })->filter()->values()->toArray();

            if (!empty($vehicleNumbers)) {
                $datas[] = [
                    'department_id' => $departmentId,
                    'vehicle_no' => $vehicleNumbers,
                ];
            }
        }

        // Issue #851: 拠点別の例外通知者を取得し payload に付与（type 3 でも 通知対象設定 に送る）
        // Repository を直接呼び出し（DB 参照）。自システムのため HTTP 自呼び出しは不要。
        $extraRecipientsByDepartment = $this->inspectionNotificationRecipientRepository->getForNotificationMap();
        Log::info('SendNotiOfVehicleInspectionDeadline: extra recipients from repository', [
            'extra_by_department' => $extraRecipientsByDepartment,
        ]);
        foreach ($datas as &$data) {
            $data['extra_recipient_user_ids'] = $extraRecipientsByDepartment[$data['department_id']] ?? [];
        }
        unset($data);

        if(!empty($datas)) {
            try {
                Log::info('SendNotiOfVehicleInspectionDeadline: gửi payload lên Web App', [
                    'url' => $url,
                    'type' => 3,
                    'datas_count' => count($datas),
                ]);
                $response = Http::timeout(60)->withoutVerifying()->post($url, [
                    'datas' => $datas,
                    'type' => 3
                ]);

            } catch (\Exception $e) {
                Log::error('Lỗi khi gửi thông báo xe sắp hết hạn kiểm định', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }
}
