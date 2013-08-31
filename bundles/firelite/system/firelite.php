<?php
namespace Firelite\System;

use \URI;
use \URL;
use \FireliteTree;
use \Response;
use \FireliteNode;
use \Request;
use \Config;
use \Event;
use \Log;
use \Str;

/**
 * 
 */
class Firelite {

	static protected $_plugins = array();
	
	static protected $_datatype_editors = array();
	static protected $_nodetype_editors = array();
	
	static protected $_editor_datatypes = array();
	static protected $_editor_nodetypes = array();

	/**
	 * 
	 *
	 * @param integer $tree_id = null
	 * @return mixed 
	 */
	static public function handleRoute( $route = null, $tree_id = null ){

		$response = Event::until( 'firelite.handleroute:before', array( $route, $tree_id ) );

		if ( !is_null( $response ) ){
			return $response;
		}

		$site_tree_id = ( $tree_id !== null ) ? $tree_id : Firelite::config( 'default_tree', 1 );
		$route = ( $route !== null ) ? $route : Request::getPathInfo();

		$node = static::getNodeFromRoute($route, $site_tree_id);
		
		if ( $node && $node->nodetype && $node->nodetype->name !== '' ){

			Event::fire( 'firelite.handleroute:after', array( $route, $site_tree_id, $node ) );
			
			return static::renderNode( $node );
		} else {
			//TODO: get the 404 working using a default node if set?
			//or the default laravel 404 in last instance
			$response = Event::until( 'firelite.handleroute.notfound:before', array( $route, $site_tree_id ) );

			if ( !is_null( $response ) ){
				return $response;
			}
			return Response::error('404');
		}
	}
	
	/**
	 * 
	 * @param string $route
	 * @return null|FireliteNode
	 */
	static public function getNodeFromRoute($route, $tree_id = null){
		static $last_node = null;
		static $last_route = '';
		
		if ($route === $last_route && $last_node !== null){
			return $last_node;
		}
		
		if ( !empty( $route ) ){
			
			$site_tree_id = ( $tree_id !== null ) ? $tree_id : Firelite::config( 'default_tree', 1 );
			
			$last_node = FireliteNode::with( 'nodetype' )
				->where( 'path', '=', $route )
				->where( FireliteNode::$_col_tree, '=', $site_tree_id )
				->where( 'published', '=', 1 )
				->first();
			$last_route = $route;
			return $last_node;
		}
		return null;
	}
	
	/**
	 * 
	 * @param string $route
	 * @param int|null $tree_id
	 * @return mixed
	 */
	static public function getHandlerFromRoute($route, $tree_id = null){
		$node = static::getNodeFromRoute($route, $tree_id);
		return static::resolveNode($node);
	}

	/**
	 * Get a firelite config item (application config always overrides bundle config)
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed 
	 */
	static public function config( $key, $default = null ){
		$key = 'firelite.' . $key;
		return Config::get( $key, Config::get( 'firelite::' . $key, $default ) );
	}

	/**
	 *
	 * @param FireliteTemplate $template
	 * @return string 
	 */
	static public function getTemplateView( $template ){
		return \FireliteTemplate::getTemplateView($template);
		
	}

	/**
	 * This method locates the NodeType handler for the supplied node 
	 * and invokes it's render method
	 * 
	 * @param Node $node
	 * @return mixed 
	 */
	static public function renderNode( $node ){

		$response = Event::until( 'firelite.rendernode:before', array( $node ) );

		if ( !is_null( $response ) ){
			return $response;
		}

		$handler = static::resolveNode($node);
		
		if ( $handler ){
			$render_res = $handler->render();
			Event::until( 'firelite.rendernode:after', array( $node, $render_res ) );
			return $render_res;
		} else {
			return 'Unable to locate handler for Nodetype ' . $node->nodetype->name;
		}
	}
	
	/**
	 * 
	 * @param FireliteNode $node
	 * @return mixed
	 */
	static public function resolveNode($node){
		
		static $last_handler = null;
		static $last_node_id = 0;
		
		if ( !empty($node) && $node->id === $last_node_id && $last_handler !== null ){
			return $last_handler;
		}
		
		if ( !empty( $node ) && is_a( $node, 'FireliteNode' ) ){
			$handler_class = static::getNodeTypeHandler( $node->nodetype );
			if ( class_exists( $handler_class ) ){
				$last_handler = $handler_class::from_node( $node );
				$last_node_id = $node->id;
				return $last_handler;
			}
		}
		return null;
	}

	/**
	 * 
	 * @param type $nodetype
	 * @return type 
	 */
	static public function getNodeTypeHandler( $nodetype ){
		return "Firelite" . $nodetype->name;
	}

	/**
	 * this is a test function used to generate a simple nav structure
	 * 
	 * @param $node $nav_item
	 * @return string 
	 */
	static public function buildNav( $nav_item ){
		if (empty($nav_item) || !isset($nav_item->children)){
			return '';
		}
		if ( count( $nav_item->children ) === 0 ){
			return '';
		}
		$result = '<ul>';

		foreach ( $nav_item->children as $child ){

			$result .= '<li>' . '<a href="' . $child->getPath() . '" ';
			if ( !empty( $child->link_title ) ){
				$result .= "title=\"{$child->link_title}\" ";
			}
			$result .= '>' . $child->link_text . '</a>' . static::buildNav( $child ) . '</li>';
		}

		$result .= '</ul>';

		return $result;
	}

	/**
	 *
	 * @return string 
	 */
	static public function version(){
		return "0.2.0 alpha";
	}

	/**
	 *
	 * @param string $plugin_name
	 * @param string $plugin_path
	 * @return boolean 
	 */
	static public function registerPlugin( $plugin_name, $class_name, $plugin_path = null ){
		if ( is_null( $plugin_path ) ) {
			$plugin_path = Firelite::config( 'admin.plugins_dir' );
		}
		//$class_name = 'Firelite' . Str::camelCase($plugin_name, false).'Plugin';
		if (class_exists( $class_name )){
			if ( file_exists( $plugin_path ) ){
				static::$_plugins[ Str::lower( $plugin_name ) ] = $class_name;
				return true;
			} else {
				
				Log::firelite('plugin NOT registered: '. $plugin_name . '@' . $plugin_path . ' can\t find file');
			}
		} else {
		
			Log::firelite('plugin NOT registered: '. $plugin_name . '@' . $plugin_path . ' can\'t find class ' . $class_name);
			return false;
		}
	}
	
	/**
	 * Register multiple plugins at once
	 * 
	 * @param type $plugins
	 */
	static public function registerPlugins($plugins, $plugin_path = null){
		if ( !empty( $plugins ) ) {
			foreach ( Firelite::config( 'admin.plugins' ) as $plugin ) {
				Firelite::registerPlugin( $plugin['name'], $plugin['class'], $plugin_path );
			}
		}
	}

	/**
	 *
	 * @param string $plugin_name 
	 */
	static public function pluginExists( $plugin_name ){
		return isset( static::$_plugins[ Str::lower( $plugin_name ) ] );
	}

	/**
	 * 
	 * 
	 * @return array
	 */
	static public function getPlugins(){
		return static::$_plugins;
	}

	/**
	 * 
	 * 
	 * @return array
	 */
	static public function getPluginClass( $plugin_name ){
		if ( static::pluginExists( $plugin_name ) ){
			return static::$_plugins[$plugin_name];
		}
		return null;
	}
	
	/**
	 * return the URL to the specified plugin (with an optional action and params)
	 * 
	 * @param string $plugin_name
	 * @param string $action
	 * @param array $params
	 */
	static public function getPluginURL( $plugin_name, $action = '', $params = array() ){
		//$plugin_class = static::getPluginClass( $plugin_name );
		$plugin_name = Str::lower($plugin_name);
		if ( $action === 'index' ){//convert index action to empty string since we don't need /index on the url
			$action = '';
		}
		$params = (array)$params;
		if ( !empty( $params ) ){
			$params = '/'. implode('/' , $params);
		} else {
			$params = '';
		}
		
		return URL::to_route( 'firelite_admin' ). '/' . $plugin_name . '/' . $action . $params ;
	}
	
	
	/**
	 * 
	 * @param string $editor_name
	 * @param array $handles
	 * @param string $editor_path (optional)
	 */
	static public function registerEditor( $editor_name, $class_name, $handles, $type = 'datatype' ) {
		switch($type){
			case 'datatype':
				return static::registerDatatypeEditor( $editor_name, $class_name, $handles );
			break;
			case 'nodetype':
				return static::registerNodetypeEditor( $editor_name, $class_name, $handles );
			break;
		}
	}
	
	/**
	 * Register a series of editors
	 */
	static public function registerEditors($editors, $editor_type = 'datatype'){
		
		if ( !empty( $editors ) ){
			
			foreach ( $editors as $editor ){
				if ( !isset($editor['name']) || !isset($editor['class']) || !isset($editor['handles']) ){

					echo 'Invalid editor: <pre>', print_r($editor, true), '</pre>';
					exit;


				} else {
					Firelite::registerEditor( $editor['name'], $editor['class'], $editor['handles'], $editor_type );
				}
			}
			
		} else {
			Log::firelite( 'Firelite::registerEditor editors array is empty' );
		}
	}
	
	/**
	 * Register a datatype editor with the system
	 * 
	 * @param type $editor_name
	 * @param type $handles_datatypes
	 * @return boolean
	 */
	static public function registerDatatypeEditor($editor_name, $class_name, $handles_datatypes){
		//$class_name = 'Firelite' . Str::camelCase($editor_name, false) . 'Editor';
		if ( class_exists( $class_name ) ) {
			$handles_datatypes = array_unique(array_merge((array)$handles_datatypes, $class_name::$datatypes));
			
			//check if the content type is already in the 
			
			foreach ( $handles_datatypes as $datatype ){
				$datatype = Str::lower($datatype);
				if ( !array_key_exists( $datatype, static::$_editor_datatypes ) ){
					static::$_editor_datatypes[$datatype] = array();
				}
				static::$_editor_datatypes[$datatype][] = $class_name;
				//Log::firelite('Registering editor: '. $editor_name . ' with datatype ' . $datatype );
			}

			static::$_datatype_editors[ Str::lower($editor_name) ] = $class_name;

			return true;
			
		}
		
		Log::firelite('editor NOT registered: '. $editor_name );
		return false;
	}
	
	/**
	 * Register a datatype editor with the system
	 * 
	 * @param type $editor_name
	 * @param type $handles_nodetypes
	 * @return boolean
	 */
	static public function registerNodetypeEditor($editor_name, $class_name, $handles_nodetypes){

		Log::firelite('Registering node editor: '. $class_name );

		if ( class_exists( $class_name ) ) {
			$handles_nodetypes = array_unique(array_merge((array)$handles_nodetypes, $class_name::$nodetypes));
			
			//check if the content type is already in the 
			
			foreach ( $handles_nodetypes as $nodetype ){
				$nodetype = Str::lower($nodetype);
				if ( !array_key_exists( $nodetype, static::$_editor_nodetypes ) ){
					static::$_editor_nodetypes[$nodetype] = array();
				}
				static::$_editor_nodetypes[$nodetype][] = $class_name;
				//Log::firelite('Registering editor: '. $editor_name . ' with datatype ' . $datatype );
			}

			static::$_nodetype_editors[ Str::lower($editor_name) ] = $class_name;
			Event::fire('firelite::nodetype.registered', array(
				array(
					'editor_name' => $editor_name,
					'class_name' => $class_name,
				)
			));
			
			return true;
			
		}
		
		Log::firelite('node editor NOT registered: '. $class_name );
		return false;
	}
	
	/**
	 * 
	 * 
	 * @return array
	 */
	static public function getDataEditors($datatype = null){
		$datatype = (string)$datatype;
		$result = array();
		
		if ( empty( $datatype ) ){
			
			return static::$_datatype_editors;
			
		} else {
			
			$datatype = Str::lower($datatype);
			
			if (isset(static::$_editor_datatypes[ $datatype ])){
				return static::$_editor_datatypes[ $datatype ];
			}
		}
		Log::firelite('No editor defined for datatype ' . $datatype);
		return $result;
	}
	
	/**
	 * 
	 * 
	 * @return array
	 */
	static public function getNodeEditors($nodetype = null){
		$nodetype = (string)$nodetype;
		$result = array();
		
		if ( empty( $nodetype ) ){
			
			$result = static::$_nodetype_editors;
			
		} else {
			
			$nodetype = Str::lower($nodetype);
			
			if (isset(static::$_editor_nodetypes[ $nodetype ])){
				$result = static::$_editor_nodetypes[ $nodetype ];
			} else {
				Log::firelite('No editor defined for nodetype ' . $nodetype);
			}
		}
		return $result;
	}

	static public function getNodeEditor($nodetype){
		$res = static::getNodeEditors($nodetype);
		if (count($res) > 0){
			return array_pop($res);
		}
		return null;
	}
}