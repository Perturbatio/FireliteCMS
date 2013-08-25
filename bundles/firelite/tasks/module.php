<?php
require_once('basetask.php');
use \FireliteNodetype;

class Firelite_Nodetype_Task extends Firelite_Basetask {

	/**
	* 
	*/
	public function run(){
		echo "\nSpecify the command you want to run.\n";
	}
	
	
	/**
	 *
	 * @param type $arguments 
	 */
	public function install($arguments){
		//check if it's already installed
		if ( !empty( $arguments ) ){
			//get the nodetype name in Class format
			$base_class_name = static::get_base_classname($arguments);
			$class_name = static::get_class_name($arguments);
			
			if ( class_exists( $class_name ) ){
				$required_methods = static::has_required_methods($class_name, array('firelite_install', 'firelite_uninstall', 'firelite_version', 'firelite_description') );

				if ( $required_methods === true ){

					//check if the nodetype is already taken
					$nodeType = FireliteNodetype::where( 'name', '=', $base_class_name )->first();
					
					if ( !$nodeType || $nodeType->version < $class_name::firelite_version() ){
						
						//invoke the install method on the class
						if ( $class_name::firelite_install() ){
							if (!$nodeType){
								$nodeType = new FireliteNodetype();
							}
							$nodeType->name = $base_class_name;
							$nodeType->version = $class_name::firelite_version();
							$nodeType->description = $class_name::firelite_description();
							if ( $nodeType->save() ){
								echo $class_name . ' installed.' . CRLF;
							} else {
								echo 'Error saving nodetype info.' . CRLF;
							}
							
						} else {
							echo 'Error installing ' . $class_name . CRLF;
						}

					} else {
						echo 'Nodetype ', $class_name, ' is already installed.' . CRLF;
					}

				} else {
					foreach($required_methods as $error){
						echo $error . CRLF;
					}
				}
			} else {
				echo 'Cannot locate class alias ' . $class_name;
			}
		} else {
			echo 'You need to specify the class name.' . CRLF;
		}
	}

	/**
	* 
	*/
	public function uninstall($arguments){
		//check if it's already installed
		if ( !empty( $arguments ) ){
			//get the nodetype name in Class format
			$base_class_name = static::get_base_classname($arguments);
			$class_name = static::get_class_name($arguments);
			
			if ( class_exists( $class_name ) ){
				
				if ( method_exists( $class_name, 'firelite_uninstall' ) ){

					//check if the nodetype is already taken
					$nodeType = \FireliteNodetype::where( 'name', '=', $base_class_name )->first();
					
					if ( $nodeType && $nodeType->version >= $class_name::firelite_version() ){
						
						//invoke the install method on the class
						$res = $class_name::firelite_uninstall($nodeType->version);
						if ( $res !== false ){
							
							if ( $res === 'delete_nodetype' || static::config('purge') ){
								if ( $nodeType->delete() ){
									echo $class_name . ' uninstalled.' . CRLF;
								} else {
									echo 'Error saving nodetype info.' . CRLF;
								}
								
							} else {
								$nodeType->version = $nodeType->version - 1;
								$nodeType->save();
								echo $class_name . ' uninstall complete, nodetype preserved';
							}
							
						} else {
							echo 'Error uninstalling ' . $class_name . CRLF;
						}

					} else {
						echo 'Nodetype ', $class_name, ' is not installed.' . CRLF;
					}

				} else {
					echo 'Nodetype ', $class_name, ' does not have an uninstall method.' . CRLF;
				}
				
			} else {
				echo 'Cannot locate class alias ' . $class_name;
			}
		} else {
			echo 'You need to specify the class name.' . CRLF;
		}
	}

	
	public function upgrade($arguments){
		
		print_r($arguments);
	}

}