<?php namespace Firelite\Models;

use Event;
use Firelite;

/**
 * The Node class 
 */
class Node extends \FireliteNested {
	
	public static $_col_parent = 'parent_node_id';
	public static $_nested_model = 'FireliteNode';
	
	/**
	 * This array contains all rules and messages used by the node validation function
	 * 
	 * @var array 
	 */
	public $validation = array(
		'rules' => array(
			'name' => 'required|node_name|between:1,255', //the node_unique validation rule will be inserted dynamically since it relies on variables
			'link_text'=>'required|between:1,255',
			'link_title'=>'between:0,64',
			'parent_node_id' => 'integer|min:0',
		),
		'messages' => array(
			'name' => 'Name must be numbers, letters hyphens and underscores only, (only the root node can contain a slash) max length is 255',
			'link_text' => 'Link text is required (it\'s the text that is surrounded by the link) and must be no longer than 255 characters',
			'link_title' => 'The link title is not required, but should not be longer than 64 characters',
			'parent_node_id' => 'An invalid parent node was specified',
		)
	);
	
	/**
	 * 
	 * @return type
	 */
	public function nodetype(){
		return $this->belongs_to('FireliteNodetype');
	}
	
	/**
	 * overrides the base nested addChild to implement positional validation
	 * 
	 * @param Node $node
	 * @param string $where ('first' or 'last')
	 * @return boolean 
	 */
	public function addChild( $node, $where = 'last' ){
		//verify constraints are satisfied before proceeding
		
		$satisfied = false; //not satisfied by default
		
		if ( count( $this->children ) > 0 ){
			
			$found = false;
			//no node can have the same name as another node at the same depth (on the same tree)
			foreach( $this->children as $child ){
				
				if ( $child->name === $node->name ){
					$found = true;
					break;
				}
				
			}
			
			$satisfied = !$found;
		} else {
			//if there are no children, then we automatically satisfy the current rule
			$satisfied = true;
		}
		
		
		if ( $satisfied ){
			
			$eventParams = 	array(
				'parentNode' => $this,
				'childNode' => $node,
				'where' => $where
			);
			
			$responses = Event::fire('firelite.node.add:before', array( $eventParams ));
			$cancelled = false;
			
			//if any event handler can find any reason why these two nodes should not be joined...
			foreach( $responses as $response ){
				if ($response === false){
					$cancelled = true;
					break;
				}
			}
			
			if ( !$cancelled ){
				
				$res = parent::addChild( $node, $where ); //allow nested class to handle the child add
				
				$eventParams['add_result'] = $res;
				
				Event::fire('firelite.node.add:after', array($eventParams));
				
				if ( $res === false ){
					$this->throwError(__CLASS__ . '::' . __FUNCTION__ . ': A database error occurred whilst calling addChild on the parent class (\FireliteNested)');
					return false;
				} else {
					return true;
				}
			}
			
		} else {
			$this->throwError('A node with that name already exists at this point in the tree.', __CLASS__ . '::' . __FUNCTION__);
			return false;
		}
		return false;
	}
	
	/**
	 *
	 * @param type $node 
	 */
	public function render(){
		return 'No render function implemented for node';
	}
	
	/**
	 * Determine if the page is publised or not
	 * @return boolean
	 */
	public function isPublished(){
		return (boolean)$this->published;
	}
	
	public function set_published($val){
		if ($val){
			$this->attributes['published'] = 1;
		} else {
			$this->attributes['published'] = 0;
		}
	}
	
	/**
	 * Publish the page
	 * @return type
	 */
	public function publish( $save = true ){
		$this->published = 1;
		if ( $save ){
			return $this->save();
		}
		return true;
	}

	/**
	 * unpublish the page
	 * @return type
	 */
	public function unpublish( $save = true ){
		$this->published = 0;
		if ( $save ){
			return $this->save();
		}
		return true;
	}

	/**
	 * creates specific validation rules for the node model 
	 * 
	 * @param type $input
	 * @param type $rules_override
	 * @param type $messages_override
	 * @return type
	 */
	public function validate($input, $rules_override = array(), $messages_override = array()){
		$rules_override = (array)$rules_override;
		
		$rules = $this->validation['rules'];
		
		if ( !isset( $rules_override['name'] ) ){
			
			if (isset($input['parent_node_id'])){
				$parent_node = (int)$input['parent_node_id'];
				
			} else {
				$parent_node = 0;
			}
			
			if (!$this->exists ||  $this->tree_id < 1 && empty( $input['tree_id']  )){
				if (!empty($input['tree_id'])){
					$tree_id = (int)$input['tree_id'];
				} else {
					$tree_id = Firelite::config( 'default_site_tree', 1 );
				}
			} else {
				$tree_id = $this->tree_id;
			}
			if ($parent_node == 0){
				$rules['name']  = 'required|node_name:is_root|between:1,255';
			}
			
			$rules_override['name'] = $rules['name'] . '|node_unique:'.$parent_node . ',' . $tree_id;
			
		}
		
		return parent::validate($input, $rules_override, $messages_override);
	}
	
}
