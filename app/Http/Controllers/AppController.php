<?php
namespace App\Http\Controllers;

use App\Database\Models\App;
use App\Http\Core\ModelController;
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
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return ApiJsonResponse
	 */
	public function index( Request $request ) {
		return new ApiJsonResponse(
			$this->getModelCollection( $request, App::query() )
		);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  App $app
	 * @return JsonResponse
	 */
	public function show( App $app ) {
		return $this->showModel( $app );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return ApiJsonResponse
	 */
	public function store( Request $request ) {

		$updates = $request->all();
		unset( $updates[ '_id' ] );

		$model = App::create( $updates );
		$model->saveOrFail();

		return new ApiJsonResponse( $model );
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
