<?php namespace Firelite\Models\Datatypes;

use Firelite;
use FireliteModel;
use Form;
use Input;

class Basedatatype extends FireliteModel {
	
	static public $edit_as_datatype = '';
	
	/**
	 * 
	 * @return string
	 */
	public function editor( $name, $default = '', $attributes = array(), $extra_params = array() ){
		$default = Input::get( $name, (string) $default );
		$editors = Firelite::getDataEditors( static::$edit_as_datatype );
		
		$field = $extra_params[ 'template_field' ];
		$editor = false;
		$extra_params[ 'attributes' ] = $attributes;
		
		if ( !empty( $field->preferred_editor ) ){
			$preferred = 'Firelite'.$field->preferred_editor.'Editor';
			$editor = array_search( $preferred, $editors );
			if ($editor !== false){
				$editor = $editors[$editor];
			}
		}

		if ( $editor === false && count( $editors ) > 0 ){
			$editor = $editors[ 0 ]; //TODO: find a good mechanism for specifying which editor to use (currently just takes the first one)
		}
		if ( !empty( $editor ) ){
			return $editor::render( $name, $default, $extra_params );
		}
		
		return Form::textarea( $name, Input::get( $name, $default ) );
	}
}
