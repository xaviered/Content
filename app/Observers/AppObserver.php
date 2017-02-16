<?php
namespace App\Events;

use App\Model\App;
use Illuminate\Support\Facades\App as LaravelApp;

/**
 * Class AppObserver handle events for App model
 *
 * @package App\Events
 */
class AppObserver
{
	public function saved( App $app ) {
		// @todo: check to see if slug changed and update the
//		$this->fixDatabaseConfigs($app);
	}

	public function load( App $app ) {
		$this->fixDatabaseConfigs( $app );
	}

	/**
	 * Adds database configs based on $app slug
	 *
	 * @param App $app
	 */
	protected function fixDatabaseConfigs( App $app ) {
		$config = LaravelApp::make( 'config' );
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
