<?php

return [
    'defaults' => [
        'guard' => 'backend',
        'passwords' => 'users',
    ],

    'guards' => [
        'backend' => [
            'driver' => 'session',
            'provider' => 'backend',
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
        'backend' => [
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
        'backend' => [
            'provider' => 'backend',
            'table' => 'password_resets',
            'expire' => 1440,
            'throttle' => 60,
        ],
    ],
];
