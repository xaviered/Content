<?php
namespace App\Database\Observers;

use App\Database\Models\App;

/**
 * Class AppObserver handle events for App model
 *
 * @package App\Database\Observers
 */
class AppObserver
{
	use ModelObserverTrait;

//	public function saved( App $app ) {
//		// @todo: check to see if slug changed and clear cache for all resources using this app
//	}
}
