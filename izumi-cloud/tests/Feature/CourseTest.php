<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Route;
use App\Models\Store;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
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

        $response = $this->post('/api/auth/login', ['id' => '111111', 'password' => '123456789']);
        $response->assertJson(['code' => 200], $strict = false);
        $user = User::query()->where('id', '111111')->first();
        $this->assertNotNull($user);
        // doLogin trả attempt=bool; access_token từ API không phải JWT — dùng JWTAuth trong test.
        $this->token = 'Bearer '.JWTAuth::fromUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        return [
            'Authorization' => $this->token,
            'Accept' => 'application/json',
        ];
    }

    private function initData(): void
    {
        $route = Route::factory()->count(1)->create();
        $fake_1_course = $this->withHeaders($this->authHeaders())->post(
            '/api/course',
            [
                'course_code' => $this->faker->name,
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [$route->first()->id],
            ]
        );
    }

    public function test_validate_of_course_type()
    {
        $response = $this->withHeaders($this->authHeaders())->post(
            '/api/course',
            [
                'course_code' => $this->faker->name,
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => 100,
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択されたコース種別は、有効ではありません。',
            'data_error' => null,
        ]);
    }

    public function test_validate_of_delivery_type()
    {
        $response = $this->withHeaders($this->authHeaders())->post(
            '/api/course',
            [
                'course_code' => $this->faker->name,
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => 100,
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択された配送種別は、有効ではありません。',
            'data_error' => null,
        ]);
    }

    public function test_validate_of_gate_type()
    {
        $response = $this->withHeaders($this->authHeaders())->post(
            '/api/course',
            [
                'course_code' => $this->faker->name,
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => 3,
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択されたゲートは、有効ではありません。',
            'data_error' => null,
        ]);
    }

    public function test_validate_of_wing_type()
    {
        $response = $this->withHeaders($this->authHeaders())->post(
            '/api/course',
            [
                'course_code' => $this->faker->name,
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => 3,
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択されたウイングは、有効ではありません。',
            'data_error' => null,
        ]);
    }

    // ----------------- //

    public function test_validate_update_of_course_type()
    {
        if (! Course::first()) {
            $this->initData();
        }
        $response = $this->withHeaders($this->authHeaders())->put(
            '/api/course/'.Course::first()->id,
            [
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => 100,
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択されたコース種別は、有効ではありません。',
            'message_content' => ['選択されたコース種別は、有効ではありません。', 'ルート名は必須です'],
            'data_error' => null,
        ]);
    }

    public function test_validate_update_of_delivery_type()
    {
        if (! Course::first()) {
            $this->initData();
        }
        $response = $this->withHeaders($this->authHeaders())->put(
            '/api/course/'.Course::first()->id,
            [
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => 100,
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択された配送種別は、有効ではありません。',
            'message_content' => ['選択された配送種別は、有効ではありません。', 'ルート名は必須です'],
            'data_error' => null,
        ]);
    }

    public function test_validate_update_of_gate_type()
    {
        if (! Course::first()) {
            $this->initData();
        }
        $response = $this->withHeaders($this->authHeaders())->put(
            '/api/course/'.Course::first()->id,
            [
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => 3,
                'wing' => WING_VALUE['existing'],
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択されたゲートは、有効ではありません。',
            'data_error' => null,
        ]);
    }

    public function test_validate_update_of_wing_type()
    {
        if (! Course::first()) {
            $this->initData();
        }
        $response = $this->withHeaders($this->authHeaders())->put(
            '/api/course/'.Course::first()->id,
            [
                'start_date' => '2022-05-01',
                'end_date' => '2022-06-01',
                'course_type' => COURSE_TYPE_VALUE['cvs'],
                'bin_type' => BIN_TYPE_VALUE['one_day'],
                'delivery_type' => DELIVERY_TYPE_VALUE['dry'],
                'start_time' => '20:00:00',
                'gate' => GATE_VALUE['existing'],
                'wing' => 3,
                'tonnage' => TONNAGE[2],
                'quantity' => 5,
                'allowance' => 1000,
                'department_id' => Department::first()->id,
                'routes' => [],
            ]
        );

        $response->assertJson([
            'code' => 422,
            'message' => '選択されたウイングは、有効ではありません。',
            'message_content' => ['選択されたウイングは、有効ではありません。', 'ルート名は必須です'],
            'data_error' => null,
        ]);
    }
}
