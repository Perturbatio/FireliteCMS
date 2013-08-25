<?php
namespace Firelite\Models;

use \FireliteModel;
use \Eloquent;
use \Str;

/**
 * this class provides a lookup between datatype ids and the name of the datatype
 * use it to resolve the datatype name to a class instance
 * 
 */
class Datatype extends FireliteModel {
	
	/**
	 *
	 * @return type 
	 */
	public function template_fields(){
		return $this->has_many('FireliteTemplateField');
	}
	
	/**
	 * Create a new instance of the specific datatype
	 * 
	 * @return type
	 */
	public function instance(){
		return static::factory($this->name);
	}
	
	/**
	 * 
	 * @param string $type_name
	 * @return string
	 */
	static public function getDatatypeClass($type_name){
		return 'FireliteDatatype' . Str::classify( $type_name );
	}
	
	/**
	 * Produce a new instance of a specific datatype
	 * 
	 * @param type $datatype_name
	 * @return \Firelite\Models\class
	 */
	static public function factory($datatype_name){
		
		$class = static::getDatatypeClass($datatype_name);
		if ( class_exists( $class ) ){
			return new $class();
		}
		return null;
	}
	
}