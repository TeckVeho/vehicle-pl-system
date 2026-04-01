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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RouteUnitTest extends TestCase
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
    public function test_route_list()
    {
        Route::factory()->count(10)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10');
        $this->assertEquals(10, $response->decodeResponseJson()['data']['pagination']['total_records']);
    }

    public function test_list_with_route_non_delivery_week()
    {
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $route = Route::factory()->count(1)
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 7), 'is_week' => 1];
                    })
            )->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10');
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonPath('data.result.0.id', 1);
        $response->assertJsonStructure([
            'data' => ['result' => ['*' => ['id', 'list_week']],
            ],
        ]);
    }

    public function test_list_with_route_non_delivery_month()
    {
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $route = Route::factory()->count(1)
            ->has(
                RouteNonDelivery::factory()->count(3)
                    ->state(function (array $attributes, Route $route) {
                        return ['number_at' => rand(1, 31), 'is_week' => 0];
                    })
            )->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10');
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertEquals(3, count($response->decodeResponseJson()['data']['result'][0]['list_month']));
        $response->assertJsonPath('data.result.0.id', 1);
        $response->assertJsonStructure([
            'data' => ['result' => ['*' => ['id', 'list_month']],
            ],
        ]);
    }

    public function test_list_with_store()
    {
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $route = Route::factory()->count(1)
            ->hasAttached(Store::factory()->count(3))->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10');
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertEquals(3, $response->decodeResponseJson()['data']['result'][0]['store_count']);
        $response->assertJsonPath('data.result.0.id', 1);
        $response->assertJsonStructure([
            'data' => ['result' => ['*' => ['id', 'store_count']],
            ],
        ]);
    }

    public function test_data_list_with_page()
    {
        Route::factory()->count(20)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?page=2&per_page=10');
        $this->assertEquals(2, $response->decodeResponseJson()['data']['pagination']['current_page']);
    }

    public function test_list_with_per_page()
    {
        Route::factory()->count(20)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=20');
        $this->assertEquals(20, $response->decodeResponseJson()['data']['pagination']['per_page']);
    }

    public function test_list_with_filter_by_department()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $listDepartment = Department::all()->pluck('name', 'id')->toArray();
        $customer = Arr::random(array_keys($listDepartment));

        $route = Route::factory()->count(1)
            ->state(function (array $attributes) use ($customer) {
                return ['department_id' => $customer];
            })->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10&department_id='.$customer);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertEquals($listDepartment[$customer], $response->decodeResponseJson()['data']['result'][0]['department_name']);
        $response->assertJsonPath('data.result.0.id', 1);
    }

    public function test_list_with_filter_by_name()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $name = $this->faker->name;
        $route = Route::factory()->count(1)
            ->state(function (array $attributes) use ($name) {
                return ['name' => $name];
            })->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10&name='.$name);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertEquals($name, $response->decodeResponseJson()['data']['result'][0]['name']);
        $response->assertJsonPath('data.result.0.name', $name);
    }

    public function test_list_with_filter_by_customer()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $listCustomer = Customer::all()->pluck('customer_name', 'id')->toArray();
        $customer = Arr::random(array_keys($listCustomer));

        $route = Route::factory()->count(1)
            ->state(function (array $attributes) use ($customer) {
                return ['customer_id' => $customer];
            })->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10&customer_id='.$customer);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        // $this->assertEquals($listCustomer[$customer], $response->decodeResponseJson()['data']['result'][0]['customer_name']);
        $response->assertJsonPath('data.result.0.customer_name', $listCustomer[$customer]);
    }

    public function test_list_with_sort_by_random()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $sort_by = Arr::random(['epartment_name', 'name', 'customer_name', 'route_fare_type', 'fare', 'highway_fee', 'highway_fee_holiday', 'store']);

        $route = Route::factory()->count(20)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10&sort_by='.$sort_by);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure([
            'data' => ['result' => ['*' => ['id', 'store_count']],
            ],
        ]);
    }

    public function test_list_with_sort_by_and_type_random()
    {
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        $sort_by = Arr::random(['department_name', 'name', 'customer_name', 'route_fare_type', 'fare', 'highway_fee', 'highway_fee_holiday', 'store']);
        $sort_type = Arr::random([true, false]);

        $route = Route::factory()->count(20)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/route?per_page=10&sort_by='.$sort_by.'&sort_type='.$sort_type);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure([
            'data' => ['result' => ['*' => ['id', 'store_count']],
            ],
        ]);
    }
}
