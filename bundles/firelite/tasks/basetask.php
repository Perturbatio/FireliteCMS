<?php
use \Str;

class Firelite_Basetask {
	
	
	static protected function has_required_methods($class_name, $required_methods){
		$result = true;
		
		foreach ( $required_methods as $method ) {
			if ( !method_exists( $class_name, $method ) ) {
				if ( !is_array( $result ) ) {
					$result = array( );
				}
				$result[ ] = $class_name . ' must implement the ' . $method . ' method';
			}
		}
		return $result;
	}
		
	/**
	 * Retrieve a command line switch, as false if not set,
	 * true if set with no value, and string if given a value.
	 *
	 * @param string The switch name to query, lowercase.
	 * @return mixed Bool or value.
	 */
	public static function config($key)
	{
		if(isset($_SERVER['CLI'][Str::upper($key)]))
		{
			return ($_SERVER['CLI'][Str::upper($key)] == '') ? true : $_SERVER['CLI'][Str::upper($key)];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 
	 * @param array $arguments
	 * @return string 
	 */
	static protected function get_base_classname($arguments){
		if ( !empty( $arguments ) ){
			return Str::classify( $arguments[0] );
		}
		return 'No arguments';
	}
	
	/**
	 *
	 * @param array $arguments
	 * @return string 
	 */
	static protected function get_class_name($arguments, $prefix = 'Firelite', $suffix = ''){
		if ( !empty( $arguments ) ){
			return $prefix . static::get_base_classname($arguments) . $suffix;
		}
		return 'No arguments';
	}
}