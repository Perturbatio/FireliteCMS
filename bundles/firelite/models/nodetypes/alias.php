<?php
namespace Firelite\Models\Nodetypes;

use \Firelite;
use \FireliteSchema as Schema;
use \FireliteNode;
use \FireliteTree;
use \FireliteNodetype;
use \FireliteModel;
use \View;
use \Event;
use \Log;
use \Redirect;
use \Response;

define('FIRELITE_ALIAS_TYPE_NODE', 1);
define('FIRELITE_ALIAS_TYPE_URL', 2);

class Alias extends FireliteModel {
	
	/**
	 * This array contains all rules and messages used by 
	 * the alias validation function
	 * 
	 * @var array 
	 */
	public $validation = array(
		'rules' => array(
			'node_id' => 'required|integer|exists:nodes,id',
			'target_node_id' => 'integer|exists:nodes,id',
			'target_url' => '',
			'type' => 'required|integer|min:1|max:2',
			'redirect_response_code' => 'required|integer',
			'name' => 'required|node_name|between:1,255',
			'title' => 'between:0,255',
			'link_text'=>'required|between:1,255',
			'link_title'=>'between:0,64',
		),
		'messages' => array(
			'target_node_id' => 'Invalid node id specified',
			'target_node_id' => 'Invalid target node id specified',
			'target_url' => 'Invalid target URL specified',
			'type' => 'You must specify a valid alias type',
			'name' => 'Name must be numbers, letters hyphens and underscores only, (only the root node can contain a slash) max length is 255',
			'title' => 'Title should be text only and no longer than 255 characters (recommended is less than 64)',
			'link_text' => 'Link text is required (it\'s the text that is made into the link) and must be no longer than 255 characters',
			'link_title' => 'The link title is not required, but should not be longer than 64 characters',
		)
	);
	
	/**
	 *
	 * @return \Laravel\Database\Eloquent\Relationships\Belongs_To
	 */
	public function node(){
		return $this->belongs_to('FireliteNode', 'node_id');
	}
	
	/**
	 *
	 * @return \Laravel\Database\Eloquent\Relationships\Has_One
	 */
	public function target(){
		return $this->belongs_to('FireliteNode', 'target_node_id');
	}
	
	
	/**
	 *
	 * @param FireliteNode|Integer $node
	 * @return FireliteAlias|Null 
	 */
	public static function from_node($node){
		if ( is_numeric( $node ) ){
			$node_id = $node;
		} else if ( is_a( $node, 'FireliteNode' ) ){
			$node_id = $node->id;
		} else {
			return null;
		}
		$alias = static::with(array(
				'node',
				'target'
			))->where('node_id', '=', $node_id)->first();
		return $alias;
	}
	
	
	/**
	 * Redirect to the aliased node or url
	 * 
	 * @return View
	 */
	public function render(){
		
		$response = Event::until('firelite.alias.render:before', array($this));
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		switch ($this->type){
			case FIRELITE_ALIAS_TYPE_NODE:
				$response = Redirect::to($this->target->path, $this->redirect_response_code);
			break;
			case FIRELITE_ALIAS_TYPE_URL:
				$response = Redirect::to($this->target_url, $this->redirect_response_code);
			break;
			default:
				$response = Response::error('404');
			break;
		}
		
		Event::fire( 'firelite.alias.render:after', array($this, $response) );
		
		return $response;
		
	}
	
	/**
	 * 
	 * @param Boolean $recursive 
	 */
	public function save($recursive = true){
		$res =  (boolean)parent::save();
		$this->node->save();
		
		if ( $recursive === true && $res === true ){
			
			$this->node->save();
			
			foreach($this->fields as $field){
				$field->save();
			}
		}
		return $res;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_name(){
		return $this->node->name;
	}
	
	/**
	 * 
	 * @param string $name
	 */
	public function set_name($name){
		$this->node->set_name($name);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_link_text(){
		return $this->node->link_text;
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public function set_link_text($value){
		$this->node->set_link_text($value);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_link_title(){
		return $this->node->link_title;
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public function set_link_title($value){
		$this->node->set_link_title($value);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_title(){
		return $this->attributes['title'];
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public function set_title($value){
		$this->attributes['title'] = $value;
	}
	
	
	/**
	 * Determine if the alias is published or not
	 * @return boolean
	 */
	public function isPublished(){
		return (boolean)$this->node->isPublished();
	}
	
	/**
	 * Publish the alias
	 * 
	 * Sets the published state of the alias to 1
	 * saves the state to DB by default
	 * 
	 * @param boolean $save = true
	 * @return boolean
	 */
	public function publish( $save = true ){
		return $this->node->publish($save);
	}
	
	/**
	 * Unpublish the alias
	 * 
	 * Set the published state of the alias to 0
	 * saves the state to DB by default
	 * 
	 * @param type $save
	 * @return type
	 */
	public function unpublish( $save = true ){
		return $this->node->unpublish($save );
	}
	
	/**
	 * 
	 * @param type $val
	 */
	public function set_published($val){
		$this->node->published = (int)$val;
	}
	
	/**
	 * This function will try and map all values in $data to properties of the alias
	 * It is intended to be used with the plugin for editing
	 * 
	 * @param array $data
	 * @return boolean
	 */
	public function update_from_array( $data ){
		foreach ( $data as $key => $value ){

			$prop_name = $key;
			//only allow changing of items that have a set_ method (this is an easy way to stop fields like csrf being inserted)
			if ( method_exists( $this, "set_{$prop_name}" ) ){
				$this->$prop_name = $data[$key];
			}
		}
		return $this->save();
	}
	
	/**
	 * 
	 * @param type $recursive
	 * @return boolean
	 */
	public function delete( $recursive = true ){
		//TODO: get this all working with cascading
		//TODO: make sure nodes don't delete children unless it's recursive, if we delete only this node, the children need to be reassigned or moved to an orphanage
		return false;
		
		if ($recursive){
			foreach($this->children as $child){
				$child->delete($recursive);
			}
		}
		$this->node->delete($recursive);
		parent::delete();
	}
	
	/**
	 * 
	 * @param string $input
	 * @param array $rules_override
	 * @param array $messages_override
	 * @return Validator|true
	 */
	public function validate($input, $rules_override = array(), $messages_override = array()){
		if ($this->parent_id == 0){
			$rules_override['name'] = 'required|node_name:is_root|between:1,255';
		}
		return parent::validate($input, $rules_override, $messages_override);
	}

	
	/**
	 * Install the Alias NodeType
	 * 
	 * @return boolean 
	 */
	static public function firelite_install(){
		//create aliases table
		if ( !Schema::exists( 'aliases' ) ){
			Schema::create( 'aliases', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'node_id');
				$table->integer( 'target_node_id');//if alias type 1
				$table->string( 'target_url');//if alias type 2
				$table->integer( 'type' );//1 = node alias 2 = url alias
				$table->integer( 'redirect_response_code' );//i.e. 301 or 302

				$table->timestamps();

				$table->index( array( 'node_id', 'target_node_id' ), 'index_node_aliases_node' );
			} );
			return true;
		} else {
			return false;
		}

	}
	
	/**
	 * Uninstall the Alias NodeType
	 * 
	 * @param integer $version
	 * @return boolean 
	 */
	static public function firelite_uninstall( $version ){
		if ( $version > static::firelite_version() ){
			return false;
		}
		
		Schema::drop('aliases');
		return true;
	}
	
	/**
	 * the version number of the node (if modifications are made that 
	 * could affect existing code, this number should be incremented)
	 * 
	 * @return int 
	 */
	static public function firelite_version(){
		return 1;
	}
	
	/**
	 * 
	 * 
	 * @return string 
	 */
	static public function firelite_description(){
		return 'Aliases are redirects to other nodes on your site, or to a URL (useful for inserting external urls or application specific ones into a nav)';
	}
	
}