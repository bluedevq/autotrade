<?php
return [
    // mail config
    'mail' => [
        'from' => 'no-reply@xoano.net',
        'sender' => env('APP_NAME'),
        'subject' => [
            'register_verify' => 'Xác nhận tài khoản',
            'register_verify_success' => 'Chào mừng bạn đến với ' . env('APP_NAME'),
            'reset_password_verify' => 'Xác nhận lấy lại mật khẩu',
            'reset_password_success' => 'Đổi mật khẩu thành công',
        ],
    ],
    // user status
    'user_status' => [
        'stop' => 0,
        'active' => 1,
        'verify' => 2,
        'forgot_password' => 2,
    ],
    // hash config
    'hash' => [
        'delimiter' => '@',
        'default' => [
            'password' => 'bluedevq_default'
        ],
        'verify' => [
            'password' => 'bluedevq_verify'
        ],
        'forgot_password' => [
            'password' => 'bluedevq_forgot_password'
        ],
    ],
    // token param name
    'token_param_name' => '_t',
    // verify expired date time
    'verify_expired' => 60, // minutes
    // forgot password expired date time
    'forgot_password_expired' => 60, // minutes
    // aresbo config
    'aresbo' => [
        // title bot
        'bot_title' => env('APP_NAME'),

        // api
        'api_url' => [
            'captcha_token' => '03AGdBq27jgvS3PSiB_IiRCWqGcC-sANBQu9ha6axMhMw1ZszFeLELEJZpzt5z1-53gmy-rfLN-iwMVtg4tEJ_GHmMRwKAg5CcisjEzlWfxAFtAHZML0KEmhrRZK0F_F9VzhNbEGW2mQ7OipxMuubvg6a3mwQZ0hXfotyZnDSxWoZZW28sWvJZNCQVudvnFweFuMxbw6Xrb-kybfotNECD_sK2PGZBephuR2n_sby5HkI9UgS4QLI2zffAgedbNw0eEXO_JEPfKr8q-ZBuLnzrwBH8sciTGvJmL0t-E0AbJWgBSi62QSsEY0sl9plpyDmpNX6psGwk6GDN1MKRWno_BaX2xb7aP2keqA7Z6bLF1kihioOcUgCdrVzySPfuKyb440aly1ComIaJFaHc9tz9PGKhDhx52idYD-MmpvyVa19n-IJbVfO-nP3zUUgghqoRsFosoliW2WFv',
            'get_token_url' => 'https://aresbo.com/api/auth/auth/token',
            'get_token2fa_url' => 'https://aresbo.com/api/auth/auth/token-2fa',
            'get_overview' => 'https://aresbo.com/api/wallet/binaryoption/user/overview',
            'get_profile' => 'https://aresbo.com/api/auth/me/profile',
            'get_balance' => 'https://aresbo.com/api/wallet/binaryoption/spot-balance',
            'bet' => 'https://aresbo.com/api/wallet/binaryoption/bet',
            'open_order' => 'https://aresbo.com/api/wallet/binaryoption/transaction/open',
            'close_order' => 'https://aresbo.com/api/wallet/binaryoption/transaction/close',
            'get_prices' => 'https://aresbo.com/api/wallet/binaryoption/prices',
        ],

        // config
        'bot_status' => [
            'stop' => 0,
            'start' => 1,
        ],
        'account_demo' => 1,
        'account_live' => 2,
        'bet_account_type' => [
            1 => 'DEMO',
            2 => 'LIVE',
        ],
        'method' => [
            'stop' => 0,
            'active' => 1,
            'text' => [
                0 => 'Dừng',
                1 => 'Hoạt động',
            ],
        ],
        'method_type' => [
            'text' => [
                1 => 'PAROLI',
                2 => 'MARTINGALE',
            ],
        ],
        'order_signal_delimiter' => '-',
        'order_pattern_delimiter' => '-',
        'order_type_pattern' => [
            't' => 'UP',
            'g' => 'DOWN',
        ],
        'order_type_text' => [
            'up' => 'T',
            'down' => 'G',
        ],
        'chart_tension' => '0.4',
    ],
];
