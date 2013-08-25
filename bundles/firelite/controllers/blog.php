<?php

/**
 * The main blog controller
 */
class Firelite_Blog_Controller extends Controller {

	/**
	 * Admin Controller Constructor
	 */

	public function __construct() {
	}

	/**
	 * Default action for the controller
	 *
	 * @return Response
	 */
	public function action_index($name = null) {

		//get an array of all posts
		$posts = FirelitePost::with( 'post.fields' )->all();
		//A php bug causes a fatal error in usort if an exception is thrown in the sort function (this will happen if the post has no published_date.
		//Suppress PHP errors for the sort.
		@usort( $posts, function ($a, $b) {
			return strcmp( $b->getField( 'publish_date' ), $a->getField( 'publish_date' ) );
		} );

		if ( $name == null ) {

			return View::make( 'firelite/templates.blog-index.index' )->with( 'posts', $posts );
		} else {
			// get the single post by name and add it to an array for the next step
			$post = FirelitePost::where( 'name', '=', $name )->first();

			return View::make( Firelite::getTemplateView( $posts[ 0 ]->template ) )->with( 'posts', $posts )->with( 'post', $post );

		}

	}


}