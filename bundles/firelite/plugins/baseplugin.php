<?php namespace Firelite\Plugins;

use Controller;
use Event;

/**
 * Any plugin that extends this should also implement the IFirelitePlugin interface 
 */
abstract class BasePlugin extends Controller implements IFirelitePlugin {
	
	protected static $needlogin = true;
	
	/**
	 * 
	 */
	public function __construct(){
		parent::__construct();
		
		Event::flusher('firelite.plugin.activate:after', function($plugin, $plugin_class){
			
		});
	}

	/**
	 * return the array that defines the main nav item (if any)
	 * 
	 * @return array|null
	 */
	static public function getMainNav(){
		if ( !static::hasMainNav() ){
			return null;
		}
		return static::$nav['main_nav'];
	}
	
	/**
	 * Determine if this plugin has an entry on the main nav
	 * 
	 * @return Boolean 
	 */
	static public function hasMainNav(){
		return isset(static::$nav['main_nav']);
	}
	
	/**
	 * returns the array that defines the sub nav (if any)
	 * @return array|null 
	 */
	static public function getSubNav(){
		if ( !static::hasSubNav() ){
			return null;
		}
		return static::$nav['sub_nav'];
	}
	
	/**
	 *
	 * @return type 
	 */
	static public function hasSubNav(){
		return isset(static::$nav['sub_nav']);
	}
	
	
	/**
	 * Activate the plugin, returns true if successful, false if not
	 * 
	 * @return Boolean 
	 */
	public function firelite_activate(){
		$response = Event::until('firelite.plugin.activate:before', array($this, __CLASS__) );
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		//TODO: this needs to fire after the extend class' activate, event flush doesn't quite work for it
		Event::fire('firelite.plugin.activate:after', array($this, __CLASS__) );
		return true;
	}
	
	/**
	 *  
	 */
	public function firelite_get_nav(){
		
	}
	
	public static function needlogin(){
		return static::$needlogin;
	}
	
}