<?php
namespace App\Http\Responses;

use App\Database\Collections\ModelCollection;
use App\Database\Core\Model;
use Illuminate\Http\JsonResponse;
use \Illuminate\Database\Eloquent\Collection;

/**
 * Class ApiJsonResponse is a JsonResponse representation of a model
 * @package App\Http\Responses
 */
class ApiJsonResponse extends JsonResponse
{
	/**
	 * ApiJsonResponse constructor.
	 * @param \App\Database\Core\Model|Collection $model Model or collection
	 * @param int $status
	 * @param array $headers
	 * @param int $options
	 */
	public function __construct( $model = null, $status = 200, array $headers = [], $options = 0 ) {
		$response = [];
		if ( $model ) {
			// model(s)
			if ( $model instanceof Model ) {
				$response = $model->toApiArray();
			}
			else if ( $model instanceof ModelCollection ) {
				$response = $model->toApiArray();
			}
			else {
				$response = [ 'data' => $model ];
			}
		}

		parent::__construct( $response, $status, $headers, $options );
	}
}
