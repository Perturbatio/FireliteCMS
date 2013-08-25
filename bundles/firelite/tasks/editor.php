<?php
require_once('basetask.php');
use \FireliteEditor;

class Firelite_Editor_Task extends Firelite_Basetask {

	/**
	 * 
	 */
	public function run(){
		echo "\nSpecify the command you want to run.\n";
	}
	
	
	/**
	 * 
	 * @param array $arguments 
	 */
	public function install($arguments){
		//check if it's already installed
		if ( !empty( $arguments ) ){
			//get the name in Class format
			$base_class_name = static::get_base_classname($arguments);
			$class_name = static::get_class_name($arguments, 'Firelite', 'Editor');
			
			if ( class_exists( $class_name ) ){
				$required_methods = static::has_required_methods($class_name, array('firelite_install', 'firelite_uninstall', 'firelite_version', 'firelite_description') );
				
				if ( $required_methods === true ){
					
					//check if the datatype is already taken
					$editor = FireliteEditor::where( 'name', '=', $base_class_name )->first();
					
					if ( empty($editor) || !$editor || $editor->version < $class_name::firelite_version() ){
						
						//invoke the install method on the class
						if ( $class_name::firelite_install() ){
							if ( !$editor ){
								$editor = new FireliteEditor();
							}
							$editor->name = $base_class_name;
							$editor->version = $class_name::firelite_version();
							
							if ( static::config('enable') ){
								$editor->enabled = 1;
							} else {
								$editor->enabled = 0;
							}
							
							$editor->description = $class_name::firelite_description();
							if ( $editor->save() ){
								echo $class_name . ' installed.' . CRLF;
							} else {
								echo 'Error saving datatype info.' . CRLF;
							}
							
						} else {
							echo 'Error installing ' . $class_name . CRLF;
						}

					} else {
						echo 'Editor ', $class_name, ' is already installed.' . CRLF;
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
			//get the name in Class format
			$base_class_name = static::get_base_classname($arguments);
			$class_name = static::get_class_name($arguments, 'Firelite', 'Editor');
			
			if ( class_exists( $class_name ) ){
				
				if ( method_exists( $class_name, 'firelite_uninstall' ) ){

					//check if already taken
					$editor = \FireliteEditor::where( 'name', '=', $base_class_name )->first();
					
					if ( $editor && $editor->version >= $class_name::firelite_version() ){
						
						//invoke the install method on the class
						$res = $class_name::firelite_uninstall($editor->version);
						if ( $res !== false ){

							if ( $editor->delete() ){
								echo $class_name . ' uninstalled.' . CRLF;
							}

							
						} else {
							echo 'Error uninstalling ' . $class_name . CRLF;
						}

					} else {
						echo 'Editor ', $class_name, ' is not installed.' . CRLF;
					}

				} else {
					echo 'Editor ', $class_name, ' does not have an uninstall method.' . CRLF;
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