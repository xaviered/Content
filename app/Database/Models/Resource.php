<?php
namespace App\Database\Models;

use App\Database\Model;
use App\Observers\ResourceObserver;
use Illuminate\Support\Facades\App as LaravelApp;
use ixavier\Libraries\Core\RestfulRecord;

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

	/** @var string Default to content house collection in MongoDB */
	protected $collection = 'contenthouse';

	/** @var string Default to content house database in regular DBs */
	protected $table = 'contenthouse';

	/**
	 * Resource constructor.
	 * @param array $attributes
	 * @param App|string $app Load resource from the given app (or slug of app)
	 */
	public function __construct( $attributes = [] ) {
		// precedence for values: slug, attributes, else
		$defaults = [
			'app' => $attributes[ '__app' ] ?? null,
			'type' => $attributes[ 'type' ] ?? 'page'
		];
		$matches = RestfulRecord::parseResourceSlug( $attributes[ 'slug' ] ?? '', $defaults );
		list( $app, $attributes[ 'type' ], $attributes[ 'slug' ] ) = $matches;

		// do not allow this, as this will be dynamic
		unset( $attributes[ '__app' ] );

		// load app
		if ( is_string( $app ) && !empty( $app ) ) {
			$app = App::query()->where( [ 'slug' => $app ] )->firstOrFail();
		}

		$this->app = $app;

		parent::__construct( $attributes );
	}

	/**
	 * Perform tasks once for all App models
	 */
	public static function boot() {
		self::observe( ResourceObserver::class );
	}

	/**
	 * Get the custom connection for this model.
	 *
	 * @return string
	 */
	public function getConnectionName() {
		$app = $this->getApp();
		$config = LaravelApp::make( 'config' );
		$dbName = $app->getResourcesConnectionName();

		// add configs for custom connection
		$conn = $config->get( 'database.connections.' . $dbName );
		if ( !isset( $conn ) ) {
			$appConn = $app->getConnection();
			$newConnection = [
				'driver' => null,
				'host' => $config->get( 'database.connections.hubs.' . $app->hub ),
				'port' => null,
				'database' => $dbName,
				'username' => null,
				'password' => null,
				'options.db' => null
			];
			foreach ( $newConnection as $optionName => $optionValue ) {
				if ( empty( $optionValue ) ) {
					$optionValue = $appConn->getConfig( $optionName );
				}
				$config->set( 'database.connections.' . $dbName . '.' . $optionName, $optionValue );
			}

			$this->setConnection( $dbName );
		}

		return $this->connection;
	}

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable() {
		$this->getConnection();
		$this->collection = !empty( $this->type ) ? 't_' . $this->type : 'contenthouse';

		return $this->collection;
	}

	/**
	 * @return App|\Illuminate\Database\Eloquent\Builder
	 */
	public function getApp() {
		return $this->app;
	}

	/**
	 * @param App $app
	 */
	public function setApp( App $app ) {
		$this->app = $app;
	}

	/**
	 * Create a new instance of the given model.
	 * Overwrites parent function to add app and type.
	 *
	 * @param  array $attributes
	 * @param  bool $exists
	 * @return static
	 */
	public function newInstance( $attributes = [], $exists = false ) {
		$attributes = array_merge(
			[
				'type' => $this->type ?? null,
				'__app' => $this->app ?? null
			],
			$attributes
		);
		
		// This method just provides a convenient way for us to generate fresh model
		// instances of this current model. It is particularly useful during the
		// hydration of new objects via the Eloquent query builder instances.
		$model = new static( (array)$attributes );

		$model->exists = $exists;

		$model->setConnection(
			$this->getConnectionName()
		);

		return $model;
	}

	/**
	 * Begin querying the model.
	 * Overwrites parent function to add app and type.
	 *
	 * @param array $attributes Pass in type and app for resource
	 * @return \Illuminate\Database\Eloquent\Builder|self;
	 */
	public static function query( ...$attributes ) {
		// @todo: This is returning deleted items by default; disable this
		return ( new static( ...$attributes ) )->newQuery();
	}

	/**
	 * Gets URL of current model
	 *
	 * @param string $action Route action to get
	 * @param array $parameters
	 * @return string
	 */
	public function uri( $action = 'index', $parameters = null ) {
		switch ( $action ) {
			case 'show':
				$parameters = array_merge(
					is_array( $parameters ) ? $parameters : request()->query->all(),
					[ 'app' => $this->getApp()->slug, 'type' => $this->type, 'resource' => $this ]
				);
				break;

			case 'index':
				$parameters = array_merge(
					is_array( $parameters ) ? $parameters : request()->query->all(),
					[ 'app' => $this->getApp()->slug, 'type' => $this->type ]
				);
				break;
		}

		return parent::uri( $action, $parameters );
	}
}
