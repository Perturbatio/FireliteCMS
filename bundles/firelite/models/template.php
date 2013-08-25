<?php namespace Firelite\Models;

use Firelite;
use FireliteModel;
use FireliteTemplateField;
use FireliteTemplate;
use FireliteDatatype;

use \View;
use \Str;


class Template extends FireliteModel {
	
	public function fields(){
		return $this->has_many('FireliteTemplateField');
	}
	
	/**
	 * 
	 * @param string $name
	 */
	public function getField( $name ){
		foreach ( $this->fields as $field ){
			if ( $field->name === $name ){
				return $field;
			}
		}
		return null;
	}
	
	/**
	 * 
	 */
	static public function export() {
		$result = new stdClass();
		
		$result->fields = array();
		
		foreach ( $this->fields as $field ) {
			
			if ( method_exists( $field, 'export' ) ) {
				$result->fields[] = $field->export();
			} else {
				
			}
			
		}

		return $result;
	}
	
	/**
	 *
	 * @param FireliteTemplate|String $template
	 * @return string 
	 */
	static public function getTemplateView( $template ){
		$view = '';
		if (is_a($template, "FireliteTemplate")){
			$view = $template->view;
		} else {
			$view = (string)$template;
		}
		return Firelite::config( 'templates.path', 'firelite' ) . '.' . $view;
	}
	
	/**
	 * 
	 * @param type $data
	 * @return array()
	 */
	static public function import($data){
		$canImport = true;
		$import_messages = array();
		
		//check template name
		$template = static::where_name( $data->name );
		
		if ( $template->count() > 0 ) {
			$canImport = false;
			$import_messages[ 'template_name' ] = "Template name '{$data->name}' is already in use";
		}
		
		//verify view
		if ( !View::exists( \FireliteTemplate::getTemplateView( $data->view ) ) ) {
			$canImport = false;
			$import_messages['view'] = 'View does not exist: ' . $data->view;
		}
		
		foreach( $data->fields as $field ){
			$firelite_datatype_classname = 'FireliteDatatype' . Str::classify( $field->datatype );
			//verify datatypes exist
			if ( !class_exists( $firelite_datatype_classname ) ){
				if ( !isset( $import_messages[ 'fields' ] ) ) {
					$import_messages[ 'fields' ] = array( );
				}
				$import_messages[ 'fields' ][$field->datatype] = $firelite_datatype_classname . ' does not exist';
				$canImport = false;
			}
			
			$firelite_editor_classname = 'Firelite' . Str::classify( $field->editor ). 'Editor';
			//verify editors
			if ( !class_exists( 'Firelite'.Str::classify( $field->editor ) . 'Editor' ) ){
				if ( !isset( $import_messages[ 'editors' ] ) ) {
					$import_messages[ 'editors' ] = array( );
				}
				$import_messages[ 'editors' ][$field->datatype] = $firelite_editor_classname . ' does not exist';
				$canImport = false;
			}
		}
		
		if ( $canImport === true){
			
			$template = new FireliteTemplate();
			$template->name = $data->name;
			$template->description = $data->description;
			$template->view = $data->view;
			
			if ( $template->save() ){
				$datatype_lookup = array();

				//template_fields
				foreach( $data->fields as $field ){

					if ( !isset( $datatype_lookup[$field->datatype] ) ){
						$firelite_datatype_classname = 'FireliteDatatype' . Str::classify( $field->datatype );
						//$datatype = $firelite_datatype_classname::where_name($field->datatype)->first();
						$datatype = FireliteDatatype::where_name($field->datatype)->first();
					} else {
						$datatype = $datatype_lookup[$field->datatype];
					}

					//FireliteTemplateField
					$templateField = new FireliteTemplateField();
						$templateField->template_id = $template->id;
						$templateField->datatype_id = $datatype->id;
						$templateField->name = $field->name;
						$templateField->label = $field->label;
						$templateField->description = $field->description;
						
					if ( !$templateField->save() ){
						if ( !isset( $import_messages[ 'template_field' ] ) ) {
							$import_messages[ 'template_field' ] = array( );
						}
						$import_messages['template_field'][$field->name] = "Error saving template field '{$field->name}' in DB";
					}
				}
				
			} else {
				$import_messages['template'] = 'Error saving template in DB';
			}
			
		}
		
		if ( empty( $import_messages ) ){
			$import_messages['result'] = 'Successfully imported ' . $data->name;
		}
		
		return $import_messages;
		
	}
}
