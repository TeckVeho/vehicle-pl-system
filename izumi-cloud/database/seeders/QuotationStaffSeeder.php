<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuotationStaff;

class QuotationStaffSeeder extends Seeder
{
    public function run(): void
    {
        $existing = QuotationStaff::where('name', 'test')->first();
        if (!$existing) {
            QuotationStaff::create([
                'name' => 'test',
            ]);
        }
    }
}
