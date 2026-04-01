<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteNonDeliveryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RouteNonDelivery::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'route_id' => Route::factory(),
            'number_at' => rand(1, 7),
            'is_week' => rand(0, 1),
        ];
    }
}
