<?php

namespace Tests\Feature;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserManagementFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    private $crew;

    private $manager;

    private $url = 'api/system';

    protected $faker;

    use RefreshDatabase;

    protected function setUp(): void
    {
        $this->faker = Faker::create();
        parent::setUp();
        $this->artisan('db:seed');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function actingAs($user, $driver = 'api')
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', "Bearer {$token}");
        parent::actingAs($user, $driver);

        return $this;
    }

    public function test_user_create_validation_full_field_blank()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', []);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_user_create_validation_more_field()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', ['role' => '', 'name' => 12, 'password' => 1, 'password_confirmation' => '']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_user_create_validation_one_field()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', ['role' => 1, 'name' => 'name test', 'id' => 1, 'password' => '', 'password_confirmation' => '']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_update_create_validation_full_field_blank()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->putJson('api/user/'.$user->id, []);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_update_create_validation_more_field()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->putJson('api/user/'.$user->id, ['role' => '', 'name' => 12, 'password' => 1, 'password_confirmation' => '']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_update_create_validation_one_field()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->putJson('api/user/'.$user->id, ['role' => '', 'name' => 'name test']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_validation_not_in_list_role()
    {
        $role = Str::random(6);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', ['role' => $role, 'name' => 'name test', 'id' => 1, 'password' => '123456789', 'password_confirmation' => '']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_validation_max_name()
    {
        $name = Str::random(256);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', ['role' => 1, 'name' => $name, 'id' => 1, 'password' => '123456789', 'password_confirmation' => '']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_validation_user_id_duplicate()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', ['role' => 1, 'name' => 'name', 'id' => $user->id, 'password' => '123456789', 'password_confirmation' => '']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }

    public function test_validation_min_password()
    {
        $pass = Str::random(6);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('api/user', ['role' => 1, 'name' => 'name', 'id' => $user->id, 'password' => $pass, 'password_confirmation' => $pass]);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getData()->code);
    }
}
