<?php
require_once('basetask.php');
use \FireliteTemplate;
use \Firelite;

class Firelite_Template_Task extends Firelite_Basetask {

	/**
	 * 
	 */
	public function run(){
		echo "Specify the command you want to run.". CRLF;
		echo "--view <view_name> - a valid laravel view". CRLF;
	}
	
	
	/**
	 * 
	 * @param array $arguments 
	 */
	public function install($arguments){
		//check if it's already installed
		$template_view = $this->config('view');
		
		if ( !empty( $template_view ) ){
			//get the name in Class format
			$class_name =  'FireliteTemplate';
			
			$view = View::exists( Firelite::getTemplateView($template_view), true );
			$json_config_no_blade = str_replace( BLADE_EXT, '', $view ) . '.firelite.json';
			$json_config_no_php = str_replace( EXT, '', $view ) . '.firelite.json';
			
			if ( file_exists( $json_config_no_blade ) ) {
				$json_data = file_get_contents( $json_config_no_blade );
			} else if ( file_exists( $json_config_no_php ) ) {
				$json_data = file_get_contents( $json_config_no_php );
			} else {
				echo 'Can\'t locate template json file' . CRLF;
				return;
			}
			
			$template_config = json_decode($json_data);
			
			if ( $template_config !== null ){


				//echo $json_config . CRLF;

				if ( class_exists($class_name) ){
					//check if the template is already installed
					$template = FireliteTemplate::where( 'name', '=', $template_view )->first();

					if ( empty($template) || !$template ){// || $template->version < $class_name::firelite_version() ){

						//invoke the install method on the class
						$import_result = $class_name::import($template_config);
						foreach($import_result as $res){
							echo $res . CRLF;
						}

					} else {
						echo 'Template ', $class_name, ' is already installed.' . CRLF;
					}
				} else {
					echo 'FireliteTemplate class not found' . CRLF;
				}

			}
		} else {
			echo 'You need to specify the template view with --view=<view_name>.' . CRLF;
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
			$class_name = static::get_class_name($arguments, 'Firelite', 'Template');
			
			if ( class_exists( $class_name ) ){
				
				if ( method_exists( $class_name, 'firelite_uninstall' ) ){

					//check if already taken
					$template = \FireliteTemplate::where( 'name', '=', $base_class_name )->first();
					
					if ( $template && $template->version >= $class_name::firelite_version() ){
						
						//invoke the install method on the class
						$res = $class_name::firelite_uninstall($template->version);
						if ( $res !== false ){

							if ( $template->delete() ){
								echo $class_name . ' uninstalled.' . CRLF;
							}

							
						} else {
							echo 'Error uninstalling ' . $class_name . CRLF;
						}

					} else {
						echo 'Template ', $class_name, ' is not installed.' . CRLF;
					}

				} else {
					echo 'Template ', $class_name, ' does not have an uninstall method.' . CRLF;
				}
				
			} else {
				echo 'Cannot locate class alias ' . $class_name;
			}
		} else {
			echo 'You need to specify the class name.' . CRLF;
		}
	}

}