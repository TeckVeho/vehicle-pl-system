<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UpdateUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $querys = User::with('roles')
            ->select("users.uuid", "users.id", "users.department_code", "users.name", "roles.name as role_name")
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.uuid');
                $join->where('model_type', 'App\Models\User');
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', [ROLE_CLERKS, ROLE_AM_SM, ROLE_DIRECTOR])->get();

        foreach ($querys as $key => $query) {
            if ($query->hasAllRoles(ROLE_AM_SM)) {
                $query->syncRoles([ROLE_SITE_MANAGER]);
            } elseif ($query->hasAllRoles(ROLE_DIRECTOR)) {
                $query->syncRoles([ROLE_EXECUTIVE_OFFICER]);
            } elseif ($query->hasAllRoles(ROLE_CLERKS)) {
                $query->syncRoles([ROLE_CLERKS]);
            }
        }
    }
}
