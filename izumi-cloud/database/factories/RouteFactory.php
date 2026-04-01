<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Route;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class RouteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Route::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $listDepartment = Department::all()->pluck('id', 'id')->toArray();
        $listCustomer = Customer::all()->pluck('id', 'id')->toArray();
        return [
            'name' => "?",
            'department_id' => Arr::random($listDepartment),
            'customer_id' => Arr::random($listCustomer),
            'route_fare_type' => rand(1, 2),
            'fare' => rand(1000, 90000000),
            'highway_fee' => rand(1000, 90000000),
            'highway_fee_holiday' => rand(1000, 90000000),
            'is_government_holiday' => rand(0, 1),
        ];
    }
}
