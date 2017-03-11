<?php
namespace App\Http\Controllers;

use App\Database\Models\App;
use App\Database\Models\Resource;
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
	 * @return Resource Class string representation of the model. i.e. App::class
	 */
	public function rootModel() {
		return Resource::class;
	}

	/**
	 * Display a listing of the resource by $type under an given $app.
	 *
	 * @param Request $request
	 * @param string $type
	 * @param App $app
	 * @return ApiJsonResponse
	 */
	public function index( Request $request, $type, App $app ) {
		return new ApiJsonResponse(
			$this->getModelCollection( $request, ( $this->rootModel() )::queryFromType( $type, $app ) )
		);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param string $type
	 * @param App $app
	 * @param string $resource Resource slug
	 * @return JsonResponse
	 * @internal param Resource $resourceInstance
	 */
	public function show( $type, App $app, $resource ) {
		$resource = ( $this->rootModel() )::queryFromType( $type, $app )
			->where( 'slug', $resource )
			->firstOrFail()
		;

		return $this->showModel( $resource );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param string $type
	 * @param App $app
	 * @param $resource
	 * @return JsonResponse
	 */
	public function update( Request $request, $type, App $app, $resource ) {
		$resource = ( $this->rootModel() )::queryFromType( $type, $app )
			->where( 'slug', $resource )
			->firstOrFail()
		;

		return parent::updateModel( $request, $resource );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param string $type
	 * @param  App $app
	 * @return JsonResponse
	 */
	public function destroy( $type, App $app, $resource ) {
		$resource = ( $this->rootModel() )::queryFromType( $type, $app )
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
			$this->app = App::query()->where( [ 'slug' => $request->segment( 3 ) ] )->firstOrFail();
		}

		return $this->app;
	}
}
