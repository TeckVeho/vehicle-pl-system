<?php

namespace Tests\Browser;

use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Helper\Common;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class UserManagementTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {

        User::factory()->count(50)->create();

        $this->browse(function ($browser) {
            $this->login($browser);
//
            $this->listUser($browser);
            $this->filterUser($browser);
            $this->paginationUser($browser);
//            $this->userRegistry($browser);
            $this->userUpdate($browser);
//            $this->userDelete($browser);
        });
    }


    public function listUser($browser)
    {
        $browser->visit("#/master-manager/user-master")
            // test list sort
            ->pause(2000)
            //->waitUntilMissing('.loading')
            ->assertSee('データ連携')
            ->pause(2000)
            ->assertSee('フィルタ')
            ->pause(2000)
            ->click("#collapsed-show-hide-filter")
            ->pause(2000)
            ->assertSee('ユーザ名')
            ->assertSee('全クリア');
        $browser->with('#table-user-management', function ($table) {
            $table->assertSee('ユーザ権限');
        });
    }

    public function filterUser($browser)
    {
        $browser->visit("#/master-manager/user-master")
            ->pause(2000)
            ->click("#collapsed-show-hide-filter")
            ->pause(2000)
            ->assertSee('ユーザ名');
        $browser->with('#zone-filter', function ($element) {
            $element->check(".chk_filter")->pause(2000)
                ->assertChecked(".chk_filter")
                ->releaseMouse()->type('#filter-by-name', 'test filter by name')->pause(2000)
                ->click(".btn-summit-filter")->pause(5000);
        });
        $browser->with('#table-user-management', function ($table) {
            $table->assertSee('データなし');
        });

        $browser->with('#zone-filter', function ($element) {
            $element->check(".chk_filter")->pause(2000)
                ->assertChecked(".chk_filter")
                ->releaseMouse()->type('#filter-by-name', 'a')->pause(2000)
                ->click(".btn-summit-filter")->pause(5000);
        });
        $browser->with('#table-user-management', function ($table) {
            $table->assertDontSee('データなし');
        });

        $browser->with('#zone-filter', function ($element) {
            $element->click(".text-clear-all")->pause(2000)
                ->assertNotChecked(".chk_filter")
                ->assertDisabled("#filter-by-name")
                ->pause(2000)
                ->click(".btn-summit-filter")->pause(5000);
        });
        $browser->with('#table-user-management', function ($table) {
            $table->assertDontSee('データなし');
        });
    }


    public function paginationUser($browser)
    {
        $browser->visit("#/master-manager/user-master")
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


    public function userRegistry($browser)
    {
        $lstUser = User::pluck('id')->toArray();
        $faker = Faker::create();

        $browser->visit("#/master-manager/create")
            ->pause(5000);
        $browser->with('.content-body', function ($element) use ($lstUser, $faker) {
            $element->assertSee('ユーザ権限')->assertSee('ユーザ名')->assertSee('ユーザID')->assertSee('パスワード')
                ->assertButtonEnabled('#btn-back')
                ->assertButtonEnabled('#btn-save')->pause(2000)
                ->press('#btn-save')->pause(2000)
                ->select('.user-regis-select',[1,2])->pause(2000)
                ->type('.user-full-name', $faker->name)
                ->type('.user_id', Common::randNotInArr(111111, 999999, $lstUser))
                ->type('.user_password', '1234456789')->pause(2000)
                ->press('#btn-save')->pause(5000);
        });
        $browser->assertPathIs('/')->pause(1000)->assertSee('データ連携')
            ->assertSeeIn('#table-user-management', "ユーザ権限")->assertSee('編集')->assertSee('削除');
    }


    public function userUpdate($browser)
    {
        $faker = Faker::create();

        $browser->visit("#/master-manager/user-master")->pause(5000);
        $browser->with('#table-user-management', function ($table) {
            $table->assertSee('編集')->click('.btn-edit')->pause(5000);
        });
        $browser->with('.content-body', function ($element) use ($faker) {
            $element->assertSee('ユーザ権限')->assertSee('ユーザ名')->assertSee('ユーザID')->assertSee('パスワード')
                ->assertButtonEnabled('#btn-back')
                ->assertButtonEnabled('#btn-save')->pause(2000)
                //test null
                ->select('.user-regis-select', '')->pause(2000)
                //->type('.user_password', '')->pause(2000)
                ->press('#btn-save');
        });
        $browser->waitFor('#b-toaster-top-center')->assertSee('警告');
        $browser->with('.content-body', function ($element) use ($faker) {
            $element->assertSee('ユーザ権限')->assertSee('ユーザ名')->assertSee('ユーザID')->assertSee('パスワード')
                ->assertButtonEnabled('#btn-back')
                ->assertButtonEnabled('#btn-save')->pause(2000)
                //test not null
                ->select('.user-regis-select',[1,2])->pause(2000)
                //->type('.user-full-name', $faker->name)
                //->type('.user_password', '123456789')->pause(2000)
                //->type('.user_new_password', '1234456789')->pause(2000)
                ->press('#btn-save')->pause(5000);
        });
        //$browser->waitFor('#b-toaster-top-center')->assertSee('警告');
    }

    public function userDelete($browser)
    {
        //test cancel delete
        $browser->visit("#/master-manager/user-master")->pause(5000);
        $browser->assertSee('削除')->click('.btn-delete')->pause(2000)
            ->whenAvailable('#modal-cf', function ($modal) {
                $modal->pause(2000)
                    ->assertSee('削除してもよろしいですか')->assertSee('いいえ')->assertSee('はい')
                    ->pause(2000)
                    ->press('.btn-cancel')->pause(2000);
            });


        //test delete
        $browser->assertSee('削除')->click('.btn-delete')->pause(2000)
            ->whenAvailable('#modal-cf', function ($modal) {
                $modal->assertSee('削除してもよろしいですか')->assertSee('いいえ')->assertSee('はい')
                    ->pause(2000)
                    ->press('.btn-apply')->pause(2000);
            });
    }
}
