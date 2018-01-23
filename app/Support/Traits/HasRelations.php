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
 * Class HasRelations keeps all functionality for Model's relations
 *
 * @package App\Database\Models
 */
trait HasRelations
{
	/**
	 * @return ModelCollection
	 */
	public function getCollectionRelations() {
		return $this->newCollection( $this->loadRelations()->getRelations() );
	}

	/**
	 * Loads all relations inside this model.
	 * @param bool $force
	 * @return $this Chainnable method
	 */
	public function loadRelations( $force = false ) {
		if ( $force || empty( $this->relations ) ) {
			$this->setRelations( $this->retrieverelations() );
		}

		return $this;
	}

    /**
     * Gets relation object on this record.
     *
     * @param string $relationKey
     * @param string $returnInCollectionByKey Will wrap output in ModelCollection with the given column as its main key.
     * @return ModelCollection|RestfulRecord Empty ModelCollection if no relation found. Use `$this->hasRelation()` to truly find out if there is a relation.
     */
    public function getRelation( $relationKey, $returnInCollectionByKey = null ) {
        /** @var ModelCollection $relation */
        $relation = $this->getRelations()->get( $relationKey );
        if ( $relation && $returnInCollectionByKey ) {
            $relation = $relation->keyBy( $returnInCollectionByKey );
        }
        else if ( empty( $relation ) ) {
            $relation = new ModelCollection();
        }

        return $relation;
    }

    /**
     * Checks if there is an existing relation
     *
     * @param string $relationKey
     * @return bool
     */
    public function hasRelation( $relationKey ) {
        return $this->getCollectionRelations()->offsetExists( $relationKey );
    }

	// @todo: Make sure we always return an array of Collections

	/**
	 * Load and get all relations for the model.
	 *
	 * @return ModelCollection[]
	 */
	protected function retrieverelations() {
		$relations = [];
		$relationQueries = $this->getrelationsQueryBuilder();
		// @todo: Try to optimize this query so that it only executes one query, not all of them individually
		// @todo: use graphql.org
		foreach ( $relationQueries as $attribute => $relationQueryInfo ) {
			// many relations
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
			// one relation
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
	protected function getrelationsQueryBuilder() {
		$r = [];
		foreach ( $this->attributes as $attrKey => $attrValue ) {
			if ( is_string( $attrValue ) ) {
				$xUrl = XURL::create( $attrValue );
				if ( $xUrl->isValid() ) {
					$rel = $this->getrelationsQuery( $xUrl, $attrKey );
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
							$queryBuilder = $this->getrelationsQuery( $xUrl, $attrKey );
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
	protected function getrelationsQuery( XURL $xUrl ) {
		if ( $xUrl->service == config( 'app.serviceName' ) ) {
			return $this->getContentrelationsQuery( ...func_get_args() );
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
	protected function getContentrelationsQuery( ContentXURL $xUrl ) {
		// @todo: check for same domain and so on
		$localService = true;

		// one-to-one relation with resource
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
		// many-to-many relations stored on collection
		else if ( $xUrl->type == 'collection' ) {

		}
		// one-to-many relations with resources of the given type under an app
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
		// one-to-one relation with app
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
		// one-to-many relations with apps
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

    /**
     * @inheritdoc
     */
    public function __call($method, $parameters)
    {
        // load relation
        if ($this->hasRelation($method)) {
            return $this->getRelation($method, ...$parameters);
        }

        return parent::__call($method, $parameters);
    }
}
