<?php
namespace App\Http\Controllers;

use App\Model\App;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class AppController
 *
 * @package App\Http\Controllers
 */
class AppController extends ModelController
{
	/** @var string $modelClass Model class to use when creating/finding */
	protected static $modelClass = App::class;

	/**
	 * Display the specified resource.
	 *
	 * @param  App $app
	 * @return JsonResponse
	 */
	public function show( App $app ) {
		return $this->showModel($app);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request $request
	 * @param  App $app
	 * @return JsonResponse
	 */
	public function update( Request $request, App $app ) {
		return parent::updateModel( $request, $app );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  App $app
	 * @return JsonResponse
	 */
	public function destroy( App $app ) {
		return $this->destroyModel( $app );
	}
}
