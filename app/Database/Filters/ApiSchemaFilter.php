<?php
namespace App\Database\Filters;

/**
 * Class ApiSchemaFilter
 * @package App\Database\Filters
 */
class ApiSchemaFilter extends FilterBase
{
	/**
	 * @param mixed $data
	 * @return bool
	 */
	public function filter( &$data ) {
		unset( $data[ '_id' ] );

		$fields = $this->getRequest()->get( 'fields' );
		if ( $fields ) {

		}

		return true;
	}
}
