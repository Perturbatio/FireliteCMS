<?php
namespace Firelite\Models\Nodetypes;

use \Firelite;
use \FireliteSchema as Schema;
use \FireliteNode;
use \FireliteTree;
use \FireliteNodetype;
use \FireliteModel;
use \FirelitePageField;
use \View;
use \Event;
use \Log;

class Page extends FireliteModel {
	
	/**
	 * This array contains all rules and messages used by the page validation function
	 * 
	 * @var array 
	 */
	public $validation = array(
		'rules' => array(
			'template_id' => 'required|integer|exists:templates,id',
			'name' => 'required|node_name|between:1,255',
			'title' => 'between:0,255',
			'link_text'=>'required|between:1,255',
			'link_title'=>'between:0,64',
		),
		'messages' => array(
			'template_id' => 'You must specify a valid template for the page to use',
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
	 * @return \Laravel\Database\Eloquent\Relationships\Belongs_To
	 */
	public function template(){
		return $this->belongs_to('FireliteTemplate', 'template_id');
	}
	
	/**
	 *
	 * @return \Laravel\Database\Eloquent\Relationships\Has_Many
	 */
	public function fields(){
		return $this->has_many('FirelitePageField', 'page_id');
	}
	
	/**
	 * get a field by it's name
	 *
	 * @param string $name
	 * @return null|field
	 */
	public function getField( $name ){
		foreach ( $this->fields as $field ) {
			if ( $field->name === $name ) {
				return $field;
			}
		}
		return null;
	}
	
	/**
	 * set a field value by it's name
	 *
	 * @param string $name
	 * @return null|field
	 */
	public function setField( $name, $value ){
		
		foreach ( $this->fields as $field ) {
			if ( $field->name === $name ) {
				$field->setValue($value);
				return $field->save();
			}
		}
		
		//if we haven't found a field, check if our template allows one with this name
		//if it does, add it
		$template_field = $this->template->getField( $name );
		
		if ( $template_field ){
			$datatype = $template_field->datatype->instance();
			
			$datatype->setValue( $value );
			
			$pageField = new FirelitePageField(array(
				'page_id' => $this->id,
				'template_field_id' => $template_field->id,
				'data_id' => $datatype->id
			));
			$this->fields()->insert($pageField);
			$pageField->datatype()->attach($datatype);
			return $pageField->save();
		}
		
		Log::firelite('Template ' . $this->template->name . ' does not allow field ' . $name);
		return false;
	}
	
	
	/**
	 * check if a page has the named field
	 *
	 * @param string $name
	 * @return boolean 
	 */
	public function hasField($name){
		foreach ( $this->fields as $field ) {
			if ( $field->name === $name ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 *
	 * @param FireliteNode|Integer $node
	 * @return FirelitePage|Null 
	 */
	public static function from_node($node){
		if ( is_numeric( $node ) ){
			$node_id = $node;
		} else if ( is_a( $node, 'FireliteNode' ) ){
			$node_id = $node->id;
		} else {
			return null;
		}
		$page = static::with(array(
				'template',
				'node',
				'fields',
				'fields.template_field',
				'fields.template_field.datatype'
			))->where('node_id', '=', $node_id)->first();
		return $page;
	}
	
	
	/**
	 * Render the page using it's template's view
	 * 
	 * @return View
	 */
	public function render(){
		
		$response = Event::until('firelite.page.render:before', array($this));
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		$tree = FireliteTree::find(Firelite::config('default_tree', 1));
		
		$data = array(
			'tree' => $tree,
			'page' => $this
		);
		
		$render_res = View::make( Firelite::getTemplateView( $this->template ), $data );
		Event::fire('firelite.page.render:after', array($this, $render_res));
		return $render_res;
		
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
	 * Determine if the page is publised or not
	 * @return boolean
	 */
	public function isPublished(){
		return (boolean)$this->node->isPublished();
	}
	
	/**
	 * Publish the page
	 * 
	 * Sets the published state of the page to 1
	 * saves the state to DB by default
	 * 
	 * @param boolean $save = true
	 * @return boolean
	 */
	public function publish( $save = true ){
		return $this->node->publish($save);
	}
	
	/**
	 * Unpublish the page
	 * 
	 * Set the published state of the page to 0
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
	 * This function will try and map all values in $data to properties of the page
	 * or fields that the page owns.  It is intended to be used with the page plugin for editing
	 * 
	 * @param array $data
	 * @return boolean
	 */
	public function update_from_array( $data ){
		foreach ( $data as $key => $value ){
			
			if ( starts_with( $key, 'field_' ) ){
				
				$field_name = str_replace( 'field_', '', $key );
				$this->setField( $field_name, $value );
				
			} else {
				$prop_name = $key;
				//only allow changing of items that have a set_ method (this is an easy way to stop fields like csrf being inserted)
				if ( method_exists( $this, "set_{$prop_name}" ) ){
					$this->$prop_name = $data[$key];
				}
			}
			
		}
		return $this->save();
	}
	
	public function delete( $recursive = true ){
		//TODO: get this all working with cascading
		//TODO: make sure nodes don't delete children unless it's recursive, if we delete only this node, the children need to be reassigned or moved to an orphanage
		return false;
		foreach( $this->fields as $field ){
			$field->delete();
		}
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
	 * Install the Page NodeType
	 * 
	 * @return boolean 
	 */
	static public function firelite_install(){
		//handle any page specific stuff here
		//create pages table
		if ( !Schema::exists( 'pages' ) ){
			Schema::create( 'pages', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'node_id')->unique();
				$table->integer( 'template_id');
				$table->string( 'title', 255 );

				$table->timestamps();

				$table->index( array( 'node_id', 'template_id' ), 'index_node_uses_template' );
			} );
			
		}
		
		//create page metadata table
		if ( !Schema::exists( 'page_meta_data' ) ){
			Schema::create( 'page_meta_data', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'page_id');
				$table->string( 'name');
				$table->string( 'content' );
				$table->timestamps();

				$table->index( array('page_id', 'name'), 'index_page_metaname' );
			} );
		}
		
		//create page field data table
		if ( !Schema::exists( 'page_fields' ) ){
			Schema::create( 'page_fields', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'page_id');
				$table->integer( 'template_field_id');
				$table->integer( 'data_id' );
				$table->timestamps();

				$table->index( array('page_id', 'template_field_id', 'data_id'), 'index_page_templatefield_data' );
			} );
		}
		
		//create template table
		//TODO: Move templates to core
		if ( !Schema::exists( 'templates' ) ){
			Schema::create( 'templates', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->string( 'name', 64 )->unique();
				$table->string( 'description', 255 );
				$table->string( 'view', 255 );
				$table->timestamps();

				$table->index( array( 'id', 'name', 'view' ), 'index_id_name_view' );
			} );
		}
		
		//create template fields
		//TODO: Move templatefields to core
		if ( !Schema::exists( 'template_fields' ) ){
			Schema::create( 'template_fields', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer('template_id');
				$table->integer('datatype_id');
				$table->string( 'name', 64 );
				$table->string( 'label', 64 );
				$table->string( 'description', 255 );
				$table->string('preferred_editor');
				$table->integer('sort_order');
				$table->timestamps();
				
				$table->index(array('template_id', 'sort_order'), 'template_field_sort_order');
				$table->unique(array('template_id', 'name'), 'template_field_name_unique');
				$table->index(array('id', 'preferred_editor'), 'index_id_editor');	
				$table->index( array( 'template_id', 'datatype_id', 'name'), 'index_template_datatype_name' );
			} );
		}

		return true;
	}
	
	/**
	 * Uninstall the Page NodeType
	 * 
	 * @param integer $version
	 * @return boolean 
	 */
	static public function firelite_uninstall( $version ){
		if ( $version > static::firelite_version() ){
			return false;
		}
		
		Schema::drop('pages');
		Schema::drop('page_meta_data');
		Schema::drop('page_fields');
		Schema::drop('templates');
		Schema::drop('template_fields');
		return true;
	}
	
	/**
	 * the version number of the node (if modifications are made that 
	 * could affect existing code, this number should be incremented)
	 * 
	 * @return int 
	 */
	static public function firelite_version(){
		return 2;
	}
	
	/**
	 * 
	 * @return string 
	 */
	static public function firelite_description(){
		return 'Pages represent the pages on your website';
	}
	
}