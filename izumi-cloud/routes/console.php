<?php

use App\Console\Commands\DataConnect\SaveItpS3DataCommand;
use App\Console\Commands\DataconnectionCommand;
use App\Console\Commands\DeleteFileExpiredCommand;
use App\Console\Commands\JapanHolidayCommand;
use App\Console\Commands\UpdateRetirementDateCommand;
use App\Jobs\FetchS3FolderJob;
use App\Jobs\SyncCourse;
use App\Jobs\SyncDepartmentJob;
use App\Jobs\SyncEmployeeDepartmentJob;
use App\Jobs\SyncEmployeesJob;
use App\Jobs\SyncUserJob;
use App\Jobs\SyncVehicleJob;
use App\Models\DataConnection;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


try {
    $dataConnections = DataConnection::query()->where('type', '=', 'active')
        ->whereNotNull('frequency')
        ->where('frequency', '<>', 'any')->get();
} catch (\Exception $e) {
    $dataConnections = null;
}
if ($dataConnections) {
    foreach ($dataConnections as $dataConnection) {
        $frequency = $dataConnection->frequency;
        $dateOn = (int)data_get($dataConnection, 'date_on');
        $weekOn = (int)data_get($dataConnection, 'week_on');
        $timeAt = data_get($dataConnection, 'time_at', '00:00');
        $frequencyBetween = Arr::get($dataConnection, 'frequency_between');
        switch ($frequency) {
            case "hourlyDays":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->hourly()->days($timeAt);
                break;
            case "hourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0'))->format('G:i'),
                        Carbon::parse(Arr::get($frequencyBetween, 1, '0:0'))->format('G:i'));
                break;
            case "hourlyAt":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->hourlyAt($timeAt);
                break;
            case "hourlyAtBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->hourlyAt($timeAt)
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0'))->format('G:i'),
                        Carbon::parse(Arr::get($frequencyBetween, 1, '0:0'))->format('G:i'));
                break;
            case "dailyAt":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->dailyAt($timeAt);
                break;
            case "twiceDaily":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->twiceDaily();
                break;
            case "weeklyOn":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->weeklyOn($weekOn, $timeAt);
                break;
            case "monthly":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->monthly();
                break;
            case "monthlyOn":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->monthlyOn($dateOn, $timeAt);
                break;
            case "twiceMonthly":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->twiceMonthly();
                break;
            case "lastDayOfMonth":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->lastDayOfMonth();
                break;
            case "yearlyOn":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->yearlyOn(Arr::get($frequencyBetween, 0, 1), Arr::get($frequencyBetween, 1, 1), Arr::get($frequencyBetween, 2, '0:0'));
                break;
            case "weekdaysHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->weekdays()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            case "weekendsHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->weekends()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            case "sundaysHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->sundays()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            case "mondaysHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->mondays()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            case "tuesdaysHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->tuesdays()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            case "wednesdaysHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->wednesdays()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            case "thursdaysHourlyBetween":
                $scheduleConnect = Schedule::command(DataconnectionCommand::class, [$dataConnection->id]);
                $scheduleConnect->thursdays()->hourly()
                    ->between(Carbon::parse(Arr::get($frequencyBetween, 0, '0:0')), Carbon::parse(Arr::get($frequencyBetween, 1, '23:59')));
                break;
            default:
                Schedule::command(DataconnectionCommand::class, [$dataConnection->id])->$frequency();
//                        Schedule::call(function () use ($dataConnection) {
//                            DataConnectionJob::dispatch($dataConnection->id);
//                        });
                break;
        }
    }
}
//get Japan Holiday
Schedule::command(JapanHolidayCommand::class)->daily()->withoutOverlapping();
// Schedule::command(CheckAclCommand::class)->dailyAt("07:00");
//delete file expired
Schedule::command(DeleteFileExpiredCommand::class)->daily();
Schedule::command('UpdateInsuranceRate')->monthlyOn(1, '00:00');
Schedule::command('SendNotiTheMovieIsDeliveried')->everyMinute();

Schedule::job(new SyncEmployeesJob())->daily();
Schedule::job(new SyncEmployeeDepartmentJob())->daily();
Schedule::job(new SyncDepartmentJob())->daily();
Schedule::job(new SyncUserJob())->daily();

Schedule::job(new SyncVehicleJob())->daily();
Schedule::job(new SyncCourse())->daily();

Schedule::command('service:get_and_save_conf_lw')->hourly()->withoutOverlapping();
Schedule::command('get_and_save_conf_lw_translate')->hourly()->withoutOverlapping();
Schedule::command('update:flag_user_contacts')->cron('0 14 1 2 *');
Schedule::command('update:flag_user_contacts')->cron('0 14 14 7 *');
Schedule::command('app:auto-store-movie-schedule')->daily();
Schedule::command(UpdateRetirementDateCommand::class)->daily()->withoutOverlapping();
Schedule::command(SaveItpS3DataCommand::class)->dailyAt('22:00');
Schedule::command('vehicle:update-operation-schedule --all')
    ->weeklyOn(0, '08:00')
    ->withoutOverlapping()
    ->runInBackground();
Schedule::job(new FetchS3FolderJob("itp_upstream/00000000"))->everyThirtyMinutes()->name('fetch-s3-itp-folders-daily');
Schedule::command('app:send-lineworks-bot-command')->dailyAt('08:00');
Schedule::command('app:send-noti-of-vehicle-inspection-deadline')->dailyAt('09:00');
Schedule::command('app:send-noti-vehicle-due-date')->monthlyOn(1)->at('09:00');
