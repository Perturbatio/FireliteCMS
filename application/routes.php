<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Simply tell Laravel the HTTP verbs and URIs it should respond to. It is a
| breeze to setup your application using Laravel's RESTful routing and it
| is perfectly suited for building large applications and simple APIs.
|
| Let's respond to a simple GET request to http://example.com/hello:
|
|		Route::get('hello', function()
|		{
|			return 'Hello World!';
|		});
|
| You can even respond to more than one URI:
|
|		Route::post(array('hello', 'world'), function()
|		{
|			return 'Hello World!';
|		});
|
| It's easy to allow URI wildcards using (:num) or (:any):
|
|		Route::put('hello/(:any)', function($name)
|		{
|			return "Welcome, $name.";
|		});
|
*/

/*
//this is a test page render event listener
Event::listen('firelite.page.render:before', function($page){
	if ($page->name == 'services'){
		Log::firelite('Before page render ' . $page->node->name);
		//echo '<pre>', print_r($page, true), '</pre>';
		return "Services is down for maintenance";
	}
});
*/
Event::listen('plugin:after', function($name){
	Log::firelite('Heard after event on ' . $name);
	return true;
});

Event::flusher('plugin', function($key, $name){
	switch($key){
		case 'a':
		case 'c':
			echo 'flusher:', $name,'<br />';
			Event::fire('plugin:after', array($name));
			return true;
		break;
		default:
		break;
	}
});



Router::register(array('GET', 'POST'), 'blog/(:any?)', array('as' => 'firelite_blog', 'uses' => 'firelite::blog@index'));


Route::get('bloginstall', function(){
	return;
		//handle any post specific stuff here
		//create posts table
			Schema::create( 'posts', function($table) {
				if ( false ) {
					$table = new Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'template_id');
				$table->string( 'title', 255 );
				$table->string( 'name', 255 )->unique();

				$table->timestamps();
			} );
			
			Schema::create( 'post_fields', function($table) {
				if ( false ) {
					$table = new Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'post_id');
				$table->integer( 'template_field_id');
				$table->integer( 'data_id' );
				$table->timestamps();

				$table->index( array('post_id', 'template_field_id', 'data_id'), 'index_post_templatefield_data' );
			} );
		
			Schema::create( 'postfield_largetexts', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'postfield_id')->unique();
				$table->integer( 'largetext_id')->unique();
				$table->timestamps();

				$table->index( array( 'postfield_id', 'largetext_id' ), 'postfield_datatype_index' );
			} );
		
		
			Schema::create( 'postfield_simpletexts', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'postfield_id')->unique();
				$table->integer( 'simpletext_id')->unique();
				$table->timestamps();

				$table->index( array( 'postfield_id', 'simpletext_id' ), 'postfield_datatype_index' );
			} );
		
		

});

Route::get('test-route', function(){

	$tree = FireliteTree::find( 1 );
	//$tree->rebuild_paths();
	
	$root = $tree->get_root();
	echo '<pre>', print_r($root->descendants, true), '</pre>';
	return;
	//$tree->rebuild_paths();
	$structure = $tree->getStructure();
	echo '<pre>', print_r($structure, true), '</pre>';
	return;
	echo Firelite::buildNav( $structure );
		echo '<pre>', print_r(Router::$routes, true), '</pre>';
		echo '<pre>', print_r(Autoloader::$mappings, true), '</pre>';
	return '';
	$site_tree = 1;
	
	
	$myNode = FireliteNode::where('path','=','/services')
		->where('tree_id', '=', $tree->id)
		->first();
	
	$myPage = FirelitePage::with('template')->where('node_id', '=', $myNode->id)->first();

	echo '<pre>', print_r($myPage->template, true), '</pre>';
	echo '<pre>', print_r($myPage->node, true), '</pre>';
	echo '<pre>', print_r($myPage, true), '</pre>';
	
	/*
	$targetNode = FireliteNode::where('name','=','HTML5')->where('tree_id', '=', $tree->id)->first();
	$child = FireliteNode::where('name','=','JavaScript')->where('tree_id', '=', $tree->id)->first();
	*/
	//$child->moveTo($targetNode, 'before');
	
	//echo '<pre>', print_r($parentNode, true), '</pre>';

	/*$child->name ='test';
	echo '<pre>', print_r($child, true), '</pre>';
	$child->reload();
	
	echo '<pre>', print_r($child, true), '</pre>';*/
	/*
	$child = new FireliteNode();
	$child->name = 'Laravel';
	$parentNode->addChild($child);
	
	$child = new FireliteNode();
	$child->name = 'HTML5';
	$parentNode->addChild($child);
	
	$child = new FireliteNode();
	$child->name = 'JavaScript';
	$parentNode->addChild($child);
	*/
	//$root = new FireliteNode();
	//$root->name = '/';
	
	//$tree->set_root($root);
	
	//$structure = $tree->getStructure();

	//echo showNav( $structure );
	//$root = $tree->get_root();
	
	//$parentNode = FireliteNode::where('name','=','about-us')->where('tree_id', '=', $site_tree)->first();
	
	//$newNode = new FireliteNode();
	//$newNode->name = 'delete-me';//'firelite-' . date('d-m-Y_H-i-s');
	
//	$res = $parentNode->appendChild( $newNode );
//	
//	if (!$res){
//		foreach( $parentNode->getErrors() as $error ){
//			echo $error;
//		}
//	} else {
//		echo 'success! ', $newNode->name, ' inserted';
//	}
	/*
	$nodeToDelete = FireliteNode::where('name','=','delete-me')->where('tree_id', '=', $site_tree)->first();
	
	if ($nodeToDelete){
		$nodeToDelete->delete();
	}
*/
	// echo '<pre>', print_r($deleteNode, true), '</pre>';

	
	
});

/*
|--------------------------------------------------------------------------
| Application 404 & 500 Error Handlers
|--------------------------------------------------------------------------
|
| To centralize and simplify 404 handling, Laravel uses an awesome event
| system to retrieve the response. Feel free to modify this function to
| your tastes and the needs of your application.
|
| Similarly, we use an event to handle the display of 500 level errors
| within the application. These errors are fired when there is an
| uncaught exception thrown in the application.
|
*/

Event::listen('404', function()
{
	return Response::error('404');
});

Event::listen('500', function()
{
	return Response::error('500');
});

/*
|--------------------------------------------------------------------------
| Route Filters
|--------------------------------------------------------------------------
|
| Filters provide a convenient method for attaching functionality to your
| routes. The built-in before and after filters are called before and
| after every request to your application, and you may even create
| other filters that can be attached to individual routes.
|
| Let's walk through an example...
|
| First, define a filter:
|
|		Route::filter('filter', function()
|		{
|			return 'Filtered!';
|		});
|
| Next, attach the filter to a route:
|
|		Router::register('GET /', array('before' => 'filter', function()
|		{
|			return 'Hello World!';
|		}));
|
*/

Route::filter('before', function()
{
	// Do stuff before every request to your application...
});

Route::filter('after', function($response)
{
	// Do stuff after every request to your application...
});

Route::filter('csrf', function()
{
	if (Request::forged()) return Response::error('500');
});

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::to('login');
});

/*Route::get('make_user', function(){
	DB::table('firelite_users')->insert(array(
		'username' => 'heehaw',
		'password' => Hash::make('heehaw')
	));
});*/