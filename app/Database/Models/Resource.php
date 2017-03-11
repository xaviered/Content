<?php
namespace App\Database\Models;

use App\Database\Model;
use App\Observers\ResourceObserver;
use Illuminate\Support\Facades\App as LaravelApp;

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
	 * @param App|string $app Load resource from the given app (or slug of app)
	 */
	public function __construct( array $attributes = [], $app = null ) {
		if ( is_string( $app ) && !empty( $app ) ) {
			$appSlug = $app;
			$app = null;
		}
		else {
			// @todo: do not use segment, use `app` slug or something
			$appSlug = $attributes[ 'app' ] ?? request()->segment( 3 );
		}

		if ( !$app ) {
			if ( !empty( $appSlug ) ) {
				$app = App::query()->where( [ 'slug' => $appSlug ] )->firstOrFail();
			}
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
	 * Begin querying the model.
	 *
	 * @param string $type
	 * @param App|string $app App or slug of app
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function queryFromType( $type, $app = null ) {
		$r = ( new static( [ 'type' => $type ], $app ) );

		// @todo: This is returning delete items by default; disable this
		return $r->newQuery();
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
