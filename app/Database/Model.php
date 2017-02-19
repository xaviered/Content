<?php
namespace App\Database;

use App\Database\Collections\ModelCollection;
use App\Database\Filters\ApiModelFilter;
use Illuminate\Support\Facades\Auth;
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

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deletedOn' ];

	/** @var string All primary keys will be slugs */
	protected $primaryKey = 'slug';

	/** @var array Allowed mass-fillable fields */
	protected $fillable = [
		'slug',
		'title',
		'createdBy',
		'createdOn',
		'deletedOn',
		'order',
		'type',
		'updatedBy',
		'updatedOn',
	];

	/**
	 * Creates new model with default values if they are not present on $attributes
	 *
	 * @param array $attributes
	 * @return static
	 */
	public static function create( array $attributes = [] ) {
		$time = time();
		$userId = Auth::user() ? Auth::user()->id : 1;
		$attributes = array_merge( [
			'createdBy' => $userId,
			'createdOn' => $time,
			'updatedBy' => $userId,
			'updatedOn' => $time,
			'order' => 1,
		], $attributes );

		return new static( $attributes );
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
		$modelArray = [];
		$modelArray[ 'data' ] = $this->attributesToArray();

		// @todo: Refactor so that there is a FilterFactory instead of using Request for that
		// filter out fields based on request params
		request()
			->addFilter( ApiModelFilter::class )
			->filter( $modelArray[ 'data' ] )
		;

		$relationships = $this->getCollectionRelations();
		if ( count( $relationships ) ) {
			$modelArray[ 'relationships' ] = $relationships->toApiArray( true );
		}

		$modelArray[ 'links' ][ 'self' ] = $this->uri( 'show', [ 'app' => $this ] );

		return $modelArray;
	}

	/**
	 * @return ModelCollection
	 */
	public function getCollectionRelations() {
		return new ModelCollection( $this->getRelations() );
	}

	/**
	 * Perform the actual delete query on this model instance.
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
