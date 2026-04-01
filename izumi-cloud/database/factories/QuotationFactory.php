<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationMasterData;
use App\Models\QuotationStaff;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'author_id' => function () {
                return QuotationStaff::factory()->create()->id;
            },
            'tonnage_id' => function () {
                return QuotationMasterData::factory()->create()->id;
            },
        ];
    }
}
