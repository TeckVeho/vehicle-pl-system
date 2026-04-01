<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VPL (Vehicle P&L System) API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for syncing data from Izumi Cloud to VPL system.
    | See: docs/issues/Izumi_Issue-Requests-Repo/956/ic-sync-field-mapping.md
    |
    */

    'base_url' => env('VPL_BASE_URL', 'http://localhost:4000'),

    'auth' => [
        // Accept either the Izumi user ID or email (e.g. admin@example.com for initial seed)
        'identifier' => env('VPL_AUTH_IDENTIFIER', env('VPL_AUTH_USER_ID')),
        'password'   => env('VPL_AUTH_PASSWORD'),
    ],

    // JWT token lifetime in seconds (VPL issues 7-day tokens)
    'token_ttl' => env('VPL_TOKEN_TTL', 604800), // 7 days

    // Retry settings for HTTP calls
    'retry' => [
        'times' => env('VPL_RETRY_TIMES', 3),
        'sleep' => env('VPL_RETRY_SLEEP_MS', 500), // milliseconds
    ],

    // Log channel name
    'log_channel' => env('VPL_LOG_CHANNEL', 'vpl-sync'),

];
