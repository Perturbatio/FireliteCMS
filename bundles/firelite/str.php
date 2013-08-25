<?php namespace Firelite;

class Str extends \Laravel\Str {
	
	/**
	 * Converts a string to camelCase <br>
	 * i.e. Str::camelCase('get_my_attribute') returns 'getMyAttribute'
	 * 
	 * @param string $value
	 * @return string 
	 */
	public static function camelCase( $value, $lowerfirst = true ){
		if ( strlen( $value ) === 0 ){
			return '';
		}

		$val = str_replace( '_', '', static::classify( $value ) );
		if ( $lowerfirst === true ){
			$val = lcfirst($val);
		}
		return $val;
	}
	
}