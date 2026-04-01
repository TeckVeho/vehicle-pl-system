<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class StoreTest extends TestCase
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

        $response = $this->post('/api/auth/login', ['id' => '111111', 'password' => '123456789']);
        $response->assertJson(['code' => 200], $strict = false);
        $user = User::query()->where('id', '111111')->first();
        $this->assertNotNull($user);
        $this->token = 'Bearer '.JWTAuth::fromUser($user);
        Store::factory()->count(10)->create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_paginate()
    {
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])->get('/api/store?per_page=1&page=1');
        $response->assertJson([
            'data' => [
                'pagination' => [],
            ],
        ], false);
    }

    public function test_validate_store_name_too_long()
    {
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])->post('/api/store', [
            'store_name' => $this->faker->words(255),
        ]);
        $response->assertJson([
            'code' => 422,
            'message' => '店舗名は、20文字以下にしてください。',
            'data_error' => null,
        ]);
    }

    public function test_validate_store_name_null()
    {
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])->post('/api/store', [
            'store_name' => '',
        ]);
        $response->assertJson([
            'code' => 422,
            'message' => '店舗名は必須です',
            'data_error' => null,
        ]);
    }

    public function test_create_new()
    {
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])->post('/api/store', [
            'store_name' => '112213',
            'delivery_destination_code' => '1231231',
            'destination_name_kana' => '123123',
            'destination_name' => '123123',
            'tel_number' => '1234567891234',
            'address_1' => '123123',
            'address_2' => '123123',
            'post_code' => '12345678',
            'delivery_manual' => [''],
        ]);
        $response->assertJson(['code' => 200]);
        $response->assertJson(['data' => ['store_name' => 112213]]);
    }

    public function test_create_new_validate()
    {
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])->post('/api/store', [
            'store_name' => '',
            'delivery_destination_code' => '',
            'destination_name_kana' => '',
            'destination_name' => '',
            'tel_number' => '',
            'address_1' => '',
            'address_2' => '',
            'post_code' => '',
        ]);
        $response->assertJson([
            'code' => 422,
            'message' => '店舗名は必須です',
            'data_error' => null,
        ]);
    }

    public function test_update_new()
    {
        $store = Store::factory()->count(1)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->put('/api/store/'.$store->first()->id, [
            'store_name' => '112213',
            'delivery_destination_code' => '1231231',
            'destination_name_kana' => '123123',
            'destination_name' => '123123',
            'tel_number' => '1234567891234',
            'address_1' => '123123',
            'address_2' => '123123',
            'post_code' => '12345678',
            'delivery_manual' => [''],
        ]);
        $response->assertJson(['code' => 200]);
        $response->assertJson(['data' => ['post_code' => '12345678']]);
    }

    public function test_update_new_validate()
    {
        $store = Store::factory()->count(1)->create();
        $response = $this->withHeaders(['Authorization' => $this->token])->put('/api/store/'.$store->first()->id, [
            'delivery_destination_code' => 'aaaaa',
            'destination_name_kana' => $this->faker->text(101),
            'destination_name' => $this->faker->text(101),
            'tel_number' => $this->faker->text(101),
            'address_1' => $this->faker->text(101),
            'address_2' => $this->faker->text(101),
            'post_code' => $this->faker->text(101),
        ]);
        $response->assertJson(['code' => 200]);
        $response->assertJsonStructure(['code', 'data']);
    }

    public function test_detail_a_store_without_passcode()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->get("/api/mobile/store/$store->id");
        $response->assertStatus(200);
    }

    public function test_detail_a_store_with_passcode()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', '!=', null)->first();
        $pass = (string) $store->pass_code;
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?_method=get", [
                'pass_code' => $pass,
            ]);
        $response->assertStatus(200);
    }

    public function test_detail_a_store_with_wrong_passcode()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', '!=', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?_method=get", [
                'pass_code' => '1234',
            ]);
        $response->assertStatus(422);
        $response->assertJson([
            'code' => 422,
            'message' => 'パスコードが一致しません',
            'message_content' => [
                'パスコードが一致しません',
            ],
            'message_internal' => [
                'pass_code' => [
                    'パスコードが一致しません',
                ],
            ],
            'data_error' => null,
        ]);
    }

    public function test_edit_a_store_in_mobile()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id", [
                Store::BUSSINESS_CLASSIFICATION => Store::BUSSINESS_CLASSIFICATION,
                Store::DELIVERY_FREQUENCY => Store::DELIVERY_FREQUENCY,
                Store::QUANTITY_DELIVERY => '1000',
                //                Store::SPECIFY_DELIVERY_TIME => Store::SPECIFY_DELIVERY_TIME,
                //                Store::SCHEDULED_TIME => Store::SCHEDULED_TIME,
                Store::HEIGHT => '100',
                Store::WIDTH => '100',
                Store::PARKING_PLACE => 1,
                Store::NOTE_1 => Store::NOTE_1,
                Store::DAISHA => 1,
                Store::NOTE_2 => Store::NOTE_2,
                Store::PLACE => Store::PLACE,
                Store::NOTE_3 => Store::NOTE_3,
                Store::EMPTY_RECOVERY => Store::EMPTY_RECOVERY,
                Store::KEY => 1,
                Store::NOTE_4 => Store::NOTE_4,
                Store::CANCEL_METHOD => Store::CANCEL_METHOD,
                Store::GRACE_TIME => Store::GRACE_TIME,
                Store::COMPANY_NAME => Store::COMPANY_NAME,
                Store::TEL_NUMBER => '0987654123',
                Store::INSIDE_RULE => 1,
                Store::LICENSE => Store::LICENSE,
                Store::RECEPTION_OR_ENTRY => Store::RECEPTION_OR_ENTRY,
                Store::CERFT_REQUIRED => Store::CERFT_REQUIRED,
                Store::NOTE_5 => Store::NOTE_5,
                Store::ELEVATOR => 1,
                Store::NOTE_6 => Store::NOTE_6,
                // Store::DELIVERY_ROUTE_MAP_PATH => null, // UploadedFile::fake()->image('avatar.png', 10, 10),
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
                // Store::PARKING_POSITION_1_FILE_PATH => null, //UploadedFile::fake()->image('avatar.png', 10, 10),
                Store::PARKING_POSITION_1_OTHER_REMARK => Store::PARKING_POSITION_1_OTHER_REMARK,
                // Store::PARKING_POSITION_2_FILE_PATH => null, //UploadedFile::fake()->image('avatar.png', 10, 10),
                Store::PARKING_POSITION_2_OTHER_REMARK => Store::PARKING_POSITION_2_OTHER_REMARK,
            ]);
        $response->assertStatus(200);
    }

    public function test_edit_with_valid_file_attatch()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?", [
                Store::DELIVERY_ROUTE_MAP_PATH => UploadedFile::fake()->image('avatar.png', 10, 10),
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
                Store::PARKING_POSITION_1_FILE_PATH => UploadedFile::fake()->image('avatar.png', 10, 10),
                Store::PARKING_POSITION_1_OTHER_REMARK => Store::PARKING_POSITION_1_OTHER_REMARK,
                Store::PARKING_POSITION_2_FILE_PATH => UploadedFile::fake()->image('avatar.png', 10, 10),
                Store::PARKING_POSITION_2_OTHER_REMARK => Store::PARKING_POSITION_2_OTHER_REMARK,
            ]);
        $response->assertStatus(200);
    }

    public function test_edit_with_invalidate_file_attatch()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?", [
                Store::DELIVERY_ROUTE_MAP_PATH => UploadedFile::fake()->create('test.pdf'),
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
                Store::PARKING_POSITION_1_FILE_PATH => UploadedFile::fake()->image('avatar.jpeg'),
                Store::PARKING_POSITION_1_OTHER_REMARK => Store::PARKING_POSITION_1_OTHER_REMARK,
                Store::PARKING_POSITION_2_FILE_PATH => UploadedFile::fake()->image('avatar.jpeg'),
                Store::PARKING_POSITION_2_OTHER_REMARK => Store::PARKING_POSITION_2_OTHER_REMARK,
            ]);
        $response->assertStatus(422);
    }

    public function test_update_store_from_mobile_wrong_pass_code()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', '!=', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?", [
                Store::DELIVERY_ROUTE_MAP_PATH => UploadedFile::fake()->image('avatar.jpeg'),
                Store::DELIVERY_ROUTE_MAP_OTHER_REMARK => Store::DELIVERY_ROUTE_MAP_OTHER_REMARK,
                Store::PARKING_POSITION_1_FILE_PATH => UploadedFile::fake()->image('avatar.jpeg'),
                Store::PARKING_POSITION_1_OTHER_REMARK => Store::PARKING_POSITION_1_OTHER_REMARK,
                Store::PARKING_POSITION_2_FILE_PATH => UploadedFile::fake()->image('avatar.jpeg'),
                Store::PARKING_POSITION_2_OTHER_REMARK => Store::PARKING_POSITION_2_OTHER_REMARK,
                Store::PASS_CODE => '1234',
            ]);
        $response->assertStatus(422);
    }

    public function test_invalid_the_list_of_delivery_manual_over_limit()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', '!=', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?", [
                Store::PASS_CODE => $store->pass_code,
                Store::DELIVERY_MANUAL => [
                    '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21',
                ],
            ]);
        $response->assertStatus(422);
    }

    public function test_valid_the_list_of_delivery_manual()
    {
        Store::factory()->state(function ($att) {
            return ['pass_code' => null];
        })->count(20)->create();
        $store = Store::where('pass_code', '!=', null)->first();
        $response = $this->withHeaders(['Authorization' => $this->token, 'Content-Type' => 'multipart/form-data'])
            ->post("/api/mobile/store/$store->id?", [
                Store::PASS_CODE => $store->pass_code,
                Store::DELIVERY_MANUAL => [
                    '1',
                ],
            ]);
        $response->assertStatus(200);
    }
}
