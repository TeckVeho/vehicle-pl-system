<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Arr;

class DepartmentProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            ['name' => '横浜第一', 'province_name' => '神奈川'],
            ['name' => '平塚', 'province_name' => '神奈川'],
            ['name' => '横浜第二', 'province_name' => '神奈川'],
            ['name' => '静岡', 'province_name' => '静岡'],
            ['name' => '千葉', 'province_name' => '千葉'],
            ['name' => '東京', 'province_name' => '東京'],
            ['name' => '八千代', 'province_name' => '千葉'],
            ['name' => '古河', 'province_name' => '茨城'],
            ['name' => '武蔵野', 'province_name' => '東京'],
            ['name' => '所沢', 'province_name' => '埼玉'],
            ['name' => '新潟', 'province_name' => '新潟'],
            ['name' => '名古屋', 'province_name' => '愛知'],
            ['name' => '安城', 'province_name' => '愛知'],
            ['name' => '浜松', 'province_name' => '静岡'],
            ['name' => '富山', 'province_name' => '富山'],
            ['name' => '大阪', 'province_name' => '大阪府'],
            ['name' => '神戸', 'province_name' => '兵庫'],
            ['name' => '横浜第三', 'province_name' => '神奈川'],
            ['name' => '米沢', 'province_name' => '山形'],
            //            12 => '埼玉',
        ];
        foreach ($list as $dp) {
            $department = Department::query()->where('name', Arr::get($dp, 'name'))->first();
            if ($department) {
                $department->update([
                    'province_name' => Arr::get($dp, 'province_name'),
                    'province_md5' => md5(Arr::get($dp, 'province_name')),
                ]);
            }
        }
    }
}


