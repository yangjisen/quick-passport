<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Passport will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),

    'public_key' => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Client UUIDs
    |--------------------------------------------------------------------------
    |
    | By default, Passport uses auto-incrementing primary keys when assigning
    | IDs to clients. However, if Passport is installed using the provided
    | --uuids switch, this will be set to "true" and UUIDs will be used.
    |
    */

    'client_uuids' => false,

    /*
    |--------------------------------------------------------------------------
    | Personal Access Client
    |--------------------------------------------------------------------------
    |
    | If you enable client hashing, you should set the personal access client
    | ID and unhashed secret within your environment file. The values will
    | get used while issuing fresh personal access tokens to your users.
    |
    */

    'personal_access_client' => [
        'id' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID'),
        'secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),
    ],

    /* 个人令牌有效期 */
    'personal_expire_in' => env('PASSPORT_PERSONAL_EXPIRE_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Passport Grant Client
    |--------------------------------------------------------------------------
    |
    | If you enable client hashing, you should set the personal access client
    | ID and unhashed secret within your environment file. The values will
    | get used while issuing fresh personal access tokens to your users.
    |
    */

    'password_grant_client' => [
        'id' => env('PASSPORT_PASSWORD_GRAN_CLIENT_ID'),
        'secret' => env('PASSPORT_PASSWORD_GRAN_CLIENT_SECRET'),
    ],

    /* Token 有效期 */
    'tokens_expire_in' => env('PASSPORT_TOKEN_EXPIRE_DAYS', 7),

    /* Refresh 有效期 */
    'refresh_tokens_expire_in' => env('PASSPORT_REFRESH_EXPIRE_DAYS', 15),

    /* 客户端缓存时间 */
    'cache_time' => env('PASSPORT_CACHE_TIME', 86400),

    /* 小程序自动注册配置 */
    'program_auto_register' => env('PROGRAM_AUTO_REGISTER', true),
    'auto_register_model' => \App\Models\User::class,

];
