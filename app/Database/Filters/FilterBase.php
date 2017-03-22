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

	/** @var array Fields to ignore for filtering */
	protected $ignoreFields = [ 'page', 'page_size', 'relations_max_depth' ];

	/**
	 * FilterBase constructor.
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

	/**
	 * @return array
	 */
	public function getIgnoreFields() {
		return $this->ignoreFields;
	}

	/**
	 * @param array $ignoreFields
	 */
	public function setIgnoreFields( array $ignoreFields ) {
		$this->ignoreFields = $ignoreFields;
	}
}
