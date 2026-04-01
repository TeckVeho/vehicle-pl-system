<?php

namespace App\Repositories;

use App\Http\Requests\UserRequest;
use App\Mail\RegisterMail;
use App\Models\LineWorkConf;
use App\Models\ProfileInformations;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPasswordHistory;
use App\Repositories\Contracts\UserRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Repository\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return User::class;
    }

    public function getAll($id)
    {
        return User::with('roles')->find($id);
    }

    public function getOne($id)
    {
        $user = $this->model->find($id);
        $user->getRoleNames();

        return $user;
    }

    public function getOneByCode($userCode)
    {
        return $this->model->find($userCode);
    }

    public function getPagination(UserRequest $request)
    {
        $sortby = $request->get('sortby', 'users.id');
        $sorttype = $request->get('sorttype', 'asc');

        $sortableColumns = [
            'name' => 'users.name',
            'department' => 'departments.name',
            'id' => 'users.id',
            'role_name' => 'roles.display_name',
        ];

        $sortby = $sortableColumns[$sortby] ?? 'users.id';

        $query = User::with('roles:id,name,display_name')
            ->select('users.uuid', 'users.id', 'users.department_code', 'users.name',
                'departments.name as departments_name', 'roles.name as role_name')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.uuid')
                    ->where('model_type', 'App\Models\User');
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_code');

        if ($request->has('id')) {
            $query->where('users.id', 'LIKE', '%'.$request->id.'%');
        }
        if ($request->has('name')) {
            $query->where('users.name', 'LIKE', '%'.$request->name.'%');
        }

        if ($request->has('role')) {
            $query->where(function ($query) use ($request) {
                return $query->where('users.role', '=', $request->role)
                    ->orWhere('roles.name', '=', $request->role);
            });
        }

        $results = $query->groupBy('users.id')->orderBy($sortby, $sorttype)->paginate($request->per_page);

        foreach ($results as $result) {
            if ($result->roles && count($result->roles) > 0) {
                $result->display_roles = $result->roles->pluck('display_name');
            }
        }

        return $results;
    }

    public function getInterviewPic(UserRequest $request)
    {
        $search = $request->get('search', '');
        $query = User::query()
            ->role([ROLE_AM_SM, ROLE_QUALITY_CONTROL, ROLE_SITE_MANAGER, ROLE_HQ_MANAGER, ROLE_TL, ROLE_DEPARTMENT_OFFICE_STAFF])
            ->select('uuid', 'id as code')
            ->selectRaw("CONCAT(`id`,' - ', `name`) AS name_code")
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', '%'.$search.'%');
                    $query->orWhere('name', 'LIKE', '%'.$search.'%');
                });
            })->get();

        return $query;
    }

    public function getUserByEmpCode($id)
    {
        $user = User::with([
            'userPasswordHistory'=> function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'userPasswordHistory.user' => function ($query) {
                $query->select('id', 'name', 'email');
            },'userPasswordHistory.userCreatedBy' => function ($query) {
                $query->select('id', 'name', 'email');
            },
        ])->where([['id', $id]])->first();
        return $user;
    }

    public function update(array $request, $id)
    {
        $this->applyScope();
        $model = $this->model->where([['id', $id]])->first();
        $mes = '';
        if (! $model) {
            $mes = 'user_id is not found';
        }
        if (isset($request['password'])) {
            if (! Hash::check($request['current_password'], $model->password)) {
                $mes = 'server.current_pass_incorrect';
            } else {
                $model->password = $request['password'];
            }
        }
        if ($mes) {
            return ['message' => $mes];
        }
        if (isset($request['role'])) {
            $model->role = $request['role'];
        }
        if (isset($request['email'])) {
            $model->email = $request['email'];
        }
        if (isset($request['name'])) {
            $model->name = $request['name'];
        }
        if (isset($request['id'])) {
            $model->id = $request['id'];
        }
        if (isset($request['assign_vehicle_personnel'])) {
            $model->assign_vehicle_personnel = $request['assign_vehicle_personnel'];
        }
        $model->save();

        if ($role = Role::findById($request['role'], 'api')) {
            $model->syncRoles($role);
        } else {
            return false;
        }

        return $model;
    }

    public function destroy($id)
    {
        return $this->model->where('id', '=', $id)->delete();

    }

    public function register(array $params)
    {
        $user = User::create($params);
        //        $this->grantRoleNewUser($user);

        return $user;
    }

    public function create(array $attributes)
    {
        $user = $this->model->create([
            'id' => $attributes['id'],
            'name' => $attributes['name'],
            'password' => $attributes['password'],
            'role' => $attributes['role'],
            'assign_vehicle_personnel' => $attributes['assign_vehicle_personnel'] ?? 0,
        ]);

        if ($role = Role::findById($attributes['role'], 'api')) {
            $user->syncRoles($role);
        } else {
            DB::rollBack();

            return false;
        }

        return $user;
    }

    public function getListMemberLW()
    {
        $lw_conf = Config::get('line_works_conf');
        $dataReturn = [];
        $systemLwAccessToken = LineWorkConf::query()->where('code', 'LW_ACCESS_TOKEN')->first();
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            if (! $this->checkTokenLw($systemLwAccessToken)) {
                $this->refreshTokenLw($systemLwAccessToken);
            }
        } else {
            $this->getAndSaveAccessTokenLw($lw_conf, $systemLwAccessToken);
        }
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            $headers = [
                'Authorization' => 'Bearer '.data_get($systemLwAccessToken, 'value.access_token'),
            ];
            $urlApi = Str::replaceArray('?',
                [Arr::get($lw_conf, 'bot_id'), Arr::get($lw_conf, 'channel_id')],
                LW_API_GET_ALL_MEMBER_IN_CHANNEL);

            //            $dataJson = Http::send('POST', $urlApi, ['headers' => $headers, $body])->json();
            $dataJsonMb = Http::timeout(60)->withHeaders($headers)->get($urlApi)->json();
            $dataUser = [];
            if ($dataJsonMb && data_get($dataJsonMb, 'members')) {
                foreach (data_get($dataJsonMb, 'members') as $member) {
                    $urlApiUser = LW_API_GET_USER_INFO.$member;
                    $dataJsonUser = Http::timeout(60)->withHeaders($headers)->get($urlApiUser)->json();
                    $dataUser[] = [
                        'code' => $member,
                        'full_name' => data_get($dataJsonUser, 'userName.lastName').' '.data_get($dataJsonUser, 'userName.firstName'),
                    ];
                }
            } else {
                error_log('getListMemberLW function:'.json_encode($dataJsonMb));
                Log::info('getListMemberLW function:'.json_encode($dataJsonMb));
            }
            $dataReturn = $dataUser;
        }

        return $dataReturn;
    }

    private function checkTokenLw($systemLwAccessToken)
    {
        $checkTokenExpire = false;
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            $headers = [
                'Authorization' => 'Bearer '.data_get($systemLwAccessToken, 'value.access_token'),
            ];
            $check = Http::timeout(60)->withHeaders($headers)->get(LW_API_GET_LIST_BOT)->json();
            if ($check && data_get($check, 'bots')) {
                $checkTokenExpire = true;
            } else {
                error_log('checkTokenLw function:'.json_encode($check));
                Log::info('checkTokenLw function:'.json_encode($check));
            }
        }

        return $checkTokenExpire;
    }

    private function refreshTokenLw(&$systemLwAccessToken)
    {
        $lw_conf = Config::get('line_works_conf');
        if ($systemLwAccessToken && data_get($systemLwAccessToken, 'value.access_token')) {
            $body = [
                'refresh_token' => data_get($systemLwAccessToken, 'value.refresh_token'),
                'grant_type' => 'refresh_token',
                'client_id' => Arr::get($lw_conf, 'client_id'),
                'client_secret' => Arr::get($lw_conf, 'client_secret'),
            ];

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => 'LC=en_US; language=en_US',
            ];

            $dataJson = Http::timeout(60)->send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $body])->json();
            if ($dataJson && data_get($dataJson, 'access_token')) {
                $data = $dataJson;
                $data['refresh_token'] = data_get($systemLwAccessToken, 'value.refresh_token');
                $systemLwAccessToken->update([
                    'value' => $data,
                ]);
            } else {
                $this->getAndSaveAccessTokenLw($lw_conf, $systemLwAccessToken);
            }
        }
    }

    private function getAndSaveAccessTokenLw($lw_conf, &$systemLwAccessToken)
    {
        // Tạo một Builder để xây dựng token
        $now = new DateTimeImmutable;
        $pathKey = base_path(Arr::get($lw_conf, 'private_key_path'));
        $config = Configuration::forSymmetricSigner(new Sha256, InMemory::file($pathKey));
        // Thêm thông tin vào token
        $token = $config->builder()
            ->issuedBy(Arr::get($lw_conf, 'client_id'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo(Arr::get($lw_conf, 'service_account'))
            ->getToken($config->signer(), $config->signingKey());

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'LC=en_US; WORKS_RE_LOC=jp1; WORKS_TE_LOC=jp1; language=en_US',
        ];
        $options = [
            'assertion' => $token->toString(),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'client_id' => Arr::get($lw_conf, 'client_id'),
            'client_secret' => Arr::get($lw_conf, 'client_secret'),
            'scope' => Arr::get($lw_conf, 'scope'),
        ];
        $dataJson = Http::timeout(60)->send('POST', LW_AUTH_TOKEN_URL, ['headers' => $headers, 'form_params' => $options])->json();
        if ($dataJson && data_get($dataJson, 'access_token')) {
            if ($systemLwAccessToken) {
                $systemLwAccessToken->update([
                    'code' => 'LW_ACCESS_TOKEN',
                    'value' => $dataJson,
                ]);
            } else {
                $systemLwAccessToken = LineWorkConf::query()->create([
                    'code' => 'LW_ACCESS_TOKEN',
                    'value' => $dataJson,
                ]);
            }
        } else {
            error_log('getAndSaveAccessTokenLw function:'.json_encode($dataJson));
            Log::info('getAndSaveAccessTokenLw function:'.json_encode($dataJson));
        }
    }

    public function updateOrCreateProfileInfor($params)
    {
        $phone = Arr::get($params, 'phone_number');
        $image = Arr::get($params, 'image_file_id');

        // Lấy thông tin hiện tại hoặc tạo mới
        $profileInfor = ProfileInformations::firstOrNew(
            ['user_id' => auth()->user()->id]
        );

        if ($phone) {
            $profileInfor->phone_number = $phone;
        }

        // Nếu có giá trị $image, cập nhật image_file_id
        if ($image) {
            $profileInfor->image_file_id = $image;
        }

        // Lưu lại thông tin
        $profileInfor->save();

        return $profileInfor->load('image');
    }

    public function getUserProfileInfor()
    {
        $userId = auth()->user()->id;
        $profileInfor = ProfileInformations::with([
            'image' => function ($query) {
                $query->select('id', 'file_name', 'file_path', 'file_url');
            },
        ])->where('user_id', $userId)->first();

        return $profileInfor;
    }

    public function getUserWithDepartment($params)
    {
        $data = User::with(['department'])
            ->whereHas('department', function ($query) use ($params) {
                $query->where('id', $params);
            })->get();

        return $data;
    }

    public function sendMailSetUpPassword($request)
    {
        if ($request->email !== null) {
            $payLoad = json_encode([
                'user_code' => $request->user_code,
                'email' => $request->email
            ]);
            $userId = Auth::user()->id;
            UserPasswordHistory::create([
                'user_id' => $request->user_code,
                'email' => $request->email,
                'created_by' => $userId
            ]);

            Mail::to($request->email)->send(new RegisterMail(Crypt::encryptString($payLoad)));
            return [
                'code' => 200,
                'message' => 'パスワード初期設定メールが送信されました。'
            ];
        } else {
            return [
                'code' => 422,
                'message' => 'メールアドレスを入力してください。'
            ];
        }
    }

}
