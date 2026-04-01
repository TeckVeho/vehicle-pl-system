<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class LanguageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/language.php', 'language'
        );
    }

    public function boot(): void
    {
        if (config('language.cache.enabled')) {
            $this->setupTranslationCaching();
        }
    }
    
    protected function setupTranslationCaching(): void
    {
    }
}
