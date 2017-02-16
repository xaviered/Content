<?php
namespace App\Observers;

use App\Model\App;
use Illuminate\Support\Facades\App as LaravelApp;

/**
 * Class AppObserver handle events for App model
 *
 * @package App\Observers
 */
class AppObserver
{
	use ModelObserverTrait;

	public function saved( App $app ) {
		// @todo: check to see if slug changed and clear cache for all resources using this app
	}
}
