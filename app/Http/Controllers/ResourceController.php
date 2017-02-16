<?php
namespace App\Http\Controllers;

use App\Model\Resource;

/**
 * Class ResourceController
 *
 * @package App\Http\Controllers
 */
class ResourceController extends ModelController
{
	/** @var string $modelClass Model class to use when creating/finding */
	protected static $modelClass = Resource::class;
}
