<?php

use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('login', [
    'as' => 'backend.login',
    'uses' => 'Auth\LoginController@index'
]);
Route::post('auth', [
    'as' => 'backend.auth',
    'uses' => 'Auth\LoginController@auth'
]);
Route::middleware(['auth.backend'])->post('logout', [
    'as' => 'backend.logout',
    'uses' => 'Auth\LoginController@logout'
]);


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
