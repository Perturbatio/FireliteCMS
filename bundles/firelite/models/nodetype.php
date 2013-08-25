<?php namespace Firelite\Models;
use Eloquent;
use \FireliteModel;
use \FireliteNode;

/**
 * This model is an interface to the Nodetypes table
 */
class Nodetype extends FireliteModel {
	
	/**
	 * 
	 * @param Integer $tree_id
	 * @return Laravel\Database\Eloquent\Relationships\Has_Many 
	 */
	public function nodes($tree_id = null){
		$rel = $this->has_many( 'FireliteNode' );
		
		if ( !is_null( $tree_id ) ) {
			$rel->where( FireliteNode::$_col_tree, '=', $tree_id );
		}
		
		return $rel;
	}
	
	
}