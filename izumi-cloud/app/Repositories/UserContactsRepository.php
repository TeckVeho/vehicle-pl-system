<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2025-02-06
 */

namespace Repository;

use App\Models\UserContactInfo;
use App\Models\UserContacts;
use App\Models\UserContactsHistory;
use App\Repositories\Contracts\UserContactsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class UserContactsRepository extends BaseRepository implements UserContactsRepositoryInterface
{

     public function __construct(Application $app)
     {
         parent::__construct($app);

     }

    /**
       * Instantiate model
       *
       * @param UserContacts $model
       */

    public function model()
    {
        return UserContacts::class;
    }

    public function getUserContactsProfile()
    {

        $userContacProfile = $this->model::with(['userContactInfos', 'user', 'userProfileInfos', 'userProfileInfos.image'])
            ->where('user_id', auth()->user()->id)->first();
        return $userContacProfile;
    }
    public function createUserContacts($data)
    {
        $postCode = Arr::get($data, 'post_code');
        $address = Arr::get($data, 'address');
        $tel = Arr::get($data, 'tel');
        $personalTel = Arr::get($data, 'personal_tel');
        $userContactInfos = Arr::get($data, 'list_user_contact_info');
        $flagCheckPersonalContactInfo = Arr::get($data, 'flag_check_personal_contact_info');
        $flagCheckEmergencyContactInfo1 = Arr::get($data, 'flag_check_emergency_contact_info_1');
        $flagCheckEmergencyContactInfo2 = Arr::get($data, 'flag_check_emergency_contact_info_2');
        DB::beginTransaction();
        try {
            $userContacts = $this->model->where('user_id', auth()->id())->first();

            $oldData = $userContacts ? $userContacts->toArray() : [];

            $dataToUpdate = [
                'post_code' => $postCode,
                'address' => $address,
                'tel' => $tel,
                'personal_tel' => $personalTel
            ];
            $checkUpdate = false;
            if (!$userContacts) {
                $userContacts = $this->model->create(array_merge(['user_id' => auth()->id()], $dataToUpdate));
                $checkUpdate = true;
            } else {
                $userContacts->flag_check_personal_contact_info = $flagCheckPersonalContactInfo;
                $userContacts->flag_check_emergency_contact_info_1 = $flagCheckEmergencyContactInfo1;
                $userContacts->flag_check_emergency_contact_info_2 = $flagCheckEmergencyContactInfo2;
                if($flagCheckPersonalContactInfo == 1 && $flagCheckEmergencyContactInfo1 == 1 && $flagCheckEmergencyContactInfo2 == 1){
                    $userContacts->flag_send_noti = 0;
                }
                $userContacts->save();
                $changedData = array_diff_assoc($dataToUpdate, $oldData);
                if (!empty($changedData) && $postCode != null && $address != null && $tel != null) {
                    $userContacts->update($changedData);
                    $checkUpdate = true;
                }
            }

            if (!empty($userContactInfos)) {
                foreach ($userContactInfos as $key => $info) {
                    $group = $info['group'] ?? ($key + 1);

                    $userContactInfo = UserContactInfo::where([
                        'user_contact_id' => $userContacts->id,
                        'group' => $group
                    ])->first();

                    $infoData = [
                        'urgent_contact_name' => $info['urgent_contact_name'],
                        'urgent_contact_relation' => $info['urgent_contact_relation'],
                        'urgent_contact_tel' => $info['urgent_contact_tel'],
                    ];

                    if (!$userContactInfo) {
                        UserContactInfo::create(array_merge(['user_contact_id' => $userContacts->id, 'group' => $group], $infoData));
                        $checkUpdate = true;
                    } else {
                        $changedInfo = array_diff_assoc($infoData, $userContactInfo->toArray());
                        if (!empty($changedInfo)) {
                            if($group == 2) {
                                $userContacts->update([
                                    'flag_send_noti' =>  0
                                ]);
                            }
                            $userContactInfo->update($changedInfo);
                            $checkUpdate = true;
                        }
                    }
                }
            }

            if ($checkUpdate) {
                UserContactsHistory::create([
                    'user_contacts_id' => $userContacts->id,
                    'data' => json_encode($userContacts->load(['userContactInfos', 'userProfileInfos'])->toArray()),
                ]);
            }

            DB::commit();
            return $userContacts;

        } catch (\Exception $exception){
            DB::rollBack();
            Log::error($exception->getMessage());
            return [$exception->getMessage()];
        }

    }

    public function getList($params)
    {
        $perPage = Arr::get($params, 'per_page', 20);
        $userId = Arr::get($params, 'user_id');
        $userName = Arr::get($params, 'user_name');
        $departmentName = Arr::get($params, 'department_name');
        $urgentContact = $this->model::with([
                'userContactInfos',
                'user',
                'user.department'
            ])
            ->whereHas('user',function($query) {
                $query->whereNull('deleted_at');
            })->whereHas('user.department',function($query) {
                $query->whereNull('deleted_at');
            });

        if($userId) {
            $urgentContact =  $urgentContact->whereHas('user', function($query) use ($userId) {
                $query->where('id', 'LIKE', '%' . $userId . '%');
            });
        }

        if($userName) {
            $urgentContact =  $urgentContact->whereHas('user', function($query) use ($userName) {
                $query->where('name', 'LIKE', '%' . $userName . '%');
            });
        }

        if($departmentName) {
            $urgentContact =  $urgentContact->whereHas('user', function($query) use ($departmentName) {
                $query->whereHas('department', function($query) use ($departmentName) {
                    $query->where('name', 'LIKE', '%' . $departmentName . '%');
                });
            });
        }


        return $urgentContact->paginate($perPage);
    }

    public function download($params)
    {
        $userId = Arr::get($params, 'user_id');
        $userName = Arr::get($params, 'user_name');
        $departmentName = Arr::get($params, 'department_name');


        $dataAll =  $this->model::with([
            'userContactInfos',
            'user',
            'user.department'
        ])->whereHas('user',function($query) {
            $query->whereNull('deleted_at');
        })->whereHas('user.department',function($query) {
                $query->whereNull('deleted_at');
            });

            if($userId) {
                $dataAll =  $dataAll->whereHas('user', function($query) use ($userId) {
                    $query->where('id', 'LIKE', '%' . $userId . '%');
                });
            }

        if($userName) {
            $dataAll =  $dataAll->whereHas('user', function($query) use ($userName) {
                $query->where('name', 'LIKE', '%' . $userName . '%');
            });
        }

        if($departmentName) {
            $dataAll =  $dataAll->whereHas('user', function($query) use ($departmentName) {
                $query->whereHas('department', function($query) use ($departmentName) {
                    $query->where('name', 'LIKE', '%' . $departmentName . '%');
                });
            });
        }

        $dataAll = $dataAll->get();

        $groupedData = $dataAll->groupBy(function ($item) {
            return $item->user['department']['name'] ?? 'Unknown Department';
        });

        return ['data_all' => $dataAll, 'data' => $groupedData];
    }

    public function checkUpdateUserContact()
    {
        $isUpdate = 0;
        $isFirstTime = 0;
        $userContacts = $this->model->query()->where('user_id', auth()->user()->id)->first();
        if($userContacts) {
            $isUpdate = $userContacts->flag_send_noti;
        } else {
            $isFirstTime = 1;
        }
        return [
            'is_update' =>  $isUpdate,
            'is_first_time' => $isFirstTime
        ];
    }

}
