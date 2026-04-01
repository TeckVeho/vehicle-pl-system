<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->faker = Faker::create();

        $user = User::query()->where('id', '111111')->firstOrFail();
        $this->token = 'Bearer '.JWTAuth::fromUser($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_edit()
    {
        Schema::disableForeignKeyConstraints();
        Vehicle::query()->truncate();
        Schema::enableForeignKeyConstraints();
        $count = 1;

        $ehicle = Vehicle::factory()->count($count)->create();

        $dataEdit = [
            'no_number_plate' => 'TEST-PLATE-001',
            'leasing' => [
                'start_of_leasing' => Carbon::now()->format('Y-m-d'),
                'end_of_leasing' => Carbon::now()->addYear()->format('Y-m-d'),
                'leasing_period' => 12,
                'leasing_company' => 'Test Co',
                'garage' => 'Garage 1',
                'tel' => '000-0000',
            ],
            'department_id' => 2,
            'driving_classification' => 2,
            'tonnage' => 2,
            'truck_classification' => 2,
            'truck_classification_number' => 2,
            'truck_classification_2' => 2,
            'manufactor' => 2,
            'first_registration' => Carbon::now()->format('Y-m'),
            'box_distinction' => 2,
            'inspection_expiration_date' => Carbon::now()->format('Y-m-d'),
            'vehicle_identification_number' => 2,
            'owner' => 2,
            'etc_certification_number' => 2,
            'etc_number' => 2,
            'fuel_card_number_1' => 2,
            'fuel_card_number_2' => 2,
            'driving_recorder' => 2,
            'box_shape' => 2,
            'mount' => 2,
            'refrigerator' => 2,
            'eva_type' => 2,
            'gate' => 2,
            'humidifier' => 2,
            'type' => 2,
            'motor' => 2,
            'displacement' => 2,
            'length' => 2,
            'width' => 2,
            'height' => 2,
            'maximum_loading_capacity' => 2,
            'vehicle_total_weight' => 2,
            'in_box_length' => 2,
            'in_box_width' => 2,
            'in_box_height' => 2,
            'voluntary_insurance' => 2,
            'liability_insurance_period' => 2,
            'insurance_company' => 2,
            'agent' => 2,
            'tire_size' => 2,
            'battery_size' => 2,
            'monthly_mileage' => 2,
            'remark_old_car_1' => 2,
            'remark_old_car_2' => 2,
            'remark_old_car_3' => 2,
            'remark_old_car_4' => 2,
            'mileage' => 1000,
        ];

        $response = $this->withHeaders([
            'Authorization' => $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/vehicle/'.$ehicle->first()->id, $dataEdit);
        $response->assertOk();
        $this->assertEquals(200, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'data']);
    }

    public function test_edit_with_validate_required()
    {
        Schema::disableForeignKeyConstraints();
        //        DB::table('vehicle_department')->truncate();
        //        DB::table('vehicle_working_department')->truncate();
        Vehicle::query()->truncate();
        Schema::enableForeignKeyConstraints();
        $count = 1;

        $dataEdit = [
            'driving_classification' => 2,
            'tonnage' => 2,
            'truck_classification_number' => 2,
            'truck_classification_2' => 2,
            'manufactor' => 2,
            'box_distinction' => 2,
            'vehicle_identification_number' => 2,
            'owner' => 2,
            'etc_certification_number' => 2,
            'etc_number' => 2,
            'fuel_card_number_1' => 2,
            'fuel_card_number_2' => 2,
            'driving_recorder' => 2,
            'box_shape' => 2,
            'mount' => 2,
            'refrigerator' => 2,
            'eva_type' => 2,
            'gate' => 2,
            'humidifier' => 2,
            'type' => 2,
            'motor' => 2,
            'displacement' => 2,
            'length' => 2,
            'width' => 2,
            'height' => 2,
            'maximum_loading_capacity' => 2,
            'vehicle_total_weight' => 2,
            'in_box_length' => 2,
            'in_box_width' => 2,
            'in_box_height' => 2,
            'voluntary_insurance' => 2,
            'liability_insurance_period' => 2,
            'insurance_company' => 2,
            'agent' => 2,
            'tire_size' => 2,
            'battery_size' => 2,
            'monthly_mileage' => 2,
            'remark_old_car_1' => 2,
            'remark_old_car_2' => 2,
            'remark_old_car_3' => 2,
            'remark_old_car_4' => 2,
        ];

        $ehicle = Vehicle::factory()->count($count)->create();

        $response = $this->withHeaders([
            'Authorization' => $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/vehicle/'.$ehicle->first()->id, $dataEdit);
        $this->assertEquals(422, $response->decodeResponseJson()['code']);
        $response->assertJsonStructure(['code', 'message', 'message_content',
            'message_internal' => ['department_id', 'truck_classification', 'first_registration', 'inspection_expiration_date']]);
    }
}
