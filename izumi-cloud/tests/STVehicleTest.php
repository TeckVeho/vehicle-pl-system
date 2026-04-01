<?php

namespace Tests;

use App\Models\Course;
use App\Models\Customer;
use App\Models\DriverRecorder;
use App\Models\MaintenanceLease;
use App\Models\MileageHistory;
use App\Models\PlateHistory;
use App\Models\Route;
use App\Models\RouteNonDelivery;
use App\Models\Store;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDepartmentHistory;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use App\Models\Role;

class STVehicleTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    const PAUSE = 500;

    public function testGeneral()
    {
        $user = User::where('id', 111111)->first();
        $roleDxM = Role::findByName(ROLE_DX_MANAGER, 'api');
        $user->syncRoles([$roleDxM]);

        $this->browse(function ($browser) {
            $this->login($browser);
            $this->initData();
            $browser->pause(3000);
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $this->testCreate($browser);
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $this->testEdit($browser);
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $this->testFilter($browser);
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $browser->pause(self::PAUSE * 3);
            $this->testDelete($browser);
        });
    }

    public function initData()
    {
        Schema::disableForeignKeyConstraints();
        MaintenanceLease::query()->truncate();
        MileageHistory::query()->truncate();
        PlateHistory::query()->truncate();
        VehicleDepartmentHistory::query()->truncate();
        Vehicle::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function testCreate($b)
    {
        $b->pause(self::PAUSE * 2);
        $b->click('.btn-registration');
//        $b->visit("/#/master-manager/vehicle-master/create")->pause(self::PAUSE);
        $b->pause(self::PAUSE);
        $this->input($b, '.vehicle_identification_number', 'NPR85-7031178');
        $this->input($b, '.no_number_plate', '葛飾800あ86');
        $b->select('.department_id', 7)->pause(self::PAUSE);
        $b->select('.driving_classification', 'CVS')->pause(self::PAUSE);
        $b->select('.truck_classification', '1トン以下')->pause(self::PAUSE);
        $this->input($b, '.manufactor', 'manufactor');
        $dateTimePicker = $b->driver->findElements(WebDriverBy::xpath('//*[@id="dropdown-content"]/div/div[6]/div/div[1]/div[1]/div/span'));
        $dateTimePicker[0]->click();
        $b->pause(self::PAUSE);
        $jan = $b->driver->findElements(WebDriverBy::xpath('//*[@id="dropdown-content"]/div/div[6]/div/div[1]/div[2]/div/div[2]/div[1]'));//jan
        $jan[0]->click();
        $b->pause(self::PAUSE);

        //
        $b->click('.inspection_expiration_date')->pause(self::PAUSE);
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="' . $endMonth . '"]'))[0]->click();

        $this->input($b, '.owner', "イズミ物流㈱");
        $this->input($b, '.etc_certification_number', "10007-00270464-132295");
        $this->input($b, '.etc_number', "113696-0223-05691-6");
        $this->input($b, '.fuel_card_number_1', "3-01-3990-0428-1004");
        $this->input($b, '.fuel_card_number_2', "506955-56107-9966-1");
        $this->input($b, '.box_shape', "25");
        $this->input($b, '.mount', "フルハーフ");
        $this->input($b, '.eva_type', "2ｴﾊﾞ");
//        $this->input($b, '.gate', "IDT-gate");
//        $this->input($b, '.humidifier', "IDT-humidifier");
        $this->input($b, '.type', "SKG-NPR85AN");
        $this->input($b, '.motor', "4JJ1");
        $this->input($b, '.displacement', "2.99");
        $this->input($b, '._length', "766");
        $this->input($b, '.width', "223");
        $this->input($b, '.height', "328");
        $this->input($b, '.maximum_loading_capacity', "2000");
        $this->input($b, '.vehicle_total_weight', "5975");
        $this->input($b, '.in_box_length', "39");
        $this->input($b, '.in_box_width', "40");
        $this->input($b, '.in_box_height', "41");
        $b->click('[class-name="scrap_date"]')->pause(self::PAUSE);
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="' . $endMonth . '"]'))[0]->click();

        $this->input($b, '.optional_detail', "6630");
        $this->input($b, '.liability_insurance_period', "2022/06/15");
        $this->input($b, '.insurance_company', "損保ｼﾞｬﾊﾟﾝ");
        $this->input($b, '.agent', "ヤマザキ");
        $this->input($b, '.mileage', "362600");
        $this->input($b, '.monthly_mileage', "3400");
        $this->input($b, '.tire_size', "195/85R16");
        $this->input($b, '.battery_size', "120E41L");

        $b->click('.start_of_leasing')->pause(self::PAUSE);
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="' . $endMonth . '"]'))[0]->click();

        $b->click('.end_of_leasing')->pause(self::PAUSE);
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="' . $endMonth . '"]'))[0]->click();

//        $this->input($b, '.old_car_1', "old_car_1");
//        $this->input($b, '.old_car_2', "old_car_2");
//        $this->input($b, '.old_car_3', "old_car_3");
//        $this->input($b, '.old_car_4', "IDT-old_car_4");
        $b->click('.btn-save-main');
        $b->pause(self::PAUSE * 4);
        $b->assertSee('新規登録に成功しました');
        $b->pause(self::PAUSE * 5);
    }


    private function testEdit($b)
    {
        $edit = $b->driver->findElements(WebDriverBy::xpath('//*[@id="table-vehicle-master"]/tbody/tr[1]/td[5]/i'));
        $edit[0]->click();
        $b->pause(self::PAUSE * 3);

        $plateHistory = $b->driver->findElements(WebDriverBy::xpath('//*[@id="dropdown-content"]/div/div[2]/div/div/i/span'));
        $plateHistory[0]->click();
        $b->pause(self::PAUSE * 3);
        $x = $b->driver->findElements(WebDriverBy::xpath('//*[@id="modal-history-no-number-plate___BV_modal_header_"]/div/div/i'));
        $x[0]->click();

        $plateHistory = $b->driver->findElements(WebDriverBy::xpath('//*[@id="dropdown-content"]/div/div[3]/div/div[1]/div/div/i/span'));
        $plateHistory[0]->click();
        $b->pause(self::PAUSE * 3);
        $x = $b->driver->findElements(WebDriverBy::xpath('//*[@id="modal-history-department___BV_modal_header_"]/div/div/i'));
        $x[0]->click();

        $b->click('.btn-edit ');
        $b->pause(self::PAUSE * 5);
        $this->input($b, '.no_number_plate', "葛飾800あ88");
        $b->pause(self::PAUSE * 5);
        $b->click('.btn-save-main');
        $b->pause(self::PAUSE * 2);
        $b->assertSee('編集が完了しました');
    }

    private function testFilter($b)
    {
        $b->click('.filter-title');
        $b->pause(self::PAUSE);
        $b->click('.status-filter-department-id');
        $b->select('.custom-select', 7)->pause(self::PAUSE);
        $b->click('.btn-summit-filter');
        $b->pause(self::PAUSE * 3);
        $b->assertSee('東京');
        $b->assertSee('NPR85-7031178');
        $b->assertSee('葛飾800あ88');
        $endMonth = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $b->assertSee($endMonth);
        $b->pause(self::PAUSE * 3);
        $b->click('.text-clear-all');
    }

    private function testDelete($b)
    {
        $delete = $b->driver->findElements(WebDriverBy::xpath('//*[@id="table-vehicle-master"]/tbody/tr[1]/td[6]/i'));
        $delete[0]->click();
        $b->pause(self::PAUSE);
        $b->click('.btn-cancel');
        $b->pause(self::PAUSE);

        $delete = $b->driver->findElements(WebDriverBy::xpath('//*[@id="table-vehicle-master"]/tbody/tr[1]/td[6]/i'));
        $delete[0]->click();
        $b->click('.btn-apply');
        $b->pause(self::PAUSE);
    }

    private function input($browser, $object, $value)
    {
        $browser->type($object, $value)->pause(self::PAUSE);
    }
}

