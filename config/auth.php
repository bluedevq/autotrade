<?php

return [
    'defaults' => [
        'guard' => 'admins',
        'passwords' => 'users',
    ],

    'guards' => [
        'admins' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'frontend' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Model\Entities\User::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Model\Entities\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 1440,
            'throttle' => 60,
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_resets',
            'expire' => 1440,
            'throttle' => 60,
        ],
    ],
];
