<?php

use \Log;

/**
 * The main admin controller, the index action
 * is the entry point to firelite's admin
 */
class Firelite_Admin_Controller extends Firelite_Base_Controller {

	/**
	 * Admin Controller Constructor
	 */
	public function __construct() {
		$plugins = Firelite::config( 'admin.plugins' );
		Firelite::registerPlugins( $plugins );

		Firelite::registerEditors( Firelite::config( 'admin.datatype_editors' ), 'datatype' );
		Firelite::registerEditors( Firelite::config( 'admin.nodetype_editors' ), 'nodetype' );
		
		parent::__construct();
		
	}

	/**
	 * Default action for the controller
	 * 
	 * @return Response 
	 */
	public function action_index(){
		
		$requested_plugin_details = $this->getRequestedPluginDetails();
		$plugin = new $requested_plugin_details['class']();
		$is_authorised = FireliteAuth::check();
		
		if ( Firelite::pluginExists('login') && !$is_authorised ) {
			if ( $plugin::needlogin() === true ) {
				return Redirect::to( Firelite::getPluginURL( 'login', 'index' ) );
			}
		} else {
			if ( !$is_authorised && $plugin::needlogin() === true ) {
				return 'The plugin you are trying to access requires a login, but there is no login handler currently installed';;
			}
		}

		$view_prefix = Firelite::config( 'admin.view_dir', 'firelite::admin.' );

		$view = View::make( $view_prefix . 'admin.index' )
			->with('requested_plugin_details', $requested_plugin_details);

		$plugin_params = array(
			'requested_plugin_details' => $requested_plugin_details,
			'view_dir' => $view_prefix
		);

		//  parameters passed after the controller and the action will be 
		// passed straight through as parameters for the plugin
		$params = $requested_plugin_details['params'];
		$plugin_params = array_merge($plugin_params, $params);

		// pass the result of the plugin's action to the admin view to render in the main area
		$plugin_response = $plugin->execute(
			$requested_plugin_details['action'], 
			$plugin_params
		);
		if ( is_a( $plugin_response, 'Redirect' ) ){
			return $plugin_response;
		} else if ( (int)$plugin_response->status() === 404) {
			$view->with( 'plugin_view', View::make($view_prefix . 'error.index' )->with('errors', array('Invalid request')));
		} else {
			$view->with( 'plugin_view', $plugin_response );
		}
		return $view;
			
	}

	/**
	 * 
	 * @return type
	 */
	protected function getRequestedPluginDetails(){
		$segments = Str::segments( Request::uri() );
		array_shift( $segments ); //first item is the admin segment

		$plugins = Firelite::getPlugins();
		
		$result = array(
			'name' => $this->getRequestedPluginName( $segments ),
			'action' => $this->getRequestedPluginAction( $segments ),
			'params' => $segments
		);

		$result[ 'class' ] = $plugins[ $result[ 'name' ] ];

		return $result;
	}
	
	/**
	 *
	 * @param array $segments
	 * @return string 
	 */
	protected function getRequestedPluginName( &$segments ){
		$requested_plugin_name = '';

		if ( count( $segments ) > 0 ){
			$requested_plugin_name = array_shift( $segments );
		}

		if ( !Firelite::pluginExists( $requested_plugin_name ) ){
			$requested_plugin_name = $this->getDefaultPlugin();
		}

		//return $this->getDefaultPlugin();
		return $requested_plugin_name;
	}

	/**
	 * 
	 * @param array $segments
	 * @return string
	 */
	protected function getRequestedPluginAction( &$segments ){
		$requested_plugin_action = 'index';

		if ( count( $segments ) > 0 ){
			$requested_plugin_action = array_shift( $segments );
		}

		return $requested_plugin_action;
	}

	/**
	 *
	 * @return string 
	 */
	protected function getDefaultPlugin(){
		$default_plugin = Firelite::config( 'admin.default_plugin' );

		if ( !$default_plugin ){

			$plugins = Firelite::getPlugins();
			if ( !empty( $plugins ) ){
				foreach ( $plugins as $plugin_name => $plugin ){
					$default_plugin = $plugin_name;
					break;
				}
			}
		}
		return $default_plugin;
	}

}