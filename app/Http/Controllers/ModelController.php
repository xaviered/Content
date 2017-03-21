<?php

namespace App\Http\Controllers;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiSearchFilter;
use App\Http\Responses\ApiJsonResponse;
use App\Database\Models\Model;
use App\Http\Request;

/**
 * Class ModelController has helper methods to handle model CRUD methods.
 *
 * @package App\Http\Controllers
 */
abstract class ModelController extends Controller
{
	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Database\Models\Model $model
	 * @return ApiJsonResponse
	 */
	public function showModel( Model $model ) {
		return new ApiJsonResponse( $model );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request $request
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

		return new ApiJsonResponse( [ 'success' => $result ] );
	}

	/**
	 * Gets ModelCollection from $query based on $request params
	 *
	 * @param Request $request
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return ModelCollection
	 */
	protected function getModelCollection( Request $request, $query ) {
		// @todo: Refactor so that there is a FilterFactory instead of using Request for that
		// filter out fields based on request params
		request()
			->addFilter( ApiSearchFilter::class )
			->filter( $query )
		;

		/** @var ModelCollection $col */
		$col = $query->get();

		if ( $request->get( 'page_size' ) ) {
			$page_size = intval( $request->get( 'page_size' ) );
			if ( $page_size > 0 ) {
				$col->setPerPage( $page_size );
			}
		}

		return $col;
	}
}
