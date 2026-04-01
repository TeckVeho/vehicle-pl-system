<?php

namespace Tests\Browser;

use App\Models\Data;
use App\Models\DataConnection;
use Helper\Common;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class DataListTest extends DuskTestCase
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
            $this->listDataOrder($browser);
            $this->filterData($browser);
            $this->paginationData($browser);
        });
    }


    public function listData($browser)
    {
        $browser->visit("#/data-list/list")
            // test list sort
            ->pause(2000)
            ->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(2000)
            ->assertSee('フィルタ')
            ->pause(2000)
            ->click("#collapsed-show-hide-filter")
            ->pause(2000)
            ->assertSee('データ名')
            ->assertSee('適用する');
        $browser->with('#table-data-list', function ($table) {
            $table->assertSee('データID');
        });
    }

    public function listDataOrder($browser)
    {
        $classOrder = ['.data_id', '.data_name', '.from'];
        shuffle($classOrder);
        $browser->visit("#/data-list/list")
            // test list sort
            ->pause(5000)
            ->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(2000)
            ->assertSee('フィルタ')
            ->pause(2000);
        $browser->with('#table-data-list', function ($table) use ($classOrder) {
            foreach ($classOrder as $clOrder) {
                $table->scrollIntoView($clOrder)->pause(1000)->click($clOrder)->pause(5000);
                $table->scrollIntoView($clOrder)->pause(1000)->click($clOrder)->pause(5000);
            }
        });

        $browser->driver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
        sleep(5);
        $browser->driver->executeScript("window.scrollTo(0,0);");
        sleep(5);

    }

    public function filterData($browser)
    {
        $browser->visit("#/data-list/list")
            ->pause(5000)
            ->assertSee('データ連携')
            ->click("#collapsed-show-hide-filter")
            ->pause(2000);

        $browser->with('#zone-filter', function ($element) {
            $element->check(".chk_filter")->pause(2000)
                ->assertChecked(".chk_filter")
                ->type(".v-custom-input", 'test text filter')
                ->click(".btn-summit-filter")->pause(5000);
        });
        $browser->with('#table-data-list', function ($table) {
            $table->assertSee('データなし');
        });

        $browser->with('#zone-filter', function ($element) {
            $element->click(".text-clear-all")->pause(2000)
                ->assertNotChecked(".chk_filter")
                ->pause(2000)
                ->click(".btn-summit-filter")->pause(5000);
        });
        $browser->with('#table-data-list', function ($table) {
            $table->assertDontSee('データなし');
        });
    }


    public function paginationData($browser)
    {
        $browser->visit("#/data-list/list")
            ->pause(5000)
            ->scrollIntoView('.pagination')
            // Go to next page
            ->click('.next')->waitUntilMissing('.loading')
            ->pause(2000)
            ->scrollIntoView('.pagination')
            ->assertSeeIn('.pagination .active', '2')
            // Go to previous page
            ->pause(2000)
            ->click('.prev')->waitUntilMissing('.loading')
            ->assertSeeIn('.pagination .active', '1');
    }


}
