<?php

namespace Repository;

use App\Http\Requests\RemindRequest;
use App\Jobs\SyncUserJob;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\RemindPasswordEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    protected $shopRepository;

    public function __construct()
    {
    }

    /**
     *
     * Handle action login of user.
     *
     * @param LoginRequest $request
     * @param null $guard
     * @return array
     */
    public function doLogin(LoginRequest $request, $guard = null): array
    {
        $input = $request->only('id', 'password');
        $attempt = auth()->attempt($input);
        $mes = '';
        $checkUpdateUserContacts = 0;

        if ($attempt) {
            $user = auth()->user()->load([
                'department:id,name,position,post_code',
                'employee:id,employee_code',
                'user_contacts'
            ]);
            $user->update_user_contact = $checkUpdateUserContacts;
            return [
                'user' => $user,
                'attempt' => $attempt,
            ];
        } else {
            $checkUser = User::where('id', $request->id)->first();
            if ($checkUser && !$checkUser->password && ($checkUser->mail == '' || !$checkUser->mail)) {
                return [
                    'attempt' => false,
                    'mes' => '初期登録が完了していません' // not sign
                ];
            }

            $mes = '社員番号、またはパスワードが一致しません';
            return [
                'attempt' => false,
                'mes' => $mes
            ];
        }
    }

    public function doLoginMobile(LoginRequest $request, $guard = null): array
    {
        $input = $request->only('id', 'password');
        $customClaims = ['app' => 'cloud_' . app()->environment()];
        $attempt = auth()->setTTL(525600)->claims($customClaims)->attempt($input);
        $mes = '';
        if ($attempt) {
            $user = auth()->user()->load([
                'department:id,name,position,post_code',
                'employee:id,employee_code']);
            return [
                'user' => $user,
                'attempt' => $attempt,
            ];
        } else {
            $checkUser = User::query()->where('id', $request->id)->first();
            if ($checkUser && !$checkUser->password && ($checkUser->mail == '' || !$checkUser->mail)) {
                return [
                    'attempt' => false,
                    'mes' => '初期登録が完了していません' // not sign
                ];
            }

            $mes = '社員番号、またはパスワードが一致しません';
            return [
                'attempt' => false,
                'mes' => $mes
            ];
        }
    }

    /**
     * @param array $params
     * @return bool|void
     */
    public function register(array $params)
    {
        $user = User::create($params);
        //        $this->grantRoleNewUser($user);

        return $user;
    }

    public function changeTempPass(array $attr, $emp_code)
    {
        $user = User::where('emp_code', $emp_code)->first();
        $mes = "";
        if (!$user)
            //            $mes='server.emp_code_not_exist';
            //        elseif(!$user->is_new_pw)
            //            $mes='server.temp_pass_not_active';
            //        elseif ($attr['temp_pass'] != $user->temp_pass)
            //            $mes='server.temp_pass_not_match';
            //        if($mes)
            //            return ['message'=>$mes];
            $user->password = $attr['password'];
        //        if (isset($attr['email']))
        //            $user->email = $attr['email'];
        //        if (isset($attr['proxy_email']))
        //            $user->proxy_email = $attr['proxy_email'];
        //$user->is_new_pw = 0;
        if ($user->save()) {
            return $user;
        }
    }

    protected function grantRoleNewUser(User &$user)
    {
        $roleOwnerDefault = array_key_first(config('laratrust_seeder.roles_structure', []));
        $shopOwner = Role::where('name', $roleOwnerDefault)->first();
        $user->attachRole($shopOwner);
    }

    public function remindPassword(RemindRequest $request)
    {
        $user = User::where('id', $request->emp_code)->first();

        if (!$user) {
            $this->mes = '入力した社員番号は存在しません';
            return false;
        } else if ($user->email == "" || $user->email == null) {
            $this->mes = '初期登録が完了していません';
            return false;
        }

        $email = $user->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mes = 'server.email_invalid';
            return false;
        }
        //        $randPass = Str::random(9);
        //        $user->update([
        //            // 'temp_pass' => Hash::make($randPass)
        //            'temp_pass' => $randPass
        //        ]);
        $detail = [
            'email' => $email,
            //'password' => $randPass,
            'emp_code' => $user->id,
            'value' => Crypt::encryptString(json_encode(['user_code' => $user->id])),
        ];
        Mail::to($email, $user->last_name)->send(new RemindPasswordEmail($detail));
        return $user;
    }

    public function RegisterAccount($request)
    {
        if ($user = User::where('id', $request->user_code)->first()) {
            if ($user->email == null) {
                $payLoad = json_encode([
                    'user_code' => $user->id,
                    'email' => $request->email
                ]);
                Mail::to($request->email)->send(new RegisterMail(Crypt::encryptString($payLoad)));
                $token = Crypt::encryptString($user->id);
                return [
                    'code' => 200,
                    'message' => 'send mail register success'
                ];
            } else {
                return [
                    'code' => 401,
                    'message' => '入力した社員番号はすでに初期登録を完了しています'
                ];
            }
        }
        return [
            'code' => 401,
            'message' => '入力した社員番号は存在しません'
        ];
    }

    public function RegisterPassword($request)
    {
        $decodeToken = json_decode(Crypt::decryptString($request->value));
        if ($user = User::where('id', $decodeToken->user_code)->first()) {
            if (isset($decodeToken->email)) {
                if ($user->email == null) {
                    $user->email = $decodeToken->email;
                    $user->password = $request->password;
                    $user->save();
                    SyncUserJob::dispatch($user->id);
                    return [
                        'code' => 200,
                        'message' => 'register success'
                    ];
                } else {
                    $user->password = $request->password;
                    $user->save();
                    SyncUserJob::dispatch($user->id);
                    return [
                        'code' => 200,
                        'message' => 'already register'
                    ];
                }
            } else {
                $user->password = $request->password;
                $user->save();
                SyncUserJob::dispatch($user->id);
                return [
                    'code' => 200,
                    'message' => 'change password success'
                ];
            }
        }
        return [
            'code' => 200,
            'message' => 'user not found'
        ];
    }
}
