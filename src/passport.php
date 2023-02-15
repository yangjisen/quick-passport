<?php

return [
    'password_grant_client' => [
        'id' => env('PASSPORT_PASSWORD_GRAN_CLIENT_ID'),
        'secret' => env('PASSPORT_PASSWORD_GRAN_CLIENT_SECRET'),
    ],

    'tokens_expire_in' => env('PASSPORT_TOKEN_EXPIRE_DAYS', 7),

    'refresh_tokens_expire_in' => env('PASSPORT_REFRESH_EXPIRE_DAYS', 15),

    'personal_expire_id' => env('PASSPORT_PERSONAL_EXPIRE_DAYS', 7),

    'cache_time' => env('PASSPORT_CACHE_TIME', 86400),
];
