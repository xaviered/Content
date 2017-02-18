<?php
namespace App\Http\Responses;

use App\Database\Model;
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
	 * @param \App\Database\Model|Collection $model Model or collection
	 * @param int $status
	 * @param array $headers
	 * @param int $options
	 */
	public function __construct( $model = null, $status = 200, array $headers = [], $options = 0 ) {
		$response = [];
		if ( $model ) {
			// model(s)
			if ( is_subclass_of( $model, Model::class ) || is_subclass_of( $model, Collection::class ) ) {
				$response = $model->toApiArray();
			}
			else {
				$response = [ 'data' => $model ];
			}
		}

		parent::__construct( $response, $status, $headers, $options );
	}
}
