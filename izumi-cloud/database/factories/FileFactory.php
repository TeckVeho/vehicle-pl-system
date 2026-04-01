<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\File;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "file_name" => $this->faker->name . '.mp4',
            "file_extension" => 'mp4',
            "file_path" => $this->faker->filePath(),
            "file_size" => 1,
            "file_sys_disk" => 'public',
            "expired_at" => Carbon::now()->addHours(24),
        ];
    }
}
