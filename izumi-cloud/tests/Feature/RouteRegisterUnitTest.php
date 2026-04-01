<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Department;
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

class RouteRegisterUnitTest extends TestCase
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

        $this->dataTest = [
            'department_id' => 1,
            'name' => 'Name route',
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
        ];
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
    public function test_register_with_non_delivery_week()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $cus = Customer::factory()->count(1)->create();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();

        $depart = Department::first()->id;
        $dataTest = $this->dataTest;
        unset($dataTest['list_month']);

        $dataTest['department_id'] = $depart;
        $dataTest['customer_id'] = $cus->first()->id;
        $dataTest['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        //        $response->assertJsonPath('data.department_id', $depart);
        //        $response->assertJsonPath('data.customer_id', $cus->first()->id);
        $this->assertDatabaseCount('route_non_delivery', count($dataTest['list_week']));
    }

    public function test_register_with_non_delivery_month()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $cus = Customer::factory()->count(1)->create();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();

        $depart = Department::first()->id;
        $dataTest = $this->dataTest;
        unset($dataTest['list_week']);

        $dataTest['department_id'] = $depart;
        $dataTest['customer_id'] = $cus->first()->id;
        $dataTest['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('route_non_delivery', count($dataTest['list_month']));
    }

    public function test_register_with_store()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $cus = Customer::factory()->count(1)->create();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();

        $depart = Department::first()->id;
        $dataTest = $this->dataTest;
        unset($dataTest['list_week']);
        unset($dataTest['list_month']);

        $dataTest['department_id'] = $depart;
        $dataTest['customer_id'] = $cus->first()->id;
        $dataTest['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('route_store', count($store));
    }

    public function test_register_with_validation()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', []);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('routes', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
    }

    public function test_register_with_validation_department_not_exists()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $cus = Customer::factory()->count(1)->create();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();

        $dataTest = $this->dataTest;
        unset($dataTest['list_week']);
        unset($dataTest['list_month']);

        $dataTest['department_id'] = 9999999999999;
        $dataTest['customer_id'] = $cus->first()->id;
        $dataTest['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('routes', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $response->assertJsonPath('message', '拠点は存在していません');
    }

    public function test_register_with_validation_customer_not_exists()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();
        $depart = Department::first()->id;

        $dataTest = $this->dataTest;
        unset($dataTest['list_week']);
        unset($dataTest['list_month']);

        $dataTest['department_id'] = $depart;
        $dataTest['customer_id'] = 999999999999999;
        $dataTest['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('routes', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $response->assertJsonPath('message', '荷主は存在していません');
    }

    public function test_register_with_validation_store_not_exists()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $depart = Department::first()->id;
        $cus = Customer::factory()->count(1)->create();

        $dataTest = $this->dataTest;
        unset($dataTest['list_week']);
        unset($dataTest['list_month']);

        $dataTest['department_id'] = $depart;
        $dataTest['customer_id'] = $cus->first()->id;
        $dataTest['list_store'] = [99999999999999];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('routes', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $response->assertJsonPath('message', '配送店舗は存在していません');
    }

    public function test_register_all()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $depart = Department::first()->id;
        $cus = Customer::factory()->count(1)->create();
        $store = Store::factory()->count(3)->create()->pluck('id', 'id')->toArray();

        $dataTest = $this->dataTest;

        $dataTest['department_id'] = $depart;
        $dataTest['customer_id'] = $cus->first()->id;
        $dataTest['list_store'] = $store;

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/route', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonPath('data.department_id', $depart);
        $response->assertJsonPath('data.customer_id', $cus->first()->id);
        $this->assertDatabaseCount('routes', 1);
        $this->assertDatabaseCount('route_non_delivery', count($dataTest['list_week']) + count($dataTest['list_month']));
        $this->assertDatabaseCount('route_store', count($store));
    }
}
