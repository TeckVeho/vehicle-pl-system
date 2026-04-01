<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Helper\Common;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lstUser = User::pluck('id')->toArray();
        return [
            'id' => Common::randNotInArr(111111, 999999, $lstUser),
            'name' => $this->faker->name,
            'password' =>'123456789',
        ];
    }
}
