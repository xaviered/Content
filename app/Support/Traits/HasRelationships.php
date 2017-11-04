<?php

namespace App\Support\Traits;

use App\Database\Collections\ModelCollection;
use App\Database\Models\Resource;
use Illuminate\Database\Eloquent\Builder;
use ixavier\Libraries\Server\Core\RestfulRecord;
use ixavier\Libraries\Server\Http\ContentXURL;
use ixavier\Libraries\Server\Http\XURL;
use ixavier\Libraries\Server\RestfulRecords\App;

/**
 * Class HasRelationships keeps all functionality for Model's relations
 *
 * @package App\Database\Models
 */
trait HasRelationships
{
	/**
	 * @return ModelCollection
	 */
	public function getCollectionRelationships() {
		return $this->newCollection( $this->loadRelationships()->getRelations() );
	}

	/**
	 * Loads all relations inside this model.
	 * @param bool $force
	 * @return $this Chainnable method
	 */
	public function loadRelationships( $force = false ) {
		if ( $force || empty( $this->relations ) ) {
			$this->setRelations( $this->retrieveRelationships() );
		}

		return $this;
	}

	// @todo: Make sure we always return an array of Collections

	/**
	 * Load and get all relations for the model.
	 *
	 * @return ModelCollection[]
	 */
	protected function retrieveRelationships() {
		$relations = [];
		$relationQueries = $this->getRelationshipsQueryBuilder();
		// @todo: Try to optimize this query so that it only executes one query, not all of them individually
		// @todo: use graphql.org
		foreach ( $relationQueries as $attribute => $relationQueryInfo ) {
			// many relationships
			if ( is_array( $relationQueryInfo ) && is_array( $relationQueryInfo[ 0 ] ) ) {
				$cols = [];
				foreach ( $relationQueryInfo as $itemRelationKey => $itemRelationInfo ) {
					if ( !isset( $itemRelationInfo[ 1 ] ) ) {
						$itemRelationInfo[ 1 ] = null;
					}
					/** @var Builder $itemRelationQuery */
					list( $itemRelationQuery, $resultsCallback ) = $itemRelationInfo;
					$col = $itemRelationQuery->get();
					if ( isset( $resultsCallback ) ) {
						$resultsCallback( $col );
					}
					if ( $col ) {
						$cols[ $itemRelationKey ] = $col;
					}

				}
				$relations[ $attribute ] = $this->newCollection( $cols );
			}
			// one relationship
			else {
				list( $relationQuery, $resultsCallback ) = $relationQueryInfo;
				$col = $this->newCollection( $relationQuery->get()->getDictionary() );
				if ( isset( $resultsCallback ) ) {
					$resultsCallback( $col );
				}
				$relations[ $attribute ] = $col;
			}
		}

		return $relations;
	}

	/**
	 * Traverses through attributes and gets an array of queries to load relations
	 *
	 * @return Builder[]|RestfulRecord[]|Builder[][]|RestfulRecord[][]
	 */
	protected function getRelationshipsQueryBuilder() {
		$r = [];
		foreach ( $this->attributes as $attrKey => $attrValue ) {
			if ( is_string( $attrValue ) ) {
				$xUrl = XURL::create( $attrValue );
				if ( $xUrl->isValid() ) {
					$rel = $this->getRelationshipsQuery( $xUrl, $attrKey );
					if ( $rel ) {
						$r[ $attrKey ] = $rel;
					}
				}
			}
			else if ( is_array( $attrValue ) ) {
				foreach ( $attrValue as $relUrl ) {
					if ( is_string( $relUrl ) ) {
						$xUrl = XURL::create( $relUrl );
						if ( $xUrl->isValid() ) {
							$queryBuilder = $this->getRelationshipsQuery( $xUrl, $attrKey );
							// @todo: Create new query with union
							if ( $queryBuilder ) {
								if ( $queryBuilder ) {
									$r[ $attrKey ][] = $queryBuilder;
								}
							}
						}
					}
				}
			}
		}

		return $r;
	}

	/**
	 * Given a XURL, will get a Closure to get query to obtain relation
	 *
	 * @param XURL $xUrl
	 * @return Builder|RestfulRecord|array|null Returns null if no valid relation found
	 */
	protected function getRelationshipsQuery( XURL $xUrl ) {
		if ( $xUrl->service == config( 'app.serviceName' ) ) {
			return $this->getContentRelationshipsQuery( ...func_get_args() );
		}

		return null;
	}

	/**
	 * Given a ContentXURL, will get a Closure to get query to obtain relation
	 *
	 * @param ContentXURL $xUrl
	 * @return array Returns null if no valid relation found
	 *  First arg: Builder|RestfulRecord|null
	 *  Second arg: \Closure callback after getting results
	 */
	protected function getContentRelationshipsQuery( ContentXURL $xUrl ) {
		// @todo: check for same domain and so on
		$localService = true;

		// one-to-one relationship with resource
		if ( !empty( $xUrl->resource ) ) {

			// local service
			if ( $localService ) {
				$parentAttr = $xUrl->getRestfulRecordAttributes( true );
				// @todo: Find better way to get the app from a resource
				$parentAttr[ '__app' ] = $this->getApp();
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return [
					Resource::query( $parentAttr )->where( $attributes ),
					function( &$collection ) {
						$collection = $collection->first();
					}
				];
			}
			// external content service
			else {
				return [
					RestfulRecord::query( $xUrl ),
					function( &$collection ) {
						$collection = $collection->first();
					}
				];
			}
		}
		// many-to-many relationships stored on collection
		else if ( $xUrl->type == 'collection' ) {

		}
		// one-to-many relationships with resources of the given type under an app
		else if ( !empty( $xUrl->type ) ) {
			// local service
			if ( $localService ) {
				/** @var ContentXURL $xUrl */
				$parentAttr = $xUrl->getRestfulRecordAttributes( true );
				$parentAttr[ '__app' ] = $this->getApp();
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return [ Resource::query( $parentAttr )->where( $attributes ) ];
			}
			// external content service
			else {
				return [ RestfulRecord::query( $xUrl ) ];
			}
		}
		// one-to-one relationship with app
		else if ( !empty( $xUrl->app ) ) {
			// local service
			if ( $localService ) {
				/** @var ContentXURL $xUrl */
				$parentAttr = $xUrl->getRestfulRecordAttributes( true );
				$parentAttr[ '__app' ] = $this->getApp();
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return [
					App::query( $parentAttr )->where( $attributes ),
					function( &$collection ) {
						$collection = $collection->first();
					}
				];
			}
			// external content service
			else {
				return [
					App::query( $xUrl ),
					function( &$collection ) {
						$collection = $collection->first();
					}
				];
			}
		}
		// one-to-many relationships with apps
		else {
			// local service
			if ( $localService ) {
				/** @var ContentXURL $xUrl */
				$parentAttr = $xUrl->getRestfulRecordAttributes( true );
				$parentAttr[ '__app' ] = $this->getApp();
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return [ App::query( $parentAttr )->where( $attributes ) ];
			}
			// external content service
			else {
				return [ App::query( $xUrl ) ];
			}
		}

		return null;
	}
}
