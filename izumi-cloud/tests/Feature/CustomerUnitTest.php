<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerUnitTest extends TestCase
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
    public function test_data_list()
    {
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/customer');
        $this->assertEquals(10, $response->decodeResponseJson()['data']['pagination']['total_records']);
    }

    public function test_data_list_with_page()
    {
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/customer?page=2');
        $this->assertEquals(2, $response->decodeResponseJson()['data']['pagination']['current_page']);
    }

    public function test_data_list_with_per_page()
    {
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/customer?per_page=20');
        $this->assertEquals(20, $response->decodeResponseJson()['data']['pagination']['per_page']);
    }

    public function test_customer_register()
    {
        $response = $this->withHeaders(['Authorization' => $this->token])->post('api/customer', ['customer_name' => Str::substr($this->faker->name, 0, 19)]);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
    }

    public function test_customer_register_with_data_null()
    {
        $response = $this->withHeaders(['Authorization' => $this->token])->post('api/customer', []);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJson([
            'code' => 422,
            'message' => '荷主名は必須です',
        ], $strict = false);
    }

    public function test_customer_update()
    {
        $cus = Customer::factory()->count(1)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->put('api/customer/'.$cus[0]->id, ['customer_name' => Str::substr($this->faker->name, 0, 19)]);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
    }

    public function test_customer_register_update_with_validate()
    {
        $cus = Customer::factory()->count(1)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->put('api/customer/'.$cus[0]->id, []);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJson([
            'code' => 422,
            'message' => '荷主名は必須です',
        ], $strict = false);
    }

    public function test_customer_detail()
    {
        $cus = Customer::factory()->count(1)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->get('api/customer/'.$cus[0]->id, []);
        $response->assertJson(['code' => 200], $strict = true);
    }
}
