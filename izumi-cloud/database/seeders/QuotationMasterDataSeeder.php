<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuotationMasterData;
use Illuminate\Support\Facades\Log;

class QuotationMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $tonnages = ['3t', '4t', '10t'];
        
        foreach ($tonnages as $tonnage) {
            $existing = QuotationMasterData::where('tonnage', $tonnage)->first();
            if (!$existing) {
                Log::info('Creating quotation master data for tonnage: ' . $tonnage);
                QuotationMasterData::create([
                    'tonnage' => $tonnage,
                    'car_inspection_price' => 264000,
                    'regular_inspection_price' => 22000,
                    'tire_price' => 50000,
                    'oil_change_price' => 20000,
                    'fuel_unit_price' => 5.00,
                ]);
            }
        }
    }
}
