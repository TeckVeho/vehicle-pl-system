<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('id', '111111')->first();
//        $roleHq = Role::findByName(ROLE_HEADQUARTER, 'api');
        if (!$user) {
            $role = Role::findByName(ROLE_DX_MANAGER, 'api');
            $user = User::create(
                [
                    'id' => 111111,
                    'password' => '123456789',
                    'name' => 'MANAGER',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_CLERKS, 'api');
            $user = User::create(
                [
                    'id' => 222222,
                    'password' => '123456789',
                    'name' => 'Clerks',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_TL, 'api');
            $user = User::create(
                [
                    'id' => 333333,
                    'password' => '123456789',
                    'name' => 'TL',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_GENERAL_AFFAIR, 'api');
            $user = User::create(
                [
                    'id' => 444444,
                    'password' => '123456789',
                    'name' => 'General Affair',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_ACCOUNTING, 'api');
            $user = User::create(
                [
                    'id' => 555555,
                    'password' => '123456789',
                    'name' => 'Accounting',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_PERSONNEL_LABOR, 'api');
            $user = User::create(
                [
                    'id' => 666666,
                    'password' => '123456789',
                    'name' => 'Personnel Labor',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_AM_SM, 'api');
            $user = User::create(
                [
                    'id' => 777777,
                    'password' => '123456789',
                    'name' => 'AM/SM',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_DIRECTOR, 'api');
            $user = User::create(
                [
                    'id' => 888888,
                    'password' => '123456789',
                    'name' => 'Director',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_DX_USER, 'api');
            $user = User::create(
                [
                    'id' => 999999,
                    'password' => '123456789',
                    'name' => 'DX User',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);

            $role = Role::findByName(ROLE_CREW, 'api');
            $user = User::create(
                [
                    'id' => 123456,
                    'password' => '123456789',
                    'name' => 'DX Manager',
                    'role' => $role->id,
                    'email' => 'i.kohei2323@gmail.com'
                ]
            );
            $user->syncRoles([$role]);
        } else {
            $user->supervisor_email = 'i.kohei2323@gmail.com';
            $user->email = 'i.kohei2323@gmail.com';
            $user->save();
        }
    }
}
