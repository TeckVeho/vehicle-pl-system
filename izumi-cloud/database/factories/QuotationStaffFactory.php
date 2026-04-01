<?php

namespace Database\Factories;

use App\Models\QuotationStaff;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationStaffFactory extends Factory
{
    protected $model = QuotationStaff::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}

