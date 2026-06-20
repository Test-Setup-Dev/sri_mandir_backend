<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            // By default Laravel stores public files in `storage/app/public` and serves them via
            // the `public/storage` symlink (created by `php artisan storage:link`).
            //
            // Some shared-hosting environments block symlinks; in that case, set:
            // PUBLIC_FILES_ROOT=public/storage
            // to store files directly under `public/storage`.
            'root' => (function () {
                $override = env('PUBLIC_FILES_ROOT');
                if (is_string($override) && $override !== '') {
                    return str_starts_with($override, '/') ? $override : base_path($override);
                }
                return storage_path('app/public');
            })(),
            // Set this to force a specific domain in generated URLs, e.g.:
            // PUBLIC_FILES_URL=https://rapidload.in/sanatanlok/public/storage
            //
            // Note: this project serves assets under `/public/...` (see other controllers),
            // so defaulting to `/public/storage` avoids 404s on shared-hosting setups
            // where the document root is the project root (not `public/`).
            'url' => env('PUBLIC_FILES_URL', env('ASSET_URL', env('APP_URL')).'/public/storage'),
            'visibility' => 'public',
            'throw' => false,
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
        ],

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
    ],

];
