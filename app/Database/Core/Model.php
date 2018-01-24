<?php
namespace App\Database\Core;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiModelFilter;
use App\Http\Request;
use Illuminate\Support\Collection;
use ixavier\Libraries\Server\Core\RestfulRecord;
use ixavier\Libraries\Server\Http\XURL;

/**
 * Class Model has a one-to-one relation between a request on the API to a record in the DB
 *
 * @internal int $id
 * @internal string $slug
 * @internal string $title
 * @internal string $type
 *
 * @package App\Database\Core
 */
abstract class Model extends BaseModel
{
	/** @var bool Do not increment primary key */
	public $incrementing = false;

	/** @var string All primary keys will be slugs */
	protected $primaryKey = 'slug';

    /** @var array|XURL|string */
    private $__fixedAttributes;

    /**
	 * API array representation of this model
	 *
	 * @param int $relationsDepth Current depth of relations loaded. Default = 1
	 * @param bool $hideSelfLinkQuery Don't add query info to self link for Models
	 * @return array
	 */
	public function toApiArray( $relationsDepth = 0, $hideSelfLinkQuery = false ) {
		// load relations
		$relations = [];

		if ( !request( 'ignore_relations' ) ) {
			if ( $relationsDepth < intval( request( 'relations_max_depth', 1 ) ) ) {
				$relations = $this
						->getCollectionRelations()
						->toApiArray( $relationsDepth, true, true, true )[ 'data' ] ?? [];
			}
		}

		$r = Request::create( $this->uri( 'show' ) );

		$modelArray = [
			'data' => $this->attributesToArray(),
			'relations' => $relations,
			'links' => [
				'self' => $hideSelfLinkQuery ? $r->url() : $r->fullUrl()
			]
		];

		// @todo: Refactor so that there is a FilterFactory instead of using Request for that
		// filter out fields based on request params
		request()
			->addFilter( ApiModelFilter::class )
			->filter( $modelArray[ 'data' ] )
		;

		return $modelArray;
	}

	/**
	 * Create a new Eloquent Collection instance.
	 *
	 * @param  ModelCollection|Collection|array $models
	 * @return ModelCollection
	 */
	public function newCollection( array $models = [] ) {
		$col = new ModelCollection( $models );
		$col->setRootModel( $this );

		return $col;
	}

	// @todo: Remove from DB any attributes that are set to `null`
	/**
	 * Fill the model with an array of attributes.
	 *
	 * @param  array $attributes
	 * @return self
	 *
	 * @throws \Illuminate\Database\Eloquent\MassAssignmentException
	 */
	public function fill( array $attributes ) {
		$attributes = $this->setFixedAttributes( $attributes );
		$attributes = RestfulRecord::cleanAttributes( $attributes );

		parent::fill( $attributes );

		return $this;
	}

	/**
	 * Set runtime vars
	 * @param array $attributes
	 * @return array
	 */
	protected function setFixedAttributes( $attributes ) {
		$attributes = RestfulRecord::fixAttributes( $attributes );

		// save only special attributes
		$specialAttributes = array_combine( RestfulRecord::$specialAttributes, RestfulRecord::$specialAttributes );
		$this->__fixedAttributes = array_intersect_key( $attributes, $specialAttributes );

		return $attributes;
	}

    /**
     * Gets fixed attributes, without being cleaned, so we can create more instances like these
     *
     * @return array|XURL|string
     */
    protected function getFixedAttributes() {
        return $this->__fixedAttributes;
    }

	/**
	 * Gets index URL of current model
	 *
	 * @param string $action Route action to get
	 * @param array $parameters
	 * @return string
	 */
	public function uri( $action = 'index', $parameters = [] ) {
		if ( empty( $parameters ) ) {
			$parameters = request()->query->all();
		}

		switch ( $action ) {
			case 'show':
				unset( $parameters[ 'page' ] );
				unset( $parameters[ 'page_size' ] );
				break;
		}

		// get url based on model
		return url()->route( static::ROUTE_NAME . '.' . $action, $parameters );
	}

	/**
	 * An array of validation rules to use for this model's fields
	 * @return array
	 */
	public function getValidationRules() {
		return array_merge(
			[
				'slug' => 'required|unique:' . $this->getTable() . ',slug|max:255',
				'title' => 'required|unique:' . $this->getTable() . ',title|max:255',
				'type' => 'required|max:65',
			],
			parent::getValidationRules()
		);
	}
}
