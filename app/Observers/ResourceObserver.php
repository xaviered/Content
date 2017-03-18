<?php
namespace App\Observers;

use App\Database\Models\Resource;

/**
 * Class ResourceObserver handle events for Resource model
 *
 * @package App\Observers
 */
class ResourceObserver
{
	use ModelObserverTrait;

	/**
	 * Handles events when saving
	 * @param Resource $model
	 */
	public static function saving( Resource $model ) {
//		$model->findRelations( );
	}

	/**
	 * Handles events when saved
	 * @param self $model
	 */
	public static function saved( $model ) {

	}


}
