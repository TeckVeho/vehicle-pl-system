<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistertAccountTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Bus::fake();
        $this->artisan('db:seed');
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_register_account_not_exist_in_shain_data() // employee code doesnt exist in database.
    {
        $response = $this->post('/api/register-account', [
            'user_code' => '12345678910',
            'email' => 'nam27512345678@gmail.com',
        ]);
        $response->assertJson(['code' => 401, 'message' => '入力した社員番号は存在しません'], $strict = false);
    }

    public function test_already_register()
    {
        $user = User::first();
        $user->email = 'nam27512345678@gmail.com';
        $user->save();
        $response = $this->post('/api/register-account', [
            'user_code' => $user->id,
            'email' => 'nam27512345678@gmail.com',
        ]);
        $response->assertJson([
            'code' => 401,
            'message' => '入力した社員番号はすでに初期登録を完了しています',
        ], $strict = false);
    }

    public function test_register_invalid_user_code()
    {
        $user = User::first();
        $response = $this->post('/api/register-account', [
            'user_code' => null,
            'email' => 'nam27512345678@gmail.com',
        ]);
        $response->assertJson([
            'code' => 422,
            'message' => 'user codeは、必ず指定してください。',
            'message_internal' => [
                'user_code' => [
                    'user codeは、必ず指定してください。',
                ],
            ],
            'data_error' => null,
        ], $strict = false);
    }

    public function test_register_invalid_email()
    {
        $user = User::first();
        $response = $this->post('/api/register-account', [
            'user_code' => $user->id,
            'email' => 'nam27512345678mail.com', // invalid
        ]);
        $response->assertJson([
            'code' => 422,
            'message' => 'emailは、有効なメールアドレス形式で指定してください。',
            'message_internal' => [
                'email' => [
                    'emailは、有効なメールアドレス形式で指定してください。',
                ],
            ],
            'data_error' => null,
        ], $strict = false);
    }

    public function test_case_invalid_password() // less than 8
    {
        $user = User::first();
        $payLoad = json_encode([
            'user_code' => $user->id,
            'email' => 'nam27512345678@gmail.com',
        ]);

        $response = $this->post('/api/register-password', [
            'value' => Crypt::encryptString($payLoad),
            'password' => '123', // invalid
        ]);
        $response->assertJson([
            'code' => 422,
            'message' => 'パスワードは、8文字以上にしてください。',
            'message_internal' => [
                'password' => [
                    'パスワードは、8文字以上にしてください。',
                ],
            ],
            'data_error' => null,
        ], $strict = false);
    }

    public function test_case_invalid_password2() // more than 16
    {
        $user = User::first();
        $payLoad = json_encode([
            'user_code' => $user->id,
            'email' => 'nam27512345678@gmail.com',
        ]);

        $response = $this->post('/api/register-password', [
            'value' => Crypt::encryptString($payLoad),
            'password' => '1234567891011123141516', // invalid
        ]);

        $response->assertJson([
            'code' => 422,
            'message' => 'パスワードは、16文字以下にしてください。',
            'message_internal' => [
                'password' => [
                    'パスワードは、16文字以下にしてください。',
                ],
            ],
            'data_error' => null,
        ], $strict = false);
    }

    public function test_register_password_already_register()
    {
        $user = User::first();
        $payLoad = json_encode([
            'user_code' => $user->id,
            'email' => 'nam27512345678@gmail.com',
        ]);

        $response = $this->post('/api/register-password', [
            'value' => Crypt::encryptString($payLoad),
            'password' => '123456789', // valid
        ]);

        $response2 = $this->post('/api/register-password', [
            'value' => Crypt::encryptString($payLoad),
            'password' => '123456789', // valid
        ]);

        $response2->assertJson([
            'code' => 200,
            'message' => 'already register',
        ], $strict = false);
    }

    public function test_register_password_not_exist_in_shain_data() // employee code doesnt exist in database.
    {
        $user = User::first();
        $payLoad = json_encode([
            'user_code' => 'vut_het_di_moi_suy_tu_va_cu_de_niem_vui_quanh_em',
            'email' => 'nam27512345678@gmail.com',
        ]);

        $response = $this->post('/api/register-password', [
            'value' => Crypt::encryptString($payLoad),
            'password' => '123456789', // valid
        ]);

        $response->assertJson([
            'code' => 200,
            'message' => 'user not found',
        ], $strict = false);
    }
}
