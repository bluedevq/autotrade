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
        Route::post('/token', [
            'as' => 'bot.token',
            'uses' => 'BotController@getToken'
        ]);
        Route::post('/token-2fa', [
            'as' => 'bot.token2fa',
            'uses' => 'BotController@getToken2Fa'
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
    });

    // method trade
    Route::prefix('method-trade')->group(function () {
        Route::get('/', [
            'as' => 'method-trade.index',
            'uses' => 'MethodTradeController@index'
        ]);
        Route::get('/create', [
            'as' => 'method-trade.create',
            'uses' => 'MethodTradeController@create'
        ]);
        Route::get('/edit/{id}', [
            'as' => 'method-trade.edit',
            'uses' => 'MethodTradeController@edit'
        ]);
        Route::post('/valid', [
            'as' => 'method-trade.valid',
            'uses' => 'MethodTradeController@valid'
        ]);
        Route::post('/delete/{id}', [
            'as' => 'method-trade.delete',
            'uses' => 'MethodTradeController@destroy'
        ]);
    });
});
