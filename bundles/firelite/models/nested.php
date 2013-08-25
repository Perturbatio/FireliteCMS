<?php namespace Firelite\Models;
use DB;
use \Firelite\Database\Eloquent\Relationships\Nests;
use \FireliteModel;
use \Log;
use \FireliteTree;

/**
 * An eloquent based nested set model
 */
class Nested extends FireliteModel {
	
	public static $_col_left = 'lft';
	public static $_col_right = 'rgt';
	public static $_col_parent = 'parent_id';
	public static $_col_tree = 'tree_id';
	public static $_nested_model = 'nested';
	
	/*
	public function __construct($attributes = array(), $exists = false)
	{
		parent::__construct($attributes, $exists);
	}
	*/
	
	/**
	 * @return Relationships\Has_Many
	 */
	public function children(){
		//return static::where( static::$_col_parent, '=', $this->id )->get();
		return $this->has_many(static::$_nested_model, static::$_col_parent);
	}
	
	/**
	 * Get all desendants of the current node
	 * @return Relationships\Nests
	 */
	public function descendants(){
		return $this->nests(static::$_nested_model);
	}
	
	
	/**
	 * get the parent node
	 * 
	 * @return \Laravel\Database\Eloquent\Relationships\Has_One
	 */
	public function parent(){
		return $this->has_one(static::$_nested_model, static::$_col_parent);
	}
	
	/**
	 * Get all ancestors of the current node
	 * 
	 * @return Nested[] 
	 */
	public function getAncestors(){
		return static::where(static::$_col_left, '<', $this->get_left())
			->where(static::$_col_right, '>', $this->get_right())
			->where(static::$_col_tree, '=', $this->get_tree())
			->order_by(static::$_col_left)
			->get();
	}
	
	/**
	 * Delete the Nested model from the tree
	 * 
	 * @return Boolean
	 */
	public function delete(){
		$result = false;
		
		if ( $this->removeGap() ){
			$result = parent::delete();
		}
		
		return $result;
	}
	
	/**
	 * if the $newPos is a Nested object, then it should be on the same tree 
	 * (until this method is updated to allow tree switching)
	 * 
	 * @param Integer/Nested $newPos
	 * @param string $where
	 * @param integer $tree_id
	 * @return boolean 
	 */
	public function moveTo( $new_left , $where = 'first', $tree_id = null){
		return false;
		$this->reload();
		
		if ( is_a( $newPos, __CLASS__) ) {
			$targetNode = $newPos;
		} else {
			
			$new_left = (int)$new_left;
			$targetNode = static::where( static::$_col_left, '=', $newPos )
				->where(static::$_col_tree, '=', $this->get_tree())
				->first();
		}
		
		if ( !$targetNode ){
			$this->throwError('New position must be greater than zero');
			return false;
		}
		
		switch($where){
			case 'first':
				$parent_id = $targetNode->get_parent_id();
				$newPos = (int)$targetNode->get_left();
			break;
			case 'last'://TODO: make this work
				$parent_id = $targetNode->get_parent_id();
				$newPos = (int)$targetNode->get_right()+1;//TODO:Test this
			break;
			default:
				$this->throwError(__CLASS__ . '::' . __FUNCTION__.' - Invalid location '.$where);
				return false;
			break;
		}
		
		$tree_id = (is_null( $tree_id )) ? $targetNode->get_tree() : $tree_id;
		
		$width = $this->getWidth();
		$distance = $newPos - $this->get_left();
		$tempPos = $this->get_left();
		
		if ( $distance < 0 ){
			$distance -= $width;
			$tempPos += $width;
		}
		
		//create new gap
		$targetNode->makeGap($width, $where);//$where?
		
		//move to new gap
		DB::table( static::table() )
			->where( static::$_col_left, '>=' , $tempPos )
			->where(static::$_col_right, '<' , $tempPos + $width)
			->where( static::$_col_tree, '=' , $tree_id )
			->update(
				array(
					static::$_col_left => DB::raw( static::$_col_left .' + ' . $distance ),
					static::$_col_right => DB::raw( static::$_col_right .' + ' . $distance )
				)
			);
		//remove old gap
		//update left
		DB::table( static::table() )
			->where(static::$_col_left, '>' , $tempPos)
			->where(static::$_col_tree, '=', $tree_id)
			->update(
				array(
					static::$_col_left => DB::raw( static::$_col_left .' - ' . $width )
				)
			);
		//update right
		DB::table( static::table() )
			->where( static::$_col_right, '>', $tempPos )
			->where(static::$_col_tree, '=', $tree_id)
			->update(
				array(
					static::$_col_right => DB::raw( static::$_col_right . ' - ' . $width )
				)
			);
		
		$this->reload();
		$this->set_parent_id($parent_id);
		$this->save();
		return true;
	}
	
	/**
	 * add the supplied Nested model to the current Nested object as the last child
	 * 
	 * @param Nested $nested 
	 * @return Boolean
	 */
	public function appendChild($nested){
		return $this->addChild($nested, 'last');
	}
	
	/**
	 * add the supplied Nested model to the current Nested object as the first child
	 * 
	 * @param Nested $nested
	 * @return Boolean 
	 */
	public function prependChild($nested){
		return $this->addChild($nested, 'first');
	}
	
	/**
	 * add $nested as a child of the current Nested model as either the first or 
	 * last child (default is last)
	 * 
	 * @param Nested $nested
	 * @param string $where ('first' or <b>'last' (default)</b>)
	 * @return type 
	 */
	public function addChild($nested, $where='last'){
		if ( !$this->exists ){
			Log::firelite('addChild called on non-existent parent node');
			return false;
		}
		
		$old_left = 0;
		$old_right = 0;
		$old_tree = 0;
		$width = 2;
		
		$orphans = array();
		
		
		if ( $nested->exists ){
			$old_left = $nested->get_left();
			$old_right = $nested->get_right();
			$old_tree = $nested->get_tree();
			
			$width = ( $old_right - $old_left ) + 1;
			if ($width < 2){
				$width = 2;
			}
			$orphans[] = $nested->id;
			foreach ( $nested->descendants as $orphan ) {
				$orphans[] = $orphan->id;
			}
		}
		

		$nested->parent_node_id = $this->id;
		
		if ( $nested->exists && $old_left > 0 && $old_right > 0 ){//if it already exists, we're moving it 
			$nested->set_left(0 - $nested->get_left());
			$nested->set_right(0 - $nested->get_right());//$new_pos + ($width - 1));
			$nested->set_tree( $this->tree_id );
			$nested->save();
			
			foreach ( $nested->descendants as $descendant ){
				$descendant->set_left(0 - $descendant->get_left());
				$descendant->set_right(0 - $descendant->get_right());
				$descendant->set_tree( $this->tree_id );
				$descendant->save();
			}
			
			
			$this->removeGap($old_left, $old_left + $width - 1, $old_tree);
			$this->reload();
		
			switch( $where ){
				case 'first':
					$new_pos = $this->get_left()+1;
				break;
				case 'last':
					$new_pos = $this->get_right();
				break;
			}
			
			$this->makeGap( $width, $new_pos, $this->tree_id );
			
			
			$nested->set_left($new_pos);
			$nested->set_right($new_pos + ($width - 1));
			$nested->set_tree( $this->tree_id );
			$nested->save();
			
			$descendant_pos = $new_pos;
			foreach( $nested->descendants as $descendant ){
				$descendant->set_left(++$descendant_pos);
				$descendant->set_right(++$descendant_pos);
				$descendant->save();
			}
			
		} else {
				
		
			$this->reload();
			
			switch( $where ){
				case 'first':
					$new_pos = $this->get_left()+1;
				break;
				case 'last':
					$new_pos = $this->get_right();
				break;
				default:
					$this->throwError('Invalid position in addChild method (' . $where . ')');
				break;
			}
			
			$this->makeGap( $width, $new_pos, $this->tree_id );
			$nested->set_left($new_pos);
			$nested->set_right($new_pos+1);//$new_pos + ($width - 1));
			$nested->set_tree( $this->tree_id );
			$nested->save();
			
		}
		
		$this->clear_relationship('children');
		$this->reload();
		
		return true;
	}
	
	/**
	 * Append the current Nested object as the last child of the supplied Nested object
	 * 
	 * @param type $nested 
	 */
	public function appendTo($nested){
		$nested->appendChild($this);
	}
	
	/**
	 * Create a gap in the nested left/right structure for insertion of one or 
	 * more Nested models.
	 * 
	 * Specify <b>gap</b> as the width of the whole sub tree you want to insert,
	 * i.e. 1 node will require a gap of 2 (default)
	 * 
	 * @param Integer $size
	 * @param Integer $pos
	 */
	protected function makeGap( $gap = 2,  $pos = null, $tree_id = null ){
		
		$tree_id = (int)$tree_id;
		
		if ( $tree_id < 1 ){
			$tree_id = $this->get_tree();
		}
		//Log::firelite( 'Making gap of size ' . $gap . ' after ' .$pos . ' tree: ' . $tree_id );
		
		
		if ( empty( $pos ) ){//assume last
			$pos = $this->get_right();
		}
		
		
		//increment the left and right of all Nested models whose position is after $at_pos by the gap size
		//update rights
		$res = DB::table( static::table() )
			->where(static::$_col_right, '>=' , $pos)
			->where(static::$_col_tree, '=', $tree_id)
			->update(
				array(
					static::$_col_right => DB::raw( static::$_col_right .' + ' . $gap )
				)
			);
		if ($res){
			//update lefts
			$res = DB::table( static::table() )
				->where(static::$_col_left, '>' , $pos)
				->where(static::$_col_tree, '=', $tree_id)
				->update(
					array(
						static::$_col_left => DB::raw( static::$_col_left .' + ' . $gap )
					)
				);
		}
		return true;
	}
	
	/**
	 * Remove the Nested model's gap//, preserving children?
	 * 
	 * @param type $left
	 * @param type $right
	 * @param type $tree_id
	 * @return boolean
	 */
	protected function removeGap($left = 0, $right = 0, $tree_id = null, $relocate_to_tree = null){
		//Log::firelite("Removing Gap: \$left = $left, \$right = $right, \$tree_id = $tree_id, \$relocate_to_tree = $relocate_to_tree");
		
		if ( $left < 1 || $right < 1 ){
			$gap = (int)$this->getWidth();
			$left = $this->get_left();
			$right = $this->get_right();
		} else {
			$gap = ($right - $left)+1;
		}
		
		
		if ( $gap < 2 ){
			$this->throwError( 'Gap cannot be less than two', __CLASS__ . '::' . __FUNCTION__ );
			return false;
		}
		
		$size = $gap / 2;
		
		$tree_id = (int)$tree_id;
		if ($tree_id < 1){
			$tree_id = $this->get_tree();
		}
		
		
		$res = array();
		
		//clear out
		$gap_updates = array(
				static::$_col_left => DB::raw( static::$_col_left . ' - ' . $size ),
				static::$_col_right => DB::raw( static::$_col_right . ' - ' . $size )
		);
		
		if ( !is_null($relocate_to_tree) ){
			$gap_updates['tree_id'] = (int)$relocate_to_tree;
		}
		
		$res[] = DB::table( static::table() )
			->where( static::$_col_left, '>=', $left)
			->where( static::$_col_right, '<=', $right )
			->where(static::$_col_tree, '=', $tree_id)
			->update($gap_updates);
		
		//update left
		$res[] = DB::table( static::table() )
			->where(static::$_col_left, '>' , $right)
			->where(static::$_col_tree, '=', $tree_id)
			->update(
				array(
					static::$_col_left => DB::raw( static::$_col_left .' - ' . $gap )
				)
			);
		//update right
		$res[] = DB::table( static::table() )
			->where( static::$_col_right, '>', $right )
			->where(static::$_col_tree, '=', $tree_id)
			->update(
				array(
					static::$_col_right => DB::raw( static::$_col_right . ' - ' . $gap )
				)
			);
		
		foreach ( $res as $result ){
			if ( $result === false ){
				return false;
			}
		}
		return true;
	}
	
	/**
	 *
	 * @return Nested 
	 */
	public function getLastChild(){
		$result = null;
		$child_count = count( $this->children );
		
		if ( $child_count > 0 ) {
			$result = $this->children[ $child_count - 1 ]; //can't use end() function on overloaded array property
		}
		
		return $result;
	}
	
	/**
	 * 
	 * @return Integer
	 */
	public function get_tree(){
		return $this->{static::$_col_tree};
	}
	
	/**
	 * 
	 * @param Integer $tree_id
	 */
	public function set_tree($tree_id){
		$this->{static::$_col_tree} = (int)$tree_id;
	}
	
	/**
	 * 
	 * @param Integer $val
	 */
	public function set_left($val){
		//Log::firelite('setting left '. $val);
		$this->{static::$_col_left} = (int)$val;
	}
	
	/**
	 *
	 * @return Integer $val 
	 */
	public function get_left(){
		return $this->{static::$_col_left};
	}
	
	/**
	 * 
	 * @param Integer $val
	 */
	public function set_right($val){
		//Log::firelite('setting right '. $val);
		$this->{static::$_col_right} = (int)$val;
	}
	
	/**
	 * 
	 * @return Integer $val 
	 */
	public function get_right(){
		return $this->{static::$_col_right};
	}
	
	/**
	 *
	 * @param Integer $id 
	 */
	public function get_parent_id(){
		return $this->{static::$_col_parent};
	}
	
	/**
	 *
	 * @param Integer $id 
	 */
	public function set_parent_id($id){
		$this->{static::$_col_parent} = (int)$id;
	}
	
	/**
	 * Return the width of the node in the tree
	 * 
	 * @return Integer 
	 */
	public function getWidth(){
		return ($this->get_right() - $this->get_left()) + 1;
	}
	
	/**
	 * Returns the path to the node (i.e. people/bob)
	 * 
	 * @return string
	 */
	public function getPath(){
		return $this->get_path();
	}
	
	
	/**
	 * return
	 * @return string 
	 */
	protected function getGeneratedPath( $skip_root = true ){
		$ancestry = $this->getAncestors();
		if ($skip_root === true){ array_shift($ancestry); }
		
		$segments = array();
		
		foreach ( $ancestry as $node ) {
			$segments[] = $node->name;
		}
		
		$segments[] = $this->name;
		return implode( '/', $segments );
	}
	
	/**
	 * Rebuild the node's path and save it
	 * 
	 * @param boolean $skip_root
	 * @return boolean 
	 */
	public function rebuild_path( $skip_root = true, $prefix = '/' ){
		$this->path = $this->getGeneratedPath( $skip_root );
		
		if ( $this->name !== $prefix ){
			$this->path = $prefix . $this->path;
		}
		return $this->save();
	}
	
	/**
	 *
	 * @param integer $tree_id
	 * @return Nested 
	 */
	static public function getRoot($tree_id){
		return static::where(static::$_col_tree, '=', $tree_id)
			->where( static::$_col_left , '=', 1)
			->first();
	}
	
	
	/**
	 *
	 * @param type $model
	 * @param type $col_left
	 * @param type $col_right
	 * @return \Firelite\Database\Eloquent\Relationships\Nests 
	 */
	protected function nests($model, $col_left = null, $col_right = null){
		$col_left = ($col_left == null) ? static::$_col_left : $col_left;
		$col_right = ($col_right == null) ? static::$_col_right : $col_right;
		
		return new Nests( $this, $model, static::$_col_parent, $col_left, $col_right );
	}
	
	
}