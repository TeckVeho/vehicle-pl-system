<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-06-10
 */

namespace Repository;

use App\Events\HotlinesSendLWEvent;
use App\Models\Hotlines;
use App\Models\HotlineSettingLwms;
use App\Repositories\Contracts\HotlineRepositoryInterface;
use Illuminate\Support\Arr;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeMobileInfo;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class HotlineRepository extends BaseRepository implements HotlineRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param Hotlines $model
       */

    public function model()
    {
        return Hotlines::class;
    }

    public function createHotline($params)
    {
        $user = Auth::user();
        $userName = $user->name;
        $employee = Employee::where('employee_code', $user->id)->first();
        $phone = null;
        $email = $user->email;
        if($employee) {
            $employeeMobileInfo = EmployeeMobileInfo::where('employee_id', $employee->id)->orderby('created_at', 'DESC')->first();
            $phone = $employeeMobileInfo ? $employeeMobileInfo->tel : null;
        }
        $categoryName = Arr::get($params, 'category_name', null);
        $channel_id = Arr::get($params, 'channel_id', null);
        $params['username'] = $userName;
        $params['phone'] = $phone;
        $params['email'] = $email;
        $params['check_anonymous_flag'] = 1;
        $data = $this->model->create($params);
        $hotlineSettingLwms = HotlineSettingLwms::where('id', $channel_id)->first();
        HotlinesSendLWEvent::dispatch($data, $hotlineSettingLwms, $categoryName);
        return $data;
       


    }


    public function createChannelId($params)
    {
        $data = HotlineSettingLwms::create($params);
        return $data;
    }

    public function getlistChannel()
    {
        $environment = app()->environment() !== "local"  ? app()->environment() : 'dev';
        $data = HotlineSettingLwms::where('environment', $environment)->get([
            'id',
            'channel_id',
            'name',
        ])->map(function ($item) {
            return [
                'channel_id' => $item->id,
                'name' => $item->name,
            ];
        })->toArray();
        return $data;
    }
}
