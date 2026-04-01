<?php

namespace App\Console\Commands;

use App\Jobs\SyncEmployeesJob;
use App\Jobs\SyncUserJob;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateRetirementDateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_retirement_date';

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
        $users = User::query()->with('employee')->whereNotNull('expected_retirement_date')->get();
        foreach ($users as $user) {
            if (Carbon::parse($user->expected_retirement_date)->format('Ymd') <= Carbon::now()->format('Ymd')) {
                if ($user->employee) {
                    $user->employee()->update(['retirement_date' => $user->expected_retirement_date]);
                    SyncEmployeesJob::dispatch($user->employee->id);
                }
                $user->deleted_at = $user->expected_retirement_date;
//                $user->expected_retirement_date = null;
                $user->save();
                SyncUserJob::dispatch($user->id);
            }
        }
    }
}
