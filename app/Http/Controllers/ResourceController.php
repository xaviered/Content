<?php
namespace App\Http\Controllers;

use App\Database\Models\App;
use App\Database\Models\Resource;
use App\Http\Core\ModelController;
use App\Http\Request;
use App\Http\Responses\ApiJsonResponse;
use Illuminate\Http\JsonResponse;

/**
 * Class ResourceController
 *
 * @package App\Http\Controllers
 */
class ResourceController extends ModelController
{
	/** @var App */
	protected $app;

	/**
	 * Display a listing of the resource by $type under an given $app.
	 *
	 * @param Request $request
	 * @param App $app
	 * @param string $type
	 * @return ApiJsonResponse
	 */
	public function index( Request $request, App $app, $type ) {
		return new ApiJsonResponse(
			$this->getModelCollection( $request, Resource::query( [ 'type' => $type, '__app' => $app ] ) )
		);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param App $app
	 * @param string $type
	 * @param string $resource Resource slug
	 * @return JsonResponse
	 * @internal param Resource $resourceInstance
	 */
	public function show( App $app, $type, $resource ) {
		$resource = Resource::query( [ 'type' => $type, '__app' => $app ] )
			->where( 'slug', $resource )
			->firstOrFail()
		;

		return $this->showModel( $resource );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @param string $app slug of app
	 * @param string $type
	 * @return ApiJsonResponse
	 */
	public function store( Request $request, $app, $type ) {

		$attributes = array_merge(
			[ '__app' => $app, 'type' => $type ],
			$request->all()
		);
		unset( $attributes[ '_id' ] );

		$model = Resource::create( $attributes );
		$model->saveOrFail();

		return new ApiJsonResponse( $model );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param App $app
	 * @param string $type
	 * @param string $resource Resource slug
	 * @return JsonResponse
	 */
	public function update( Request $request, App $app, $type, $resource ) {
		$resource = Resource::query( [ 'type' => $type, '__app' => $app ] )
			->where( 'slug', $resource )
			->firstOrFail()
		;

		return parent::updateModel( $request, $resource );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param App $app
	 * @param string $type
	 * @param string $resource Resource slug
	 * @return JsonResponse
	 */
	public function destroy( App $app, $type, $resource ) {
		$resource = Resource::query( [ 'type' => $type, '__app' => $app ] )
			->where( 'slug', $resource )
			->firstOrFail()
		;

		return $this->destroyModel( $resource );
	}

	/**
	 * @param Request $request
	 * @return App|\Illuminate\Database\Eloquent\Builder
	 */
	protected function getApp( Request $request = null ) {
		if ( empty( $this->app ) ) {
			// @todo: do not use segment, use `app` slug or something
			$this->app = App::query()->where( [ 'slug' => $request->segment( 2 ) ] )->firstOrFail();
		}

		return $this->app;
	}
}
