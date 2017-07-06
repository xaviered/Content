<?php
namespace App\Database\Collections;

use App\Database\Core\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Custom Paginator
 * @package App\Database\Collections
 */
class Paginator extends LengthAwarePaginator
{
	/** @var Model */
	protected $rootModel;

	/**
	 * Get the URL for a given page number.
	 *
	 * @param  int $page
	 * @return string
	 */
	public function url( $page ) {
		$parameters = [];

		$url = $this->path;

		// If we have any extra query string key / value pairs that need to be added
		// onto the URL, we will put them in query string form and then attach it
		// to the URL. This allows for extra information like sortings storage.
		if ( $page > 1 ) {
			$parameters = [ $this->pageName => $page ];
		}

		if ( count( $this->query ) > 0 ) {
			$parameters = array_merge( $this->query, $parameters );
		}

		if ( count( $parameters ) ) {
			$url .= ( Str::contains( $this->path, '?' ) ? '&' : '?' )
				. Request::normalizeQueryString( http_build_query( $parameters, '', '&' ) )
				. $this->buildFragment();
		}

		return $url;
	}

	/**
	 * @return Model
	 */
	public function getRootModel() {
		return $this->rootModel;
	}

	/**
	 * @param \App\Database\Core\Model $rootModel
	 * @return $this Chainnable method
	 */
	public function setRootModel( $rootModel ) {
		$this->rootModel = $rootModel;

		$this->setPath( Request::create( $rootModel->uri() )->url() );

		return $this;
	}
}
