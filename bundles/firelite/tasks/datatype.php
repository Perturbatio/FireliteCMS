<?php
require_once('basetask.php');
use \FireliteDatatype;

class Firelite_Datatype_Task extends Firelite_Basetask {

	/**
	 * 
	 */
	public function run(){
		echo "\nSpecify the command you want to run.\n";
	}
		
	/**
	 *
	 * @param array $arguments
	 * @return string 
	 */
	static protected function get_class_name($arguments, $prefix = 'Firelite', $suffix = ''){
		if ( !empty( $arguments ) ){
			return "FireliteDatatype" . static::get_base_classname($arguments);
		}
		return 'No arguments';
	}
	
	/**
	 *
	 * @param array $arguments 
	 */
	public function install($arguments){
		//check if it's already installed
		if ( !empty( $arguments ) ){
			//get the datatype name in Class format
			$base_class_name = static::get_base_classname($arguments);
			$class_name = static::get_class_name($arguments);
			
			if ( class_exists( $class_name ) ){
				$required_methods = static::has_required_methods($class_name, array('firelite_install', 'firelite_uninstall', 'firelite_version', 'firelite_description') );
				
				if ( $required_methods === true ){
					
					//check if the datatype is already taken
					$dataType = FireliteDatatype::where( 'name', '=', $base_class_name )->first();
					
					if ( empty($dataType) || !$dataType || $dataType->version < $class_name::firelite_version() ){
						
						//invoke the install method on the class
						if ( $class_name::firelite_install() ){
							if (!$dataType){
								$dataType = new FireliteDatatype();
							}
							$dataType->name = $base_class_name;
							$dataType->version = $class_name::firelite_version();
							$dataType->description = $class_name::firelite_description();
							if ( $dataType->save() ){
								echo $class_name . ' installed.' . CRLF;
							} else {
								echo 'Error saving datatype info.' . CRLF;
							}
							
						} else {
							echo 'Error installing ' . $class_name . CRLF;
						}

					} else {
						echo 'Datatype ', $class_name, ' is already installed.' . CRLF;
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
			//get the datatype name in Class format
			$base_class_name = static::get_base_classname($arguments);
			$class_name = static::get_class_name($arguments);
			
			if ( class_exists( $class_name ) ){
				
				$required_methods = static::has_required_methods($class_name, array('firelite_install', 'firelite_uninstall', 'firelite_version', 'firelite_description') );
				
				if ( $required_methods === true ){

					//check if the datatype is already taken
					$dataType = \FireliteDatatype::where( 'name', '=', $base_class_name )->first();
					
					if ( $dataType && $dataType->version >= $class_name::firelite_version() ){
						
						//invoke the install method on the class
						$res = $class_name::firelite_uninstall($dataType->version);
						if ( $res !== false ){
							
							if ( $res === 'delete_datatype' || static::config('purge') ){
								if ( $dataType->delete() ){
									echo $class_name . ' uninstalled.' . CRLF;
								} else {
									echo 'Error saving datatype info.' . CRLF;
								}
								
							} else {
								$dataType->version = $dataType->version - 1;
								$dataType->save();
								echo $class_name . ' uninstall complete, datatype preserved';
							}
							
						} else {
							echo 'Error uninstalling ' . $class_name . CRLF;
						}

					} else {
						echo 'Datatype ', $class_name, ' is not installed.' . CRLF;
					}

				} else {
					echo 'Datatype ', $class_name, ' does not have an uninstall method.' . CRLF;
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