<?php

namespace App\Http\Controllers;

use App\Model\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return JsonResponse
	 */
	public function store( Request $request ) {
		$class = static::$modelClass;
		/** @var Model $app */
		$app = $class::create( $request->all() );
		$app->saveOrFail();

		return new JsonResponse( [ $app, $request->all() ] );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Model\Model $model
	 * @return \Illuminate\Http\Response
	 */
	public function show( Model $model ) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Model\Model $model
	 * @return \Illuminate\Http\Response
	 */
	public function edit( Model $model ) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Model $model
	 * @return JsonResponse
	 */
	public function updateModel( Request $request, Model $model ) {
		$model->fill($request->all());
		$model->saveOrFail();

		return new JsonResponse( $model );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Model\Model $model
	 * @return \Illuminate\Http\Response
	 */
	public function destroy( Model $model ) {
		//
	}
}
