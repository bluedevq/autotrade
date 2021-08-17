<?php

use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('login', [
    'as' => 'backend.login',
    'uses' => 'LoginController@index'
]);
Route::post('auth', [
    'as' => 'backend.auth',
    'uses' => 'LoginController@auth'
]);
Route::middleware(['auth.backend'])->post('logout', [
    'as' => 'backend.logout',
    'uses' => 'LoginController@logout'
]);

// register new account
Route::prefix('register')->group(function () {
    Route::get('/', [
        'as' => 'backend.register',
        'uses' => 'RegisterController@index'
    ]);
    Route::post('valid', [
        'as' => 'backend.register.valid',
        'uses' => 'RegisterController@valid'
    ]);
    Route::get('success', [
        'as' => 'backend.register.success',
        'uses' => 'RegisterController@success'
    ]);
    Route::get('verify', [
        'as' => 'backend.register.verify',
        'uses' => 'RegisterController@verify'
    ]);
});


// forgot password
Route::prefix('password')->group(function () {
    Route::get('forgot', [
        'as' => 'backend.password.forgot',
        'uses' => 'PasswordController@index'
    ]);
    Route::post('forgot-valid', [
        'as' => 'backend.password.forgot.valid',
        'uses' => 'PasswordController@valid'
    ]);
    Route::get('forgot-success', [
        'as' => 'backend.password.forgot.success',
        'uses' => 'PasswordController@success'
    ]);
    Route::get('forgot-verify', [
        'as' => 'backend.password.forgot.verify',
        'uses' => 'PasswordController@verify'
    ]);
    Route::post('new-valid', [
        'as' => 'backend.password.new.valid',
        'uses' => 'PasswordController@validNew'
    ]);
    Route::get('new-success', [
        'as' => 'backend.forgot.password.new.success',
        'uses' => 'PasswordController@newSuccess'
    ]);
});

// backend
Route::middleware(['auth.backend'])->group(function () {
    // home
    Route::get('/', [
        'as' => 'dashboard.index',
        'uses' => 'HomeController@index'
    ]);
    Route::get('/home', [
        'as' => 'home.index',
        'uses' => 'HomeController@index'
    ]);

    // bot auto trade
    Route::prefix('bot')->group(function () {
        Route::get('/', [
            'as' => 'bot.index',
            'uses' => 'BotController@index'
        ]);
        // get token to login aresbo
        Route::post('/login', [
            'as' => 'bot.login',
            'uses' => 'BotController@login'
        ]);
        // request code
        Route::post('/request-code', [
            'as' => 'bot.request.code',
            'uses' => 'BotController@requestCode'
        ]);
        Route::post('/login-2fa', [
            'as' => 'bot.loginWith2FA',
            'uses' => 'BotController@loginWith2FA'
        ]);
        // clear token and logout aresbo
        Route::get('/clear-token', [
            'as' => 'bot.clear_token',
            'uses' => 'BotController@clearToken'
        ]);
        // start-stop auto trade
        Route::post('/start-auto', [
            'as' => 'bot.start_auto',
            'uses' => 'BotController@startAuto'
        ]);
        Route::post('/stop-auto', [
            'as' => 'bot.stop_auto',
            'uses' => 'BotController@stopAuto'
        ]);
        // bet auto
        Route::post('/bet', [
            'as' => 'bot.bet',
            'uses' => 'BotController@bet'
        ]);
        // create method
        Route::get('/create-method', [
            'as' => 'bot_method.create',
            'uses' => 'BotController@createMethod'
        ]);
        // edit method
        Route::get('/edit-method/{id}', [
            'as' => 'bot_method.edit',
            'uses' => 'BotController@editMethod'
        ]);
        // validation method
        Route::post('/validate-method', [
            'as' => 'bot_method.valid',
            'uses' => 'BotController@validateMethod'
        ]);
        // delete method
        Route::post('/delete-method', [
            'as' => 'bot_method.delete',
            'uses' => 'BotController@deleteMethod'
        ]);
        // research method
        Route::post('/research', [
            'as' => 'bot_method.research',
            'uses' => 'BotController@research'
        ]);
        // move usdt
        Route::get('/move-money', [
            'as' => 'bot.move.money',
            'uses' => 'BotController@moveMoney'
        ]);
        // move money validation
        Route::post('/move-money-valid', [
            'as' => 'bot.move.money.valid',
            'uses' => 'BotController@moveMoneyValid'
        ]);
        // update stop loss / take profit
        Route::post('/update-profit', [
            'as' => 'bot.update.profit',
            'uses' => 'BotController@updateProfit'
        ]);
    });
});
