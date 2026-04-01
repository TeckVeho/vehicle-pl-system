<?php

return [
    'oauth2' => [
        'client_id' => env('GOOGLE_OAUTH_CLIENT_ID'),
        'client_secret' => env('GOOGLE_OAUTH_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_OAUTH_REDIRECT_URI', 'http://localhost:8000/auth/google/callback'),
        'scopes' => [
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/userinfo.email',
            // Google Apps Script API scopes
            'https://www.googleapis.com/auth/script.projects',
            'https://www.googleapis.com/auth/script.deployments',
            'https://www.googleapis.com/auth/script.processes',
        ],
    ],
    'sheets' => [
        'application_name' => 'Laravel Google Sheets Integration',
    ],
    'webhook_token' => env('GOOGLE_WEBHOOK_TOKEN', 'your-secret-webhook-token'),
];
