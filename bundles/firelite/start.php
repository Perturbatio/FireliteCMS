<?php
set_exception_handler(function($e)
{
	require_once Bundle::path('firelite').'error'.EXT;

	\Firelite\Error::exception($e);
});


set_error_handler(function($code, $error, $file, $line)
{
	require_once Bundle::path('firelite').'error'.EXT;

	\Firelite\Error::native($code, $error, $file, $line);
});


register_shutdown_function(function()
{
	require_once Bundle::path('firelite').'error'.EXT;

	\Firelite\Error::shutdown();
});

require_once('constants.php');

Autoloader::namespaces( array(
	'Firelite' => Bundle::path('firelite'),
	'Firelite\\Plugins' => Bundle::path('firelite') . 'plugins',
	'Firelite\\Editors' => Bundle::path('firelite') . 'editors',
) );

Autoloader::alias('Firelite\\Str', 'Str');

Autoloader::directories(Bundle::path('firelite') . 'libraries');

Autoloader::map(array(
	'Firelite_Base_Controller' => Bundle::path('firelite') . 'controllers' . DS . 'base' . EXT,
));

//Firelite class aliases to allow the application to override the class if needed
//all models should extend the aliases rather than the actual class
$firelite_aliases = Config::get('firelite::firelite.aliases');

if ( !empty( $firelite_aliases ) ) {
	foreach ( $firelite_aliases as $alias => $path ) {
		Autoloader::alias( $path, $alias );
	}
}

//Allow application defined aliases for Firelite to override the Firelite bundle
//this requires an aliases array to be defined in the application's firelite config
$application_aliases = Config::get('aliases');

if ( !empty( $application_aliases ) ) {
	foreach ( $application_aliases as $alias => $path ) {
		Autoloader::alias( $path, $alias );
	}
}

$admin_master = Firelite::config('admin.master_view', 'firelite::admin.master');
View::name($admin_master, 'admin_master');

//TODO: See if there's a cleaner way of doing this
/**
 * This composer generates data for the admin left nav 
 * It relies on a modification to the View::composer method (in \Firelite\View)
 * which allows for named views
 * 
 */
\Firelite\View::composer('name: admin_master', function($view){
	$segments = Str::segments( Request::uri() );

	//Log::firelite( print_r( $segments, true ) );
	$plugins = Firelite::getPlugins();
	
	$nav_items = array();
	
	foreach ( $plugins as $plugin_name => $plugin_class ){
		
		$main_nav = $plugin_class::getMainNav();
		
		if ( empty( $main_nav ) ){
			continue;
		}
		
		$nav_action = str_replace( 'action_', '', $main_nav[ 'action' ] );
		
		$main_nav[ 'url' ] = Firelite::getPluginURL($plugin_name, $nav_action);
		$sub_nav = $plugin_class::getSubNav();
		
		if ( !empty( $sub_nav ) ){
			foreach ( $sub_nav as &$sub_item ){
				$sub_nav_action = str_replace( 'action_', '', $sub_item[ 'action' ] );
				
				$sub_item[ 'url' ] = Firelite::getPluginURL( $plugin_name, $sub_nav_action );
			}
		}
		$nav_items[ $plugin_class ] = array(
			'main_nav' => $main_nav,
			'sub_nav' => $sub_nav
		);
		
	}
	
	$current_plugin = null;
	
	if ( count( $segments ) > 1 ){
		if ( Firelite::pluginExists( $segments[ 1 ] ) ){
			$current_plugin = Firelite::getPluginClass( $segments[ 1 ] );
		}
	}
	$view->with('nav_items', $nav_items)->with('current_plugin', $current_plugin);
	
	
});

//define validation rules for a valid node name
Validator::register('node_name', function($attribute, $value, $parameters){
	//essentially this is the same as alpha_dash but also allows '/' on its own (for a root node)
	if (!empty($parameters) && $parameters[0] == 'is_root'){
		return preg_match('/^([-a-z0-9_-])+|\/$/i', $value);
	} else {	
		return preg_match('/^([-a-z0-9_-])+$/i', $value);
	}
});

//determine if a node is unique within its level
//usage "node_unique:<parent_node_id>,<tree_id>
Validator::register('node_unique', function($attribute, $value, $parameters){
	$result = false;//fail by default
	if ( !empty( $parameters ) ){
		$query = \FireliteNode::where_name( $value )->where_parent_node_id( $parameters[ 0 ] );
		
		if ( isset( $parameters[ 1 ] ) ){
			$query = $query->where_tree_id( $parameters[ 1 ] );
		}

		$nodes = $query->get();
		$result = ( count( $nodes ) == 0 );
		
	}
	return $result;
});

Event::listen('laravel.query', function($sql, $bindings, $time) {
	//echo 'SQL: ', $sql, ' <br />bindings: ', '<pre>', print_r($bindings, true), '</pre>';
});
/*
Auth::extend('firelite', function() {
	return new FireliteAuthDriver(Firelite::config('auth.model'));
});
*/