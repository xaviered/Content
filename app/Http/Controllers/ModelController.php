<?php

namespace App\Http\Controllers;

use App\Model\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ModelController has helper methods to handle model CRUD methods.
 *
 * @package App\Http\Controllers
 */
class ModelController extends Controller
{
	/** @var string $modelClass Model class to use when creating/finding */
	protected static $modelClass;

	/**
	 * Display a listing of the resource.
	 *
	 * @return JsonResponse
	 */
	public function index() {
		//
		return new JsonResponse( [] );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return JsonResponse
	 */
	public function store( Request $request ) {
		$class = static::$modelClass;

		$updates = $request->all();
		unset( $updates[ '_id' ] );

		/** @var Model $app */
		$app = $class::create( $updates );
		$result = $app->saveOrFail();

		return new JsonResponse( [ 'success' => $result, 'data' => $app ] );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Model $model
	 * @return JsonResponse
	 */
	public function showModel( Model $model ) {
		return new JsonResponse( [ 'data' => $model ] );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Model $model
	 * @return JsonResponse
	 */
	public function updateModel( Request $request, Model $model ) {
		$updates = $request->all();

		// do not update _id
		unset( $updates[ '_id' ] );
		$model->update( $updates );

		return new JsonResponse( $model );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Model $model
	 * @return JsonResponse
	 */
	public function destroyModel( Model $model ) {
		$result = $model->delete();

		return new JsonResponse( [ 'success' => $result, 'data' => $model ] );
	}
}
