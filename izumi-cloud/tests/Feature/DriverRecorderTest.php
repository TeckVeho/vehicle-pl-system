<?php

namespace Tests\Feature;

use App\Models\DriverRecorder;
use App\Models\File;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DriverRecorderTest extends TestCase
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
            'record_date' => '2022-01-01',
            'title' => 'title driver recorder',
            'department_id' => 1,
            'type' => 0,
            'type_one' => 1,
            'type_two' => 1,
            'shipper' => 1,
            'accident_classification' => 1,
            'place_of_occurrence' => 1,
            'remark' => 'remark note',
            'list_recorder' => [
                [
                    'movie_title' => 'string',
                    'list_movie' => [
                        'front' => 0,
                        'inside' => 0,
                        'behind' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_register_validate_all_field()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/driver-recorder', []);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('driver_recorders', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $internal = $response->decodeResponseJson()['message_internal'];
        foreach (['record_date', 'department_id', 'title', 'list_recorder', 'type_one', 'type_two', 'shipper', 'accident_classification', 'place_of_occurrence'] as $field) {
            $this->assertArrayHasKey($field, $internal, "Thiếu key validation: {$field}");
        }
    }

    public function test_register_validate_exists()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();
        $dataTest = $this->dataTest;
        $dataTest['department_id'] = 999999;
        $dataTest['list_recorder'] = [
            [
                'movie_title' => 'string',
                'list_movie' => [
                    'front' => 0,
                    'inside' => 0,
                    'behind' => 0,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/driver-recorder', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('driver_recorders', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $this->assertArrayHasKey('department_id', $response->decodeResponseJson()['message_internal']);
    }

    public function test_register_validate_record_date()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $front = File::factory()->count(1)->create();
        $inside = File::factory()->count(1)->create();
        $behind = File::factory()->count(1)->create();

        $dataTest = $this->dataTest;
        $dataTest['record_date'] = '0000/00/00';
        $dataTest['list_recorder'] = [
            [
                'movie_title' => 'string',
                'list_movie' => [
                    'front' => $front->first()->id,
                    'inside' => $inside->first()->id,
                    'behind' => $behind->first()->id,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/driver-recorder', $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('driver_recorders', 0);
        $response->assertJsonStructure(['code', 'message', 'message_content',
            'message_internal' => ['record_date']]);
    }

    public function test_register_success()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $front = File::factory()->count(1)->create();
        $inside = File::factory()->count(1)->create();
        $behind = File::factory()->count(1)->create();

        $dataTest = $this->dataTest;
        $dataTest['list_recorder'] = [
            [
                'movie_title' => 'string',
                'list_movie' => [
                    'front' => $front->first()->id,
                    'inside' => $inside->first()->id,
                    'behind' => $behind->first()->id,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => $this->token])->postJson('api/driver-recorder', $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('driver_recorders', 1);
        $this->assertDatabaseCount('driver_recorder_file', 3);
        $response->assertJsonStructure(['code', 'data' => ['department_id', 'record_date', 'title', 'type', 'id']]);
    }

    public function test_get_detail()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $factory = DriverRecorder::factory()->count(1)->create()->each(function ($data) {
            $front = File::factory()->count(1)->create();
            $inside = File::factory()->count(1)->create();
            $behind = File::factory()->count(1)->create();
            $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front']);
            $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside']);
            $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind']);
        });

        $response = $this->withHeaders(['Authorization' => $this->token])->getJson('api/driver-recorder/'.$factory->first()->id);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('driver_recorders', 1);
        $this->assertDatabaseCount('driver_recorder_file', 3);
        $response->assertJsonStructure(['code', 'data' => ['department_id', 'record_date', 'title', 'type', 'id',
            'list_recorder' => [['movie_title', 'front' => ['id', 'file_name', 'file_url'],
                'inside' => ['id', 'file_name', 'file_url'],
                'behind' => ['id', 'file_name', 'file_url']]],
        ]]);
    }

    public function test_edit_validate_all_field()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $factory = DriverRecorder::factory()->count(1)->create()->each(function ($data) {
            $front = File::factory()->count(1)->create();
            $inside = File::factory()->count(1)->create();
            $behind = File::factory()->count(1)->create();
            $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front']);
            $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside']);
            $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind']);
        });

        $response = $this->withHeaders(['Authorization' => $this->token])->putJson('api/driver-recorder/'.$factory->first()->id, []);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $internal = $response->decodeResponseJson()['message_internal'];
        foreach (['record_date', 'department_id', 'title', 'type_one', 'type_two', 'shipper', 'accident_classification', 'place_of_occurrence'] as $field) {
            $this->assertArrayHasKey($field, $internal, "Thiếu key validation: {$field}");
        }
    }

    public function test_edit_validate_exists()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $factory = DriverRecorder::factory()->count(1)->create()->each(function ($data) {
            $front = File::factory()->count(1)->create();
            $inside = File::factory()->count(1)->create();
            $behind = File::factory()->count(1)->create();
            $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front']);
            $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside']);
            $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind']);
        });

        $dataTest = $this->dataTest;
        $dataTest['department_id'] = 999999;
        $dataTest['list_recorder'] = [
            [
                'movie_title' => 'string',
                'list_movie' => [
                    'front' => 0,
                    'inside' => 0,
                    'behind' => 0,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => $this->token])->putJson('api/driver-recorder/'.$factory->first()->id, $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'message', 'message_content', 'message_internal']);
        $this->assertArrayHasKey('department_id', $response->decodeResponseJson()['message_internal']);
    }

    public function test_edit_validate_record_date()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();
        $front = File::factory()->count(1)->create();
        $inside = File::factory()->count(1)->create();
        $behind = File::factory()->count(1)->create();

        $factory = DriverRecorder::factory()->count(1)->create()->each(function ($data) use ($front, $inside, $behind) {

            $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front']);
            $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside']);
            $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind']);
        });

        $dataTest = $this->dataTest;
        $dataTest['record_date'] = '0000/00/00';
        $dataTest['list_recorder'] = [
            [
                'movie_title' => 'string',
                'list_movie' => [
                    'front' => $front->first()->id,
                    'inside' => $inside->first()->id,
                    'behind' => $behind->first()->id,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => $this->token])->putJson('api/driver-recorder/'.$factory->first()->id, $dataTest);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'message', 'message_content',
            'message_internal' => ['record_date']]);
    }

    public function test_edit_success()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('driver_recorder_file')->truncate();
        DriverRecorder::query()->truncate();
        File::query()->truncate();
        Schema::enableForeignKeyConstraints();
        $front = File::factory()->count(1)->create();
        $inside = File::factory()->count(1)->create();
        $behind = File::factory()->count(1)->create();

        $factory = DriverRecorder::factory()->count(1)->create()->each(function ($data) use ($front, $inside, $behind) {
            $data->file()->attach($front->first()->id, ['group_position' => 0, 'movie_title' => 'title1', 'type' => 'front']);
            $data->file()->attach($inside->first()->id, ['group_position' => 0, 'movie_title' => 'title2', 'type' => 'inside']);
            $data->file()->attach($behind->first()->id, ['group_position' => 0, 'movie_title' => 'title3', 'type' => 'behind']);
        });

        $dataTest = $this->dataTest;
        $dataTest['list_recorder'] = [
            [
                'movie_title' => 'string',
                'list_movie' => [
                    'front' => $front->first()->id,
                    'inside' => $inside->first()->id,
                    'behind' => $behind->first()->id,
                ],
            ],
        ];

        $response = $this->withHeaders(['Authorization' => $this->token])->putJson('api/driver-recorder/'.$factory->first()->id, $dataTest);
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $this->assertDatabaseCount('driver_recorders', 1);
        $this->assertDatabaseCount('driver_recorder_file', 3);
        $this->assertDatabaseHas('driver_recorders', [
            'department_id' => 1,
            'title' => 'title driver recorder',
            'type' => 0,
            'type_one' => 1,
            'type_two' => 1,
            'shipper' => 1,
            'accident_classification' => 1,
            'place_of_occurrence' => 1,
        ]);
        $response->assertJsonStructure(['code', 'data' => ['department_id', 'record_date', 'title', 'type', 'id']]);
    }
}
