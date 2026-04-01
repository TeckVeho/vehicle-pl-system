<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('language.default', 'ja');
        
        if ($request->user()) {
            $userLocale = $request->user()->language;
            $availableLocales = array_keys(config('language.available', []));
            
            if ($userLocale && in_array($userLocale, $availableLocales)) {
                $locale = $userLocale;
            }
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
}
