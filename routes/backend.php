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

    // bot
    Route::get('/bot', [
        'as' => 'bot.index',
        'uses' => 'BotAresController@index'
    ]);

    Route::post('/bot/token', [
        'as' => 'bot.token',
        'uses' => 'BotAresController@getToken'
    ]);

    Route::post('/bot/token-2fa', [
        'as' => 'bot.token2fa',
        'uses' => 'BotAresController@getToken2Fa'
    ]);

    Route::get('/bot/clear-token', [
        'as' => 'bot.clear_token',
        'uses' => 'BotAresController@clearToken'
    ]);
});
