<?php

namespace Database\Factories;

use App\Models\DataConnection;
use App\Models\System;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class DataConnectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DataConnection::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sys = System::pluck('id')->toArray();
        $name = $this->faker->name;

        $frequencyArr = ['monthly' => '月次', 'daily' => '日次'];
        $frequency = array_rand($frequencyArr);
        return [
            "name" => $name,
            "from" => Arr::random($sys),
            "to" => Arr::random($sys),
            "type" => array_rand(DataConnection::CONNECTION_TYPE),
            "frequency" => $frequency,
            "frequency_between" => null,
            "connection_frequency" => $frequencyArr[$frequency],
            "connection_timing" => '00:00',
            "final_connect_time" => Carbon::now(),
            "final_status" => array_rand(DataConnection::CONNECTION_STATUS),
        ];
    }
}
