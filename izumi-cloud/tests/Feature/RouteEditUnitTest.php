<?php

namespace Tests\Feature;

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

class RouteEditUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected $faker;

    protected $dataTest;

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

        Customer::factory()->count(10)->create();
        Store::factory()->count(10)->create();

        $this->dataTest = [[
            'id' => 1,
            'name' => 'test name route',
            'customer_id' => 1,
            'route_fare_type' => 1000,
            'fare' => 1000,
            'highway_fee' => 1000,
            'highway_fee_holiday' => 1000,
            'is_government_holiday' => 1,
            'list_week' => [
                1,
                2,
            ],
            'list_month' => [
                1,
                28,
                29,
                30,
                31,
            ],
            'list_store' => [
                1,
                2,
                3,
            ],
        ]];
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
    public function test_edit_with_non_delivery_week()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 1];
                    })
            )
            ->create();

        $cus = Customer::first()->id;
        $store = Store::take(2)->pluck('id', 'id')->toArray();
        $dataTest = $this->dataTest;
        unset($dataTest[0]['list_month']);

        $dataTest[0]['customer_id'] = $cus;
        $dataTest[0]['list_store'] = $store;
        $dataTest[0]['list_week'] = [1];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route/update-many', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('route_non_delivery', count($dataTest[0]['list_week']));
    }

    public function test_edit_with_non_delivery_month()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 0];
                    })
            )
            ->create();

        $cus = Customer::first()->id;
        $store = Store::take(2)->pluck('id', 'id')->toArray();
        $dataTest = $this->dataTest;
        unset($dataTest[0]['list_week']);

        $dataTest[0]['customer_id'] = $cus;
        $dataTest[0]['list_store'] = $store;
        $dataTest[0]['list_month'] = [1];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route/update-many', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('route_non_delivery', count($dataTest[0]['list_month']));
    }

    public function test_edit_with_store()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 1];
                    })
            )
            ->create();

        $cus = Customer::first()->id;
        $store = Store::take(5)->pluck('id', 'id')->toArray();
        $dataTest = $this->dataTest;
        unset($dataTest[0]['list_week']);
        unset($dataTest[0]['list_month']);

        $dataTest[0]['customer_id'] = $cus;
        $dataTest[0]['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route/update-many', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('route_store', count($store));
    }

    public function test_edit_with_validation()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route/update-many', [[]]);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('routes', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
    }

    public function test_edit_with_validation_customer_not_exists()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 1];
                    })
            )
            ->create();

        $dataTest = $this->dataTest;
        unset($dataTest[0]['list_week']);
        unset($dataTest[0]['list_month']);

        $dataTest[0]['customer_id'] = 999999999999999;

        $response = $this->withHeaders(['Authorization' => $this->token])->post('api/route/update-many', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $response->assertJsonPath('message', '荷主は存在していません');
    }

    public function test_edit_with_validation_store_not_exists()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 1];
                    })
            )
            ->create();

        $dataTest = $this->dataTest;
        unset($dataTest[0]['list_week']);
        unset($dataTest[0]['list_month']);

        $dataTest[0]['list_store'] = [99999999999999];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route/update-many', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $response->assertJsonPath('message', '配送店舗は存在していません');
    }

    public function test_edit_all()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 1];
                    })
            )
            ->create();
        $cus = Customer::factory()->count(1)->create();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();

        $dataTest = $this->dataTest;

        $dataTest[0]['customer_id'] = $cus->first()->id;
        $dataTest[0]['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route/update-many', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonPath('data.0.customer_id', $cus->first()->id);
        $this->assertDatabaseCount('route_non_delivery', count($dataTest[0]['list_week']) + count($dataTest[0]['list_month']));
        $this->assertDatabaseCount('route_store', count($store));
    }
}
