<?php
namespace App\Support\Traits;

use App\Database\Core\ResourceType;

/**
 * Class HasCustomResourceType loads custom validation rules on a per App per type basis
 *
 * @package App\Support\Traits
 */
trait HasCustomResourceType
{
	/** @var bool */
	private $__loadedCustomRules;

	/** @var ResourceType */
	private $__customResourceType;

	/**
	 * @return ResourceType|null
	 */
	public function getCustomResourceType() {
		if ( !isset( $this->__customResourceType ) && !empty( $this->type ) ) {
			$this->__customResourceType = ResourceType::query()->get( $this->type )->first() ?? null;
		}

		return $this->__customResourceType;
	}

	/**
	 * An array of validation rules to use for this model's fields
	 * @return array
	 */
	public function getValidationRules() {
		if ( !$this->__loadedCustomRules ) {
			$this->__loadedCustomRules = true;
			$rules = parent::getValidationRules();

			$resourceType = $this->getCustomResourceType();
			if ( $resourceType && $resourceType->validationRules ) {
				$rules = array_merge( $rules, $resourceType->validationRules ?? [] );
				$this->setValidationRules( $rules );
			}
		}

		return parent::getValidationRules();
	}
}
