<?php

namespace App\Console\Commands;

use App\Models\UserContacts;
use Illuminate\Console\Command;

class UpdateFlagUserContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:flag_user_contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UserContacts::query()->get()->each(function ($userContacts) {
            $userContacts->update([
                'flag_send_noti' => 1,
                'flag_check_personal_contact_info' => 0,
                'flag_check_emergency_contact_info_1' => 0,
                'flag_check_emergency_contact_info_2' => 0
            ]);
        });
    }
}
