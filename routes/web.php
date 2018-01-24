<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('ping', 'RootController@ping');
Route::get('/', function () {
    return view('welcome');
});

// user login options
Route::post('register', 'RootController@postRegister');
Route::post('login', 'RootController@postLogin');
Route::post('recover', 'RootController@postRecover');

// User management
Route::get('register', 'RootController@register');
Route::get('login', 'RootController@login')->name('login');
Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
Route::post('password/reset', 'Auth\PasswordController@reset');

