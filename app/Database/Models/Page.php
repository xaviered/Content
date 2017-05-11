<?php
namespace App\Database\Models;

/**
 * Class Page
 * @package App\Database\Models
 */
class Page extends Resource
{
	public $type = 'page';
	public $body;
	public $image;

	protected $validationRules = [
		'body' => 'text',
	];
}
