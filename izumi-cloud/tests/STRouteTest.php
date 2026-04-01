<?php

namespace Tests;

use App\Models\Course;
use App\Models\Customer;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use App\Models\Store;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class STRouteTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {
        //Artisan::call('migrate:fresh --seed');
        $this->browse(function ($browser) {
            $this->login($browser);
            $this->initData();
            $this->testCreateStore($browser);
            $this->testCreateCustomer($browser);
            $this->routeRegistry($browser);
            $this->testNewCourse($browser);
            $this->login($browser);
//            $this->testEditStore($browser);
            $this->testEditCustomer($browser);
            $this->editRoute($browser);
            $this->editCourse($browser);
            $this->login($browser);
            $this->testDeleteStore($browser);
            $this->testDeleteSuccessCustomer($browser);
            $this->deleteCourseAndRoute($browser);
        });
    }

    public function initData()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('course_non_delivery')->truncate();
        DB::table('course_route')->truncate();
        DB::table('course_schedule')->truncate();
        Course::query()->truncate();
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        Customer::query()->truncate();
        Store::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function testCreateStore($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/store-master"]')->pause(2000);
        });
        $browser
            ->assertSee('店舗マスタ')
            ->click('.btn-registration')->pause(2000);
        $browser
            ->assertSee('店舗マスタ')
            ->assertSee('店舗名');
        $browser->pause(1000);
        $browser->type('#input-store-name', 'Store_Test1');
        $browser->pause(1000);
        $browser->click('.btn-registration');
        $browser->pause(2000);

        $browser
            ->assertSee('店舗マスタ')
            ->assertSee('Store_Test1')
            ->assertSee('新規登録に成功しました')
            ->click('.btn-registration')->pause(2000);
        $browser
            ->assertSee('店舗マスタ')
            ->assertSee('店舗名');
        $browser->pause(1000);
        $browser->type('#input-store-name', 'Store_Test2');
        $browser->pause(1000);
        $browser->click('.btn-registration');
        $browser->pause(2000);
        $browser
            ->assertSee('店舗マスタ')
            ->assertSee('Store_Test1')
            ->assertSee('Store_Test2')
            ->assertSee('新規登録に成功しました')
            ->click('.btn-registration')->pause(1000);
    }

    private function testCreateCustomer($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/customer-master"]')->pause(2000);
        });
        $browser
            ->assertSee('荷主マスタ')
            ->click('.btn-registration')->pause(2000);
        $browser
            ->assertSee('荷主マスタ')
            ->assertSee('荷主名');
        $browser->pause(2000);
        $browser->type('#input-customer-name', 'Customer_Test1');
        $browser->pause(1000);
        $browser->click('.btn-registration');
        $browser->pause(2000);

        $browser
            ->assertSee('荷主マスタ')
            ->assertSee('Customer_Test1')
            ->assertSee('新規登録に成功しました');

        $browser->click('.btn-registration')->pause(2000);
        $browser
            ->assertSee('荷主マスタ')
            ->assertSee('荷主名');
        $browser->pause(1000);
        $browser->type('#input-customer-name', 'Customer_Test2');
        $browser->pause(1000);
        $browser->click('.btn-registration');
        $browser->pause(2000);

        $browser
            ->assertSee('荷主マスタ')
            ->assertSee('Customer_Test2')
            ->assertSee('新規登録に成功しました');
    }


    public function routeRegistry($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/route-master"]')->pause(2000);
        });

        $browser
            ->assertSee('ルートマスタ')
            ->click('.button-register')->pause(2000);
        $browser
            ->assertSee('ルートマスタ');

        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('拠点');
            $table->assertSee('ルート名');
            $table->assertSee('運賃');
            $table->assertSee('高速代');
            $table->pause(1000);

            $table->select('#select-department', 1)->pause(2000)
                ->type('#input-route-name', 'Route_Test1')
                ->select('#select-customer', 1)->pause(300)
                ->select('#select-fare-type', 2)->pause(300)
                ->type('#input-fare', '3000')->pause(300)
                ->type('#input-highway-fee', '100')->pause(300)
                ->type('#input-highway-fee-holiday', '100')->pause(300)
                ->scrollIntoView('.the-number-of-store-th')->pause(300)
                ->press('.multiselect')->pause(300);
            $elment = $table->elements('.multiselect__element');
            $elment[0]->click();
            $elment[1]->click();
            $table->pause(300);
            $table->scrollIntoView('.suspension-of-service-th')->pause(2000);
            $elmentTd = $table->elements('tr > td.route-master-table-td');
            $elmentTd[8]->click();
            $table->pause(300);
            $table->scrollIntoView('.suspension-of-service-date-th')->pause(2000);
            $elmentTd[16]->click();
            $table->pause(300);
            $table->scrollIntoView('.remark-th')->type('#input-remark', 'test remark')->pause(300);
        });

        $browser->press('.button-save')->pause(2000)->waitUntilMissing('.loading')->assertSee('ルートを作成しました')->pause(3000);
        $browser
            ->assertSee('ルートマスタ')
            ->assertSee("フィルタ")
            ->assertSee('本社')
            ->assertSee('Route_Test1')
            ->assertSee('月額')->pause(3000);


        $browser
            ->assertSee('ルートマスタ')
            ->click('.button-register')->pause(2000);
        $browser
            ->assertSee('ルートマスタ');

        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('拠点');
            $table->assertSee('ルート名');
            $table->assertSee('運賃');
            $table->assertSee('高速代');
            $table->pause(1000);

            $table->select('#select-department', 2)->pause(2000)
                ->type('#input-route-name', 'Route_Test2')
                ->select('#select-customer', 2)->pause(300)
                ->select('#select-fare-type', 2)->pause(300)
                ->type('#input-fare', '3000')->pause(300)
                ->type('#input-highway-fee', '100')->pause(300)
                ->type('#input-highway-fee-holiday', '100')->pause(300)
                ->scrollIntoView('.the-number-of-store-th')->pause(300)
                ->press('.multiselect')->pause(300);
            $elment = $table->elements('.multiselect__element');
            $elment[0]->click();
            $elment[1]->click();
            $table->pause(300);
            $table->scrollIntoView('.suspension-of-service-th')->pause(2000);
            $elmentTd = $table->elements('tr > td.route-master-table-td');
            $elmentTd[8]->click();
            $table->pause(300);
            $table->scrollIntoView('.suspension-of-service-date-th')->pause(2000);
            $elmentTd[16]->click();
            $table->pause(300);
            $table->scrollIntoView('.remark-th')->type('#input-remark', 'test remark')->pause(300);
        });

        $browser->press('.button-save')->pause(2000)->waitUntilMissing('.loading')->assertSee('ルートを作成しました')->pause(3000);
        $browser
            ->assertSee('ルートマスタ')
            ->assertSee("フィルタ")
            ->assertSee('本社')
            ->assertSee('Route_Test2')
            ->assertSee('月額')->pause(3000);
    }

    private function testNewCourse($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/course-master"]')->pause(2000);
        });

        $browser
            ->assertSee('コースマスタ')
            ->click('.text-right button:nth-child(1)')->pause(2000);
        $browser
            ->assertSee('コースマスタ');

        $browser->pause(1000);
        $browser->select('#select-base', 1);
        $browser->pause(2000);

        $browser->type('#input-course-id', 'Course_Test1');
        $browser->pause(1000);
        $browser->click('#select-date-shipping-start-date__value_');
        $browser->pause(1000);

        $browser->click('button[title="Previous month"]');
        $browser->pause(1000);
        $startMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $startMonth . '"]');

        $browser->pause(1000);
        $browser->click('#select-date-delivery-end-date__value_');
        $endMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');

        $browser->select('#select-irregular-course', 0);
        $browser->select('#select-course-type', 1);
        $browser->select('#select-flight-type', 1);
        $browser->select('#select-delivery-type', 1);
        $browser->type('#input-quanity', 1);

        $browser->select('#select-route-start-time-hour', 0);
        $browser->pause(1000);
        $browser->select('#select-route-start-time-min', 0);
        $browser->type('#input-course-allowance', 100);
        $browser->select('#select-gate', 0);
        $browser->pause(1000);
        $browser->select('#select-wing', 0);
        $browser->pause(1000);
        $browser->select('#select-tonnage', 2);
        $browser->pause(1000);
        $browser->select('.delivery-course-creation select[class^="custom-select"]', 1);
        $browser->pause(1000);
        $browser->click('.delivery-course-creation .input-group-append button.btn');
        $browser->pause(1000);
        $browser->click('.btn-sign-up');
        $browser->pause(1000);
        $browser->assertSee("新規登録に成功しました");
        $browser->pause(2000);
        $browser
            ->assertSee('コースマスタ')
            ->assertSee("フィルタ")
            ->assertSee('本社')
            ->assertSee('Course_Test1')
            ->assertSee('Route_Test1')->pause(1000);

        $browser
            ->assertSee('コースマスタ')
            ->click('.text-right button:nth-child(1)')->pause(2000);
        $browser
            ->assertSee('コースマスタ');

        $browser->pause(1000);
        $browser->select('#select-base', 2);
        $browser->pause(2000);

        $browser->type('#input-course-id', 'Course_Test2');
        $browser->pause(1000);
        $browser->click('#select-date-shipping-start-date__value_');
        $browser->pause(1000);

        $browser->click('button[title="Previous month"]');
        $browser->pause(1000);
        $startMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $startMonth . '"]');

        $browser->pause(1000);
        $browser->click('#select-date-delivery-end-date__value_');
        $endMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');

        $browser->select('#select-irregular-course', 0);
        $browser->select('#select-course-type', 1);
        $browser->select('#select-flight-type', 1);
        $browser->select('#select-delivery-type', 1);
        $browser->type('#input-quanity', 1);

        $browser->select('#select-route-start-time-hour', 0);
        $browser->pause(1000);
        $browser->select('#select-route-start-time-min', 0);
        $browser->type('#input-course-allowance', 100);
        $browser->select('#select-gate', 0);
        $browser->pause(1000);
        $browser->select('#select-wing', 0);
        $browser->pause(1000);
        $browser->select('#select-tonnage', 2);
        $browser->pause(1000);
        $browser->scrollIntoView('.delivery-course-creation');
        $browser->select('.delivery-course-creation select[class^="custom-select"]', 2);
        $browser->pause(1000);
        $browser->click('.delivery-course-creation .input-group-append button.btn');
        $browser->pause(1000);
        $browser->click('.btn-sign-up');
        $browser->pause(1000 * 2);
        $browser->assertSee("新規登録に成功しました");
        $browser->pause(2000);
        $browser
            ->assertSee('コースマスタ')
            ->assertSee("フィルタ")
            ->assertSee('本社')
            ->assertSee('Course_Test2')
            ->assertSee('Route_Test2')->pause(1000);

        $browser->click('.btn-logout')->pause(3000);
    }


//    private function testEditStore($browser)
//    {
//        $browser->mouseover('.display-menu a:nth-child(4)');
//        $browser->with('.sub-menu', function ($menu) {
//            $menu->click('a[href="#/master-manager/store-master"]')->pause(2000);
//        });
//        $browser
//            ->assertSee('店舗マスタ');
//
//        $browser->with('#table-store-master tbody', function ($table) use ($browser) {
//            $table->click('tr:nth-child(1) .td-edit i');
//        });
//        $browser->pause(2000);
//        $browser
//            ->assertSee('店舗マスタ')
//            ->assertSee('店舗名');
//        $browser->pause(1000);
//        $browser->type('#input-store-name', 'Store1');
//        $browser->click('.btn-registration');
//        $browser->pause(2000);
//        $browser->assertSee('Store1');
//        $browser->pause(5000);
//    }

    private function testEditCustomer($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/customer-master"]')->pause(2000);
        });
        $browser
            ->assertSee('荷主マスタ');

        $browser->with('#table-customer-master tbody', function ($table) use ($browser) {
            $table->click('tr:nth-child(1) .td-edit i');
        });
        $browser->pause(2000);
        $browser
            ->assertSee('荷主マスタ')
            ->assertSee('荷主名');
        $browser->pause(1000);
        $browser->type('#input-customer-name', 'Customer1');
        $browser->click('.btn-registration');
        $browser->pause(2000);
        $browser->assertSee('Customer1');
        $browser->pause(5000);
    }

    public function editRoute($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/route-master"]')->pause(2000);
        });

        $browser->with('.table-route-master', function ($table) use ($browser) {
            $table->assertSee('拠点');
            $table->assertSee('ルート名');
            $table->assertSee('運賃');
            $table->assertSee('高速代');
            $table->assertSee('Customer1');
            $table->click('tr:nth-child(1) td.store-td div')->pause(1000);
            $table->assertSee('Store_Test1');
        });

        $browser->scrollIntoView('tbody tr:nth-child(1) .fa-pen')
            ->press('.fa-pen')->pause(2000);
        $browser
            ->assertSee('ルートマスタ');

        $browser->with('.table-route-master', function ($table) use ($browser) {
            $table->with('tbody', function ($row0) use ($table, $browser) {
                $this->setGetValInputTable($row0, false);

                $table->scrollIntoView('.the-number-of-store-th');
                $elmentSelect1 = $row0->elements('tr:nth-child(1) div.multiselect');
                $elmentSelect1[0]->click();
                $mtElment1 = $row0->elements('tr:nth-child(1) .multiselect__element');
                $mtElment1[1]->click();
                $row0->pause(300);
                $table->scrollIntoView('.suspension-of-service-th')->pause(2000);
                $row0->pause(300);
                $table->scrollIntoView('.suspension-of-service-date-th')->pause(2000);
                $row0->click('tr:nth-child(1) td:nth-child(26)');
                $row0->pause(300);
                $table->scrollIntoView('.remark-th');
                $row0->type('tr:nth-child(1) td #input-remark', 'test remark 1')->pause(300);
            });
        });
        $browser->press('.button-save')->pause(2000)->waitUntilMissing('.loading')->assertSee('編集が完了しました')->pause(3000);
        $browser
            ->assertSee('ルートマスタ')
            ->assertSee("フィルタ")
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '本社')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', 'Route1')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Customer_Test2')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(5)', '日額')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(6)', '100')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(7)', '200')
            ->assertSeeIn('tbody tr:nth-child(1) td:nth-child(8)', '200')
            ->click('tbody tr:nth-child(1) td.store-td div')->pause(1000)
            ->assertSee('Store_Test1')
            ->scrollIntoView('.suspension-of-service-date-th')->pause(2000);
//            ->assertAttributeContains('tbody tr:nth-child(1) td:nth-child(26)', 'class', 'day-off-date');
        $browser->pause(2000);
    }

    private function setGetValInputTable($row, $isEmpty = false)
    {
        $row->type('tr:nth-child(1) td:nth-child(3) input', $isEmpty ? '' : 'Route1')->pause(300)
            ->select('tr:nth-child(1) td:nth-child(4) #select-customer', $isEmpty ? '' : 2)->pause(300)
            ->select('tr:nth-child(1) td:nth-child(5) #select-fare-type', $isEmpty ? '' : 1)->pause(300)
            ->type('tr:nth-child(1) td:nth-child(6) #input-fare', $isEmpty ? '' : '100')->pause(300)
            ->type('tr:nth-child(1) td:nth-child(7) #input-highway-fee', $isEmpty ? '' : '200')->pause(300)
            ->type('tr:nth-child(1) td:nth-child(8) #input-highway-fee-holiday', $isEmpty ? '' : '200')->pause(1000);
    }


    private function editCourse($browser)
    {   // update start-end-date
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/course-master"]')->pause(2000);
        });

        $browser
            ->assertSee('コースマスタ');
        $browser->scrollIntoView('.handle')->pause(2000);
        $btn = $browser->elements('tbody tr:nth-child(1) .fa-eye');
        $browser->pause(5000);
        $btn[0]->click();
        $browser->pause(1000);

        $browser
            ->assertSee('コースマスタ');

        $browser->scrollIntoView('.course-master-detail__handle')->pause(2000);
        $browser->click('.course-master-detail div:nth-child(3) div > div:nth-child(2) button');
        $browser->pause(2000);

        $browser->type('.course-master-edit__basic-information div:nth-child(3) input', 'Course1');
        $browser->pause(2000);

        $browser->click('#select-date-delivery-end-date');
        $browser->pause(1000);
        $browser->click('button[aria-label="Next month"]');
        $endMonth = Carbon::now()->addMonth()->endOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');
        $browser->pause(1000);

        $browser->select('#select-irregular-course', 0);
        $browser->select('#select-course-type', 2);
        $browser->select('#select-flight-type', 2);
        $browser->select('#select-delivery-type', 2);
        $browser->type('#input-quanity', 2);

        $browser->select('#select-route-start-time-hour', 1);
        $browser->pause(1000);
        $browser->select('#select-route-start-time-min', 10);
        $browser->type('#input-course-allowance', 200);
        $browser->select('#select-gate', 1);
        $browser->pause(1000);
        $browser->select('#select-wing', 1);
        $browser->pause(1000);
        $browser->select('#select-tonnage', 4);
        $browser->pause(1000);

        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="page-content-wrapper"]/div[2]/div/div/div/div/div[3]/div/div[2]/button'));
        $btn[0]->click();
        $browser->pause(1000);
        $browser->assertSee("編集が完了しました");

        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/course-master"]')->pause(2000);
        });

        $browser
            ->assertSee('コースマスタ');
        $browser->scrollIntoView('.handle')->pause(2000);
        $btn = $browser->elements('.fa-eye');
        $browser->pause(5000);
        $btn[0]->click();
        $browser->pause(2000);

        $browser
            ->assertSee('コースマスタ')
            ->assertValue('#input-course-id', 'Course1')
            ->assertSeeIn('#select-date-delivery-end-date__value_', Carbon::now()->addMonth()->endOfMonth()->format('Y/m/d'))
            ->assertSeeIn('#select-date-shipping-start-date__value_ ', Carbon::now()->firstOfMonth()->format('Y/m/d'))
            ->assertSee('大量販売')
            ->assertSee('一便')
            ->assertSee('チルド')
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('10')
            ->assertValue('#input-course-allowance', 200)
            ->assertSee('なし')
            ->assertSee('4');
        $browser->click('.btn-logout')->pause(3000);
    }

    private function testDeleteStore($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/store-master"]')->pause(2000);
        });
        $browser
            ->assertSee('店舗マスタ')->pause(2000);

        $browser->click('tbody tr:nth-child(1) .td-delete i');
        $browser->pause(1000);
        $buttonConfirmDelete = $browser->driver->findElements(WebDriverBy::xpath(' //*[@id="modal-cf___BV_modal_footer_"]/button[2]'));
        $buttonConfirmDelete[0]->click();
        $browser->pause(2000);
        $browser->assertSee('店舗を削除しました');
        $browser->assertDontSee('Store_Test1');
        $browser->pause(1000);
    }


    private function testDeleteSuccessCustomer($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/customer-master"]')->pause(2000);
        });
        $browser
            ->assertSee('荷主マスタ')->pause(2000);

        $browser->click('tbody tr:nth-child(2) .td-delete i');
        $browser->pause(1000);
        $buttonConfirmDelete = $browser->driver->findElements(WebDriverBy::xpath(' //*[@id="modal-cf___BV_modal_footer_"]/button[2]'));
        $buttonConfirmDelete[0]->click();
        $browser->pause(2000);
        $browser->assertSee('荷主を削除しました');
        $browser->assertDontSee('Customer_Test2');
        $browser->pause(1000);
    }

    public function deleteCourseAndRoute($browser)
    {
        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/route-master"]')->pause(2000);
        });

        $browser
            ->assertSee('ルートマスタ')
            ->assertDontSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Customer_Test2')
            ->click('tbody tr:nth-child(1) td.store-td div')->pause(1000)
            ->assertDontSeeIn('tbody tr:nth-child(1) td.store-td div', 'Store_Test1')
            ->pause(2000);

        //test not delete
        $browser->visit("#/master-manager/route-master")->pause(2000);
        $browser->with('.b-table', function ($table) use ($browser) {
            $table->scrollIntoView('.delete-th')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-trash.icon-delete')->pause(1000);
            $browser->whenAvailable('#modal-cf', function ($modal) {
                $modal->assertSee('このルートを削除してもよろしいですか？')->assertSee('いいえ')->assertSee('はい')
                    ->pause(2000)
                    ->press('.btn-apply')->pause(1000);
            });
            $browser->assertSee('このルートはコースに組み込まれているため削除できません。')->pause(3000);
        });


        $browser->mouseover('.display-menu a:nth-child(4)');
        $browser->with('.sub-menu', function ($menu) {
            $menu->click('a[href="#/master-manager/course-master"]')->pause(2000);
        });

        $browser
            ->assertSee('コースマスタ');
        $browser->scrollIntoView('.handle')->pause(2000);
        $btn = $browser->elements('tbody tr:nth-child(1) .fa-trash');
        $browser->pause(1000);
        $btn[0]->click();

        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="modal-cf___BV_modal_footer_"]/button[2]'));
        $btn[0]->click();
        $browser->pause(1000);
        $browser->assertSee("コースが削除しました");

        //test delete success
        $browser->visit("#/master-manager/route-master")->pause(2000);
        $browser->with('.b-table', function ($table) use ($browser) {
            $table->scrollIntoView('.delete-th')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-trash.icon-delete')->pause(1000);
            $browser->whenAvailable('#modal-cf', function ($modal) {
                $modal->assertSee('このルートを削除してもよろしいですか？')->assertSee('いいえ')->assertSee('はい')
                    ->pause(2000)
                    ->press('.btn-apply')->pause(2000);
            });
            $browser->assertSee('ルートを削除しました')->pause(3000);
        });
    }
}
