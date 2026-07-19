<?php

use App\Identity\Models\Identity;

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'identities',
        ],
    ],

    'providers' => [
        'identities' => [
            'driver' => 'eloquent',
            'model' => Identity::class,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
