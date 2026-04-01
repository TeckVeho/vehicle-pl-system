<?php

namespace Database\Seeders;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lisRoles = [
            ['name' => ROLE_CREW, 'position' => 1, 'display_name' => 'CREW'],
            ['name' => ROLE_CLERKS, 'position' => 2, 'display_name' => '事務員'],
            ['name' => ROLE_TL, 'position' => 3, 'display_name' => 'TL'],
            ['name' => ROLE_ACCOUNTING, 'position' => 6, 'display_name' => '経理財務'],
            ['name' => ROLE_GENERAL_AFFAIR, 'position' => 5, 'display_name' => '総務広報'],
            ['name' => ROLE_PERSONNEL_LABOR, 'position' => 4, 'display_name' => '人事労務'],
            ['name' => ROLE_AM_SM, 'position' => null, 'deleted_at' => Carbon::parse('2024-12-02'), 'display_name' => 'MG'],
            ['name' => ROLE_DIRECTOR, 'position' => 13, 'display_name' => '取締役'],
            ['name' => ROLE_DX_USER, 'position' => 14, 'display_name' => 'DX'],
            ['name' => ROLE_DX_MANAGER, 'position' => 15, 'display_name' => 'DX管理者'],
            ['name' => ROLE_QUALITY_CONTROL, 'position' => 7, 'display_name' => '品質管理'],
            ['name' => ROLE_SALES, 'position' => 8, 'display_name' => '営業'],
            ['name' => ROLE_SITE_MANAGER, 'position' => 9, 'display_name' => '現場MG'],
            ['name' => ROLE_HQ_MANAGER, 'position' => 10, 'display_name' => '本社MG'],
            ['name' => ROLE_DEPARTMENT_MANAGER, 'position' => 11, 'display_name' => '部長'],
            ['name' => ROLE_EXECUTIVE_OFFICER, 'position' => 12, 'display_name' => '執行役員'],
            ['name' => ROLE_DEPARTMENT_OFFICE_STAFF, 'position' => 16, 'display_name' => '事業部事務員'],
        ];
        $roles = Role::query()->get();
        foreach ($lisRoles as $key => $val) {
            $id = $key + 1;
            $checkRole = $roles->where('name', Arr::get($val, 'name'))->first();
            if ($checkRole) {
                $checkRole->position = Arr::get($val, 'position');
                $checkRole->display_name = Arr::get($val, 'display_name');
                if (Arr::get($val, 'deleted_at', null)) {
                    $checkRole->deleted_at = Arr::get($val, 'deleted_at');
                }
                $checkRole->save();
            } else {
                Role::query()->create(
                    [
                        'id' => $id,
                        'name' => Arr::get($val, 'name'),
                        'guard_name' => 'api',
                        'position' => Arr::get($val, 'position'),
                        'display_name' => Arr::get($val, 'display_name'),
                    ]);
            }
        }
    }
}
