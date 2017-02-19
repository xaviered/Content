<?php

namespace App\Http\Controllers;

use App\Database\Collections\ModelCollection;
use App\Http\Responses\ApiJsonResponse;
use App\Database\Model;
use App\Http\Request;
use Illuminate\Support\Facades\Facade;
use Jenssegers\Mongodb\Query\Builder;

/**
 * Class ModelController has helper methods to handle model CRUD methods.
 *
 * @package App\Http\Controllers
 */
abstract class ModelController extends Controller
{
	/**
	 * @return Model|Builder|Facade Class string representation of the model. i.e. App::class
	 */
	abstract public function rootModel();

	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return ApiJsonResponse
	 */
	public function index( Request $request ) {
		$query = $request->addFilters( ( $this->rootModel() )::query() );

		/** @var ModelCollection $col */
		$col = $query->get();

		if ( $request->get( 'page_size' ) ) {
			$page_size = intval( $request->get( 'page_size' ) );
			if ( $page_size > 0 ) {
				$col->setPerPage( $page_size );
			}
		}

		return new ApiJsonResponse( $col );
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

		/** @var Model $model */
		$model = ( $this->rootModel() )::create( $updates );
		$model->saveOrFail();

		return new ApiJsonResponse( $model );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Model $model
	 * @return ApiJsonResponse
	 */
	public function showModel( Model $model ) {
		return new ApiJsonResponse( $model );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Model $model
	 * @return ApiJsonResponse
	 */
	public function updateModel( Request $request, Model $model ) {
		$updates = $request->all();

		// do not update _id
		unset( $updates[ '_id' ] );
		$model->update( $updates );

		return new ApiJsonResponse( $model );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Model $model
	 * @return ApiJsonResponse
	 */
	public function destroyModel( Model $model ) {
		$result = $model->delete();
		if ( !$result ) {
			return null;
		}

		return new ApiJsonResponse( 'data' );
	}
}
