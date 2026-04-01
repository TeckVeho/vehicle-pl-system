<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\UserController;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Mockery as m;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $userRepository;

    protected UserController $userController;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->artisan('db:seed');

        $this->faker = Faker::create();

        $userRepository = new UserRepository($this->app);
        $this->userRepository = m::mock($userRepository)->makePartial();
        $this->userController = new UserController(
            $this->app->instance(UserRepositoryInterface::class, $this->userRepository)
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function newUserRequest(): UserRequest
    {
        return new UserRequest;
    }

    public function test_user_create_success(): void
    {
        $request = $this->newUserRequest();
        $request->merge([
            'role' => '1',
            'id' => (string) random_int(200000, 299999),
            'name' => 'nguyen',
            'password' => '123456789',
            'confirm_password' => '123456789',
        ]);

        $response = $this->userController->store($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_update_success(): void
    {
        $user = User::factory()->create([
            'role' => 1,
            'email' => 'update-success@example.test',
        ]);

        $request = $this->newUserRequest();
        $request->merge([
            'role' => '1',
            'name' => 'Updated',
            'email' => $user->email,
            'current_password' => '123456789',
            'password' => '123456789',
            'confirm_password' => '123456789',
        ]);

        $response = $this->userController->update($request, $user->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_show_id(): void
    {
        $user = User::factory()->create(['email' => 'show@example.test']);
        $response = $this->userController->show($user->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_show_all(): void
    {
        User::factory()->create(['email' => 'index@example.test']);
        $request = $this->newUserRequest();
        $response = $this->userController->index($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_destroy(): void
    {
        $user = User::factory()->create(['email' => 'destroy@example.test']);
        $response = $this->userController->destroy($user->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_update_not_name(): void
    {
        $user = User::factory()->create([
            'role' => 1,
            'email' => 'noname@example.test',
        ]);

        $request = $this->newUserRequest();
        $request->merge([
            'role' => '1',
            'name' => '',
            'email' => $user->email,
        ]);

        $response = $this->userController->update($request, $user->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_user_not_update_id(): void
    {
        $user = User::factory()->create([
            'role' => 1,
            'email' => 'noidchange@example.test',
        ]);

        $request = $this->newUserRequest();
        $request->merge([
            'role' => '1',
            'name' => $this->faker->name(),
            'email' => $user->email,
        ]);

        $response = $this->userController->update($request, $user->id);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame($user->id, $user->fresh()->id);
    }

    public function test_user_create_not_name(): void
    {
        $id = (string) random_int(200000, 299999);
        $request = $this->newUserRequest();
        $request->merge([
            'role' => '1',
            'name' => '',
            'id' => $id,
            'password' => '123456789',
            'confirm_password' => '123456789',
        ]);

        $response = $this->userController->store($request);
        // Gọi trực tiếp controller: không qua HTTP nên UserRequest không validate — vẫn 200.
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('', User::query()->where('id', $id)->value('name'));
    }

    public function test_user_create_only_password(): void
    {
        $id = (string) random_int(200000, 299999);
        $request = $this->newUserRequest();
        $request->merge([
            'role' => '1',
            'name' => $this->faker->name(),
            'id' => $id,
            'password' => null,
            'confirm_password' => null,
        ]);

        $response = $this->userController->store($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotNull(User::query()->where('id', $id)->value('password'));
    }
}
