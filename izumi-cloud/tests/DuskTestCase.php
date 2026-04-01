<?php

namespace Tests;

use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use DuskCreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (!static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments(collect([
            '--window-size=1920,1080',
            '--start-maximized',
        ])->all());

//        $options = (new ChromeOptions)->addArguments(collect([
//            '--window-size=1920,1080',
//        ])->unless($this->hasHeadlessDisabled(), function ($items) {
//            return $items->merge([
//                '--disable-gpu',
//                '--headless',
//            ]);
//        })->all());

        $options->setExperimentalOption('prefs', [
            'download.default_directory' => Storage::disk('local')->path('test_download')
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled()
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
            isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    public function login($browser, $role = 'MANAGER', $text = '')
    {
        $u_id = $role == 'MANAGER' ? '111111' : '666666';
        $url = '#/login';
        $browser->visit($url)->maximize()
            ->type('#user_id', $u_id)
            ->type('#password', '123456789')->releaseMouse()->press('.login-btn')
            ->waitFor('.toast-body');
    }
}
