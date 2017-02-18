<?php
namespace App\Database\Collections;

use App\Database\Model;
use App\Database\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * Class ModelCollection serves as a custom collection for models
 *
 * @package App\Models
 */
class ModelCollection extends Collection
{
	/** @var int Items per page */
	protected static $perPage;

	/** @var Model */
	protected $rootModel;

	/**
	 * Same as toArray() but ready to be sent to an API
	 *
	 * @param bool $withKeys
	 * @param bool $showPaging Will show paging info and links
	 * @return array
	 */
	public function toApiArray( $withKeys = false, $showPaging = true ) {
		$count = 0;
		$modelsArray = [];
		$paginator = $this->paginate();
		foreach ( $paginator as $itemKey => $item ) {
			/** @var $item Model */
			$key = ( $withKeys ? $itemKey : $count );
			$modelsArray[ 'data' ][ $key ] = $item->toApiArray( $showPaging );
			$count++;
		}

		// get url based on model
		$selfUrl = $this->getRootModel()->uri();
		$request = Request::create( $selfUrl );

		// remove page=0|1 param for caching performance
		if ( $request->query->get( 'page' ) <= 1 ) {
			$request->query->remove( 'page' );
			$request->server->set( 'QUERY_STRING', Request::normalizeQueryString( http_build_query( $request->query->all() ) ) );
		}

		$modelsArray[ 'count' ] = $paginator->count();
		if ( $paginator->hasPages() && $showPaging ) {
			$page = $paginator->currentPage();
			$paginator->setRootModel( $this->getRootModel() );

			$hasQuery = count( $request->query );
			if ( $hasQuery ) {
				$parameters = $request->query->all();
				$paginator->appends( $parameters );
			}

			$modelsArray[ 'total_count' ] = $paginator->total();
			$modelsArray[ 'page' ] = $page;
			$modelsArray[ 'total_pages' ] = $paginator->lastPage();

			if ( $paginator->previousPageUrl() ) {
				if ( $page - 1 > 1 ) {
					$modelsArray[ 'links' ][ 'prev' ] = $paginator->previousPageUrl();
				}
				else {
					$modelsArray[ 'links' ][ 'prev' ] = $paginator->url( $page - 1 );
				}
			}
			if ( $paginator->hasMorePages() ) {
				$modelsArray[ 'links' ][ 'next' ] = $paginator->nextPageUrl();
			}
		}

		$modelsArray[ 'links' ][ 'self' ] = $request->getUri();

		return $modelsArray;
	}

	/**
	 * @return Model
	 */
	public function getRootModel() {
		return $this->rootModel;
	}

	/**
	 * @param Model $rootModel
	 */
	public function setRootModel( $rootModel ) {
		$this->rootModel = $rootModel;
	}

	/**
	 * @return int
	 */
	public function getPerPage() {
		if ( !isset( static::$perPage ) ) {
			$this->setPerPage( Config::get( 'pagination_size' ) ?? 1000 );
		}

		return static::$perPage;
	}

	/**
	 * @param int $perPage
	 */
	public function setPerPage( $perPage ) {
		static::$perPage = $perPage;
	}

	/**
	 * Get a paginator for the "select" statement.
	 *
	 * @param  int $perPage
	 * @return Paginator
	 */
	public function paginate( $perPage = null ) {
		$page = Paginator::resolveCurrentPage();
		$perPage = $perPage ?: $this->getPerPage();

		$resultsCol = $this->slice( ( $page - 1 ) * $perPage, $perPage );

		//Create our paginator and pass it to the view
		return new Paginator( $resultsCol, $this->count(), $perPage );
	}
}
