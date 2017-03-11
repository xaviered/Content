<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Database\Models\App;

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

Route::middleware( 'auth:api' )->get( '/user', function( Request $request ) {
	return $request->user();
} )
;

// App controller
//Route::middleware('auth:api')->resource( App::ROUTE_NAME, 'AppController' );
Route::resource( App::ROUTE_NAME, 'AppController' );

// index
Route::get( '{type}/{app}', 'ResourceController@index' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->name( 'resource.index' )
;
// show
Route::get( '{type}/{app}/{resource}', 'ResourceController@show' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->where( 'resource', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->name( 'resource.show' )
;
// store
Route::post( '{type}/{app}', 'ResourceController@store' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->name( 'resource.store' )
;
// update
Route::match( [ 'put', 'patch' ], '{type}/{app}/{resource}', 'ResourceController@update' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->where( 'resource', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->name( 'resource.update' )
;
// delete
Route::delete( '{type}/{app}/{resource}', 'ResourceController@destroy' )
	->where( 'app', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->where( 'type', '[A-Za-z]+' )
	->where( 'resource', '[A-Za-z\-_][A-Za-z0-9\-_]+' )
	->name( 'resource.destroy' )
;

