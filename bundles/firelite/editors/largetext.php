<?php namespace Firelite\Editors;

use \Str;
use \Form;
use \FireliteBaseEditor;
use \Event;

/**
 * The Largetext editor is a single line input
 * 
 */
class Largetext extends FireliteBaseEditor {
	
	/**
	 *
	 * @var type 
	 */
	static public $datatypes = array(
		'Largetext'
	);
	
	/**
	 * Determine if the editor can handle the specified data type
	 * 
	 * @param string $datatypes
	 * @return type
	 */
	static public function handles( $datatype ){
		if (is_a($datatype, 'FireliteModel')){
			$datatype = $datatype->name;
		}
		return in_array( $datatype, static::$datatypes );
	}
	
	/**
	 * Render the editor and return the markup necessary, 
	 * the editor may also insert assets
	 * 
	 * @param mixed $data
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	static public function render($name, $data, $params = array()){
		$event_data = array( $name, $data, $params, static::$datatypes );
		
		$response = Event::until('firelite.editor.largetext.render:before', $event_data);
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		$attributes = array_get($params, 'attributes', array());
		
		if (!isset($attributes['rows'])) {
			$attributes['rows'] = 15;
		}
		
		if (!isset($attributes['cols'])) {
			$attributes['cols'] = 80;
		}
		
		$classes = array(
			'editor-largetext'
		);
		
		$attr_class = array_get( $attributes, 'class', '' );
		
		if ( !empty( $attr_class ) ){
			
			if ( !is_array( $attr_class ) ){
				$attr_class = explode( ' ', $attr_class );
			}

			$classes = array_merge( $classes, $attr_class );
		}
		
		$attributes['class'] = implode( ' ', $classes );
		
		$res = Form::textarea( $name, $data, $attributes );
		
		Event::fire('firelite.editor.largetext.render:after', $event_data );
		return $res;
	}
		
	/**
	 *
	 * @return boolean 
	 */
	static public function firelite_install(){
		return true;
	}

	/**
	 *
	 * @return boolean 
	 */
	static public function firelite_uninstall(){
		return true;
	}

	/**
	 *
	 * @return int 
	 */
	static public function firelite_version(){
		return 1;
	}

	/**
	 *
	 * @return string 
	 */
	static public function firelite_description(){
		return 'Allows editing of multi-line text';
	}
}
