<?php
return [
    'aresbo' => [
        // api
        'captcha_token' => '03AGdBq27jgvS3PSiB_IiRCWqGcC-sANBQu9ha6axMhMw1ZszFeLELEJZpzt5z1-53gmy-rfLN-iwMVtg4tEJ_GHmMRwKAg5CcisjEzlWfxAFtAHZML0KEmhrRZK0F_F9VzhNbEGW2mQ7OipxMuubvg6a3mwQZ0hXfotyZnDSxWoZZW28sWvJZNCQVudvnFweFuMxbw6Xrb-kybfotNECD_sK2PGZBephuR2n_sby5HkI9UgS4QLI2zffAgedbNw0eEXO_JEPfKr8q-ZBuLnzrwBH8sciTGvJmL0t-E0AbJWgBSi62QSsEY0sl9plpyDmpNX6psGwk6GDN1MKRWno_BaX2xb7aP2keqA7Z6bLF1kihioOcUgCdrVzySPfuKyb440aly1ComIaJFaHc9tz9PGKhDhx52idYD-MmpvyVa19n-IJbVfO-nP3zUUgghqoRsFosoliW2WFv',
        'get_token_url' => 'https://aresbo.com/api/auth/auth/token',
        'get_token2fa_url' => 'https://aresbo.com/api/auth/auth/token-2fa',
        'get_overview' => 'https://aresbo.com/api/wallet/binaryoption/user/overview',
        'get_profile' => 'https://aresbo.com/api/auth/me/profile',
        'get_balance' => 'https://aresbo.com/api/wallet/binaryoption/spot-balance',
        'bet' => 'https://aresbo.com/api/wallet/binaryoption/bet',

        // config
        'account_demo' => 1,
        'live_demo' => 2,
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
    ],
];
