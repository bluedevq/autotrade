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
        Route::get('/research', [
            'as' => 'bot_method.research',
            'uses' => 'BotController@research'
        ]);
    });
});
