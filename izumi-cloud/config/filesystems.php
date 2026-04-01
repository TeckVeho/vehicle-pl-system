<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
            'visibility' => 'public',
            'directory_visibility' => 'public',
            'permissions' => [
                'dir' => [
                    'public' => 0775,
                ],
                'file' => [
                    'public' => 0664,
                ],
            ],
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
            'directory_visibility' => 'public',
            'permissions' => [
                'dir' => [
                    'public' => 0775,
                ],
                'file' => [
                    'public' => 0664,
                ],
            ],
        ],

        'database' => [
            'driver' => 'local',
            'root' => base_path('database'),
            'visibility' => 'public',
            'directory_visibility' => 'public',
        ],


        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],
        's3_itp' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID_ITP'),
            'secret' => env('AWS_SECRET_ACCESS_KEY_ITP'),
            'region' => env('AWS_DEFAULT_REGION_ITP'),
            'bucket' => env('AWS_BUCKET_ITP'),
            'url' => env('AWS_URL_ITP'),
            'endpoint' => env('AWS_ENDPOINT_ITP'),
        ],
        'data' => [
            'driver' => 'local',
            'root' => storage_path('app/data'),
        ],
        'stores_files_web' => [
            'driver' => 'local',
            'root' => storage_path('app/public/stores_files_web'),
            'url' => env('APP_URL') . '/storage',
        ],
        STORE_FILE_ATTATCH_DISK => [
            'driver' => 'local',
            'root' => storage_path('app/stores_files'),
            'url' => env('APP_URL') . '/stores_files',
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('stores_files') => storage_path('app/stores_files'),
        public_path('storage\driver_upload') => storage_path('app/public/driver_upload'),
    ],

];
