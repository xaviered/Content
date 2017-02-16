<?php
namespace Plugins\Mongodb;

use Jenssegers\Mongodb\Connection as JenssegersCollection;

/**
 * Class Connection fixes problem with Laravel needing a PDO to have beginTransaction
 * @package Plugins\Mongodb
 */
class Connection extends JenssegersCollection
{
	/**
	 * Placeholder to fix problem with Laravel needing a PDO to have beginTransaction
	 */
	public function beginTransaction() {
	}

	/**
	 * Begin a fluent query against a database collection.
	 *
	 * @param  string  $collection
	 * @return Query\Builder
	 */
	public function collection($collection)
	{
		$processor = $this->getPostProcessor();

		$query = new Query\Builder($this, $processor);

		return $query->from($collection);
	}
}
