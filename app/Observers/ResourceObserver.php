<?php
namespace App\Observers;

use App\Database\Models\App;
use Illuminate\Support\Facades\App as LaravelResource;

/**
 * Class ResourceObserver handle events for Resource model
 *
 * @package App\Observers
 */
class ResourceObserver
{
	use ModelObserverTrait;

	/**
	 * Called before loading object from data-store
	 *
	 * @param App $app
	 */
	public function load( App $app ) {
		$this->fixDatabaseConfigs( $app );
	}

	/**
	 * Adds database configs based on $app slug
	 *
	 * @param App $app
	 */
	protected function fixDatabaseConfigs( App $app ) {
		$config = LaravelResource::make( 'config' );
		$dbName = $app->getResourcesConnectionName();

		if ( !$config->get( 'database.connections.' . $dbName ) ) {
			$default = $app->getConnection();
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
					$optionValue = $default->getConfig( $optionName );
				}
				$config->set( 'database.connections.' . $dbName . '.' . $optionName, $optionValue );
			}
		}
	}
}
