<?php
namespace App\Database;
namespace App\Support\Traits;

use Illuminate\Support\Traits\Macroable;

trait SoftMacroable
{
	use Macroable {
		__call as Macroable___call;
	}

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call( $method, $parameters ) {
		if ( static::hasMacro( $method ) ) {
			return $this->Macroable___call( ...func_get_args() );
		}

		return parent::__call( ...func_get_args() );
	}
}
