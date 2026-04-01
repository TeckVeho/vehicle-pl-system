<?php

namespace App\Imports;

use App\Jobs\SyncUserJob;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserRoleImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $row) {
            $user_chk = User::withTrashed()->where('id', intval(Arr::get($row, 'emp_code')))->first();
            $role = null;
            try {
                $role = Role::find(intval(Arr::get($row, 'role_id')));
            } catch (\Exception $exception) {
                $role = null;
            }

            if ($user_chk && $role) {
                $user_chk->role = $role->id;
                $user_chk->syncRoles($role);
                $user_chk->save();
            }
        }
        SyncUserJob::dispatch();
    }

    public function headingRow(): int
    {
        return 1;
    }
}
