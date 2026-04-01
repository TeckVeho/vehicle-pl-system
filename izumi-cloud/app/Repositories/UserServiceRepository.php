<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-09-20
 */

namespace Repository;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserServiceRepository
{
    public function syncUser($user_code)
    {
        Log::info("Cloud Sync User Data Start at:" . Carbon::now()->toDateTimeString());
        $listBaseUrl = LIST_BASE_URL_USER_SYNC;
        if (App::environment('staging')) {
            $listBaseUrl = LIST_BASE_URL_USER_SYNC_STAGE;
        }
        if (App::environment('production')) {
            $listBaseUrl = LIST_BASE_URL_USER_SYNC_PRODUCTION;
        }

        if (!$user_code) {
            $users = User::query()->select('uuid', 'id', 'name', 'password as password_up', 'department_code', 'deleted_at as deleted_up', 'email', 'assign_vehicle_personnel', 'language')
                ->with('department:id,name')
                ->withTrashed()->get();
        } else {
            $users = User::query()->select('uuid', 'id', 'name', 'password as password_up', 'department_code', 'deleted_at as deleted_up', 'email', 'assign_vehicle_personnel', 'language')
                ->with('department:id,name')
                ->withTrashed()->where('id', $user_code)->get();
        }
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
                'language' => $user->language ?? 'ja',
            ];
        }
        foreach ($listBaseUrl as $baseUrl) {
            try {
                Log::info("Cloud Sync User to:" . $baseUrl . '/api/sync/user');
                $response = Http::timeout(3600)->withoutVerifying()->post($baseUrl . '/api/sync/user', $dataUsers)->json();
                Log::info("Cloud Sync User response:" . json_encode($response));
            } catch (\Exception $exception) {
                Log::error("Error Sync User to:" . $exception->getMessage());
                continue;
            }
        }
        return true;
    }
}
