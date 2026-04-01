<?php

namespace Tests\Feature;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();

        $response = $this->post('/api/auth/login', [
            'id' => '111111',
            'password' => '123456789',
        ]);
        $response->assertJson(['code' => 200], false);
        $user = User::query()->where('id', '111111')->first();
        $this->assertNotNull($user);
        $this->token = 'Bearer '.JWTAuth::fromUser($user);
    }

    public function test_vehicle_placeholder(): void
    {
        $this->markTestSkipped('Vehicle tests are not implemented yet.');
    }
}
