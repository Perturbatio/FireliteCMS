<?php

Route::get('/form', function(){
	
});

Route::any(Firelite::config('admin.route'), array('as'=>'firelite_admin', 'uses' => 'firelite::admin@index'));

Route::any('(.*)*',  array( 'before'=>'firelite', function() {
	return Firelite::handleRoute();
}));


Route::any('install_tables', function(){
	$posts = new FirelitePost();
	$posts->firelite_install();
});
