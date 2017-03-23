<?php
namespace App\Database\Models;

use App\Database\Collections\ModelCollection;
use Illuminate\Database\Eloquent\Builder;
use ixavier\Libraries\Core\RestfulRecord;
use ixavier\Libraries\Http\ContentXUrl;
use ixavier\Libraries\Http\XUrl;
use ixavier\Libraries\RestfulRecords\App;

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
			$this->setRelations( $this->retrieveRelations() );
		}

		return $this;
	}

	// @todo: Make sure we always return an array of Collections
	/**
	 * Load and get all relations for the model.
	 *
	 * @return ModelCollection[]
	 */
	protected function retrieveRelations() {
		$relations = [];
		$relationQueries = $this->getRelationshipQueryBuilder();
		// @todo: Try to optimize this query so that it only executes one query, not all of them individually
		foreach ( $relationQueries as $attribute => $relationQuery ) {
			// many relationships
			if ( is_array( $relationQuery ) ) {
				$cols = [];
				foreach ( $relationQuery as $itemRelationKey => $itemRelationQuery ) {
					/** @var Builder $itemRelationQuery */
//					$cols[ $itemRelationKey ] = $itemRelationQuery->get( [ 'ignore_relations' => 1 ] );
					$cols[ $itemRelationKey ] = $itemRelationQuery->get();
				}
				$relations[ $attribute ] = $this->newCollection( $cols );
			}
			// one relationship
			else {
//				$relations[ $attribute ] = $this->newCollection( $relationQuery->get( [ 'ignore_relations' => 1 ] )->getDictionary() );
				$relations[ $attribute ] = $this->newCollection( $relationQuery->get()->getDictionary() );
			}
		}

		return $relations;
	}

	/**
	 * Traverses through attributes and gets an array of queries to load relations
	 *
	 * @return Builder[]|RestfulRecord[]|Builder[][]|RestfulRecord[][]
	 */
	protected function getRelationshipQueryBuilder() {
		$r = [];
		foreach ( $this->attributes as $attrKey => $attrValue ) {
			if ( is_string( $attrValue ) ) {
				$xUrl = XUrl::create( $attrValue );
				if ( $xUrl->isValid() ) {
					$rel = $this->getRelationQuery( $xUrl, $attrKey );
					if ( $rel ) {
						$r[ $attrKey ] = $rel;
					}
				}
			}
			else if ( is_array( $attrValue ) ) {
				foreach ( $attrValue as $relUrl ) {
					if ( is_string( $relUrl ) ) {
						$xUrl = XUrl::create( $relUrl );
						if ( $xUrl->isValid() ) {
							$queryBuilder = $this->getRelationQuery( $xUrl, $attrKey );
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
	 * Given a XUrl, will get a Closure to get query to obtain relation
	 *
	 * @param XUrl $xUrl
	 * @return Builder|RestfulRecord|null Returns null if no valid relation found
	 */
	protected function getRelationQuery( XUrl $xUrl ) {
		if ( $xUrl->service == config( 'app.serviceName' ) ) {
			return $this->getContentRelationQuery( ...func_get_args() );
		}

		return null;
	}

	/**
	 * Given a ContentXUrl, will get a Closure to get query to obtain relation
	 *
	 * @param ContentXUrl $xUrl
	 * @return Builder|RestfulRecord|null Returns null if no valid relation found
	 */
	protected function getContentRelationQuery( ContentXUrl $xUrl ) {
		// @todo: check for same domain and so on
		$localService = true;

		// one-to-one relationship with resource
		if ( !empty( $xUrl->resource ) ) {

			// local service
			if ( $localService ) {
				$parentAttr = $xUrl->getRestfulRecordAttributes(true);
				// @todo: Find better way to get the app from a resource
				$parentAttr[ '__app' ] = method_exists( $this, 'getApp' ) ? $this->getApp() : $this;
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return Resource::query( $parentAttr )->where( $attributes );
			}
			// external content service
			else {
				return RestfulRecord::query( $xUrl );
			}
		}
		// many-to-many relationships stored on collection
		else if ( $xUrl->type == 'collection' ) {

		}
		// one-to-many relationships with resources of the given type under an app
		else if ( !empty( $xUrl->type ) ) {
			// local service
			if ( $localService ) {
				/** @var ContentXUrl $xUrl */
				$parentAttr = $xUrl->getRestfulRecordAttributes(true);
				$parentAttr[ '__app' ] = $this;
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return Resource::query( $parentAttr )->where( $attributes );
			}
			else {
				return RestfulRecord::query( $xUrl );
			}
		}
		// one-to-one relationship with app
		else if ( !empty( $xUrl->app ) ) {
			// local service
			if ( $localService ) {
				/** @var ContentXUrl $xUrl */
				$parentAttr = $xUrl->getRestfulRecordAttributes(true);
				$parentAttr[ '__app' ] = $this;
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return App::query( $parentAttr )->where( $attributes );
			}
			else {
				return App::query( $xUrl );
			}
		}
		// one-to-many relationships with apps
		else {
			// local service
			if ( $localService ) {
				/** @var ContentXUrl $xUrl */
				$parentAttr = $xUrl->getRestfulRecordAttributes(true);
				$parentAttr[ '__app' ] = $this;
				$attributes = RestfulRecord::cleanAttributes( $parentAttr );
				$params = $xUrl->getQueryParameterBag();
				if ( $params->count() ) {
					$attributes = array_merge( $attributes, $params->all() );
				}

				return App::query( $parentAttr )->where( $attributes );
			}
			else {
				return App::query( $xUrl );
			}
		}

		return null;
	}
}
