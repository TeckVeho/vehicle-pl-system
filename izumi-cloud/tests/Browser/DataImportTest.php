<?php

namespace Tests\Browser;

use App\Models\Data;
use App\Models\DataConnection;
use Tests\DuskTestCase;

class DataImportTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {
        DataConnection::factory()->count(30)->create()->each(function ($data) {
            $data->syncRoles(ROLE_DX_MANAGER);
        });

        $this->browse(function ($browser) {
            $this->login($browser);

            $this->import($browser);
        });
    }

    public function import($browser)
    {
        $browser->visit("#/data-import/index")
            ->pause(2000)
            ->waitUntilMissing('.loading')
            ->assertSee('データインポート')
            ->pause(2000)
            ->assertSee('データ')
            ->assertSee('データ選択')
            ->assertSee('インポートファイル')
            ->assertSee('インポート実行')
            ->pause(2000);

        $browser->press('.v-button-import-data')
            ->pause(2000)
//            ->assertSee('危険')
            ->pause(5000);

        $browser->with('.data-import', function ($table) {
            $table->select('.btn-select-data',[10])->pause(2000)
                ->attach('#fileUpload', base_path('tests/csv/test.csv'))->pause(2000)
                ->press('.v-button-import-data')->pause(2000)
            ;
        });
//        $browser->assertSee('成功')->pause(3000);
    }

}
