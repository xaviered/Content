<?php
namespace App\Database\Collections;

use App\Database\Models\Model;
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
	 * @param bool $withKeys Show keys for Collections
	 * @param bool $hideLink Hide self link in Models
	 * @param bool $hideSelfLinkQuery Don't add query info to self link for Models
	 * @return array
	 */
	public function toApiArray( $withKeys = false, $hideLink = false, $hideSelfLinkQuery = false ) {
		$count = 0;
		$modelsArray = [];
		$paginator = $this->paginate();
		foreach ( $paginator as $itemKey => $item ) {
			/** @var $item Model|Collection */
			$key = ( $withKeys ? $itemKey : $count );
			if ( $item instanceof Collection ) {
				$item = $item->toApiArray( true, true, true  )[ 'data' ] ?? [];
			}
			else if ( $item instanceof Model ) {
				$item = $item->toApiArray( true, true, true  );
			}

			$modelsArray[ 'data' ][ $key ] = $item;
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
		if ( $paginator->hasPages() ) {
			$page = $paginator->currentPage();
			$paginator->setRootModel( $this->getRootModel() );

			if ( $request->query->count() ) {
				$parameters = $request->query->all();
				$paginator->appends( $parameters );
			}

			$modelsArray[ 'total_count' ] = $paginator->total();
			$modelsArray[ 'page' ] = $page;
			$modelsArray[ 'total_pages' ] = $paginator->lastPage();

			if ( !$hideLink && $paginator->previousPageUrl() ) {
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

		if ( !$hideLink ) {
			$modelsArray[ 'links' ][ 'self' ] = $this->getRootModel()->uri( 'show' );
		}

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
