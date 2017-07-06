<?php
namespace App\Database\Models;

use App\Database\Core\Model;
use Illuminate\Support\Facades\App as LaravelApp;

/**
 * Class Resource is the representation of a basic working Model tied to a particular App
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
	protected $collection = 'content';

	/** @var string Default to content house database in regular DBs */
	protected $table = 'content';

	/**
	 * Perform tasks once for all App models
	 */
//	public static function boot() {
//		self::observe( ResourceObserver::class );
//	}

	/**
	 * Get the custom connection for this model.
	 *
	 * @return string
	 */
	public function getConnectionName() {
		$app = $this->getApp();
		if ( $app ) {
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
		$this->collection = !empty( $this->type ) ? 't_' . $this->type : 'content';

		return $this->collection;
	}

	/**
	 * @return App|\Illuminate\Database\Eloquent\Builder
	 */
	public function getApp() {
		return $this->app;
	}

	/**
	 * @param App|string $app App or slug of app
	 * @return $this Chainable method.
	 */
	public function setApp( $app ) {
		if ( is_string( $app ) ) {
			$this->app = App::query()->find( $app ) ?? $this->app;
		}
		else {
			$this->app = $app;
		}

		return $this;
	}

	/**
	 * Set runtime vars from $attributes
	 *
	 * @param array $attributes
	 * @return array
	 */
	protected function setFixedAttributes( $attributes ) {
		if ( !empty( $attributes[ '__app' ] ) ) {
			$this->setApp( $attributes[ '__app' ] );

			if ( $attributes[ '__app' ] instanceof Model ) {
				$attributes[ '__app' ] = $attributes[ '__app' ]->getAttributes();
			}
		}

		$return = parent::setFixedAttributes( $attributes );

		$this->setConnection( $this->getConnectionName() );

		return $return;
	}

	/**
	 * Gets the full path to this requested resource
	 * @return string
	 */
	public function getRequestedResourceSlug() {
		$parts = [];
		$app = $this->getApp();
		if ( $app ) {
			$parts[] = $app->slug;
		}
		if ( !empty( $this->type ) ) {
			$parts[] = $this->type;
		}
		if ( !empty( $this->slug ) ) {
			$parts[] = $this->slug;
		}

		return $parts ? '/' . implode( '/', $parts ) : '';
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
