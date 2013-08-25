<?php namespace Firelite\Plugins;

use Event;
use Firelite;
use FireliteBasePlugin;
use FirelitePost;
use FireliteTemplate;
use Input;
use Log;
use Redirect;
use Request;
use View;

class BlogPlugin extends FireliteBasePlugin {
	
	/**
	 * 
	 * @var type 
	 */
	static public $nav = array(
		'main_nav' => array(
			'action' => 'action_index',
			'link_text' => 'Blog'
		),
		'sub_nav' => array(
			array(
				'action' => 'action_add',
				'link_text' => 'Add Post'
			),
		)
	);
	
	/**
	 *
	 * @return View 
	 */
	public function action_index($requested_plugin_details, $view_dir){
		
			$posts = FirelitePost::all();
			return View::make($view_dir . 'posts.index')->with('posts', $posts);
	}
	
	/**
	 * 
	 * @param type $requested_plugin_details
	 * @param type $view_dir
	 * 
	 * @return View
	 */
	public function action_add( $requested_plugin_details, $view_dir ){
		

		if ( Request::method() == 'POST' ){
			$input = Input::all();
			
			//remap the keys of input, strip out the post_ prefix which is 
			//there to help prevent ids and input names clashing with other items
			$new_input = array();
			foreach ( $input as $key => $value ){
				$new_input[ str_replace( 'post_', '', $key ) ] = $value;
			}
			$input = $new_input;
				
			$post = new FirelitePost();
			$validation_result = $post->validate( $input );
			if ( $validation_result === true ){
				$post->set_title($input['title']);
				$post->set_name($input['name']);
				$post->template_id = $input['template_id'];
				$post->save(false);
				$post->reload();
				//Log::firelite('redirecting to: ' . Firelite::getPluginURL('blog','edit', array($post->id)));
				return Redirect::to( Firelite::getPluginURL('blog','edit', array($post->id) ) );
			} else {
				print_r($validation_result->errors->all());
				exit;
			}			
		}
		
		
		$all_templates = FireliteTemplate::all();

		$template_choices = array();

		foreach ( $all_templates as $template ){
			$template_choices[ $template->id ] = $template->name;
		}

		$data = array(
			'templates' => $template_choices,
		);

		return View::make( $view_dir . 'posts.add', $data );
	}
	
	/**
	 * 
	 * @param array $requested_plugin_details
	 * @param string $view_dir
	 * @param integer $post_id
	 * 
	 * @return View
	 */
	public function action_edit( $requested_plugin_details, $view_dir, $post_id = 0 ){
		$event_params = array(
			$post_id,
		);
		
		$response = Event::until('firelite.plugin.post.edit:before', $event_params);
		
		if ( is_a( $response, 'Redirect' ) ){
			
			return $response;
			
		} else if ( $response === false ){
			
			return Redirect::to( Firelite::getPluginURL( 'blog', 'index' ) )->with( 'flash_message', 'You are not allowed to edit this post' );
			
		}
		
		$post_id = (int) $post_id;
		
		if ( $post_id < 1 ){
			return Redirect::to( Firelite::getPluginURL('blog','index') );
		}
		
		$post = FirelitePost::where_id($post_id)->first();
		$validation_result = null;

		if ( $post ){
			
			if ( Request::method() == 'POST' ){
				$input = Input::all();
				
				//remap the keys of input, strip out the post_ prefix which is 
				//there to help prevent ids and input names clashing with other items
				$new_input = array();
				foreach ( $input as $key => $value ){
					$new_input[ str_replace( 'post_', '', $key ) ] = $value;
				}
				$input = $new_input;
				
				//TODO: use the model's error methods
				$validation_result = $post->validate( $input );
				
				if ( $validation_result === true ){
					if(Input::has('post_published')){
						$post->publish();
					}else{
						$post->unpublish();
					}
					$update_res = $post->update_from_array($input);
				}
				//TODO: post template change
				
			}
			$data = array(
				'post' => $post,
				'validate_res' => $validation_result,
			);
			return View::make( $view_dir . 'posts.edit', $data);
		} else {
			//TODO: implement better handler for editing non-existent posts, perhaps redirect (or offer a link) to post/add?
			return 'Post not found';
		}
		
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
		return 'Administer your blog';
	}
	
}