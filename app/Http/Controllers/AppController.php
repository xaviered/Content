<?php
namespace App\Http\Controllers;

use App\Database\Models\App;
use App\Http\Responses\ApiJsonResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Request;

/**
 * Class AppController
 *
 * @package App\Http\Controllers
 */
class AppController extends ModelController
{
	/**
	 * @return App Class string representation of the model. i.e. App::class
	 */
	public function rootModel() {
		return App::class;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return ApiJsonResponse
	 */
	public function index( Request $request ) {
		return new ApiJsonResponse(
			$this->getModelCollection( $request, ( $this->rootModel() )::query() )
		);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  App $app
	 * @return JsonResponse
	 */
	public function show( App $app ) {
		// @todo: add a 'resources' relationship with all types of resources

		return $this->showModel( $app );
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
