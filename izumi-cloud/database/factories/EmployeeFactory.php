<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            Employee::EMPLOYEE_CODE => rand(1000, 90000000),
            Employee::NAME => $this->faker->name,
            Employee::SEX => Arr::random([0, 1]),
            Employee::BIRTHDAY => $this->faker->date,
            Employee::HIRE_START_DATE => $this->faker->date,
//            Employee::RETIREMENT_DATE => $this->faker->date,
            Employee::LICENSE_TYPE => rand(0, 3),
            Employee::EMPLOYEE_TYPE => rand(1, 3),
            Employee::JOB_TYPE => rand(1, 3),
            Employee::MIDNIGHT_WORKTIME => rand(1, 24),
            Employee::SCHEDULE_WORKING_HOURS => rand(1, 24),
            Employee::GRADE => rand(1, 24),
            Employee::EMPLOYEE_GRADE_2 => rand(1, 24),
            Employee::BOARDING_EMPLOYEE_GRADE => rand(1, 24),
            Employee::BOARDING_EMPLOYEE_GRADE_2 => rand(1, 24),
            Employee::TRANSPORTATION_COMPENSATION => rand(1, 31),
            Employee::DAILY_TRANSPORTATION_CP => rand(1, 31),
        ];
    }
}
