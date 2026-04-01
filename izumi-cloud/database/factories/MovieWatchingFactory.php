<?php

namespace Database\Factories;

use App\Models\MovieWatching;
use App\Models\Movies;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieWatchingFactory extends Factory
{
    protected $model = MovieWatching::class;

    public function definition()
    {
        return [
            'movie_id' => Movies::factory(),
            'user_id' => User::factory(),
            'is_watching' => $this->faker->boolean(),
            'date' => $this->faker->date('Y-m-d'),
            'is_like_app' => $this->faker->boolean(),
            'is_like_list' => $this->faker->boolean(),
            'time' => $this->faker->numberBetween(1, 3600),
        ];
    }
}

