<?php

namespace Database\Factories;

use App\Models\QuotationRouteLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationRouteLocationFactory extends Factory
{
    protected $model = QuotationRouteLocation::class;

    public function definition(): array
    {
        return [
            'route_id' => 1,
            'sequence_order' => $this->faker->numberBetween(1, 10),
            'location_type' => $this->faker->randomElement(['pickup', 'delivery', 'return', 'waypoint']),
            'location_name' => $this->faker->company,
            'address' => $this->faker->address,
            'prefecture' => '東京都',
            'city' => '港区',
            'latitude' => $this->faker->latitude(35, 36),
            'longitude' => $this->faker->longitude(139, 140),
            'arrival_time' => $this->faker->time('H:i'),
            'departure_time' => $this->faker->time('H:i'),
            'stay_duration_minutes' => $this->faker->numberBetween(30, 120),
            'distance_from_previous_km' => $this->faker->randomFloat(2, 10, 50),
            'travel_time_from_previous_min' => $this->faker->numberBetween(20, 90),
            'contact_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
            'notes' => null,
        ];
    }
}
