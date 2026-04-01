<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Facebook\WebDriver\WebDriverBy;
use App\Models\Route;
use Facebook\WebDriver\WebDriverSelect;

class CourseTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */

    protected $pause = 500;
    protected $route;

    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')->waitFor('.login-btn');
            $this->loginSuccess($browser);
            $this->testValidateOfCreateStoreOverThan20($browser);
//            $this->testValidateOfCreateStoreNameBlank($browser);
            $this->testCreateStore($browser);
            $this->testCreateStoreAlreadyExist($browser);
//            $this->testEditStore($browser);
//            $this->testEditStoreBlankName($browser);
            $this->testDeleteSuccess($browser);

            $this->testValidateOfCreateCustomerOverThan20($browser);
            $this->testValidateOfCreateCustomerNameBlank($browser);
            $this->testCreateCustomer($browser);
            $this->testCreateCustomerAlreadyExist($browser);
            $this->testEditCustomer($browser);
            $this->testEditCustomerBlankName($browser);
            $this->testDeleteSuccessCustomer($browser);
            $this->createRouteFake();

            $this->testNewCourse($browser, 'Course 01');
            $this->testEditCourse($browser);
            $this->testDeleteCourse($browser);

//            for ($i = 0; $i < 10; $i++) {
//                $this->testNewCourse($browser, 'Course ' . $i);
//            }

            $this->testFilterByDepartment($browser);
            $browser->pause($this->pause * 2);
            $this->testFilterByID($browser);
            $browser->pause($this->pause * 2);
            $this->testSortByDepartment($browser);
            $this->testSortByCourse($browser);
            $this->testSortByTotalFare($browser);
            $this->testSortByTotalHighway($browser);
        });
    }


    public function loginSuccess($browser)
    {
        $browser->visit('/')->waitFor('.login-btn')
            ->type('#user_id', '111111')
            ->type('#password', '123456789')->press('.login-btn');
        $browser->pause($this->pause * 5);
    }

    private function testValidateOfCreateStoreOverThan20($browser)
    {
        $browser->visit('#/master-manager/store-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-store-name', 'This is store name over than 20 characters. please validate this one'); // blank
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause);
        $browser->assertSee('店舗名は20文字以内で入力ください。');
        $browser->pause($this->pause * 2);
    }

//    private function testValidateOfCreateStoreNameBlank($browser)
//    {
//        $browser->visit('#/master-manager/store-master');
//        $browser->pause($this->pause * 3);
//        $browser->click('.btn-registration');
//        $browser->pause($this->pause * 2);
//        $browser->type('#input-store-name', '');
//        $browser->pause($this->pause * 2);
//        $browser->click('.btn-registration');
//        $browser->pause($this->pause);
//        $browser->assertSee('店舗名を入力してください。');
//        $browser->pause($this->pause * 2);
//    }

    private function testCreateStore($browser)
    {
        $browser->visit('#/master-manager/store-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-store-name', 'Test store');
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 4);
        $browser->assertSee('新規登録に成功しました');
        $browser->pause($this->pause * 2);
    }

    private function testCreateStoreAlreadyExist($browser)
    {
        $browser->visit('#/master-manager/store-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-store-name', 'Test store');
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 4);
        $browser->assertSee('指定の店舗名は既に作成されています。');
        $browser->pause($this->pause * 2);
    }

//    private function testEditStore($browser)
//    {
//        $browser->visit('#/master-manager/store-master');
//        $browser->pause($this->pause * 2);
//        $browser->click('tr:nth-child(1) .fa-eye');
//        $browser->pause($this->pause * 2);
//        $browser->click('.btn-to-edit');
//        $browser->pause($this->pause * 2);
//        $browser->type('#input-store-name', 'Test store 1');
//        $browser->click('.btn-registration');
//        $browser->pause($this->pause * 2);
////        $browser->assertSee('Test store 1');
//        $browser->pause($this->pause * 5);
//    }

    private function testEditStoreBlankName($browser)
    {
        $browser->visit('#/master-manager/store-master');
        $browser->pause($this->pause * 3);
        $browser->click('tr:nth-child(1) .fa-eye');
        $browser->pause($this->pause * 2);
        $browser->click('.btn-to-edit');
        $browser->pause($this->pause * 2);
        $browser->type('#input-store-name', '');
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->assertSee('店舗名を入力してください。');
        $browser->pause($this->pause * 2);
    }

    private function testDeleteSuccess($browser)
    {
        $browser->visit('#/master-manager/store-master');
        $browser->pause($this->pause * 3);
        $browser->click('.td-delete');
        $browser->pause($this->pause);
        $buttonConfirmDelete = $browser->driver->findElements(WebDriverBy::xpath(' //*[@id="modal-cf___BV_modal_footer_"]/button[2]'));
        $buttonConfirmDelete[0]->click();
        $browser->pause($this->pause * 4);
        $browser->assertSee('店舗を削除しました');
        //*[@id="modal-cf___BV_modal_footer_"]/button[2]
    }


    // -----------------------------------------
    private function testValidateOfCreateCustomerOverThan20($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-customer-name', 'This is customer name over than 20 characters. please validate this one'); // blank
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->assertSee('荷主名は20文字以内で入力ください。');
    }

    private function testValidateOfCreateCustomerNameBlank($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-customer-name', '');
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->assertSee('荷主名を入力してください。');
    }

    private function testCreateCustomer($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-customer-name', 'Test customer');
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        //$browser->assertSee('新規登録に成功しました');
    }

    private function testCreateCustomerAlreadyExist($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->type('#input-customer-name', 'Test customer');
        $browser->pause($this->pause * 2);
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        //$browser->assertSee('指定の荷主名は既に使用されています。');
    }

    private function testEditCustomer($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.td-edit');
        $browser->pause($this->pause * 2);
        $browser->type('#input-customer-name', 'Test customer 1');
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 3);
        $browser->assertSee('Test customer 1');
        $browser->pause($this->pause * 5);
    }

    private function testEditCustomerBlankName($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.td-edit');
        $browser->pause($this->pause * 2);
        $browser->type('#input-customer-name', '');
        $browser->click('.btn-registration');
        $browser->pause($this->pause * 2);
        $browser->assertSee('荷主名を入力してください。');
    }

    private function testDeleteSuccessCustomer($browser)
    {
        $browser->visit('#/master-manager/customer-master');
        $browser->pause($this->pause * 3);
        $browser->click('.td-delete');
        $browser->pause($this->pause);
        $buttonConfirmDelete = $browser->driver->findElements(WebDriverBy::xpath(' //*[@id="modal-cf___BV_modal_footer_"]/button[2]'));
        $buttonConfirmDelete[0]->click();
        $browser->pause($this->pause * 2);
//        $browser->assertSee('荷主を削除しました');
        //*[@id="modal-cf___BV_modal_footer_"]/button[2]
    }

    // -------------------------- course

    private function testNewCourse($browser, $course_code)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $browser->pause($this->pause);
        $buttonRegister = $browser->driver->findElements(
            WebDriverBy::xpath(
                '//*[@id="page-content-wrapper"]/div[2]/div/div/div/div/div[3]/div/div[2]/div/button[1]'
            )
        );
        $buttonRegister[0]->click();
        $browser->pause($this->pause);
        $browser->select('#select-base', 1);
        $browser->pause($this->pause * 2);

        $browser->type('#input-course-id', $course_code);
        $browser->pause($this->pause);
        $browser->click('#select-date-shipping-start-date__value_');
        $browser->pause($this->pause);

        $browser->click('button[title="Previous month"]');
        $browser->pause($this->pause);
        $startMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $startMonth . '"]');

        $browser->pause($this->pause);
        $browser->click('#select-date-delivery-end-date__value_');
        $endMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $browser->click('div[data-date="' . $endMonth . '"]');

        $browser->select('#select-course-type', 1);
        $browser->select('#select-flight-type', 1);
        $browser->select('#select-delivery-type', 1);
        $browser->type('#input-quanity', 10);
        $browser->select('#select-irregular-course', 0);


        $browser->select('#select-route-start-time-hour', 1);
        $browser->pause($this->pause);
        $browser->select('#select-route-start-time-min', 10);
        $browser->type('#input-course-allowance', "1000");
        $browser->select('#select-gate', 0);
        $browser->pause($this->pause);
        $browser->select('#select-wing', 0);
        $browser->pause($this->pause);
        $browser->select('#select-tonnage', 10);
        $browser->pause($this->pause * 2);
        $browser->select('#select-base', 1);
        $browser->pause($this->pause * 4);
        $browser->select('.delivery-course-creation select[class^="custom-select"]', 1);
        $browser->pause($this->pause * 2);
        $browser->click('.delivery-course-creation .input-group-append button.btn');
        $browser->pause($this->pause);
        $browser->click('.btn-sign-up');
        $browser->pause($this->pause * 4);
//        $browser->assertSee($course_code);
//        $browser->assertSee("新規登録に成功しました");
        $browser->pause($this->pause * 2);
    }

    private function testEditCourse($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->elements('tbody tr:nth-child(1) .fa-eye');
        $btn[0]->click();
        $browser->pause($this->pause * 2);

        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="page-content-wrapper"]/div[2]/div/div/div/div/div[3]/div/div[2]/button'));
        $btn[0]->click();

        $browser->pause($this->pause * 2);
        $browser->select('#select-course-type', 1);
        $browser->pause($this->pause * 2);

        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="page-content-wrapper"]/div[2]/div/div/div/div/div[3]/div/div[2]/button'));
        $btn[0]->click();
        $browser->pause($this->pause * 2);
        $browser->assertSee("編集が完了しました");
    }

    private function testDeleteCourse($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->elements('tbody tr:nth-child(1) .fa-trash');
        $browser->pause(1000);
        $btn[0]->click();

        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="modal-cf___BV_modal_footer_"]/button[2]'));
        $btn[0]->click();
        $browser->pause($this->pause * 2);
        $browser->assertSee("コースが削除しました");
    }

    private function testFilterByDepartment($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="collapsed-show-hide-filter"]/span'));
        $btn[0]->click();
        $browser->pause($this->pause);
        $browser->check('.status-filter-department');
        $browser->pause($this->pause);
        $browser->select('#filter-department', 1);
        $browser->pause($this->pause * 2);
        $browser->click('.btn-summit-filter');
        $browser->pause($this->pause * 2);
        $browser->assertSee("本社");
        $browser->pause($this->pause);
    }

    private function testFilterByID($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="collapsed-show-hide-filter"]/span'));
        $btn[0]->click();
        $browser->pause($this->pause);
        $browser->check('.status-filter-course-id');
        $browser->pause($this->pause);
        $browser->select('#filter-course-id', 'Course 0');
//        $browser->assertSee('Course 0');
        $browser->pause($this->pause);
    }

    private function testSortByDepartment($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="table-course"]/thead/tr[1]/th[1]/div/div[2]/i'));
        $btn[0]->click();
        $browser->pause($this->pause * 5);
        $btn[0]->click();
        $browser->pause($this->pause * 5);
    }

    private function testSortByCourse($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="table-course"]/thead/tr[1]/th[2]/div/div[2]/i'));
        $btn[0]->click();
        $browser->pause($this->pause * 5);
        $btn[0]->click();
        $browser->pause($this->pause * 5);
    }

    private function testSortByTotalFare($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="table-course"]/thead/tr[1]/th[3]/div/div[2]/i'));
        $btn[0]->click();
        $browser->pause($this->pause * 5);
        $btn[0]->click();
        $browser->pause($this->pause * 5);
    }

    private function testSortByTotalHighway($browser)
    {
        $browser->visit('#/master-manager/course-master');
        $browser->pause($this->pause * 3);
        $btn = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="table-course"]/thead/tr[1]/th[4]/div/div[2]/i'));
        $btn[0]->click();
        $browser->pause($this->pause * 5);
        $btn[0]->click();
        $browser->pause($this->pause * 5);
    }

    private function createRouteFake()
    {
        for ($i = 1; $i < 10; $i++) {
            $this->route = Route::create([
                'name' => "Route Fake " . $i,
                'department_id' => 1,
                'customer_id' => 1,
                'route_fare_type' => 1,
                'fare' => rand(100, 999999),
                'highway_fee' => rand(100, 9999),
                'highway_fee_holiday' => rand(100, 9999),
                'is_government_holiday' => 1
            ]);

            $this->route->route_non_delivery()->updateOrCreate([
                'number_at' => 1,
                'is_week' => 1
            ]);
        }
    }
}
