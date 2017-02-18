<?php
namespace App\Database\Models;

use App\Database\Model;
use App\Observers\AppObserver;

/**
 * Class App is the model for an App being hosted at this service.
 * Resources are attached to this app.
 *
 * @package App\Models
 */
class App extends Model
{
	/** Route name */
	const ROUTE_NAME = 'app';

	/**
	 * Perform tasks once for all App models
	 */
	public static function boot() {
		self::observe( AppObserver::class );
	}

	/**
	 * Custom connection for all resources under this app
	 *
	 * @return string
	 */
	public function getResourcesConnectionName() {
		if ( empty( $this->slug ) ) {
			return parent::getConnectionName();
		}

		return 'a_' . $this->slug;
	}
}
