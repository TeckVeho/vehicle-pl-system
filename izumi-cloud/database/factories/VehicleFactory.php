<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'department_id' => 1,
            'driving_classification' => 1,
            'tonnage' => 1,
            'truck_classification' => 1,
            'truck_classification_number' => 1,
            'truck_classification_2' => 1,
            'manufactor' => 1,
            'first_registration' => $this->faker->date('Y-m-d'),
            'box_distinction' => 1,
            'inspection_expiration_date' => $this->faker->date('Y-m-d'),
            'vehicle_identification_number' => 1,
            'owner' => 1,
            'etc_certification_number' => 1,
            'etc_number' => 1,
            'fuel_card_number_1' => 1,
            'fuel_card_number_2' => 1,
            'driving_recorder' => 1,
            'box_shape' => 1,
            'mount' => 1,
            'refrigerator' => 1,
            'eva_type' => 1,
            'gate' => 1,
            'humidifier' => 1,
            'type' => 1,
            'motor' => 1,
            'displacement' => 1,
            'length' => 1,
            'width' => 1,
            'height' => 1,
            'maximum_loading_capacity' => 1,
            'vehicle_total_weight' => 1,
            'in_box_length' => 1,
            'in_box_width' => 1,
            'in_box_height' => 1,
            'voluntary_insurance' => 1,
            'liability_insurance_period' => 1,
            'insurance_company' => 1,
            'agent' => 1,
            'tire_size' => 1,
            'battery_size' => 1,
            'monthly_mileage' => 1,
            'remark_old_car_1' => 1,
            'remark_old_car_2' => 1,
            'remark_old_car_3' => 1,
            'remark_old_car_4' => 1,
        ];
    }
}
