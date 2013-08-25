<?php namespace Firelite\Editors;

use Event;
use Firelite;
use FireliteBaseEditor;
use Laravel\Asset;
use URL;
use RTE;
use \FireliteAsset;
/**
 * The Largetext editor is a single line input
 * 
 */
class Richtext extends FireliteBaseEditor {
	
	/**
	 *
	 * @var type 
	 */
	static public $datatypes = array(
		'Largetext',
		'Richtext'
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
		
		FireliteAsset::container('firelite.header')
			->script('tinymce', URL::to_asset( Asset::container('laramce')->path('tiny_mce/tiny_mce.js') ) );
		
		$event_data = array( $name, $data, $params, static::$datatypes );
		
		$response = Event::until('firelite.editor.richtext.render:before', $event_data);
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		$attributes = array_get($params, 'attributes', array());
		
		if ( !isset( $attributes[ 'rows' ] ) ){
			$attributes[ 'rows' ] = 15;
		}

		if ( !isset( $attributes[ 'cols' ] ) ){
			$attributes[ 'cols' ] = 60;
		}
		
		$classes = array(
			'editor-richtext'
		);
		
		$attr_class = array_get( $attributes, 'class', '' );
		
		if ( !empty( $attr_class ) ){
			
			if ( !is_array( $attr_class ) ){
				$attr_class = explode( ' ', $attr_class );
			}

			$classes = array_merge( $classes, $attr_class );
		}
		
		$attributes['class'] = implode( ' ', $classes );
		
		//try to determine which tinyMCE config we want
		$mode = 'custom';
		$default_setup = Firelite::config( 'tinymce.default_setup', 'full' );
		
		
		$setup = Firelite::config( 'tinymce.setups.' .$default_setup , array() );
		
		if ( empty( $setup ) ){
			$mode = ($default_setup !== 'simple')?'full':'simple';
		}
		
		if ( isset( $params[ 'template_field' ] ) ){
			//TODO: custom, per template field, tinyMCE config
		}
		if (isset($attributes['field_unique_id'])){
			$attributes['id'] = $name . '-' . $attributes['field_unique_id'];
		}
		$richtext_settings = array(
			'att' => $attributes,
			'selector' => $name . '-editor-richtext',
			'mode' => $mode,
			'setup'=> $setup
		);
		
		$res = RTE::rich_text_box(
			$name,
			$data,
			$richtext_settings
		);
		
		Event::fire('firelite.editor.richtext.render:after', $event_data );
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
