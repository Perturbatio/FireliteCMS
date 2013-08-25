<?php namespace Firelite\Models;

use Firelite;
use FireliteModel;
use FireliteDatatype;

class TemplateField extends FireliteModel {
	
	static public $table = 'template_fields';
	
	/**
	 * This array contains all rules and messages used by the TemplateField validate function
	 * 
	 * @var array 
	 */
	public $validation = array(
		'rules' => array(
			'template_id' => 'required|integer|exists:templates,id',
			'datatype_id' => 'required|integer|exists:datatypes,id',
			'name' => 'required|alphadash|between:1,64', 
			'label'=>'required|between:1,64',
		),
		'messages' => array(
			'template_id' => 'An invalid template was specified',
			'datatype_id' => 'An invalid datatype was specified',
			'name' => 'Name must be numbers, letters hyphens and underscores only, (only the root node can contain a slash) max length is :max',
			'label' => 'The label is required and must be no longer than :max characters',
		)
	);
	
	/**
	 *
	 * @return Has_One 
	 */
	public function template(){
		return $this->has_one('FireliteTemplate');
	}
	
	/**
	 * 
	 * @return Belongs_To
	 */
	public function datatype(){
		return $this->belongs_to('FireliteDatatype');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function editor( $name = '', $default = '', $attributes = array() ){
		if ( empty( $name ) ){
			$name = 'editor_' . $this->name;
		}
		
		$datatype_class_name = FireliteDatatype::getDatatypeClass( $this->datatype->name );
		$datatype = new $datatype_class_name();
		
		if ( is_a( $default, 'Pagefield' ) ){
			$default = $default->getValue();
		} else if (empty($default)){
			$default = '';
		}
		return $datatype->editor( $name, $default, $attributes, array( 'template_field' => $this ) );
		
	}
	
	
	
}