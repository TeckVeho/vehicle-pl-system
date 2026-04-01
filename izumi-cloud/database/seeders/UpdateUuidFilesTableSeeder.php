<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UpdateUuidFilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        File::query()->whereNull('uuid')->chunkById(200, function (Collection $files) {
            foreach ($files as $file) {
                $file->uuid = Str::uuid();
                $file->save();
            }
        }, 'id');
    }
}
