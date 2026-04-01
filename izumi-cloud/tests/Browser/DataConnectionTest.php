<?php

namespace Tests\Browser;

use App\Models\Data;
use App\Models\DataConnection;
use Helper\Common;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class DataConnectionTest extends DuskTestCase
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
            $this->listDataConnectionOrder($browser);
            $this->filterDataConnection($browser);
            $this->paginationDataConnection($browser);
        });
    }


    public function listDataConnection($browser)
    {
        $browser->visit("#/data-connect/list")
            // test list sort
            ->pause(2000)
            ->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(2000)
            ->assertSee('フィルタ')
            ->pause(2000)
            ->click("#collapsed-show-hide-filter")
            ->pause(2000)
            ->assertSee('最終連携日時')
            ->assertSee('連携データ名');
        $browser->with('.b-table', function ($table) {
            $table->assertSee('最終連携日時');
        });
    }

    public function listDataConnectionOrder($browser)
    {
        $classOrder = ['.final_transfer_time', '.from', '.active_passive', '.connection_frequency', '.connection_timing', '.status'];
        shuffle($classOrder);
        $browser->visit("#/data-connect/list")
            // test list sort
            ->pause(2000)
            ->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(2000)
            ->assertSee('フィルタ')
            ->pause(2000);
        $browser->with('.b-table', function ($table) use ($classOrder) {
            foreach ($classOrder as $clOrder) {
                $table->scrollIntoView($clOrder)->pause(1000)->click($clOrder)->pause(2000);
                $table->scrollIntoView($clOrder)->pause(1000)->click($clOrder)->pause(2000);
            }
        });

        $browser->driver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
        sleep(5);
        $browser->driver->executeScript("window.scrollTo(0,0);");
        sleep(5);

    }

    public function filterDataConnection($browser)
    {
        $browser->visit("#/data-connect/list")
            ->pause(2000)
            ->assertSee('データ連携')
            ->click("#collapsed-show-hide-filter")
            ->pause(2000);

        $browser->with('#zone-filter', function ($element) {
            $element->check(".chk_filter_date")->pause(2000)
                ->assertChecked(".chk_filter_date")
                ->releaseMouse()->press('.filter_date_from')->pause(2000)
                ->releaseMouse()->press('.btn-outline-primary')->pause(2000)
                ->click(".btn-summit-filter")->pause(2000)
                ->releaseMouse()->press('.filter_date_to')->pause(2000)
                ->releaseMouse()->press('.btn-outline-primary')->pause(2000)
                ->click(".btn-summit-filter")->pause(2000);
        });
        $browser->with('.b-table', function ($table) {
            $table->assertSee('最終連携日時');
        });

        $browser->with('#zone-filter', function ($element) {
            $element->check(".chk_filter_name")->pause(2000)
                ->assertChecked(".chk_filter_name")
                ->type(".filter_by_name", 'test text filter')
                ->releaseMouse()->press('.filter_date_from')
                ->pause(2000)
                ->click(".btn-summit-filter")->pause(2000)
                ->click('.chk_filter_date')->pause(2000)
                ->click(".btn-summit-filter")->pause(2000);
        });

        $browser->with('#zone-filter', function ($element) {
            $element->click(".text-clear-all")->pause(2000)
                ->assertNotChecked(".chk_filter_date")
                ->assertNotChecked(".chk_filter_name")
                ->pause(2000)
                ->click(".btn-summit-filter")->pause(2000);
        });
//        $browser->with('.b-table', function ($table) {
//            $table->assertDontSee('データなし');
//        });
    }


    public function paginationDataConnection($browser)
    {
        $browser->visit("#/data-connect/list")
            ->pause(2000)
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
