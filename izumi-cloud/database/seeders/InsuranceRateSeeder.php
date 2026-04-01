<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsuranceRate;
use App\Models\InsuranceRateHistory;

class InsuranceRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            '健康保険料率',
            '厚生年金料率',
            '児童拠出料率',
            '介護保険料率',
            '雇用料率',
            '労災料率'
        ];
        if (!InsuranceRate::first()) {
            foreach ($datas as $data) {
                $insuranceRate = InsuranceRate::create([
                    'kinds' => '社会保険',
                    'name' => $data,
                    'current_rate' => 0.1,
                    'change_rate' => NULL,
                    'applicable_date' => NULL
                ]);
                InsuranceRateHistory::create([
                    'insurance_rates_id' => $insuranceRate->id,
                    'current_rate' => $insuranceRate->current_rate,
                    'change_rate' => $insuranceRate->change_rate,
                    'applicable_date' => $insuranceRate->created_at
                ]);
            }
        }
    }
}
