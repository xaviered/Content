<?php
namespace App\Database\Core;

use App\Database\Observers\ModelObserver;
use App\Support\Traits\HasRelations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

/**
 * Class BaseModel is the lowest form of a database record
 *
 * @internal int $id
 * @internal string $slug
 * @internal string $title
 * @internal string $type
 *
 * @package App\Database\Core
 */
abstract class BaseModel extends Moloquent
{
	use SoftDeletes;
	use HasRelations;

	/** Key for created date */
	const CREATED_AT = 'createdOn';

	/** Key for deleted date. FYI, updatedBy will be the user that deleted too. */
	const DELETED_AT = 'deletedOn';

	/** Key for last updated date */
	const UPDATED_AT = 'updatedOn';

	/** @var array Casts these properties to a type of value */
	protected $casts = [
		'createdBy' => 'string',
		'createdOn' => 'datetime',
		'updatedBy' => 'string',
		'updatedOn' => 'datetime',
	];

    /** @var array Validation rules for the current model */
    protected $validationRules = [];

    /**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deletedOn' ];

	/** @var \Closure[] An array of dynamic relation functions */
	protected $dynamicRelations = [];

	/** @var array Don't guard any field and allow anything */
	protected $guarded = [ 'id' ];

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
	 * Even handler
	 */
	public static function boot() {
		self::observe( ModelObserver::class );
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
     * An array of validation rules to use for this model's fields
     * @return array
     */
    public function getValidationRules() {
        return $this->validationRules ?? [];
    }

	/**
	 * @param array $validationRules
	 */
	public function setValidationRules( array $validationRules ) {
		$this->validationRules = $validationRules;
	}
}
