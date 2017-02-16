<?php
namespace App\Model;

/**
 * Class Resource holds resources for a particular App
 *
 * @package App\Model
 */
class Resource extends Model
{
	/** @var App $app */
	protected $app;

	/** @var string $type */
	public $type;

	/** @var string Default to content house database */
	protected $collection = 'contenthouse';
	protected $table = 'contenthouse';

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
	 * Resource constructor.
	 * @param array $attributes
	 * @param App $app Load resource from the given app
	 * @param string $type Type of resource
	 */
	public function __construct( array $attributes = [], App $app, $type ) {
		$this->app = $app;
		$this->type = $type;

		// fix connection
		$this->setConnection( $app->getResourcesConnectionName() );
		$this->setTable( $this->getResourceTableName() );
		// just in case, let's change the collection for mongodb too
		$this->collection = $this->getResourceTableName();

		// fix fields
		$this->fillable[] = 'type';

		parent::__construct( $attributes );
	}

	/**
	 * Custom table of where the records are stored based on its $app
	 *
	 * @return string
	 */
	public function getResourceTableName() {
		return !empty( $this->type ) ? 't_' . $this->type : 'contenthouse';
	}
}
