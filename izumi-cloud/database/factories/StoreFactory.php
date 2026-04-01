<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'store_name' => $this->faker->name,
            "delivery_destination_code" => $this->faker->randomNumber(5, true),
            "destination_name_kana" => $this->faker->title,
            "destination_name" => $this->faker->title,
            "tel_number" => $this->faker->regexify('[0-9]{13}'),
            "address_1" => $this->faker->regexify('[A-Za-z0-9]{20}'),
            "address_2" => $this->faker->regexify('[A-Za-z0-9]{50}'),
            "post_code" => $this->faker->randomNumber(4, true),
            "pass_code" => $this->faker->randomNumber(4, true)
        ];
    }

    protected function withFaker()
    {
        return \Faker\Factory::create('en');
    }
}
