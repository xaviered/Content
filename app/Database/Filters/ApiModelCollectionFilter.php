<?php
namespace App\Database\Filters;

/**
 * Class ApiModelCollectionFilter
 * @package App\Database\Filters
 */
class ApiModelCollectionFilter extends FilterBase
{
	/**
	 * @param mixed $data
	 * @return bool
	 */
	public function filter( &$data ) {

		return true;
	}
}
