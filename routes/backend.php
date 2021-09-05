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
        // update stop loss / take profit
        Route::post('/update-profit', [
            'as' => 'bot.update.profit',
            'uses' => 'BotController@updateProfit'
        ]);
    });

    // method
    Route::prefix('method')->group(function () {
        // create method
        Route::get('/create-method', [
            'as' => 'bot_method.create',
            'uses' => 'MethodController@createMethod'
        ]);
        // edit method
        Route::get('/edit-method/{id}', [
            'as' => 'bot_method.edit',
            'uses' => 'MethodController@editMethod'
        ]);
        // validation method
        Route::post('/validate-method', [
            'as' => 'bot_method.valid',
            'uses' => 'MethodController@validateMethod'
        ]);
        // update method status
        Route::post('/update-status-method', [
            'as' => 'bot.method.update.status',
            'uses' => 'MethodController@updateStatusMethod'
        ]);
        // delete method
        Route::post('/delete-method', [
            'as' => 'bot_method.delete',
            'uses' => 'MethodController@deleteMethod'
        ]);
        // research method
        Route::post('/research', [
            'as' => 'bot_method.research',
            'uses' => 'MethodController@research'
        ]);
    });

    // move usdt
    Route::prefix('move-money')->group(function () {
        Route::get('/', [
            'as' => 'bot.move.money',
            'uses' => 'MoveMoneyController@index'
        ]);
        // move money validation
        Route::post('/valid', [
            'as' => 'bot.move.money.valid',
            'uses' => 'MoveMoneyController@valid'
        ]);
    });

    // management user
    Route::middleware(['role.backend'])->prefix('user')->group(function () {
        Route::get('/', [
            'as' => 'user.index',
            'uses' => 'UserController@index'
        ]);
        Route::get('/create', [
            'as' => 'user.create',
            'uses' => 'UserController@create'
        ]);
        Route::get('/edit/{id}', [
            'as' => 'user.edit',
            'uses' => 'UserController@edit'
        ]);
        Route::post('/valid', [
            'as' => 'user.valid',
            'uses' => 'UserController@valid'
        ]);
        Route::post('/delete/{id}', [
            'as' => 'user.delete',
            'uses' => 'UserController@delete'
        ]);
    });

    // management default method
    Route::prefix('default-method')->group(function () {
        Route::get('/', [
            'as' => 'default.method.index',
            'uses' => 'DefaultMethodController@index'
        ]);
        Route::get('/create', [
            'as' => 'default.method.create',
            'uses' => 'DefaultMethodController@create'
        ]);
        Route::get('/edit/{id}', [
            'as' => 'default.method.edit',
            'uses' => 'DefaultMethodController@edit'
        ]);
        Route::post('/valid', [
            'as' => 'default.method.valid',
            'uses' => 'DefaultMethodController@valid'
        ]);
        Route::post('/delete/{id}', [
            'as' => 'default.method.delete',
            'uses' => 'DefaultMethodController@delete'
        ]);
    });
});
