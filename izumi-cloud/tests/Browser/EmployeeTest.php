<?php

namespace Tests\Browser;

use App\Models\Course;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DuskTestCase;

class EmployeeTest extends DuskTestCase
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
            $this->filterEmployee($browser);
            $this->employeeDetail($browser);
            $this->employeeEdit($browser);
//            $this->paginationEmployee($browser);
//            $this->sortEmployee($browser);
        });
    }

    public function initData()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('employee_department')->truncate();
        DB::table('employee_working_department')->truncate();
        DB::table('employee_course')->truncate();
        Employee::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }

    public function initDataCourse()
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


    public function filterEmployee($browser)
    {
        $this->initData();
        $employee = Employee::factory()->count(1)
            ->state(function (array $attributes) {
                return [
                    Employee::EMPLOYEE_CODE => 5,
                    Employee::NAME => '佐々木//一三',
                ];
            })
            ->create()->each(function ($user) {
                $user->departments()->attach(7, ['employee_data' => json_encode([]), 'start_date' => '2022-03-15']);
                $user->departmentWorkings()->attach(2, [
                    'start_date' => '2022-03-10',
                    'end_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                ]);
                $user->departmentWorkings()->attach(7, [
                    'start_date' => '2022-03-10',
                    'end_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                    'is_support' => 0,
                ]);
            });
        Employee::factory()->count(1)
            ->state(function (array $attributes) {
                return [
                    Employee::EMPLOYEE_CODE => 10,
                    Employee::NAME => 'Phuong//LV',
                ];
            })
            ->create()->each(function ($user) {
                $user->departments()->attach(1, ['employee_data' => json_encode([]), 'start_date' => '2022-03-15']);
                $user->departmentWorkings()->attach(2, [
                    'start_date' => '2022-03-10',
                    'end_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                ]);
                $user->departmentWorkings()->attach(1, [
                    'start_date' => '2022-03-10',
                    'end_date' => '2022-03-15',
                    'grade' => 1,
                    'is_support' => 0,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                ]);
            });

        $browser->visit("#/master-manager/employee-master")->pause(2000)
            //->waitUntilMissing('.loading')
            ->assertSee('従業員マスタ')->pause(300)
            ->assertSee('フィルタ')->pause(300)
            ->click("#collapsed-show-hide-filter")->pause(500)
            ->assertSee('所属拠点')
            ->assertSee('勤務拠点')
            ->assertSee('従業員番号')
            ->assertSee('従業員名');
        //filter by department
        $browser->with('#zone-filter', function ($element) {
            $element->click("#filter-affiliation-base")->pause(300)
                ->assertChecked("#filter-affiliation-base")
                ->select("#filter-affiliation-base-value", 7)
                ->click(".btn-summit-filter")->pause(3000);
        });
        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('所属拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '東京');
        });

        //filter by department support
        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-support-base")->pause(300)
                ->assertChecked("#filter-support-base")
                ->select("#filter-support-base-value", 7)
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('所属拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '東京');
        });

        //filter by employee code
        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-employee-id")->pause(300)
                ->assertChecked("#filter-employee-id")
                ->type("#filter-employee-id-value", 5)
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('所属拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '東京');
        });

        //filter by employee name
        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-employee-name")->pause(300)
                ->assertChecked("#filter-employee-name")
                ->type("#filter-employee-name-value", '佐々木 一三')
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('所属拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '東京');
        });

        //filter all
        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->click("#filter-affiliation-base")->pause(300)
                ->assertChecked("#filter-affiliation-base")
                ->select("#filter-affiliation-base-value", 7);
            $element->check("#filter-support-base")->pause(300)
                ->assertChecked("#filter-support-base")
                ->select("#filter-support-base-value", 7);
            $element->check("#filter-employee-id")->pause(300)
                ->assertChecked("#filter-employee-id")
                ->type("#filter-employee-id-value", 5);
            $element->check("#filter-employee-name")->pause(300)
                ->assertChecked("#filter-employee-name")
                ->type("#filter-employee-name-value", '佐々木 一三')
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('所属拠点');
            $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(1)', '東京');
        });

        //filter by employee name not exist
        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-employee-name")->pause(300)
                ->assertChecked("#filter-employee-name")
                ->type("#filter-employee-name-value", 'Test')
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('データなし');
        });

        //filter by employee id not exist
        $browser->with('#zone-filter', function ($element) {
            $element->click('.text-clear-all')->pause(500);
            $element->check("#filter-employee-id")->pause(300)
                ->assertChecked("#filter-employee-id")
                ->type("#filter-employee-id-value", 999999)
                ->click(".btn-summit-filter")->pause(3000);
        });

        $browser->with('#table-employee-master-list', function ($table) {
            $table->assertSee('データなし');
        });
    }

    public function employeeDetail($browser)
    {
        $this->initData();
        $employee = Employee::factory()->count(1)
            ->state(function (array $attributes) {
                return [
                    Employee::EMPLOYEE_CODE => 5,
                    Employee::NAME => '佐々木//一三',
                ];
            })
            ->create()->each(function ($user) {
                $user->departments()->attach(7, [
                    'employee_data' => json_encode([]),
                    'start_date' => '2022-03-15'
                ]);
                $user->departmentWorkings()->attach(7, [
                    'start_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                    'is_support' => 0
                ]);
                $user->departmentWorkings()->attach(1, [
                    'start_date' => '2022-03-10',
                    'end_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                    'is_support' => 1
                ]);
            });

        $browser->visit("#/master-manager/employee-master")->pause(2000)
            //->waitUntilMissing('.loading')
            ->assertSee('従業員マスタ')->pause(300)
            ->assertSee('フィルタ')->pause(300);

        $browser->with('#table-employee-master-list', function ($table) {
            $table->click('tbody tr:nth-child(1) td:nth-child(6) i.icon-detail')->pause(5000);
        });

        $browser->assertPathBeginsWith('/')
            ->assertSee('従業員マスタ')
            ->assertSee("基本情報")
            ->assertSee('従業員番号')
            ->assertSee('職種')
            ->assertSee('雇用区分')
            ->assertSee('免許種別')
            ->assertSee('退職日')
            ->assertSee('所属拠点・勤務拠点')
            ->assertSee('拠点異動履歴')
            ->assertSee('東京')
            ->assertAttributeContains('#employee-id', 'value', 5)
            ->assertAttributeContains('#employee-name', 'value', '佐々木一三')
            ->assertSee('生年月日');
//        $item = $browser->element('#employee-id');
//        (int)$item->getAttribute('value');

        $browser->click('.text-link')->pause(5000);
        $browser->with('#modal-change-history', function ($modal) {
            $modal->assertSee('拠点異動履歴');
            $modal->assertSee('佐々木一三');
            $modal->assertSee('拠点異動日');
            $modal->assertSee('異動先拠点');
            $modal->pause(300)->click('button.close')->pause(1000);
        });

        //Affiliation base_detail
        $browser->click('.show-working-data > div > div:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->assertSee('勤務情報');
            $modal->assertSee('佐々木一三');
            $modal->assertSee('東京');
            $modal->assertSee('助勤開始日');
            $modal->assertSee('助勤終了日');
            $modal->assertSee('等級');
            $modal->assertSee('号棒');
            $modal->assertSee('同乗等級');
            $modal->assertSee('同乗号棒');
            $modal->assertSee('通勤手当');
            $modal->assertSee('深夜労働時間');
            $modal->assertSee('乗務可能コース');
            $modal->pause(300)->click('button.close')->pause(1000);
        });

        //Support base_detail
        $browser->click('.show-working-data div.support-base:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->assertSee('勤務情報');
            $modal->assertSee('佐々木一三');
            $modal->assertSee('本社');
            $modal->assertSee('助勤開始日');
            $modal->assertSee('助勤終了日');
            $modal->assertSee('等級');
            $modal->assertSee('号棒');
            $modal->assertSee('同乗等級');
            $modal->assertSee('同乗号棒');
            $modal->assertSee('通勤手当');
            $modal->assertSee('深夜労働時間');
            $modal->assertSee('乗務可能コース');
            $modal->pause(300)->click('button.close')->pause(1000);
        });

        //Support Other
        $browser->click('.show-working-data > div > div:nth-child(3)')->pause(2000);
        $browser->with('#modal-other-base', function ($modal) {
            $modal->assertSee('勤務情報');
            $modal->assertSee('佐々木一三');
            $modal->assertSee('横浜第一');
            $modal->assertSee('この拠点には勤務情報が未登録です。');
            $modal->assertSee('「次へ」を押して登録をお願いします。');
            $modal->pause(300)->click('button.close')->pause(1000);
        });
    }


    public function employeeEdit($browser)
    {
        $this->initData();
        $this->initDataCourse();
        $employee = Employee::factory()->count(1)
            ->state(function (array $attributes) {
                return [
                    Employee::EMPLOYEE_CODE => 5,
                    Employee::NAME => '佐々木//一三',
                ];
            })
            ->create()->each(function ($user) {
                $user->departments()->attach(7, [
                    'employee_data' => json_encode([]),
                    'start_date' => '2022-03-15'
                ]);
                $user->departmentWorkings()->attach(7, [
                    'start_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                    'is_support' => 0
                ]);
                $user->departmentWorkings()->attach(1, [
                    'start_date' => '2022-03-10',
                    'end_date' => '2022-03-15',
                    'grade' => 1,
                    'employee_grade_2' => 1,
                    'boarding_employee_grade' => 1,
                    'boarding_employee_grade_2' => 1,
                    'is_support' => 1
                ]);
            });

        $course = Course::factory()->count(1)
            ->state(function (array $attributes) {
                return ['department_id' => 7];
            })->create();
        $course = Course::factory()->count(1)
            ->state(function (array $attributes) {
                return ['department_id' => 1];
            })->create();
        $course = Course::factory()->count(1)
            ->state(function (array $attributes) {
                return ['department_id' => 2];
            })->create();

        $browser->visit("#/master-manager/employee-master")->pause(2000)
            //->waitUntilMissing('.loading')
            ->assertSee('従業員マスタ')->pause(300)
            ->assertSee('フィルタ')->pause(300);

        $browser->with('#table-employee-master-list', function ($table) {
            $table->click('tbody tr:nth-child(1) td:nth-child(6) i.icon-detail')->pause(5000);
        });

        $browser->assertPathBeginsWith('/')
            ->assertSee('従業員マスタ')
            ->assertSee("基本情報")
            ->assertSee('従業員番号')
            ->assertSee('職種')
            ->assertSee('雇用区分')
            ->assertSee('免許種別')
            ->assertSee('退職日')
            ->assertSee('所属拠点・勤務拠点')
            ->assertSee('拠点異動履歴')
            ->assertSee('東京')
            ->assertAttributeContains('#employee-id', 'value', 5)
            ->assertAttributeContains('#employee-name', 'value', '佐々木一三')
            ->assertSee('生年月日');
//        $item = $browser->element('#employee-id');
//        (int)$item->getAttribute('value');

        //Affiliation base_detail edit
        $browser->click('.show-working-data > div > div:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->assertSee('勤務情報');
            $modal->assertSee('佐々木一三');
            $modal->assertSee('東京');
            $modal->assertSee('助勤開始日');
            $modal->assertSee('助勤終了日');
            $modal->assertSee('等級');
            $modal->assertSee('号棒');
            $modal->assertSee('同乗等級');
            $modal->assertSee('同乗号棒');
            $modal->assertSee('通勤手当');
            $modal->assertSee('深夜労働時間');
            $modal->assertSee('乗務可能コース');
            $modal->pause(300)->click('button.button-to-edit-screen')->pause(2000);
        });

        $browser->with('#modal-affiliation-support-base-edit', function ($modal) use ($browser) {
            $modal->assertSee('勤務情報');
            $startDate = Carbon::now()->addMonth()->firstOfMonth()->format('Y-m-d');
            $this->mapDate($modal, '#support-start-date-0', $startDate);

            $this->modalChangeInput($browser, true);
            $modal->pause(2000);
            $browser->assertSee('編集が完了しました');
        });

        //Affiliation base_detail edit (register new date support)
        $browser->click('.show-working-data > div > div:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->pause(300)->click('button.button-to-edit-screen')->pause(2000);
        });

        $browser->with('#modal-affiliation-support-base-edit', function ($modal) use ($browser) {
            $modal->assertSee('勤務情報');
            $endDate = Carbon::now()->addMonth()->addDays(2)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-end-date', $endDate);
            $startDate = Carbon::now()->addMonth()->addDays(2)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-start-date', $startDate);

            $this->modalChangeInput($browser);
            $modal->pause(2000);
            $browser->assertSee('編集が完了しました');
        });


        //Affiliation base_detail edit (register new date support)
        $browser->click('.show-working-data > div > div:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->pause(300)->click('button.button-to-edit-screen')->pause(2000);
        });

        $browser->with('#modal-affiliation-support-base-edit', function ($modal) use ($browser) {
            $modal->assertSee('勤務情報');
            $endDate = Carbon::now()->addMonth()->addDays(2)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-end-date', $endDate);
            $startDate = Carbon::now()->addMonth()->addDays(2)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-start-date', $startDate);
            $modal->click('.button-support-addition')->pause(500);

            $this->modalChangeInput($browser);
            $modal->pause(2000);
            $browser->assertSee('編集が完了しました');
        });


        //Affiliation base_detail edit (register new date support with validate duplicated date)
        $browser->click('.show-working-data > div > div:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->pause(300)->click('button.button-to-edit-screen')->pause(2000);
        });

        $browser->with('#modal-affiliation-support-base-edit', function ($modal) use ($browser) {
            $modal->assertSee('勤務情報');
            $endDate = Carbon::now()->addMonth()->addDays(2)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-end-date', $endDate);
            $startDate = Carbon::now()->addMonth()->addDays(2)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-start-date', $startDate);
            $modal->click('.button-support-addition')->pause(500);

            $this->modalChangeInput($browser);
            $modal->pause(2000);
//            $browser->assertSee('勤務期間は既に利用されているため登録できません');
            $modal->pause(2000)->click('button.close');
        });


        //Support base_edit (register new date support)
        $browser->click('.show-working-data div.support-base:nth-child(1)')->pause(2000);
        $browser->with('#modal-affiliation-support-base-detail', function ($modal) {
            $modal->assertSee('勤務情報');
            $modal->assertSee('佐々木一三');
            $modal->pause(300)->click('button.button-to-edit-screen')->pause(2000);
        });

        $browser->with('#modal-affiliation-support-base-edit', function ($modal) use ($browser) {
            $modal->assertSee('勤務情報');
            $endDate = Carbon::now()->addMonth()->addDays(3)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-end-date', $endDate);
            $startDate = Carbon::now()->addMonth()->addDays(3)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-start-date', $startDate);
            $modal->click('.button-support-addition')->pause(500);

            $this->modalChangeInput($browser, true);
            $modal->pause(2000);
            $browser->assertSee('編集が完了しました');
        });

        //Support base_edit gary (register new date support)
        $browser->click('.show-working-data > div > div:nth-child(3)')->pause(2000);
        $browser->with('#modal-other-base', function ($modal) {
            $modal->assertSee('勤務情報');
            $modal->assertSee('佐々木一三');
            $modal->pause(300)->click('button.button-to-edit-screen')->pause(2000);
        });

        $browser->with('#modal-affiliation-support-base-edit', function ($modal) use ($browser) {
            $modal->assertSee('勤務情報');

            $endDate = Carbon::now()->addMonth()->addDays(4)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-end-date', $endDate);
            $startDate = Carbon::now()->addMonth()->addDays(4)->format('Y-m-d');
            $this->mapDate($browser, '#modal-affiliation-support-base-edit #support-start-date', $startDate);
            $modal->click('.button-support-addition')->pause(500);

            $this->modalChangeInput($browser, true);
            $modal->pause(2000);
            $browser->assertSee('編集が完了しました');
            $browser->assertAttributeContains('.show-working-data > div > div:nth-child(3) > div', 'class', 'support-base');
        });

    }

    private function mapDate($modal, $selector, $date)
    {
        $modal->pause(500);
        $modal->click($selector);
        $modal->pause(500);
        $modal->click('button[title="Current month"]');
        $modal->click('button[title="Next month"]');
        $modal->pause(500);
        $modal->click('div[data-date="' . $date . '"]');
        $modal->pause(500);
    }

    private function modalChangeInput($browser, $isSelectCourse = false)
    {
        $browser->type('#modal-affiliation-support-base-edit #employee-grade', 5);
        $browser->type('#modal-affiliation-support-base-edit #employee-grade-2', 10);
        $browser->type('#modal-affiliation-support-base-edit #boarding-employee-grade', 10);
        $browser->type('#modal-affiliation-support-base-edit #boarding-employee-grade-2', 5);
        $browser->type('#modal-affiliation-support-base-edit #transportation-compensation', 1000);
        $browser->type('#modal-affiliation-support-base-edit #daily-transportation-compensation', 1000);
        $browser->select('#modal-affiliation-support-base-edit #select-route-start-time-hour', 1);
        $browser->select('#modal-affiliation-support-base-edit #select-midnight-working-time-minute', 0);
        $browser->select('#modal-affiliation-support-base-edit #select-scheduled-labor-table-hour', 1);
        $browser->select('#modal-affiliation-support-base-edit #select-scheduled-labor-table-minute', 0);
        if ($isSelectCourse) {
            $browser->select('#modal-affiliation-support-base-edit .delivery-course-creation .custom-select');
            $browser->click('#modal-affiliation-support-base-edit .delivery-course-creation .btn-success');
        }
        $browser->click('#modal-affiliation-support-base-edit button.button-save');
    }

//    public function paginationEmployee($browser)
//    {
//        $this->initData();
//        Customer::factory()->count(3)->create();
//        $route = Employee::factory()->count(200)
//            ->hasAttached(Store::factory()->count(1))->create();
//
//        $browser->visit("/master-manager/employee-master")
//            ->pause(5000)
//            ->scrollIntoView('.pagination')
//            // Go to next page
//            ->click('.next')->waitUntilMissing('.loading')
//            ->pause(2000)
//            ->scrollIntoView('.pagination')
//            ->assertSeeIn('.pagination .active', '2')
//            // Go to previous page
//            ->pause(2000)
//            ->click('.prev')->waitUntilMissing('.loading')
//            ->assertSeeIn('.pagination .active', '1');
//    }
//
//    public function sortEmployee($browser)
//    {
//        $this->initData();
//        Customer::factory()
//            ->state(function (array $attributes) {
//                return ['customer_name' => 'Test customer 1'];
//            })->count(1)->create();
//        Customer::factory()
//            ->state(function (array $attributes) {
//                return ['customer_name' => 'Test customer 2'];
//            })->count(1)->create();
//        $route = Employee::factory()
//            ->state(function (array $attributes) {
//                return [
//                    'name' => 'Test route 1',
//                    'department_id' => 1,
//                    'customer_id' => 1,
//                    'route_fare_type' => 1,
//                    'fare' => 1000,
//                    'highway_fee' => 1000,
//                    'highway_fee_holiday' => 1000,
//                ];
//            })
//            ->count(1)
//            ->hasAttached(Store::factory()->count(1))->create();
//
//        $route = Employee::factory()
//            ->state(function (array $attributes) {
//                return [
//                    'name' => 'Test route 2',
//                    'department_id' => 2,
//                    'customer_id' => 2,
//                    'route_fare_type' => 2,
//                    'fare' => 2000,
//                    'highway_fee' => 2000,
//                    'highway_fee_holiday' => 2000,
//                ];
//            })
//            ->count(1)
//            ->hasAttached(Store::factory()->count(2))->create();
//
//        $classOrder = [
//            ['class' => '.department-th', 'sort_by' => 'department_name', 'key_map' => 'department_name'],
//            ['class' => '.route-id-th', 'sort_by' => 'route_id', 'key_map' => 'id'],
//            ['class' => '.route-name-th', 'sort_by' => 'name', 'key_map' => 'name'],
//            ['class' => '.customer-th', 'sort_by' => 'customer_name', 'key_map' => 'customer_name'],
//            ['class' => '.fare-type-th', 'sort_by' => 'route_fare_type', 'key_map' => 'route_fare_type'],
//            ['class' => '.fare-th', 'sort_by' => 'fare', 'key_map' => 'fare'],
//            ['class' => '.highway-fee-th', 'sort_by' => 'highway_fee', 'key_map' => 'highway_fee'],
//            ['class' => '.highway-fee-holiday-th', 'sort_by' => 'highway_fee_holiday', 'key_map' => 'highway_fee_holiday'],
//            ['class' => '.the-number-of-store-th', 'sort_by' => 'store', 'key_map' => 'store_count'],
//        ];
//
//        $browser->visit("/master-manager/employee-master")->pause(2000)
//            //->waitUntilMissing('.loading')
//            ->assertSee('ルートマスタ')->pause(300)
//            ->assertSee('フィルタ')->pause(300)
//            ->pause(300);
//        $browser->with('.b-table', function ($table) use ($classOrder) {
//            foreach ($classOrder as $key => $lstOrder) {
//                $index = $key + 1;
//                $clOrder = (object)$lstOrder;
//                $table->scrollIntoView($clOrder->class)->pause(1000)->click($clOrder->class)->pause(2000);
//                if (!in_array($index, [5, 9])) {
//                    $responseData = $this->callApiSortByCheck($clOrder->sort_by, true);
//                    $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(' . $index . ')', $responseData['data'][0][$clOrder->key_map]);
//                    $table->assertSeeIn('tbody tr:nth-child(2) td:nth-child(' . $index . ')', $responseData['data'][1][$clOrder->key_map]);
//                }
//                $table->scrollIntoView($clOrder->class)->pause(1000)->click($clOrder->class)->pause(2000);
//                if (!in_array($index, [5, 9])) {
//                    $responseData = $this->callApiSortByCheck($clOrder->sort_by);
//                    $table->assertSeeIn('tbody tr:nth-child(1) td:nth-child(' . $index . ')', $responseData['data'][0][$clOrder->key_map]);
//                    $table->assertSeeIn('tbody tr:nth-child(2) td:nth-child(' . $index . ')', $responseData['data'][1][$clOrder->key_map]);
//                }
//            }
//        });
//    }
//
//    private function callApiSortByCheck($sort_by, $sort_type = false)
//    {
//        $rpLogin = $this->post('/api/auth/login', ['id' => "111111", 'password' => '123456789']);
//        $data = json_decode($rpLogin->getContent());
//        $token = $data->data->access_token;
//        $response = $this->withHeaders(['Authorization' => $token])->get('api/route?sort_by=' . $sort_by . '&sort_type=' . $sort_type);
//        return $response->json();
//    }

}
