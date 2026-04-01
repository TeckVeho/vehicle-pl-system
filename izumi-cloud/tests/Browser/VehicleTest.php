<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Facebook\WebDriver\WebDriverBy;
class VehicleTest extends DuskTestCase
{
    const PAUSE = 500;

    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $this->login($browser);
            $browser->pause(self::PAUSE * 3);
            $this->visitVehicleManagement($browser);
            $this->testCreate($browser, "PLATE 01", "101010");
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $this->testCreate($browser, "Car3", "190092");
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $this->testFilter($browser);
            $browser->pause(self::PAUSE * 3);
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $this->testEdit($browser);
            $browser->pause(self::PAUSE * 3);
            $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
            $browser->pause(self::PAUSE * 3);
            $this->testDelete($browser);
        });
    }

    public function visitVehicleManagement($browser)
    {
        $browser->visit("/#/master-manager/vehicle-master")->pause(self::PAUSE);
    }

    private function testFilter($b) {
        $b->click('.filter-title');
        $b->pause(self::PAUSE);
        $b->click('.status-filter-number-plate');
        $this->input($b, '#filter-number-plate', "PLATE 01");
        $b->pause(self::PAUSE *3);
        $b->click('.btn-summit-filter');
        $b->assertSee('PLATE 01');
        $b->assertSee('101010');

        $b->click('.text-clear-all');

        $b->click('.status-filter-vehicle-no');
        $this->input($b, '#filter-vehicle-no', "190092");
        $b->click('.btn-summit-filter');
        $b->assertSee('Car3');
        $b->assertSee('190092');
        $b->pause(self::PAUSE *3);
        $b->click('.text-clear-all');

        $b->click('.status-filter-department-id');
        $b->select('.custom-select', 2)->pause(self::PAUSE);
        $b->click('.btn-summit-filter');
        $b->assertSee('PLATE 01');
        $b->assertSee('Car3');
        $b->pause(self::PAUSE *3);
        $b->click('.text-clear-all');
    }

    private function testCreate($b, $plate, $number) {
        $b->pause(self::PAUSE * 2);
        $b->click('.btn-registration');
        $b->pause(self::PAUSE);
        $this->input($b, '.vehicle_identification_number', $number);
        $this->input($b, '.no_number_plate', $plate);
        $b->select('.department_id', 2)->pause(self::PAUSE);
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
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="2022-12-29"]'))[0]->click();

        $this->input($b, '.owner', "input-owner");
        $this->input($b, '.etc_certification_number', "IDT-etc_certification_number");
        $this->input($b, '.etc_number', "IDT-etc_number");
        $this->input($b, '.fuel_card_number_1', "IDT-fuel_card_number_1");
        $this->input($b, '.fuel_card_number_2', "IDT-fuel_card_number_2");
        $this->input($b, '.box_shape', "IDT-box_shape");
        $this->input($b, '.mount', "IDT-mount");
        $this->input($b, '.eva_type', "IDT-eva_type");
        $this->input($b, '.gate', "IDT-gate");
        $this->input($b, '.humidifier', "IDT-humidifier");
        $this->input($b, '.type', "IDT-type");
        $this->input($b, '.motor', "IDT-motor");
        $this->input($b, '.displacement', "IDT-displacement");
        $this->input($b, '._length', "IDT-length");
        $this->input($b, '.width', "IDT-width");
        $this->input($b, '.height', "IDT-height");
        $this->input($b, '.maximum_loading_capacity', "IDT-maximum_loading_capacity");
        $this->input($b, '.vehicle_total_weight', "IDT-vehicle_total_weight");
        $this->input($b, '.in_box_length', "IDT-in_box_length");
        $this->input($b, '.in_box_width', "IDT-in_box_width");
        $this->input($b, '.in_box_height', "IDT-in_box_height");
        $this->input($b, '.scrap_date', '2022-02-02');

        $this->input($b, '.optional_detail', "IDT-optional_detail");
        $this->input($b, '.liability_insurance_period', "IDT-liability_insurance_period");
        $this->input($b, '.insurance_company', "IDT-insurance_company");
        $this->input($b, '.agent', "IDT-agent");
        $this->input($b, '.mileage', "99999");
        $this->input($b, '.monthly_mileage', "1000000");
        $this->input($b, '.tire_size', "IDT-tire_size");
        $this->input($b, '.battery_size', "IDT-battery_size");

        $b->click('.start_of_leasing')->pause(self::PAUSE);
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="2022-12-29"]'))[0]->click();

        $b->click('.end_of_leasing')->pause(self::PAUSE);
        $b->driver->findElements(WebDriverBy::xpath('//*[@data-date="2022-12-29"]'))[0]->click();

        $this->input($b, '.old_car_1', "old_car_1");
        $this->input($b, '.old_car_2', "old_car_2");
        $this->input($b, '.old_car_3', "old_car_3");
        $this->input($b, '.old_car_4', "IDT-old_car_4");
        $b->click('.btn-save-main');
        $b->pause(self::PAUSE);
        $b->assertSee('新規登録に成功しました');
        $b->pause(self::PAUSE * 5);
    }

    private function testEdit($b) {
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
        $this->input($b, '.no_number_plate', "New plate");
        $b->pause(self::PAUSE * 5);
        $b->click('.btn-save-main');
        $b->pause(self::PAUSE);
        $b->assertSee('編集が完了しました');
    }

    private function testDelete($b) {
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
