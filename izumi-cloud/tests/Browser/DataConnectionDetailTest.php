<?php

namespace Tests\Browser;

use App\Models\Data;
use App\Models\DataConnection;
use Helper\Common;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class DataConnectionDetailTest extends DuskTestCase
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

            $this->listDataConnection($browser);
            $this->dataConnectionDetail($browser);
        });
    }

    public function listDataConnection($browser)
    {
        $browser->visit("#/data-connect/list")
            ->pause(2000)
            ->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(2000)
            ->assertSee('フィルタ')
            ->pause(2000);
        $browser->with('.b-table', function ($table) {
            $table->assertSee('詳細');
        });
    }

    public function dataConnectionDetail($browser)
    {
        $browser->with('.b-table', function ($table) {
            $table->scrollIntoView('.fa-eye')->pause(1000)
                ->click('.fa-eye')->pause(5000);
        });

        $browser->with('#wrapper', function ($element) {
            $element->assertSee("データ連携")
                ->assertSee("最終連携日時")
                ->assertSee("連携データ名")
                ->assertSee("送信元")
                ->assertSee("受信先")
                ->assertSee("アクティブ/パッシブ")
                ->assertSee("連携頻度")
                ->assertSee("連携タイミング")
                ->assertSee("ステータス")
                ->assertSee("ログデータ")
                ->assertSee("戻る")
                ->pause(2000);
        });

    }

}
