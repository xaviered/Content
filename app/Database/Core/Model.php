<?php
namespace App\Database\Core;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiModelFilter;
use App\Database\Observers\ModelObserver;
use App\Http\Request;
use App\Support\Traits\HasRelationships;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use ixavier\Libraries\Core\RestfulRecord;
use ixavier\Libraries\Http\XURL;

/**
 * Class Model has a one-to-one relationship between a request on the API to a record in the DB
 *
 * @internal int $id
 * @internal string $slug
 * @internal string $title
 * @internal string $type
 *
 * @package App\Database\Core
 */
abstract class Model extends Moloquent
{
	use SoftDeletes;
	use HasRelationships;

	/** Key for created date */
	const CREATED_AT = 'createdOn';

	/** Key for deleted date. FYI, updatedBy will be the user that deleted too. */
	const DELETED_AT = 'deletedOn';

	/** Key for last updated date */
	const UPDATED_AT = 'updatedOn';

	/** @var bool Do not increment primary key */
	public $incrementing = false;

	/** @var array Casts these properties to a type of value */
	protected $casts = [
		'createdBy' => 'string',
		'createdOn' => 'datetime',
		'updatedBy' => 'string',
		'updatedOn' => 'datetime',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deletedOn' ];

	/** @var \Closure[] An array of dynamic relationship functions */
	protected $dynamicRelations = [];

	/** @var array Don't guard any field and allow anything */
	protected $guarded = [ 'id' ];

	/** @var string All primary keys will be slugs */
	protected $primaryKey = 'slug';

	/** @var array Validation rules for the current model */
	protected $validationRules = [];

	/** @var array|XURL|string */
	private $__fixedAttributes;

	/**
	 * Creates new model with default values if they are not present on $attributes
	 *
	 * @param array $attributes
	 * @return static
	 */
	public static function create( array $attributes = [] ) {
		$time = time();
		$userId = Auth::user() ? Auth::user()->id : 1;

		$attributes = array_merge(
			$attributes,
			[
				'createdBy' => $userId,
				'createdOn' => $time,
				'updatedBy' => $userId,
				'updatedOn' => $time
			]
		);

		return new static( $attributes );
	}

	/**
	 * API array representation of this model
	 *
	 * @param int $relationshipsDepth Current depth of relations loaded. Default = 1
	 * @param bool $hideSelfLinkQuery Don't add query info to self link for Models
	 * @return array
	 */
	public function toApiArray( $relationshipsDepth = 0, $hideSelfLinkQuery = false ) {
		// load relations
		$relationships = [];

		if ( !request( 'ignore_relations' ) ) {
			if ( $relationshipsDepth < intval( request( 'relations_max_depth', 1 ) ) ) {
				$relationships = $this
						->getCollectionRelationships()
						->toApiArray( $relationshipsDepth, true, true, true )[ 'data' ] ?? [];
			}
		}

		$r = Request::create( $this->uri( 'show' ) );

		$modelArray = [
			'data' => $this->attributesToArray(),
			'relationships' => $relationships,
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
	 * Even handler
	 */
	public static function boot() {
		self::observe( ModelObserver::class );
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

		return parent::fill( $attributes );
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

	// @todo: Remove from DB any attributes that are set to `null`
	/**
	 * Get the cleaned attributes that have been changed since last sync.
	 * Mainly used for cleaning attributes before updating
	 *
	 * @return array
	 */
	public function getDirty() {
		return parent::getDirty();
	}

	/**
	 * Marks this model as deleted
	 *
	 * @return void
	 */
	protected function runSoftDelete() {
		$query = $this->newQueryWithoutScopes()->where( $this->getKeyName(), $this->getKey() );

		$this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();

		$updateQuery = [
			'slug' => $this->slug .= '_deleted_' . $time,
			$this->getDeletedAtColumn() => $this->fromDateTime( $time )
		];
		$query->update( $updateQuery );
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
			$this->validationRules ?? []
		);
	}

	/**
	 * @param array $validationRules
	 */
	public function setValidationRules( array $validationRules ) {
		$this->validationRules = $validationRules;
	}


	/**
	 * Gets fixed attributes, without being cleaned, so we can create more instances like these
	 *
	 * @return array|XURL|string
	 */
	protected function getFixedAttributes() {
		return $this->__fixedAttributes;
	}
}
