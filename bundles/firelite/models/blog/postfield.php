<?php
namespace Firelite\Models\Blog;

use Firelite\Models\Editor;
use FireliteDatatype;
use FireliteModel;
use Laravel\Database\Eloquent\Relationships\Has_One;
use Str;
 
/**
 * Represents the data attached to the post (relates to datatype via template_field)
 */
class Postfield extends FireliteModel {
	
	static public $table = 'post_fields';
	
	/**
	 * 
	 * @return type 
	 */
	public function template_field(){
		return $this->belongs_to('FireliteTemplateField');
	}
	
	/**
	 * 
	 * @return string 
	 */
	public function getName(){
		return $this->template_field->name;
	}
	
	
	/**
	 * Passes through to FireliteTemplateField->datatype()
	 * 
	 * @return type 
	 */
	public function datatype_info(){
		return $this->template_field->datatype();
	}
	
	/**
	 * 
	 * @return Has_One
	 */
	public function datatype(){
		//public function has_many_and_belongs_to($model, $table = null, $foreign = null, $other = null)
		return $this->has_many_and_belongs_to( FireliteDatatype::getDatatypeClass( $this->datatype_info->name ), 'postfield_' . Str::plural(Str::lower($this->datatype_info->name)) );
	}
	
	/**
	 * 
	 * @return mixed 
	 */
	public function getValue(){
		$datatype = $this->datatype()->first();
		if ( $datatype ){
			
			return $datatype->value;
		}
		return null;
	}
	
	/**
	 * 
	 * @return mixed 
	 */
	public function setValue($value){
		if (!$this->exists){
			$this->save();
		}
		$datatype = $this->datatype()->first();
		$datatype->setValue( $value );
		$datatype->save();
	}
	
	/**
	 * 
	 * @return string 
	 */
	public function __toString(){
		return $this->getValue();
	}
	
	/**
	 * 
	 * @return Editor
	 */
	public function editor(){
		return $this->template_field->editor($this);
	}
	
	/**
	 * 
	 * @param FireliteTemplateField $template_field
	 * @return FirelitePageField
	 */
	static public function factory($template_field){
		
		$postField = new static();
		$postField->datatype = $template_field->datatype->instance();
		$postField->template_field = $template_field;
		
		return $postField;
	}
}