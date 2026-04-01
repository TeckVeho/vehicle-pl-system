<?php

return [
    'default' => 'ja',
    'fallback' => 'ja',
    
    'available' => [
        'ja' => [
            'code' => 'ja',
            'name' => '日本語',
            'native_name' => '日本語',
            'flag' => '🇯🇵',
            'enabled' => true,
        ],
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇬🇧',
            'enabled' => true,
        ],
        'zh' => [
            'code' => 'zh',
            'name' => 'Chinese',
            'native_name' => '中文',
            'flag' => '🇨🇳',
            'enabled' => true,
        ],
    ],
    
    'modules' => [
        'notification' => true,
        'izumi_notebook' => false,
    ],
    
    'cache' => [
        'enabled' => env('LANGUAGE_CACHE_ENABLED', true),
        'ttl' => env('LANGUAGE_CACHE_TTL', 3600),
        'prefix' => 'lang_',
    ],
];

