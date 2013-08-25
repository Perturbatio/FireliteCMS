<?php namespace Firelite\Plugins;

use Event;
use Firelite;
use FireliteBasePlugin;
use FireliteNode;
use FireliteNodetype;
use FirelitePage;
use FireliteTemplate;
use FireliteTree;
use Input;
use Log;
use Redirect;
use Request;
use stdClass;
use View;

class NodesPlugin extends FireliteBasePlugin {
	
	/**
	 * 
	 * @var type 
	 */
	static public $nav = array(
		'main_nav' => array(
			'action' => 'action_index',
			'link_text' => 'Nodes'
		),
		/*'sub_nav' => array(
			array(
				'action' => 'action_add',
				'link_text' => 'Add Node'
			),
		)*/
	);
	
	/**
	 * 
	 * @param Node $node
	 * @param Boolean $deep
	 * @return \stdClass
	 */
	protected function node_to_jsonable($node, $deep = true){
		$result = new stdClass();
		$result->data = new stdClass();
		
		$nodeType = FireliteNodeType::where_id($node->nodetype_id)->first();
		
		//resolve the nodetype handler
		$handler_class = Firelite::getNodeTypeHandler( $nodeType );

		if ( class_exists( $handler_class ) ){
			$handler = $handler_class::from_node( $node );
			$result->data->handler_id = $handler->id;
			$result->data->handler_class = $handler_class;
			$result->data->node_type = $nodeType->to_array();
			$result->data->edit_url = Firelite::getPluginURL('nodes', 'edit', array($handler->id));
			$result->data->add_child_url = Firelite::getPluginURL('nodes', 'add', array($node->tree_id,$node->id));
		} else {
			$result->data->handler_id = 0;
			$result->data->handler_class = 'unknown';
		}
		$result->id = $node->id;
		$result->name = $node->name;
		$result->label = $node->name;
		$result->data->link_text = $node->link_text;
		$result->data->link_title = $node->link_title;
		
		
		if ($node->children){
			$result->children = array();
			if ( $deep === true ){
				foreach( $node->children as $child ){
					$result->children[] = $this->node_to_jsonable($child);
				}
			}
		}
		return $result;
	}
	
	
	/**
	 * 
	 * @param type $requested_plugin_details
	 * @param type $view_dir
	 * @param type $tree_id
	 * @return View
	 */
	public function action_index($requested_plugin_details, $view_dir, $tree_id = null){
		
		if ( empty( $tree_id ) ){
			$site_tree = Firelite::config('default_site_tree', 1);
		} else {
			$site_tree = (int)$tree_id;
		}

		$tree = FireliteTree::find( $site_tree );
		$treeview_nodes = array();
			
		if ($tree){
			/*
			$nodeTypePage = FireliteNodetype::where('name', '=', 'Page')->first();
			
			if ( !$nodeTypePage ){
				return "Page nodetype is not installed";
			}*/
			
			$nodes = $tree->getNodeMap();
			//$page_to_edit = FirelitePage::from_node( $node->id );
			//Firelite::getPluginURL('pages', 'edit', array($page_to_edit->id));
			
			if ( count( $nodes ) > 1 ) {
				$root = array_shift( $nodes );
				$root->is_locked = true;
				//skip root by just iterating through the children instead
				$treeview_nodes[] = $this->node_to_jsonable($root);

			}
			
			
			
		} else {
			$nodes = array();
		}
		
		return View::make($view_dir . 'nodes.index')
			->with('nodes', $nodes)
			->with('tree', $tree)
			->with('treeview_nodes', $treeview_nodes);
		
	}
	
	/**
	 * 
	 * @param type $requested_plugin_details
	 * @param type $view_dir
	 * 
	 * @return View
	 */
	public function action_add( $requested_plugin_details, $view_dir, $tree_id = null, $parent_node_id = null ){
		if ( empty( $tree_id ) ){
			$site_tree = Firelite::config('default_site_tree', 1);
		} else {
			$site_tree = (int)$tree_id;
		}
		$tree = FireliteTree::find( $site_tree );
		$validation_result = null;
		
		
		if ( Request::method() == 'POST' ){
			
			$input = Input::all();
			
			//remap the keys of input, strip out the page_ prefix which is 
			//there to help prevent ids and input names clashing with other items
			$new_input = array();
			foreach ( $input as $key => $value ){
				$new_input[ str_replace( 'page_', '', $key ) ] = $value;
			}
			$input = $new_input;
			
			//TODO: use the model's error methods

			//create the node entry first
			$node = new FireliteNode();
			$input['tree_id'] = $tree->id;
			$node_valid = $node->validate( $input );
			
			
			if ( $node_valid === true ){
				//echo '<pre>', print_r($node, true), '</pre>';
				$node->name = $input['name'];
				$node->nodetype_id = FireliteNodetype::where_name('Page')->first()->id;
				$node->set_link_text($input['link_text']);
				$node->set_link_title($input['link_title']);
				$node->save();
				
				//$node->reload();//TODO: check if this is needed, it could just be my paranoia
				
				if (isset($input['parent_node_id'])){
					$parent_node = (int)$input['parent_node_id'];

				} else {
					$parent_node = 0;
				}
				
				$parentNode = FireliteNode::where_id( (int)$parent_node )->first();
				
				if ($parentNode){
					$parentNode->addChild($node);
					$tree->rebuild_paths();
				} else {
					$node->set_left(1);
					$node->set_right(2);
					$node->set_tree($site_tree);
					$node->path = $node->name;
					$node->save();
				}
				
				$page = new FirelitePage();
				$validation_result = $page->validate( $input );
				if ( $validation_result === true ){
					$page->set_title($input['title']);
					$page->template_id = $input['template_id'];
					$page->node_id = $node->id;
					$page->save(false);
					$page->reload();
					//Log::firelite('redirecting to: ' . Firelite::getPluginURL('pages','edit', array($page->id)));
					return Redirect::to( Firelite::getPluginURL('pages','edit', array($page->id) ) );
				} else {
					return $node_valid->errors->all();
					//echo 'PAGE NOT VALID!';
				}
			} else {
				echo 'NODE NOT VALID!';
			}
			
			//$save_res = $page->update_from_array($input);
			
		}
		
		
		$all_templates = FireliteTemplate::all();

		if ( $tree ){
			$nodes = $tree->getNodeMap();
		} else {
			$nodes = array();
		}

		$parent_pages = array();
		if (!empty($nodes)){
		foreach ( $nodes as $node ){
			$parent_pages[ $node->id ] = $node->path;
		}
		}

		$template_choices = array();

		foreach ( $all_templates as $template ){
			$template_choices[ $template->id ] = $template->name;
		}

		$data = array(
			'templates' => $template_choices,
			'parent_pages' => $parent_pages,
			'validate_res' => $validation_result,
			'parent_node_id' => (int)$parent_node_id,
		);

		return View::make( $view_dir . 'nodes.add', $data );
	}
	
	/**
	 * 
	 * @param array $requested_plugin_details
	 * @param string $view_dir
	 * @param integer $page_id
	 * 
	 * @return View
	 */
	public function action_edit( $requested_plugin_details, $view_dir, $page_id = 0 ){
		
		echo '<pre>', print_r(Firelite::getNodeEditors(), true), '</pre>';
		//$requested_plugin_details = $this->getRequestedPluginDetails();
		//$plugin = new $requested_plugin_details['class']();
		$node = null;
		if ( isset( $requested_plugin_details[ 'params' ][ 0 ] ) ) {
			$node = FireliteNode::find($requested_plugin_details[ 'params' ][ 0 ]);
		}
		
		if ( $node ) {
			$editors = Firelite::getNodeEditors($node->nodetype->name);
			echo '<pre>', print_r($editors, true), '</pre>';
		} else {
			return View::make( $view_dir . 'error.index', array( 'errors' => array(
					'Invalid node id specified'
				))
			);
		}
		
		$node = $requested_plugin_details['params'][0];
		echo '<pre>', print_r($requested_plugin_details, true), '</pre>';
		//$editor = Firelite::getNodeEditors();
		return;
		$is_authorised = FireliteAuth::check();
		
		if ( Firelite::pluginExists('login') && !$is_authorised ) {
			if ( $plugin::needlogin() === true ) {
				return Redirect::to( Firelite::getPluginURL( 'login', 'index' ) );
			}
		} else {
			if ( !$is_authorised && $plugin::needlogin() === true ) {
				return 'The plugin you are trying to access requires a login, but there is no login handler currently installed';;
			}
		}

		$view_prefix = Firelite::config( 'admin.view_dir', 'firelite::admin.' );

		$view = View::make( $view_prefix . 'admin.index' )
			->with('requested_plugin_details', $requested_plugin_details);

		$plugin_params = array(
			'requested_plugin_details' => $requested_plugin_details,
			'view_dir' => $view_prefix
		);

		//  parameters passed after the controller and the action will be 
		// passed straight through as parameters for the plugin
		$params = $requested_plugin_details['params'];
		$plugin_params = array_merge($plugin_params, $params);

		// pass the result of the plugin's action to the admin view to render in the main area
		$plugin_response = $plugin->execute(
			$requested_plugin_details['action'], 
			$plugin_params
		);
		if ( is_a( $plugin_response, 'Redirect' ) ){
			return $plugin_response;
		} else if ( (int)$plugin_response->status() === 404) {
			$view->with( 'plugin_view', View::make($view_prefix . 'error.index' )->with('errors', array('Invalid request')));
		} else {
			$view->with( 'plugin_view', $plugin_response );
		}
		return $view;
			
		
		return;
		$event_params = array(
			$page_id,
		);
		
		$response = Event::until('firelite.plugin.nodes.edit:before', $event_params);
		
		if ( is_a( $response, 'Redirect' ) ){
			
			return $response;
			
		} else if ( $response === false ){
			
			return Redirect::to( Firelite::getPluginURL( 'nodes', 'index' ) )->with( 'flash_message', 'You are not allowed to edit this node' );
			
		}
		
		$page_id = (int) $page_id;
		
		if ( $page_id < 1 ){
			return Redirect::to( Firelite::getPluginURL('nodes','index') );
		}
		
		$page = FirelitePage::where_id($page_id)->first();
		
		$node_tree = $page->node->tree_id;
		$tree = FireliteTree::find( $node_tree );
		$validation_result = null;

		if ( $page ){

			if ( $tree ){
				$nodes = $tree->getNodeMap();
			} else {
				$nodes = array();
			}

			$parent_pages = array();
			
			foreach ( $nodes as $node ){
				if ($node->id !== $page->node->id){ //exclude current page from the list
					$parent_pages[ $node->id ] = $node->path;
				}
			}
			
			if ( Request::method() == 'POST' ){
				$input = Input::all();
				
				//remap the keys of input, strip out the page_ prefix which is 
				//there to help prevent ids and input names clashing with other items
				$new_input = array();
				foreach ( $input as $key => $value ){
					$new_input[ str_replace( 'page_', '', $key ) ] = $value;
				}
				//hack for lack of a checkbox value when not checked
				if ( !isset( $new_input[ 'published' ] ) ){
					$new_input[ 'published' ] = 0;
				}
				
				$input = $new_input;
				
				//TODO: use the model's error methods
				$validation_result = $page->validate( $input );
				
				if ( $validation_result === true ){
					$old_parent = $page->node->parent_node_id;
					
					$parent_changed = false;
					$update_res = $page->update_from_array($input);
					if ($update_res){
						//detect if the parent node has changed before re-assigning
						if (isset($input['parent_node_id'])){
							$parent_node_id = (int)$input['parent_node_id'];
						} else {
							$parent_node_id = 0;
						}
						if ($old_parent !== $parent_node_id){
							$parent_changed = true;
							//Log::firelite('page parent changed');
							$parentNode = FireliteNode::where_id( (int)$input['parent_node_id'] )->first();
							$parentNode->addChild($page->node);
						}

						$page->reload();
					}
				}
				//TODO: page template change - need to decide what happens to fields if the template changes
				//
				//the tree needs to be rebuilt (since the paths may change)
				$tree->rebuild_paths();
			}
			$data = array(
				'parent_pages' => $parent_pages,
				'page' => $page,
				'validate_res' => $validation_result,
			);
			return View::make( $view_dir . 'nodes.edit', $data);
		} else {
			return View::make($view_dir . 'error.index', 
				array('errors' => array(
					'Invalid node id specified'
					)
				)
			);
		}
		
	}
	
	/**
	 * convert the adjacency list array provided by the JSON into a 
	 * nested set by calculating the lefts and rights
	 * 
	 * @param stdClass $node
	 * @param Integer $last_pos
	 */
	protected function calc_nested(&$node, &$last_pos){
		if ( $node ){
			unset($node->data);//just to tidy up the data a little

			$node->lft = $last_pos++;
			if ( isset( $node->children ) && !empty( $node->children ) && is_array( $node->children ) ){
				foreach( $node->children as $child ){
					if (!$this->calc_nested($child, $last_pos)){
						return false;
					}
				}
			}
			$node->rgt = $last_pos++;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Update all the nodes in the array
	 * 
	 * @param type $node_data
	 * @param type $parent_node_id
	 * @return boolean|string
	 */
	protected function update_structure($node_data, $parent_node_id){
		if ($node_data){
			$node = FireliteNode::find($node_data->id);
			if ($node){
				$node->set_left($node_data->lft);
				$node->set_right($node_data->rgt);
				$node->set_parent_id( $parent_node_id );
				
				if ($node->save()){
					if ( isset( $node_data->children ) && !empty( $node_data->children ) && is_array( $node_data->children ) ){
						
						foreach( $node_data->children as $child ){
							
							$parent_node_id = $node_data->id;
							$res = $this->update_structure($child, $parent_node_id);
							if ($res !== true){
								return $res;
							}
						}
						return true;
					} else {
						return true;
					}
				} else {
					
					return 'error saving node ' . $node_data->id;
				}
			} else {
				return 'invalid node';
			}
		} else {
			return 'invalid node data';
		}
	}
	
	/**
	 * 
	 * @param type $requested_plugin_details
	 * @param type $view_dir
	 * @return type
	 */
	public function action_update_structure( $requested_plugin_details, $view_dir){
		
		$data = Input::json(); //new_structure
		
		if ( Request::method() == 'POST' ){
			if ( !empty( $data ) ){
				$site_tree = Firelite::config( 'default_site_tree', 1 );//TODO: change this to recognise the correct tree
				$tree = FireliteTree::find( $site_tree );

				//because root is not in the drag and drop tree (because the user will break the site)
				//we need to re-inject it as the parent of the top level nodes
				$root_node = $tree->get_root();
				$root = new stdClass();
				$root->id = $root_node->id;
				$root->name = $root_node->name;
				$root->children = array();

				$new_structure = $data->structure;
				
				foreach($new_structure->children as $child){
					$root->children[] = $child;
				}
				$start_pos = 1;
				$this->calc_nested($root, $start_pos);
				
				$root_parent = 0;
				$update_res = $this->update_structure($root, $root_parent);
				
				if ( $update_res === true ){
					$tree = FireliteTree::find( $site_tree );
					$tree->rebuild_paths();
					
					echo json_encode(array('result'=>true,'message'=>'updated'));
					exit;
				} else {
					echo json_encode(array('result'=>false,'message'=>$update_res));
					exit;
				}
			} else {
				echo json_encode(array('result'=>false,'message'=>'No Input', 'all_input'=>Input::all()));
				exit;
			}
		} else {
			
			return View::make($view_dir . 'error.index', 
				array('errors' => array(
					'You need to send the data via POST'
					)
				)
			);
			
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
		return 'Administer the pages on your website';
	}
	
}