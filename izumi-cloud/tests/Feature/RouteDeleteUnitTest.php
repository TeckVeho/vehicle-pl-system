<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Customer;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use App\Models\Store;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RouteDeleteUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();

        Customer::factory()->count(10)->create();
        Store::factory()->count(10)->create();

        $response = $this->post('/api/auth/login', [
            'id' => '111111',
            'password' => '123456789',
        ]);
        $response->assertJson(['code' => 200], false);
        $user = User::query()->where('id', '111111')->first();
        $this->assertNotNull($user);
        $this->token = 'Bearer '.JWTAuth::fromUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * A basic unit test example 1.
     *
     * @return void
     */
    #[Test]
    public function test_delete_not_use_by_course()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)->create();

        $response = $this->withHeaders(['Authorization' => $this->token])->delete('api/route/'.$route->first()->id);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonPath('message', trans('messages.mes.delete_success'));
    }

    public function test_delete_validation_use_by_course()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $Course = Course::factory()->count(1)->create();

        $route = Route::factory()->count(1)
            ->hasAttached($Course, ['position' => 1])
            ->create();

        $response = $this->withHeaders(['Authorization' => $this->token])->delete('api/route/'.$route->first()->id);
        $this->assertEquals(422, $response->json()['code']);
        $response->assertJsonPath('message', 'このルートはコースに組み込まれているため削除できません。');
    }
}
