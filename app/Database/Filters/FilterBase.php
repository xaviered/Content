<?php
namespace App\Database\Filters;

use App\Http\Request;

/**
 * Class FilterBase defines a skeleton for Filter objects
 * @package App\Database\Filters
 */
abstract class FilterBase
{
	/** @var Request */
	protected $request;

	/**
	 * ApiSchemaFilter constructor.
	 * @param Request $request
	 */
	public function __construct( Request $request ) {
		$this->request = $request;
	}

	/**
	 * @return Request
	 */
	public function getRequest(): Request {
		return $this->request;
	}

	/**
	 * @param mixed $data
	 * @return bool
	 */
	abstract public function filter( &$data );
}
