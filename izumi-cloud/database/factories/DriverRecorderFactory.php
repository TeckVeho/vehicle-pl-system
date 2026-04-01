<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\DriverRecorder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class DriverRecorderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DriverRecorder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $listDepartment = Department::all()->pluck('id', 'id')->toArray();

        return [
            'department_id' => Arr::random($listDepartment),
            'record_date' => Carbon::now(),
            'title' => $this->faker->title(),
            'type' => Arr::random(array_values(DRIVER_RECORDER_TYPE)),
            'remark' => $this->faker->text,
            'type_one' => Arr::random(array_values(TYPE_ONE)),
            'type_two' => Arr::random(array_values(TYPE_TOW)),
            'shipper' => Arr::random(array_values(SHIPPER)),
            'accident_classification' => Arr::random(array_values(ACCIDENT_CLASSIFICATION)),
            'place_of_occurrence' => Arr::random(array_values(PLACE_OF_OCCURRENCE)),
        ];
    }
}
