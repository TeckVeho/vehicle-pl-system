<?php

namespace Database\Factories;

use App\Models\Data;
use App\Models\System;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class DataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Data::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    const dataType = [
        "Employee list",
        "Salary data get",
        "Salary data import"
    ];

    public function definition()
    {
        $sys = System::pluck('id')->toArray();
        $name = $this->faker->name;
        return [
            // "id" => $this->faker->unique()->randomNumber,
            "name" => $name,
            "from" => Arr::random($sys),
            "to" => Arr::random($sys),
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
            "deleted_at" => null,
            "remark" => $this->faker->text
        ];
    }
}
