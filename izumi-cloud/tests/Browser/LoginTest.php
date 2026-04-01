<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testGeneral()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')->waitFor('.login-btn');
            $this->validate($browser);
            $this->loginSuccess($browser);
        });
    }

    public function validate($browser)
    {
        //wrong employee number or password
        $browser->type('#user_id', '12345')->type('#password', '123456789')
            ->press('.login-btn')->waitFor('.toast-header')->assertSee('危険')
            // password <8 charactor
            ->press('.close')->type('#user_id', '123456')->type('#password', '1234567')
            ->press('.login-btn')->waitFor('.toast-header')->assertSee('危険')
            // password is null
            ->press('.close')->type('#user_id', '123456')->type('#password', '')
            ->press('.login-btn')->waitFor('.toast-header')->assertSee('警告')
            //text is null
            ->press('.close')->type('#user_id', '')->type('#password', '12345678')
            ->press('.login-btn')->waitFor('.toast-header')->assertSee('警告');
    }

    public function loginSuccess($browser)
    {
        $browser->visit('/')->waitFor('.login-btn')
            ->type('#user_id', '111111')
            ->type('#password', '123456789')->press('.login-btn')
            ->waitFor('.toast-header')->assertSee('成功')->assertPathIs('/')
            ->assertSeeIn('.toast-header', '成功')->assertPathIs('/');
    }

}


