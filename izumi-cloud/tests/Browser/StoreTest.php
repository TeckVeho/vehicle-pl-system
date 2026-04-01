<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Facebook\WebDriver\WebDriverBy;

class StoreTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    const PAUSE = 1000;

    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $this->login($browser);
            $this->visitStoreManagement($browser);
            $this->visitRegisterScreen($browser);
            $this->checkValidateStoreName($browser);
            $this->submit($browser);
            $browser->assertSee('店舗名は20文字以内で入力ください。');
            $this->checkValidateDestinationNameKana($browser);
            $this->checkValidateDestinationName($browser);
            // $this->checkValidatePostCode($browser); comment change code not check
            // $this->checkValidateTel($browser); comment change code not check
            $this->checkValidateAddress1($browser);
            $this->checkValidateAddress2($browser);
            $this->checkValidPassCode($browser);
            $this->checkStoreCreate($browser);
            $browser->pause(self::PAUSE * 1);
            $this->checkStoreEdit($browser);
            $browser->pause(self::PAUSE * 1);

        });
    }

    public function visitStoreManagement($browser)
    {
        $browser->visit("/#/master-manager/store-master")->pause(self::PAUSE);
    }

    public function visitRegisterScreen($browser)
    {
        $browser->click('.btn-registration');
        $browser->pause(self::PAUSE);
    }

    public function checkValidateStoreName($browser)
    {
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name invalid, it over than 20 characters');
        $this->input($browser, '#input-delivery-destination-code', '10000');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        //納品先名(カナ)は、100文字以下にしてください。
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');
        // 納品先名は、50文字以下にしてください。

        //郵便番号は、8桁にしてください。 post code

        //TELは、13桁にしてください。 TEL
    }

    public function checkValidateDestinationNameKana($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('納品先名(カナ)は、100文字以下にしてください。');
        //郵便番号は、8桁にしてください。 post code
        //TELは、13桁にしてください。 TEL
    }

    public function checkValidateDestinationName($browser)
    {
        // 納品先名は、50文字以下にしてください。
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');
        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('納品先名は、50文字以下にしてください。');
    }

    public function checkValidatePostCode($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '1234567891011');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('郵便番号は、8桁にしてください。');
    }

    public function checkValidateTel($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '123456789111112314');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('TELは、13桁にしてください。');
    }

    public function checkValidateAddress1($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'my first address is invalid requirement of client because it is over than 20 characters');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('大住所は、20文字以下にしてください。');
    }

    public function checkValidateAddress2($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'my first address is invalid requirement of client because it is over than 50 characters, in valid characters 大住所は、20文字以下にしてください。');
        $this->input($browser, '#input-display-pass-code', '1000');

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('小住所は、50文字以下にしてください。');
    }

    public function checkValidPassCode($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', 100000);

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('表示パスコードは、4桁にしてください。');
    }

    public function checkStoreCreate($browser)
    {
        $browser->pause(self::PAUSE);
        $browser->visit('/#/master-manager/store-master-create');
        // destination name kana will be overthan 100 characters because the requirement of the client that required the destination name kana will not over than 100 characters
        $this->input($browser, '#input-store-name', 'store name');
        $this->input($browser, '#input-delivery-destination-code', '12345678');
        $this->input($browser, '#input-delivery-destination-name-kana', 'valid');
        $this->input($browser, '#input-delivery-destination-name', 'valid');
        $this->input($browser, '#input-post-code', '12345678');
        $this->input($browser, '#input-tel', '1234567891111');
        $this->input($browser, '#input-large-address', 'valid');
        $this->input($browser, '#input-small-address', 'valid');
        $this->input($browser, '#input-display-pass-code', '1000');

        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('新規登録に成功しました');
    }

    public function checkStoreEdit($browser)
    {
        // //*[@id="table-store-master"]/tbody/tr[1]/td[2]/i
        $btns = $browser->driver->findElements(WebDriverBy::xpath('//*[@id="table-store-master"]/tbody/tr[1]/td[2]/i'));
        $btns[0]->click();
        $browser->pause(self::PAUSE / 2);
        $browser->click('.btn-to-edit');
        $browser->pause(self::PAUSE * 2);
        $this->input($browser, '#input-delivery-destination-name-kana', 'Edit this one');
        $browser->pause(self::PAUSE);
        $this->submit($browser);
        $browser->pause(self::PAUSE);
        $browser->assertSee('編集が完了しました');
    }

    //my first address is invalid requirement of client because it is over than 50 characters
//表示パスコードは、4桁にしてください。
    private function input($browser, $object, $value)
    {
        $browser->type($object, $value)->pause(self::PAUSE);
    }

    private function submit($browser)
    {
        $browser->click('.btn-registration');
        $browser->pause(self::PAUSE / 2);
    }
}
