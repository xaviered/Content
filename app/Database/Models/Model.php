<?php
namespace App\Database\Models;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiModelFilter;
use App\Database\Observers\ModelObserver;
use App\Http\Request;
use App\Support\Traits\SoftMacroable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use ixavier\Libraries\Core\RestfulRecord;
use ixavier\Libraries\Http\XUrl;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Model represents a MongoDB model
 *
 * @package App\Database\Models
 */
abstract class Model extends Moloquent
{
	use SoftDeletes;
	use SoftMacroable;
	use HasRelations;

	/** Key for created date */
	const CREATED_AT = 'createdOn';

	/** Key for last updated date */
	const UPDATED_AT = 'updatedOn';

	/** Key for deleted date. FYI, updatedBy will be the user that deleted too. */
	const DELETED_AT = 'deletedOn';

	/** @var bool Do not increment primary key */
	public $incrementing = false;

	/** @var array Casts these properties to a type of value */
	protected $casts = [
		'createdBy' => 'string',
		'createdOn' => 'datetime',
		'updatedBy' => 'string',
		'updatedOn' => 'datetime',
		'order' => 'integer'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deletedOn' ];

	/** @var string All primary keys will be slugs */
	protected $primaryKey = 'slug';

	/** @var array Don't guard any field and allow anything */
	protected $guarded = [];

	/** @var \Closure[] An array of dynamic relationship functions */
	protected $dynamicRelations = [];

	/** @var array|XUrl|string */
	private $__fixedAttributes;

	/**
	 * Even handler
	 */
	public static function boot() {
		self::observe( ModelObserver::class );
	}

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
		$this->__fixedAttributes = $attributes = RestfulRecord::fixAttributes( $attributes );
		$attributes = RestfulRecord::cleanAttributes( $attributes );

		return parent::fill( $attributes );
	}

	/**
	 * Gets fixed attributes, without being cleaned, so we can create more instances like these
	 *
	 * @return array|XUrl|string
	 */
	protected function getFixedAttributes() {
		return $this->__fixedAttributes;
	}

	// @todo: Remove from DB any attributes that are set to `null`
	/**
	 * Get the cleaned attributes that have been changed since last sync.
	 * Mainly used for cleaning attributes before updating
	 *
	 * @return array
	 */
	public function getDirty() {
		parent::getDirty();
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

		if ( $relationsDepth < intval( request( 'relations_max_depth', 1 ) ) ) {
			$relations = $this
					->getCollectionRelations()
					->toApiArray( $relationsDepth, true, false, true )[ 'data' ] ?? [];
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
}
