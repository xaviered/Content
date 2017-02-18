<?php
namespace App\Database\Models;

use App\Database\Model;
use App\Observers\ResourceObserver;

/**
 * Class Resource holds resources for a particular App
 *
 * @package App\Model
 */
class Resource extends Model
{
	/** Route name */
	const ROUTE_NAME = 'resource';

	/** @var App $app */
	protected $app;

	/** @var string Default to content house database */
	protected $collection = 'contenthouse';
	protected $table = 'contenthouse';

	/**
	 * Resource constructor.
	 * @param array $attributes
	 * @param App $app Load resource from the given app
	 */
	public function __construct( array $attributes = [], App $app ) {
		$this->app = $app;

		// fix connection
		$this->setConnection( $app->getResourcesConnectionName() );
		$this->setTable( $this->getResourceTableName() );
		// just in case, let's change the collection for mongodb too
		$this->collection = $this->getResourceTableName();

		parent::__construct( $attributes );
	}

	/**
	 * Perform tasks once for all Resource models
	 */
	public static function boot() {
		self::observe( ResourceObserver::class );
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
