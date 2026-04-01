<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Arr;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            ['id' => 1, 'name' => '本社', 'position' => 19],
            ['id' => 2, 'name' => '横浜第一', 'position' => 3],
            ['id' => 3, 'name' => '平塚', 'position' => 6],
            ['id' => 4, 'name' => '横浜第二', 'position' => 4],
            ['id' => 5, 'name' => '静岡', 'position' => 13],
            ['id' => 6, 'name' => '千葉', 'position' => 7],
            ['id' => 7, 'name' => '東京', 'position' => 1],
            ['id' => 8, 'name' => '八千代', 'position' => 8],
            ['id' => 9, 'name' => '古河', 'position' => 10],
            ['id' => 11, 'name' => '武蔵野', 'position' => 2],
            ['id' => 13, 'name' => '所沢', 'position' => 9],
            ['id' => 14, 'name' => '新潟', 'position' => 11],
            ['id' => 15, 'name' => '名古屋', 'position' => 15],
            ['id' => 16, 'name' => '安城', 'position' => 16],
            ['id' => 17, 'name' => '浜松', 'position' => 14],
            ['id' => 18, 'name' => '富山', 'position' => 12],
            ['id' => 19, 'name' => '大阪', 'position' => 17],
            ['id' => 20, 'name' => '神戸', 'position' => 18],
            ['id' => 22, 'name' => '横浜第三', 'position' => 5],
            ['id' => 23, 'name' => '不動産管理', 'position' => 20],
            ['id' => 25, 'name' => '管理本部', 'position' => 21],
            ['id' => 24, 'name' => '米沢', 'position' => 22],
            //            12 => '埼玉',
        ];
        $listId = collect($list)->pluck('id');
        Department::whereNotIn('id', $listId)->delete();
        foreach ($list as $dp) {
            $department = Department::where('id', Arr::get($dp, 'id'))->first();
            if (!$department) {
                Department::create($dp);
            } else {
                $department->update($dp);
            }
        }
    }
}


