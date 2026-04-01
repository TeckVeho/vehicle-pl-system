<?php

namespace Tests\Browser;

use App\Models\Course;
use App\Models\Customer;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use App\Models\Store;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Helper\Common;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DuskTestCase;
use Faker\Factory as Faker;

class RouteTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {

        $this->browse(function ($browser) {
            $this->login($browser);
            $this->routeRegistry($browser);
            $this->editRoute($browser);
            $this->filterRoute($browser);
            $this->paginationRoute($browser);
            $this->sortRoute($browser);
            $this->deleteRoute($browser);
        });
    }

    public function initData()
    {

        Schema::disableForeignKeyConstraints();
        DB::table('route_store')->truncate();
        RouteNonDelivery::query()->truncate();
        Route::query()->truncate();
        Customer::query()->truncate();
        Store::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }

    public function routeRegistry($browser)
    {
        $this->initData();

        Customer::factory()
            ->state(function (array $attributes) {
                return ['customer_name' => 'cus 1'];
            })->count(1)->create();
        Store::factory()
            ->state(function (array $attributes) {
                return ['store_name' => 'store 1'];
            })->count(1)->create();

        $browser->visit("#/master-manager/route-master-create")->pause(5000);
        //input blank
        $browser->press('.button-save')->pause(100)
            ->assertSee('拠点を指定してください');
        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('拠点');
            $table->assertSee('ルート名');
            $table->assertSee('運賃');
            $table->assertSee('高速代');
            $table->scrollIntoView('.highway-fee-holiday-th');
            $table->assertSee('高速代(休日)');
            $table->scrollIntoView('.the-number-of-store-th');
            $table->assertSee('配送店舗数');
            $table->scrollIntoView('.suspension-of-service-th');
            $table->assertSee('運休曜日');
            $table->scrollIntoView('.suspension-of-service-date-th');
            $table->assertSee('運行表');
            $table->scrollIntoView('.remark-th');
            $table->assertSee('備考');

            $table->select('#select-department', 1)->pause(2000)
                ->type('#input-route-name', 'name route')
                ->select('#select-customer', 1)->pause(300)
                ->select('#select-fare-type', 1)->pause(300)
                ->type('#input-fare', '1000')->pause(300)
                ->type('#input-highway-fee', '1000')->pause(300)
                ->type('#input-highway-fee-holiday', '1000')->pause(300)
                ->scrollIntoView('.the-number-of-store-th')->pause(300)
                ->press('.multiselect')->pause(300);
            $elment = $table->elements('.multiselect__element');
            $elment[0]->click();
            $table->pause(300);
            $table->scrollIntoView('.suspension-of-service-th')->pause(2000);
            $elmentTd = $table->elements('tr > td.route-master-table-td');
            $elmentTd[8]->click();
            $table->pause(300);
            $table->scrollIntoView('.suspension-of-service-date-th')->pause(2000);
            $elmentTd[17]->click();
            $table->pause(300);
            $table->scrollIntoView('.remark-th')->type('#input-remark', 'test remark')->pause(300);
        });

        $browser->press('.button-save')->pause(2000)->waitUntilMissing('.loading')->assertSee('ルートを作成しました')->pause(3000);
        $browser
            ->assertSee('ルートマスタ')
            ->assertSee("フィルタ")
            ->assertSee('本社')
            ->assertSee('name route')
            ->assertSee('日額')
            ->assertSee('cus 1');
    }

    public function editRoute($browser)
    {
        $this->initData();
        Customer::factory()->count(2)->create();
        $route = Route::factory()->count(2)
            ->hasAttached(Store::factory()->count(2))->create();

        $browser->visit("#/master-manager/route-master")
            // test list sort
            ->pause(2000)
            //->waitUntilMissing('.loading')
            ->assertSee('ルートマスタ')->pause(2000)
            ->assertSee('フィルタ')->pause(2000)
            ->click("#collapsed-show-hide-filter")->pause(2000)
            ->assertSee('拠点')
            ->assertSee('ルート名')
            ->assertSee('荷主');
        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('拠点');
            $table->assertSee('ルートID');
            $table->assertSee('ルート名');
            $table->assertSee('運賃');
            $table->assertSee('高速代');
        });
        $browser->scrollIntoView('tbody tr:nth-child(1) .fa-pen')
            ->press('.fa-pen')->pause(2000);
        $browser
            ->assertSee('ルートマスタ');

        $browser->with('.table-route-master', function ($table) use ($browser) {
            $table->assertSee('拠点');
            $table->assertSee('ルート名');
            $table->assertSee('運賃');
            $table->assertSee('高速代');
            $table->scrollIntoView('.highway-fee-holiday-th');
            $table->assertSee('高速代(休日)');
            $table->scrollIntoView('.the-number-of-store-th');
            $table->assertSee('配送店舗数');
            $table->scrollIntoView('.suspension-of-service-th');
            $table->assertSee('運休曜日');
            $table->scrollIntoView('.suspension-of-service-date-th');
            $table->assertSee('運行表');
            $table->scrollIntoView('.remark-th');
            $table->assertSee('備考');


            $table->with('tbody', function ($row0) use ($table, $browser) {
//                $test = $row0->element("td:nth-child(3) input")->getAttribute('value');
//                var_dump($test);
//                $test = $row0->element("td:nth-child(3) input")->sendKeys('name route 1');
//                dd($test);
                //$row0->driver->findElement(WebDriverBy::xpath("//td[2]/input[@id='input-route-name']"))->sendKeys('name route 1');
                $this->setGetValInputTable($row0, true);
                $browser->press('.button-save')->pause(2000)->waitUntilMissing('.loading')->assertSee('ルート名を入力してください')->pause(3000);
                $this->setGetValInputTable($row0, false);

                $table->scrollIntoView('.the-number-of-store-th');
                $elmentSelect1 = $row0->elements('tr:nth-child(1) div.multiselect');
                $elmentSelect1[0]->click();
                $mtElment1 = $row0->elements('tr:nth-child(1) .multiselect__element');
                $mtElment1[0]->click();
                $table->click('.the-number-of-store-th');
//                $elmentSelect2 = $row0->elements('tr:nth-child(2) div.multiselect');
//                $elmentSelect2[0]->click();
//                $mtElment2 = $row0->elements('tr:nth-child(2) .multiselect__element');
//                $mtElment2[0]->click();
                $row0->pause(300);
                $table->scrollIntoView('.suspension-of-service-th')->pause(2000);
                $elmentTd1 = $row0->elements('tr:nth-child(1) td.route-master-table-td');
                $elmentTd1[11]->click();
//                $elmentTd2 = $row0->elements('tr:nth-child(2) td.route-master-table-td');
//                $elmentTd2[12]->click();
                $row0->pause(300);
                $table->scrollIntoView('.suspension-of-service-date-th')->pause(2000);
                $row0->elements('tr:nth-child(1) td.route-master-table-td')[24]->click();
//                $row0->elements('tr:nth-child(2) td.route-master-table-td')[25]->click();
                $row0->pause(300);
                $table->scrollIntoView('.remark-th');
                $row0->type('tr:nth-child(1) td #input-remark', 'test remark 1')->pause(300);
//                $row0->type('tr:nth-child(2) td #input-remark', 'test remark 2')->pause(300);
            });
        });
        $browser->press('.button-save')->pause(2000)->waitUntilMissing('.loading')->assertSee('編集が完了しました')->pause(3000);
        $browser
            ->assertSee('ルートマスタ')
            ->assertSee("フィルタ")
            ->assertSee('name route 1');
//            ->assertSee('name route 2');
    }

    private function setGetValInputTable($row, $isEmpty = false)
    {
        $row->type('tr:nth-child(1) td:nth-child(3) input', $isEmpty ? '' : 'name route 1')->pause(300)
//            ->type('tr:nth-child(2) td:nth-child(3) input', $isEmpty ? '' : 'name route 2')->pause(300)
            ->select('tr:nth-child(1) td:nth-child(4) #select-customer', $isEmpty ? '' : 1)->pause(300)
//            ->select('tr:nth-child(2) td:nth-child(4) #select-customer', $isEmpty ? '' : 2)->pause(300)
            ->select('tr:nth-child(1) td:nth-child(5) #select-fare-type', $isEmpty ? '' : 1)->pause(300)
//            ->select('tr:nth-child(2) td:nth-child(5) #select-fare-type', $isEmpty ? '' : 1)->pause(300)
            ->type('tr:nth-child(1) td:nth-child(6) #input-fare', $isEmpty ? '' : '1000')->pause(300)
//            ->type('tr:nth-child(2) td:nth-child(6) #input-fare', $isEmpty ? '' : '2000')->pause(300)
            ->type('tr:nth-child(1) td:nth-child(7) #input-highway-fee', $isEmpty ? '' : '1000')->pause(300)
//            ->type('tr:nth-child(2) td:nth-child(7) #input-highway-fee', $isEmpty ? '' : '2000')->pause(300)
            ->type('tr:nth-child(1) td:nth-child(8) #input-highway-fee-holiday', $isEmpty ? '' : '1000')->pause(1000);
//            ->type('tr:nth-child(2) td:nth-child(8) #input-highway-fee-holiday', $isEmpty ? '' : '2000')->pause(1000);
    }

    public function filterRoute($browser)
    {
        $this->initData();
        Customer::factory()
            ->state(function (array $attributes) {
                return ['customer_name' => 'Test customer 1'];
            })->count(1)->create();
        Customer::factory()
            ->state(function (array $attributes) {
                return ['customer_name' => 'Test customer 2'];
            })->count(1)->create();
        $route = Route::factory()
            ->state(function (array $attributes) {
                return [
                    'name' => 'Test route',
                    'department_id' => 1,
                    'customer_id' => 1,
                ];
            })
            ->count(1)
            ->hasAttached(Store::factory()->count(2))->create();

        $route = Route::factory()
            ->state(function (array $attributes) {
                return ['name' => 'Test route 2'];
            })
            ->count(1)
            ->hasAttached(Store::factory()->count(2))->create();

        $browser->visit("#/master-manager/route-master")->pause(300)
            //->waitUntilMissing('.loading')
            ->assertSee('ルートマスタ')->pause(300)
            ->assertSee('フィルタ')->pause(300)
            ->click("#collapsed-show-hide-filter")->pause(300)
            ->assertSee('拠点')
            ->assertSee('ルート名')
            ->assertSee('荷主');
        //filter by department
        $browser->with('#zone-filter', function ($element) {
            $element->check("@filter-by-department")->pause(300)
                ->assertChecked(".chk_filter")
                ->select("#filter-by-department", 1)
                ->click(".apply-filter-button")->pause(3000);
        });
        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '本社');
        });

        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("div:nth-child(2) div:nth-child(2) input.chk_filter")->pause(300)
                ->type("#filter-by-route-name", 'Test route')
                ->click(".apply-filter-button")->pause(3000);
        });

        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('ルート名');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(3)', 'Test route');
        });

        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("@filter-by-customer")->pause(300)
                ->select("#filter-by-customer", 1)
                ->click(".apply-filter-button")->pause(3000);
        });

        $browser->with('.table-route-master', function ($table) {
            $table->assertSee('ルート名');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(4)', 'Test customer 1');
        });
    }

    public function paginationRoute($browser)
    {
        $this->initData();
        Customer::factory()->count(3)->create();
        $route = Route::factory()->count(200)
            ->hasAttached(Store::factory()->count(1))->create();

        $browser->visit("#/master-manager/route-master")
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

    public function sortRoute($browser)
    {
        $this->initData();
        Customer::factory()
            ->state(function (array $attributes) {
                return ['customer_name' => 'Test customer 1'];
            })->count(1)->create();
        Customer::factory()
            ->state(function (array $attributes) {
                return ['customer_name' => 'Test customer 2'];
            })->count(1)->create();
        $route = Route::factory()
            ->state(function (array $attributes) {
                return [
                    'name' => 'Test route 1',
                    'department_id' => 1,
                    'customer_id' => 1,
                    'route_fare_type' => 1,
                    'fare' => 1000,
                    'highway_fee' => 1000,
                    'highway_fee_holiday' => 1000,
                ];
            })
            ->count(1)
            ->hasAttached(Store::factory()->count(1))->create();

        $route = Route::factory()
            ->state(function (array $attributes) {
                return [
                    'name' => 'Test route 2',
                    'department_id' => 2,
                    'customer_id' => 2,
                    'route_fare_type' => 2,
                    'fare' => 2000,
                    'highway_fee' => 2000,
                    'highway_fee_holiday' => 2000,
                ];
            })
            ->count(1)
            ->hasAttached(Store::factory()->count(2))->create();

        $classOrder = [
            ['class' => '.department-th', 'sort_by' => 'department_name', 'key_map' => 'department_name'],
            ['class' => '.route-id-th', 'sort_by' => 'route_id', 'key_map' => 'id'],
            ['class' => '.route-name-th', 'sort_by' => 'name', 'key_map' => 'name'],
            ['class' => '.customer-th', 'sort_by' => 'customer_name', 'key_map' => 'customer_name'],
            ['class' => '.fare-type-th', 'sort_by' => 'route_fare_type', 'key_map' => 'route_fare_type'],
            ['class' => '.fare-th', 'sort_by' => 'fare', 'key_map' => 'fare'],
            ['class' => '.highway-fee-th', 'sort_by' => 'highway_fee', 'key_map' => 'highway_fee'],
            ['class' => '.highway-fee-holiday-th', 'sort_by' => 'highway_fee_holiday', 'key_map' => 'highway_fee_holiday'],
            ['class' => '.the-number-of-store-th', 'sort_by' => 'store', 'key_map' => 'store_count'],
        ];

        $browser->visit("#/master-manager/route-master")->pause(2000)
            //->waitUntilMissing('.loading')
            ->assertSee('ルートマスタ')->pause(300)
            ->assertSee('フィルタ')->pause(300)
            ->pause(300);
        $browser->with('.b-table', function ($table) use ($classOrder) {
            foreach ($classOrder as $key => $lstOrder) {
                $index = $key + 1;
                $clOrder = (object)$lstOrder;
                $table->scrollIntoView($clOrder->class)->pause(1000)->click($clOrder->class)->pause(2000);
                if (!in_array($index, [5, 9])) {
                    $responseData = $this->callApiSortByCheck($clOrder->sort_by, true);
                    $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(' . $index . ')', $responseData['data']['result'][0][$clOrder->key_map]);
                    $table->assertSeeIn('tbody tr:nth-child(2) td:nth-child(' . $index . ')', $responseData['data']['result'][1][$clOrder->key_map]);
                }
                $table->scrollIntoView($clOrder->class)->pause(1000)->click($clOrder->class)->pause(2000);
                if (!in_array($index, [5, 9])) {
                    $responseData = $this->callApiSortByCheck($clOrder->sort_by);
                    $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(' . $index . ')', $responseData['data']['result'][0][$clOrder->key_map]);
                    $table->assertSeeIn('tbody tr:nth-child(2) td:nth-child(' . $index . ')', $responseData['data']['result'][1][$clOrder->key_map]);
                }
            }
        });
    }

    private function callApiSortByCheck($sort_by, $sort_type = false)
    {
        $rpLogin = $this->post('/api/auth/login', ['id' => "111111", 'password' => '123456789']);
        $data = json_decode($rpLogin->getContent());
        $token = $data->data->access_token;
        $response = $this->withHeaders(['Authorization' => $token])->get('api/route?sort_by=' . $sort_by . '&sort_type=' . $sort_type);
        return $response->json();
    }


    public function deleteRoute($browser)
    {
        $this->initData();
        Customer::factory()->count(2)->create();
        $route = Route::factory()->count(1)->create();

        //test delete
        $browser->visit("#/master-manager/route-master")->pause(2000);
        $browser->with('.b-table', function ($table) use ($browser) {
            $table->scrollIntoView('.delete-th')->pause(1000)
                ->click('tbody tr:nth-child(1) .fa-trash.icon-delete')->pause(1000);
            $browser->whenAvailable('#modal-cf', function ($modal) {
                $modal->assertSee('このルートを削除してもよろしいですか？')->assertSee('いいえ')->assertSee('はい')
                    ->pause(2000)
                    ->press('.btn-apply')->pause(2000);
            });
            $browser->pause(3000);
        });


        $Course = Course::factory()->count(1)->create();
        $route = Route::factory()->count(1)
            ->hasAttached($Course, ['position' => 1])
            ->create();
        //test cancel delete
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
    }
}
