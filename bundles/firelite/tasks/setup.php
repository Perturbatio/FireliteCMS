<?php
require_once('basetask.php');
/*
use FireliteTree;
use FireliteNode;
*/
class Firelite_Setup_Task extends Firelite_Basetask {

	/**
	 * 
	 */
	public function run(){
		echo '<pre>', print_r($_SERVER, true), '</pre>';
		if (file_exists('snow.sh')){
			echo 'exists';
		}
		echo "\nSpecify the command you want to run.\n";
	}
	
	public function user($arguments) {

		$user = trim( $this->config('user') );
		$password = trim( $this->config('pass') );
		
		if ( !is_string( $user ) ){
			$user = trim( $this->config('u') );
		}

		if ( !is_string( $password ) ){
			$password = trim( $this->config('p') );
		}

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
	}
	
	/**
	 * 
	 */
	public function tree($arguments){
		$tree_name = trim( $this->config('name') );
		$tree_description = trim($this->config('desc'));
		if (!empty($tree_name)){
			$tree = FireliteTree::where_name($tree_name)->first();

			if (!$tree){
				$tree = new FireliteTree(array(
					'name' => $tree_name,
					'description' => $tree_description
				));
				$tree->save();
			} else {
				echo "A tree with that name already exists!";
			}
		} else {
			echo 'You need to specify a tree name';
		}
	}
	
}