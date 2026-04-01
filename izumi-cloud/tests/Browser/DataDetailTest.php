<?php

namespace Tests\Browser;

use App\Models\DataConnection;
use Tests\DuskTestCase;

class DataDetailTest extends DuskTestCase
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

            $this->listData($browser);
            $this->dataConnectionDetail($browser);
        });
    }

    public function listData($browser)
    {
        $browser->visit("#/data-list/list")
            ->pause(1000)
            ->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(1000)
            ->assertSee('フィルタ')
            ->pause(1000);
        $browser->with('.b-table', function ($table) {
            $table->assertSee('詳細');
        });
    }

    public function dataConnectionDetail($browser)
    {
        $browser->with('.b-table', function ($table) {
            $table->scrollIntoView('.fa-eye')->pause(1000)
                ->click('.fa-eye')->pause(1000);
        });

        $browser->with('#wrapper', function ($element) {
            $element->assertSee("データ一覧")
                ->assertSee("データID")
                ->assertSee("データ名")
                ->assertSee("送信元")
                ->assertSee("受信先")
                ->assertSee("備考")
                ->assertSee("保存データ一覧")
                ->assertSee("保存日")
                ->assertSee("戻る")
                ->pause(1000);
        });

    }

}
