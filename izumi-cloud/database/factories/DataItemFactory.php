<?php

namespace Database\Factories;

use App\Models\DataConnection;
use App\Models\DataItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Data;
use App\Models\User;

class DataItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DataItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::query()->first();
        // error_log(json_encode($user));
        return [
            "data_connection_id" => DataConnection::inRandomOrder()->first()->id,
            "content" => json_encode([
                "field_01" => rand(1, 1000),
                "field_02" => $this->faker->name,
                "field_03" => $this->faker->text,
                "field_04" => date('Y-m-d'),
                "field_05" => "Fake Json Data"
            ]),
            "type" => "active",
            "status" => "success",
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
        ];
    }
}
