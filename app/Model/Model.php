<?php
namespace App\Model;

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

	/** @var bool Do not increment primary key */
	public $incrementing = false;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [ 'deleted_at' ];

	/** @var string All primary keys will be slugs */
	protected $primaryKey = 'slug';

	/** @var array Allowed mass-fillable fields */
	protected $fillable = [
		'slug',
		'title',
		'createdOn',
		'createdBy',
		'status',
		'order',
		'updatedOn',
		'updatedBy'
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
			'updatedBy' => $userId,
			'createdOn' => $time,
			'updatedOn' => $time,
			'order' => 1,
			'status' => 'active'
		], $attributes );

		return static::newInstance( $attributes );
	}
}
