<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

trait DuskCreatesApplication
{
    protected static $setUpHasRunOnce = false;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh --seed');
            static::$setUpHasRunOnce = true;
        }

        return $app;
    }
}
