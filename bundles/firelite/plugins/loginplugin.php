<?php namespace Firelite\Plugins;

use FireliteBasePlugin;
use View;
use Event;
use Request;
use Input;
use FireliteAuth;
use Redirect;
use Firelite;
use Log;

class LoginPlugin extends FireliteBasePlugin {
	
	
	protected static $needlogin = false;
	
	/**
	 * 
	 * @var type 
	 */
	static public $nav = array(
		'main_nav' => array(
			'action' => 'action_logout',
			'link_text' => 'Logout'
		)
	);
	
	/**
	 *
	 * @return View 
	 */
	public function action_index($requested_plugin_details, $view_dir){
		if(Request::method() !== 'POST'){
			if(FireliteAuth::check()){
				return Redirect::to(Firelite::getPluginURL('dashboard', 'index'));
			}else{
				return View::make($view_dir . 'login.index');
			}
		}else{
			$credentials = array('username' => Input::get('username'), 'password' => Input::get('password'));

			if (FireliteAuth::attempt($credentials)){
				$event_data = array(
					'username' => Input::get('username')
				);
				\Event::fire('firelite.admin.login.success', $event_data);
				return Redirect::to(Firelite::getPluginURL('dashboard', 'index'));
			}else{
				sleep(2);
				$event_data = array(
					'username' => Input::get('username')
				);
				\Event::fire('firelite.admin.login.error', $event_data);
				return View::make($view_dir . 'login.index');
			}
		}
	}
	
	public function action_logout($requested_plugin_details, $view_dir){
		$user = FireliteAuth::user();
		FireliteAuth::logout();
		
		\Event::fire('firelite.admin.logout.success', array(
			'username' => $user->username
		));
		return Redirect::to(Firelite::getPluginURL('dashboard', 'index'));
	}

	/**
	 *
	 * @return boolean 
	 */
	static public function firelite_install(){
		return false;
	}

	/**
	 *
	 * @return boolean 
	 */
	static public function firelite_uninstall(){
		return false;
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
		return 'Firelite user login and logout';
	}
	
}