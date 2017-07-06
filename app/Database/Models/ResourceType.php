<?php
namespace App\Database\Core;

use App\Database\Models\Resource;

/**
 * Class ResourceType holds custom details for each model type under an App
 *
 * @package App\Models
 */
class ResourceType extends Resource
{
	public $type = 'resourcetype';
	public $validationRules;
}
