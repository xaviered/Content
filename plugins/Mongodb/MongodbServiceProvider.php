<?php
namespace Plugins\Mongodb;

use Jenssegers\Mongodb\MongodbServiceProvider as JenssegersMongodbServiceProvider;
use Jenssegers\Mongodb\Queue\MongoConnector;

/**
 * Class MongodbServiceProvider aids to fix problem with Laravel needing a PDO to have beginTransaction
 * @package Plugins\Mongodb
 */
class MongodbServiceProvider extends JenssegersMongodbServiceProvider
{
	/**
	 * Override method to use custom connection
	 */
	public function register() {
		// Add database driver.
		$this->app->resolving( 'db', function( $db ) {
			$db->extend( 'mongodb', function( $config ) {
				return new Connection( $config );
			} );
		} );

		// Add connector for queue support.
		$this->app->resolving( 'queue', function( $queue ) {
			$queue->addConnector( 'mongodb', function() {
				return new MongoConnector( $this->app[ 'db' ] );
			} );
		} );
	}
}
