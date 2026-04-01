<?php

namespace Database\Factories;

use App\Models\QuotationMasterData;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationMasterDataFactory extends Factory
{
    protected $model = QuotationMasterData::class;

    public function definition()
    {
        return [
            'tonnage' => $this->faker->randomElement(['2t', '4t', '6t', '8t', '10t']),
            'car_inspection_price' => 264000.00,
            'regular_inspection_price' => 22000.00,
            'tire_price' => 50000.00,
            'oil_change_price' => 20000.00,
            'fuel_unit_price' => 5.00,
        ];
    }
}

