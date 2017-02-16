<?php
namespace App\Model;

use App\Observers\AppObserver;

class App extends Model
{
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
