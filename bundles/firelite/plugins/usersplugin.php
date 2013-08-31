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
use Validator;
use FireliteUser as User;
use Hash;

class UsersPlugin extends FireliteBasePlugin implements \Firelite\Plugins\IFirelitePlugin {
	
	/**
	 * 
	 * @var type 
	 */
	static public $nav = array(
		'main_nav' => array(
			'action' => 'action_index',
			'link_text' => 'Users'
		),
		'sub_nav' => array(
			array(
				'action' => 'action_add',
				'link_text' => 'Add'
			),
		)
	);
	
	
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
	 *
	 * @return type 
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
	 * 
	 * @param type $requested_plugin_details
	 * @param type $view_dir
	 * @return View
	 */
	public function action_index($requested_plugin_details, $view_dir){
		
		$view_data = array(
			'users' => User::all()
		);
		
		return View::make($view_dir . 'users.index', $view_data);
	}
	
	/**
	 * 
	 * @param type $requested_plugin_details
	 * @param type $view_dir
	 * @param type $user_id
	 * @return View
	 */
	public function action_add($requested_plugin_details, $view_dir ){
		
		if ( Request::method() == 'POST' ){
			$input = Input::all();

			$input['username'] = trim( $input['username'] );
			$input['password'] = trim( $input['password'] );

			$rules = array(
				'username' => 'required|unique:firelite_users',
				'password' => 'required|min:8',
			);
			
			$validation = Validator::make($input, $rules);
			
			if ( !$validation->fails() ) {
				
				$user = new User();
					$user->username = $input['username'];
					$user->password = Hash::make($input['password']);
				$user->save();
				return Redirect::to( Firelite::getPluginURL( 'users', 'index' ) )->with( 'response', 'User added' );
			}
		}
		
		$view_data = array(
			'validation' => (isset( $validation )) ? $validation : null,
		);

		return View::make( $view_dir . 'users.add', $view_data );
		
		/*
		if ( is_string( $user ) && is_string( $password ) ){
			if ( empty( $user ) ) {
				echo $user;
				echo "User cannot be empty!\n";
				return;
			}

			if ( empty( $password ) ) {
				echo "Password cannot be empty!\n";
				return;
			} else if (strlen($password) < 8){
				echo "Please use a password of at least 8 characters\n";
				return;
			}

			echo "Setting password for user: {$user}\n";

			DB::table('firelite_users')->insert(array(
				'username' => $user,
				'password' => Hash::make($password)
			));

		} else {
			echo "\nYou must specify both a username and a password.\n";
			echo "Usage: artisan firelite::setup:user --user|--u=<user> --pass|--p=<password>";
		}
	*/
	}
	
	
	/**
	 *
	 * @return View 
	 */
	public function action_edit($requested_plugin_details, $view_dir, $user_id = 0 ){
		
		$user_id = (int)$user_id;

		if ($user_id > 0){
			$user = User::find($user_id);
			//handle update
			if ( Request::method() == 'POST' ){
				$input = Input::all();

				$rules = array(
					'username' =>  '', //not currently editing this attribute//'required|max:64'
					'password' =>  '', //not currently editing this attribute//'required|min:8|max:100|confirmed',
				);

				$messages = array(
					'username' => 'Bad username',
					'password' => 'Invalid password',
				);

				$validation = User::validate($input, $rules, $user_id, $messages);

				if ( $validation === true ){

					$user->username = $input['username'];
					//$user->password = Crypter::hash( $input['password'] );
					
					
					if ( $user->save() ){
						$view_data = array(
							'users' => User::all()
						);

						return Redirect::to(Firelite::getPluginURL('users','index' ))->with('flash_message', array(
							array(
								'type' => 'success',
								'message' => 'User updated succefully',
							)
						));
						
					} else {
						
						return Redirect::to(Firelite::getPluginURL('users','index' ))->with('flash_message', array(
							array(
								'type' => 'error',
								'message' => 'User not updated',
							)
						));
						
					}
				}
			}


			//handle initial edit view

			if ($user){
				$view_data = array(
					'user' => User::find($user_id),
					'validation' => (isset($validation)) ? $validation : null,
					'field_error_template' => '<div class="page-alert {{type}}">{{message}}</div>',
					'errors' => array(
						'Invalid user id specified, go <a href="'.Firelite::getPluginURL('users','index' ) . '">back to the overview</a> and try again'
					)
				);
				
				return View::make($view_dir . 'users.edit', $view_data);
				
			}
		}
		
		return View::make($view_dir . 'error.index', 
			array('errors' => array(
					'Invalid user id specified, go <a href="'.Firelite::getPluginURL('fofusers','index' ) . '">back to the overview</a> and try again'
				)
			)
		);
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
		return 'Manage Firelite Users';
	}
	
}