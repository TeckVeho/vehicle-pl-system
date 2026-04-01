<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
class UpdateColumnGMarkAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-column-g-mark-action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update column g_mark_action_radio to departments table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $departments = Department::all();
        foreach ($departments as $department) {
            if ($department->g_mark_action_radio != 0) {
                $department->g_mark_action_radio = [ intval($department->g_mark_action_radio)];
                $department->save();
            } else {
                $department->g_mark_action_radio = NULL;
                $department->save();
            }
         
        }
        $this->info('Đã cập nhật g_mark_action_radio = [1, 2] cho ' . $departments->count() . ' department(s).');
    }
}
