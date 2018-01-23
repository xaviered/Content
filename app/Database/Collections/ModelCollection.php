<?php
namespace App\Database\Collections;

use App\Database\Core\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use ixavier\Libraries\Server\Core\ModelCollection as X_ModelCollection;
use ixavier\Libraries\Server\Core\RestfulRecord;

/**
 * Class ModelCollection serves as a custom collection for models
 *
 * @package App\Models
 */
class ModelCollection extends Collection
{
	/** @var int Items per page */
	protected static $perPage;

	/** @var \App\Database\Core\Model */
	protected $rootModel;

	/**
	 * API array representation of this collection
	 *
	 * @param int $relationsDepth Current depth of relations loaded. Default = 1
	 * @param bool $hideLinks Hide links section
	 * @param bool $withKeys Show keys for Collections
	 * @param bool $ignorePaging Will not load paging mechanism
	 * @return array
	 */
	public function toApiArray( $relationsDepth = -1, $hideLinks = false, $withKeys = false, $ignorePaging = false ) {
		$count = 0;
		$modelsArray = [];
		$paginator = $ignorePaging ? $this : $this->paginate();
		foreach ( $paginator as $itemKey => $item ) {
			if ( $item instanceof self || $item instanceof X_ModelCollection ) {
				$item = $item->toApiArray( $relationsDepth + 1, true, false, true )[ 'data' ] ?? [];
			}
			else if ( $item instanceof Model || $item instanceof RestfulRecord ) {
				$item = $item->toApiArray( $relationsDepth + 1, true );
			}

			$modelsArray[ 'data' ][ $withKeys ? $itemKey : $count ] = $item;
			$count++;
		}

		// get url based on model
		$selfUrl = $this->getRootModel()->uri();
		$request = Request::create( $selfUrl );

		// remove page=0|1 param for caching performance
		if ( $request->query->get( 'page' ) <= 1 ) {
			$request->query->remove( 'page' );
//			$request->server->set( 'QUERY_STRING', Request::normalizeQueryString( http_build_query( $request->query->all() ) ) );
		}

		$modelsArray[ 'count' ] = $paginator->count();
		if ( !$ignorePaging && $paginator->hasPages() ) {
			$page = $paginator->currentPage();
			$paginator->setRootModel( $this->getRootModel() );

			if ( $request->query->count() ) {
				$parameters = $request->query->all();
				$paginator->appends( $parameters );
			}

			$modelsArray[ 'total_count' ] = $paginator->total();
			$modelsArray[ 'page' ] = $page;
			$modelsArray[ 'total_pages' ] = $paginator->lastPage();

			if ( !$hideLinks && $paginator->previousPageUrl() ) {
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

		if ( !$hideLinks ) {
			// this is a "collection", so don't pass any params
			$r = Request::create( $this->getRootModel()->uri( 'show', [ '' ] ) );
			$modelsArray[ 'links' ][ 'self' ] = $request->query->count() ? $r->fullUrlWithQuery( $request->all() ) : $r->url();
		}

		return $modelsArray;
	}

	/**
	 * @return \App\Database\Core\Model
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
