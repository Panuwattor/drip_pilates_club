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
    | Media Disk
    |--------------------------------------------------------------------------
    |
    | ดิสก์ที่ใช้เก็บไฟล์อัปโหลดของแอดมิน (รูปประกาศ, avatar ครู, ปกคลิป, สลิป)
    | ค่าเริ่มต้น "public" = เก็บในเครื่อง ตั้ง MEDIA_DISK=r2 เพื่อย้ายไป Cloudflare R2
    |
    */

    'media' => env('MEDIA_DISK', 'public'),

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
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
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

        // Cloudflare R2 — ใช้ S3-compatible API ตั้ง MEDIA_DISK=r2 เพื่อเริ่มใช้
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_BUCKET'),
            'endpoint' => env('R2_ENDPOINT'),
            // public URL ของ bucket (pub-xxxx.r2.dev หรือ custom domain) — ใช้สร้างลิงก์รูป
            'url' => env('R2_URL'),
            // R2 ต้องใช้ path-style = false (virtual-hosted ผ่าน endpoint ของ account)
            'use_path_style_endpoint' => false,
            // อัปโหลดพลาดต้องโยน exception ไม่ใช่คืน false เงียบๆ
            // ไม่งั้นจะบันทึก path ว่างลง DB แล้วรูปหายแบบไม่มีใครรู้
            'throw' => true,
            'report' => false,
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
