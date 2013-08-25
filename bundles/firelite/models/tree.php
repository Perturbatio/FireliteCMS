<?php namespace Firelite\Models;
use Eloquent;
use FireliteNode;

class Tree extends Eloquent {
	
	/**
	 * Returns the root node of the tree with all the nested node relationships
	 * 
	 * @return FireliteNode
	 */
	public function getStructure($exclude_root = true, $filter = null){
		$result = null;
		$node_map = $this->getNodeMap($filter);
		
		if ( count( $node_map ) > 1 && $exclude_root == true ) {
			$result = array_shift( $node_map );
		}
		return $result;
	}
	
	/**
	 *
	 * @return array 
	 */
	public function getNodeMap($filter = null){

		$result = null;
		$nodes = $this->nodes()->order_by(FireliteNode::$_col_left)->get();
		
		if ( count( $nodes ) > 0 ) {
			$node_map = array();
			
			
			foreach ( $nodes as $node ) {
				
				if ( is_callable( $filter ) ){
					$filter_res = call_user_func( $filter, $node );

					if ( $filter_res === false ){
						continue;
					}
					if ( is_a( $filter_res, 'FireliteNode' ) ){
						$node = $filter_res;
					}
				}
				
				if ( !isset( $node_map[ $node->id ] ) ) {
					$node_map[ $node->id ] = $node;
					$node->relationships['children'] = array();
				}
				
				if ( isset( $node_map[ $node->parent_node_id ] ) ) {
					$node_map[ $node->parent_node_id ]->relationships['children'][] = $node;
				}
				
			}
			
			$result = $node_map;
		}
		
		
		return $result;
	}
	
	/**
	 * 
	 * 
	 * @return type 
	 */
	public function nodes(){
		return $this->has_many('FireliteNode');
	}
	
	/**
	 *
	 * @return type 
	 */
	public function get_nodes(){
		return $this->nodes();
	}

	/**
	* Get the root node for the tree
	* 
	* @return FireliteNode
	*/
	public function get_root(){
		return FireliteNode::getRoot($this->id);
	}
	
	/**
	 * Make the supplied node the root of the tree
	 * 
	 * @param Node $node
	 * @return boolean
	 */
	public function set_root($node){
		if ( !$this->get_root() ) {
			$node->set_left(1);
			$node->set_right(2);
			$node->set_tree($this->id);

			return $node->save();
		} else {
			$this->throwError("The tree already has a root node", __FUNCTION__);
		}
		return false;
	}
	
	/**
	 * Rebuilds all paths in the tree
	 * The skip_root parameter is to prevent the root node being places in the 
	 * tree path (a kludge to allow for root being / since you don't want a URL of //products)
	 * 
	 * @param Boolean $skip_root
	 * @param string $prefix
	 * @return boolean
	 */
	public function rebuild_paths( $skip_root = true, $prefix = '/' ){
		$res = true;
		$this->get_root();
		$node_map = $this->getNodeMap();
		
		if ( count( $node_map ) > 1 ) {
			foreach ( $node_map as $node ) {
				if ( !$node->rebuild_path($skip_root, $prefix ) ) {
					$res = false;
					$this->throwError('Error rebuilding path for node: ' . $node->id . '(' . $node->name . ')');
				}
			}
		}

		return $res;
	}
	

}
