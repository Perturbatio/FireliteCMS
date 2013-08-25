<?php namespace Firelite\Editors;

use \Str;
use \Form;
use \FireliteBaseEditor;
use \Laravel\Asset;
use \FireliteAsset;
use \URL;
use Event;
/**
 * The Simpleimage editor is a single line input
 * 
 */
class Simpleimage extends FireliteBaseEditor {
	
	/**
	 *
	 * @var type 
	 */
	static public $datatypes = array(
		'Simpleimage'
	);
	
	/**
	 * Determine if the editor can handle the specified content type, 
	 * the datatype may be passed as a second parameter to allow for more complex
	 * checking (i.e. an editor may handle text, but only if it's in a specific datatype)
	 * 
	 * @param type $content_type
	 * @param FireliteDatatype $datatype (optional)
	 * @return boolean
	 */
	static public function handles( $content_type, $datatype = null ){
		
		$content_type = Str::lower( $content_type );
		
		foreach ( static::$content_types as $type ){
			if ( $type == $content_type ){
				return true;
			}
		}
		
		return false;
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
		
		$response = Event::until('firelite.editor.simpleimage.render:before', $event_data);
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		FireliteAsset::container('firelite.header.after')
		->script('tinymce', URL::to_asset( Asset::container('laramce')->path('tiny_mce/plugins/imagemanager/js/mcimagemanager.js') ) );

		
		FireliteAsset::container('firelite.header.after')
			->script('editor-simpleimage', 'js/datatypes/simpleimage.js' );
		
		
		$classes = array(
			'editor-simpleimage',
			'inp-text'
		);
		$attributes = array_get($params, 'attributes', array());
		$attr_class = array_get( $attributes, 'class', '' );
		
		if ( !empty( $attr_class ) ){
			
			if ( !is_array( $attr_class ) ){
				$attr_class = explode( ' ', $attr_class );
			}

			$classes = array_merge( $classes, $attr_class );
		}
		
		$attributes['class'] = implode( ' ', $classes );
		
		$res = Form::input('text', $name, $data, $attributes);
		Event::fire('firelite.editor.simpleimage.render:after', $event_data);
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
		return 'Insert image URLS';
	}
}