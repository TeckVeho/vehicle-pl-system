<?php

namespace Database\Factories;

use App\Models\Movies;
use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class MoviesFactory extends Factory
{
    protected $model = Movies::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(1),
            'position' => $this->faker->numberBetween(1, 100),
            'tag' => json_encode([$this->faker->numberBetween(1, 5)]),
            'file_length' => sprintf('%02d:%02d:%02d', 
                $this->faker->numberBetween(0, 1), 
                $this->faker->numberBetween(0, 59), 
                $this->faker->numberBetween(0, 59)
            ),
            'is_loop_enabled' => true,
        ];
    }

    public function disabled()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_loop_enabled' => false,
            ];
        });
    }
}

