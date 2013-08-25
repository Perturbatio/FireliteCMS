<?php namespace Firelite\Plugins;

use \FireliteBasePlugin;
use \View;

class DashboardPlugin extends FireliteBasePlugin {
	
	/**
	 *
	 * @var array 
	 */
	static public $nav = array(
		'main_nav' => array(
			'action' => 'action_index',
			'link_text' => 'Dashboard'
		),
	);
	
	
	/**
	 *
	 * @return string 
	 */
	public function action_index($requested_plugin_details, $view_dir){
		return View::make($view_dir . 'dashboard.index')->with('requested_plugin_details',$requested_plugin_details);
	}
	
	
	/**
	 *
	 * @return boolean 
	 */
	public function firelite_install(){
		return false;
	}

	/**
	 *
	 * @return boolean 
	 */
	public function firelite_uninstall(){
		return false;
	}

	/**
	 *
	 * @return int 
	 */
	public function firelite_version(){
		return 1;
	}

	/**
	 *
	 * @return string 
	 */
	public function firelite_description(){
		return 'The firelite dashboard page';
	}
	
}