<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Database\Models\App;
use App\Database\Models\Resource;

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
Route::resource( App::ROUTE_NAME, 'AppController' );

// Resource controller
Route::resource( Resource::ROUTE_NAME, 'ResourceController' );
