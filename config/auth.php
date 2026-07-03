<?php

return [
    'defaults' => [
        'guard'     => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        // Stateless JWT guard for staff (tymon/jwt-auth).
        'api' => [
            'driver'   => 'jwt',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\User\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
