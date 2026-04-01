<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\System;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    const systemName = [
        "イズミクラウド",
        "シフト表・タイムシート",
        "給与明細クラウド",
        "整備データシステム",
        "運転日報クラウド(後日作成)",
        "E-ラーニング",
        "人事奉行",
        "給与奉行",
        "IT点呼キーパー",
        "D1D",
        "PCA",
        "魔法陣",
        "CSV",
        "Exeファイル",
        "ITP",
        "CLOMO",
        "Excel",
        "King of Time",
        "外部採用システム",
        "IT点呼キーパー(Gmail)",
        "イズミワークス"
    ];

    public function run()
    {
        foreach (self::systemName as $key => $value) {
            $data = [
                "name" => $value,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ];
            $sysCheck = System::where('name', '=', $value)->first();
            if (!$sysCheck) {
                System::create($data);
            } else {
                $sysCheck->name = $value;
                $sysCheck->save;
            }
        }
    }
}
