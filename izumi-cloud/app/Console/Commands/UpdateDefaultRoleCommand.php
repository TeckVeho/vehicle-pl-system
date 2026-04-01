<?php

namespace App\Console\Commands;

use App\Imports\UserRoleImport;
use App\Jobs\DataConnectionJob;
use App\Jobs\SyncUserJob;
use App\Models\Employee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Repository\DataConnectionRepository;

class UpdateDefaultRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_role_default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $pathFileImport = Storage::path('users_role_import.csv');
        Excel::import(new UserRoleImport, $pathFileImport);
        dd(1);
//        $users = User::where('id', '<>', '111111')->get();
//        $roleCr = Role::findByName(ROLE_CREW);
//        foreach ($users as $user) {
//            $user->role = $roleCr->id;
//            $user->save();
//            $user->syncRoles($roleCr);
//        }

        $results = Employee::query()
            ->select([DB::raw("REPLACE(name, '//', '　') as name"), 'employee_code'])
            ->get();

        $resultsUser = User::withTrashed()
            ->select(['name', 'id'])
            ->get();

        foreach ($results as $result) {
            $user = $resultsUser->where('id', $result->employee_code)->first();
            if ($user && $result->name !== $user->name) {
                User::withTrashed()->where('id', $user->id)->update(['name' => $result->name]);
            }
        }
        SyncUserJob::dispatch();
    }
}
