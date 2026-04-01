<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InsuranceRate;
use Carbon\Carbon;
use App\Models\InsuranceRateHistory;

class UpdateInsuranceRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateInsuranceRate';

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
        $listInsuranceRate = InsuranceRate::query()
            ->whereBetween('applicable_date', [Carbon::now()->firstOfMonth(), Carbon::now()->endOfMonth()])
            ->get();
        if ($listInsuranceRate) {
            foreach ($listInsuranceRate as $key => $value) {
                InsuranceRateHistory::create([
                    'insurance_rates_id' => $value->id,
                    'current_rate' => $value->current_rate,
                    'change_rate' => $value->change_rate,
                    'applicable_date' => $value->applicable_date,
                ]);
                $value->current_rate = $value->change_rate;
                $value->change_rate = NUll;
                $value->applicable_date = null;
                $value->save();
                InsuranceRateHistory::create([
                    'insurance_rates_id' => $value->id,
                    'current_rate' => $value->current_rate,
                    'change_rate' => $value->change_rate,
                    'applicable_date' => $value->applicable_date,
                ]);
            }
        }
    }
}
