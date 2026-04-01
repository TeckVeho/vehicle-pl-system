<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Repository\AuthRepository;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected $request;

    protected $authRepository;

    protected $loginRequest;

    protected $user;

    protected $param;

    protected $faker;

    use RefreshDatabase;

    protected function setUp(): void
    {
        // Facade::clearResolvedInstances();
        $this->faker = Faker::create();
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = [
            'id' => 111111,
            'name' => $this->faker->name,
            'password' => Hash::make('123456789'),
            'role' => $this->faker->name,
        ];
        $this->request = new LoginRequest;
        $this->authRepository = new AuthRepository;
        $this->loginRequest = new LoginRequest;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_login()
    {
        // Gọi hàm tạo
        $param = [
            'id' => '111111',
            'password' => '123456789',
        ];
        $this->request->merge($param);
        $response = $this->authRepository->doLogin($this->request, $guard = null);
        // check if the object is checked
        $this->assertInstanceOf(User::class, $response['user']);
        // Check if you can check the data or not
        $this->assertEquals($param['id'], $response['user']->id);
    }

    public function test_login_wrong_id_or_pass()
    {
        $param = [
            'id' => '122112',
            'password' => '1234567899',
        ];
        $this->request->merge($param);
        $response = $this->authRepository->doLogin($this->request, $guard = null);
        $this->assertEquals(false, $response['attempt'],
            'server.emp_code_or_password_incorrect');
    }

    public function test_login_not_have_password()
    {
        $param = [
            'id' => '123456',
            'password' => '',
        ];
        $this->request->merge($param);
        $response = $this->authRepository->doLogin($this->request, $guard = null);
        // dd($response);
        $this->assertEquals(false, $response['attempt'],
            'The パスワード field is required.');
    }

    public function test_login_not_have_params()
    {
        $param = [
            'id' => '',
            'password' => '',
        ];
        $this->request->merge($param);
        $response = $this->authRepository->doLogin($this->request, $guard = null);
        $this->assertEquals(false, $response['attempt'],
            'The emp code field is required.');
    }

    public function test_login_wrong_type_id()
    {
        $param = [
            'id' => '1234@12',
            'password' => '',
        ];
        $this->request->merge($param);
        $response = $this->authRepository->doLogin($this->request, $guard = null);
        $this->assertEquals(false, $response['attempt'],
            'The emp code may only contain letters, numbers, dashes and underscores.');
    }

    public function test_login_not_have_id()
    {
        $param = [
            'id' => '',
            'password' => '123456789',
        ];
        $this->request->merge($param);
        $response = $this->authRepository->doLogin($this->request, $guard = null);
        $this->assertEquals(false, $response['attempt'],
            'The emp code field is required.');
    }
}
