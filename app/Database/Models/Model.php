<?php
namespace App\Database\Models;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiModelFilter;
use App\Database\Observers\ModelObserver;
use App\Support\Traits\SoftMacroable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use ixavier\Libraries\Core\RestfulRecord;
use ixavier\Libraries\Http\XUrl;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpFoundation\ParameterBag;

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
		$this->__fixedAttributes = RestfulRecord::fixAttributes( $attributes );

		return parent::fill( RestfulRecord::cleanAttributes( $this->__fixedAttributes ) );
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
		return parent::getDirty();
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
	 * @param bool $withKeys Show keys for Collections
	 * @param bool $hideLink Hide self link in Models
	 * @param bool $hideSelfLinkQuery Don't add query info to self link for Models
	 * @return array
	 */
	public function toApiArray( $withKeys = true, $hideLink = false, $hideSelfLinkQuery = false ) {
		// load relations
		$relations = null;
		if ( !request( 'ignoreRelations' ) ) {
			if ( request( 'separateRelations' ) ) {
				$relations = $this->getCollectionRelations()->toApiArray( true, true );
				unset( $relations[ 'count' ] );
			}
			else {
				$this->mountRelations();
			}
		}

		$modelArray = [
			'data' => $this->attributesToArray(),
		];

		array_walk_recursive( $modelArray, function( &$item, $key ) {
			if ( is_object( $item ) ) {
				if ( $item instanceof Collection ) {
					$item = $item->toApiArray( true, true, true )[ 'data' ] ?? [];
				}
				else if ( $item instanceof self ) {
					$item = $item->toApiArray( true, true, true );
				}
			}
		} );

		if ( request( 'separateRelations' ) ) {
			$modelArray[ 'relations' ] = $relations[ 'data' ] ?? new \stdClass;
		}

		// don't show links
//		if ( !$hideLink ) {
		// don't include query in URI
		$oldQuery = request()->query;
		if ( $hideSelfLinkQuery ) {
			request()->query = new ParameterBag();
		}
		$modelArray[ 'links' ][ 'self' ] = $this->uri( 'show' );
		if ( $hideSelfLinkQuery ) {
			request()->query = $oldQuery;
		}
//		}

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
