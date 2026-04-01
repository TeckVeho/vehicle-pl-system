<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\InspectionNotificationRecipientRepositoryInterface;
use App\Models\Department;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotiVehicleDueDate extends Command
{
    /** @var InspectionNotificationRecipientRepositoryInterface */
    protected $inspectionNotificationRecipientRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-noti-vehicle-due-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Log::info("Gửi thông báo xe sắp hết hạn kiểm định");

        $baseUrl = BASE_URL_WEB_APP;
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_WEB_APP_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_WEB_APP_PRODUCTION;
        }
        $url = $baseUrl . '/api/cloud/noti-send-vehicle-due-date';

        $departments = Department::query()->pluck('id');
        $datas = [];
        foreach ($departments as $department) {
            $currentMonth = Carbon::now();
            $nextMonth = Carbon::now()->addMonth();

            $vehiclesCurrentMonth = Vehicle::query()->where('department_id', $department)
                ->whereMonth('inspection_expiration_date', $currentMonth->month)
                ->whereYear('inspection_expiration_date', $currentMonth->year)
                ->get();

            $vehiclesNextMonth = Vehicle::query()->where('department_id', $department)
                ->whereMonth('inspection_expiration_date', $nextMonth->month)
                ->whereYear('inspection_expiration_date', $nextMonth->year)
                ->get();

            if($vehiclesCurrentMonth->count() > 0 || $vehiclesNextMonth->count() > 0) {
                $datas[] = [
                    'total_vehicle_now' => $vehiclesCurrentMonth->count(),
                    'total_vehicle_next_month' => $vehiclesNextMonth->count(),
                    'department_id' => $department,
                ];
            }
        }

        // Issue #810: 拠点別の例外通知者を取得し payload に付与（type 1 でも 通知対象設定 に送る）
        $extraRecipientsByDepartment = $this->inspectionNotificationRecipientRepository->getForNotificationMap();
        Log::info('SendNotiVehicleDueDate: extra recipients from repository', [
            'extra_by_department' => $extraRecipientsByDepartment,
        ]);
        foreach ($datas as &$data) {
            $data['extra_recipient_user_ids'] = $extraRecipientsByDepartment[$data['department_id']] ?? [];
        }
        unset($data);

        if(!empty($datas)) {
            Log::info('SendNotiVehicleDueDate: gửi payload lên Web App', [
                'url' => $url,
                'type' => 1,
                'datas_count' => count($datas),
            ]);
            Log::info(json_encode($datas));
            $response = Http::timeout(60)->withoutVerifying()->post($url, [
                'datas' => $datas,
                'type' => 1
            ])->json();
        }
    }
}
