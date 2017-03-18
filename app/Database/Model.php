<?php
namespace App\Database;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiModelFilter;
use Illuminate\Support\Facades\Auth;
use ixavier\Libraries\Http\ContentXUrl;
use ixavier\Libraries\Http\XUrl;
use ixavier\Libraries\RestfulRecords\Resource;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Model represents a MongoDB model
 * @package App\Model
 */
abstract class Model extends Moloquent
{
	use SoftDeletes;

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
				'updatedOn' => $time,
				'order' => 1
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

		return parent::fill( $attributes );
	}

	/**
	 * Gets index URL of current model
	 *
	 * @param string $action Route action to get
	 * @param array $parameters
	 * @return string
	 */
	public function uri( $action = 'index', $parameters = null ) {
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
	 * @param  array $models
	 * @return ModelCollection
	 */
	public function newCollection( array $models = [] ) {
		$col = new ModelCollection( $models );
		$col->setRootModel( $this );

		return $col;
	}

	/**
	 * API array representation of this model
	 * @return array
	 */
	public function toApiArray() {
		// try loading relationships at the same time as:
		// i.e. $this->load('author', 'publisher');
		$modelArray = [];
		$modelArray[ 'data' ] = $this->attributesToArray();

		// @todo: Refactor so that there is a FilterFactory instead of using Request for that
		// filter out fields based on request params
		request()
			->addFilter( ApiModelFilter::class )
			->filter( $modelArray[ 'data' ] )
		;

		$modelArray[ 'relationships' ] = $this->getCollectionRelations()->toApiArray( true, true );
		unset( $modelArray[ 'relationships' ][ 'count' ] );
		$modelArray[ 'links' ][ 'self' ] = $this->uri( 'show' );

		return $modelArray;
	}

	/**
	 * @return ModelCollection
	 */
	public function getCollectionRelations() {
		return $this->newCollection( $this->getRelations() );
	}

	/**
	 * Get all the loaded relations for the instance.
	 *
	 * @return array
	 */
	public function getRelations() {
		// @todo: fix relationships
//		$rel = $this->prepareRelationships();
//		$this->load( $rel );

		$r = parent::getRelations();

		return $r;
	}

	/**
	 * Gets the key where relationships are stored for the given $key
	 *
	 * @param string $key
	 * @return string
	 */
	protected static function getRelationshipKey( $key ) {
		return '__r_' . $key;
	}

	// @todo: fix relationships
	/**
	 * Prepares relationships from string attributes that may refer to a resource
	 *
	 */
	public function prepareRelationships() {
		$r = [];
		foreach ( $this->attributes as $attrKey => $attrValue ) {
			if ( is_string( $attrValue ) ) {
				$xUrl = XUrl::create( $attrValue );
				$relKey = static::getRelationshipKey( $attrKey );

				if ( $xUrl->isValid() && $xUrl->name == 'content' ) {
					/** @var $xUrl ContentXUrl */
					// one-to-one relationship with resource
					if ( !empty( $xUrl->resource ) ) {
//						dd([$attrKey, $xUrl, RestfulRecord::parseResourceSlug($xUrl->requestedResource)]);
						$r[] = $attrKey;
						$model = $this;
						\Jenssegers\Mongodb\Eloquent\Builder::macro( $attrKey, function() use ( $model, $relKey ) {
//							dd([get_class($this), func_get_args()]);
							return $model->hasOne( Resource::class, 'slug', $relKey );
						} );
//						dd( $this->{$attrKey}() );
					}
					// many-to-many relationships stored on collection
					else if ( $xUrl->type == 'collection' ) {

					}
					// one-to-many relationships with resources of the given type under an app
					else if ( !empty( $xUrl->type ) ) {

					}
					// one-to-one relationship with app
					else if ( !empty( $xUrl->app ) ) {

					}
					// no relationship
					else {

					}
				}
			}
		}

//		dd( [ $r, $this ] );

		return $r;
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
