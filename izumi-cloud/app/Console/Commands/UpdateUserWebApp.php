<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Models\User;
use Carbon\Carbon;

class UpdateUserWebApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user-web-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật thông tin người dùng vào Web App';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Cập nhật thông tin người dùng vào Web App Start at:" . Carbon::now()->toDateTimeString());
        $baseUrl = BASE_URL_WEB_APP;
        if (App::environment('staging')) {
            $baseUrl = BASE_URL_WEB_APP_STAGE;
        }
        if (App::environment('production')) {
            $baseUrl = BASE_URL_WEB_APP_PRODUCTION;
        }


        $users = User::query()->select('uuid', 'id', 'name', 'password as password_up', 'department_code', 'deleted_at as deleted_up', 'email', 'assign_vehicle_personnel')
            ->with('department:id,name')
            ->withTrashed()->get();
        $dataUsers = [];
        foreach ($users as $user) {
            $dataUsers[] = [
                'user_code' => $user->id,
                'user_name' => $user->name,
                'password' => $user->password_up,
                'department_id' => $user->department_code,
                'department_name' => data_get($user, 'department.name'),
                'deleted_at' => $user->deleted_up,
                'roles' => $user->getRoleNames(),
                'email' => $user->email,
                'assign_vehicle_personnel' => $user->assign_vehicle_personnel,
            ];
        }
        try {
            Log::info("Cập nhật thông tin người dùng vào Web App to:" . $baseUrl . '/api/sync/user');
            $response = Http::timeout(3600)->withoutVerifying()->post($baseUrl . '/api/sync/user', $dataUsers)->json();
            Log::info("Cập nhật thông tin người dùng vào Web App response:" . json_encode($response));
        } catch (\Exception $exception) {
            Log::error("Error Cập nhật thông tin người dùng vào Web App to:" . $exception->getMessage());
        }
        return true;
    }
}
