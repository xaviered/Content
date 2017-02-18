<?php
namespace App\Http\Controllers;

use App\Database\Models\Resource;

/**
 * Class ResourceController
 *
 * @package App\Http\Controllers
 */
class ResourceController extends ModelController
{
	/**
	 * @return Resource Class string representation of the model. i.e. App::class
	 */
	public function rootModel() {
		return Resource::class;
	}

}
