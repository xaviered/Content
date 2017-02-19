<?php
namespace App\Database\Filters;

/**
 * Class ApiModelFilter
 * @package App\Database\Filters
 */
class ApiModelFilter extends FilterBase
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
