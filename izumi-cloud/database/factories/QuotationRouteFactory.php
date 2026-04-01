<?php

namespace Database\Factories;

use App\Models\QuotationRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationRouteFactory extends Factory
{
    protected $model = QuotationRoute::class;

    public function definition(): array
    {
        return [
            'route_code' => 'QR-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'user_id' => 1,
            'quotation_id' => null,
            'title' => $this->faker->sentence,
            'pickup_location' => '東京都港区芝公園4-2-8',
            'delivery_location' => '神奈川県横浜市西区みなとみらい2-2-1',
            'return_location' => '東京都港区芝公園4-2-8',
            'start_time' => '08:00',
            'vehicle_type' => '4t',
            'loading_time_minutes' => 60,
            'unloading_time_minutes' => 60,
            'user_break_time_minutes' => null,
            'total_distance_km' => $this->faker->randomFloat(2, 30, 100),
            'estimated_end_time' => '14:00',
            'date_change' => false,
            'total_duty_time_hours' => $this->faker->randomFloat(2, 5, 10),
            'actual_working_hours' => $this->faker->randomFloat(2, 4, 9),
            'total_driving_time_minutes' => $this->faker->numberBetween(60, 240),
            'total_handling_time_minutes' => 120,
            'total_break_time_minutes' => $this->faker->numberBetween(30, 90),
            'highway_fee' => $this->faker->numberBetween(1000, 5000),
            'fuel_cost' => 0,
            'estimated_total_cost' => 0,
            'is_compliant' => true,
            'applied_rule' => '労働基準法に基づき休憩時間を追加しました',
            'ai_model_used' => 'gpt-4o',
            'calculation_duration_seconds' => $this->faker->numberBetween(2, 10),
            'status' => 'completed',
            'error_message' => null,
            'notes' => null,
        ];
    }
}
