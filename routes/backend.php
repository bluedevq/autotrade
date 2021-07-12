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
    Route::get('/', [
        'as' => 'dashboard.index',
        'uses' => 'HomeController@index'
    ]);
    Route::get('/home', [
        'as' => 'home.index',
        'uses' => 'HomeController@index'
    ]);
});
