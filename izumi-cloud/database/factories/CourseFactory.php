<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $listDepartment = Department::all()->pluck('id', 'id')->toArray();
        return [
            'course_code' => $this->faker->name,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'course_type' => Arr::random(array_keys(COURSE_TYPE)),
            'bin_type' => Arr::random(array_values(BIN_TYPE_VALUE)),
            'delivery_type' => Arr::random(array_keys(DELIVERY_TYPE)),
            'start_time' => Carbon::now()->format('H:i'),
            'gate' => Arr::random(array_values(GATE_VALUE)),
            'wing' => Arr::random(array_values(WING_VALUE)),
            'tonnage' => Arr::random(TONNAGE),
            'quantity' => rand(1, 9999),
            'allowance' => rand(1000, 9999999),
            'department_id' => Arr::random($listDepartment),
        ];
    }
}
