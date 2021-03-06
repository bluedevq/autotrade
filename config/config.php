<?php
return [
    // title bot
    'bot_title' => env('APP_NAME'),
    // pagination
    'pagination' => [
        'default' => 10,
        'users' => 10,
    ],
    // role
    'user_role' => [
        'supper_admin' => 0,
        'admin' => 1,
        'normal' => 2,
    ],
    'user_role_text' => [
        0 => 'Admin',
        1 => 'Quản lý',
        2 => 'Thường',
    ],
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
        'forgot_password' => 3,
    ],
    'user_status_text' => [
        0 => 'Dừng hoạt động',
        1 => 'Đang hoạt động',
        2 => 'Chưa xác nhận',
        3 => 'Lấy lại mật khẩu',
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
    // free days after register
    'free_days_after_register' => 3,
    // aresbo config
    'aresbo' => [
        // api
        'api_url' => [
            'captcha_token' => env('GOOGLE_CAPTCHA_TOKEN'),
            'get_token_url' => 'https://aresbo.com/api/auth/auth/token',
            'get_token2fa_url' => 'https://aresbo.com/api/auth/auth/token-2fa',
            'get_overview' => 'https://aresbo.com/api/wallet/binaryoption/user/overview',
            'get_profile' => 'https://aresbo.com/api/auth/me/profile',
            'get_balance' => 'https://aresbo.com/api/wallet/binaryoption/spot-balance',
            'bet' => 'https://aresbo.com/api/wallet/binaryoption/bet',
            'open_order' => 'https://aresbo.com/api/wallet/binaryoption/transaction/open',
            'close_order' => 'https://aresbo.com/api/wallet/binaryoption/transaction/close',
            'get_prices' => 'https://aresbo.com/api/wallet/binaryoption/prices',
            'move_usdtbo' => 'https://aresbo.com/api/wallet/binaryoption/move-usdtbo',
            'move_bousdt' => 'https://aresbo.com/api/wallet/binaryoption/move-bousdt',
            'reload_demo_balance' => 'https://aresbo.com/api/wallet/binaryoption/demo',
            'request_code' => 'https://aresbo.com/api/auth/2fa/trusted-device/request-code',
            'reward_info' => 'https://aresbo.com/api/wallet/binaryoption/events/reward/info',
            'reward_histories' => 'https://aresbo.com/api/wallet/binaryoption/events/reward/history?page=1&size=10&total=0',
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
            'break_item_pc' => 6,
            'break_item_mobile' => 1,
        ],
        'method_type' => [
            'value' => [
                'paroli' => 1,
                'martingale' => 2,
            ],
            'text' => [
                1 => 'Đánh sau thắng',
                2 => 'Đánh sau thua',
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
        'order_type' => [
            'oder' => 1,
            'result' => 0,
        ],
        'price_keys' => [
            'open_order',
            'open_price',
            'high_price',
            'low_price',
            'close_price',
            'base_volume',
            'close_order',
            'xxx',
            'order_type',
            'session',
        ],
        'chart' => [
            'chart_tension' => '0.4',
            'chart_default_step_size' => 5,
            'chart_step_size' => 10,
            'chart_border_width' => 1,
            'chart_total_border_width' => 2.5,
            'chart_total_color' => '#ff0000',
        ],
        'move_money_type' => [
            'wallet_to_trade' => 1,
            'trade_to_wallet' => 2,
        ],
        'delay_time' => 30, // seconds
    ],
];
