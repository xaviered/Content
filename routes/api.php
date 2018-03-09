<?php

use \ixavier\Libraries\Server\Core\RestfulRecord;
use Illuminate\Support\Facades\Route;
use App\Database\Models\App as AppModel;
use App\Database\Models\Resource as ResourceModel;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API authentication
Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');
Route::post('recover', 'AuthController@recover');

// Authentication
Route::group(['middleware' => ['jwt.auth']], function() {
    Route::get('logout', 'AuthController@logout');
});

Route::group(['middleware' => ['jwt.auth']], function () {

    // App controller
    //
    // index
    Route::get('/', 'AppController@index')
        ->name(AppModel::ROUTE_NAME.'.index');
    // show
    Route::get('{app}', 'AppController@show')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->name(AppModel::ROUTE_NAME.'.show');
    // store
    Route::post('/', 'AppController@store')
        ->name(AppModel::ROUTE_NAME.'.store');
    // update
    Route::match(['put', 'patch'], '{app}', 'AppController@update')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->name(AppModel::ROUTE_NAME.'.update');
    // delete
    Route::delete('{app}', 'AppController@destroy')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->name(AppModel::ROUTE_NAME.'.destroy');

    // Resource Controller
    //
    // index
    Route::get('{app}/{type}', 'ResourceController@index')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->where('type', RestfulRecord::RESOURCE_TYPE_REGEX)
        ->name(ResourceModel::ROUTE_NAME.'.index');
    // show
    Route::get('{app}/{type}/{resource}', 'ResourceController@show')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->where('type', RestfulRecord::RESOURCE_TYPE_REGEX)
        ->where('resource', RestfulRecord::SLUG_REGEX)
        ->name(ResourceModel::ROUTE_NAME.'.show');
    // store
    Route::post('{app}/{type}', 'ResourceController@store')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->where('type', RestfulRecord::RESOURCE_TYPE_REGEX)
        ->name(ResourceModel::ROUTE_NAME.'.store');
    // update
    Route::match(['put', 'patch'], '{app}/{type}/{resource}', 'ResourceController@update')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->where('type', RestfulRecord::RESOURCE_TYPE_REGEX)
        ->where('resource', RestfulRecord::SLUG_REGEX)
        ->name(ResourceModel::ROUTE_NAME.'.update');
    // delete
    Route::delete('{app}/{type}/{resource}', 'ResourceController@destroy')
        ->where('app', RestfulRecord::SLUG_REGEX)
        ->where('type', RestfulRecord::RESOURCE_TYPE_REGEX)
        ->where('resource', RestfulRecord::SLUG_REGEX)
        ->name(ResourceModel::ROUTE_NAME.'.destroy');
});
