<?php
namespace Firelite\Models\Blog;

use Event;
use Firelite;
use FireliteModel;
use FirelitePostField;
use FireliteSchema as Schema;
use FireliteTree;
use Laravel\Database\Eloquent\Relationships\Belongs_To;
use Laravel\Database\Eloquent\Relationships\Has_Many;
use Laravel\Database\Schema\Table;
use Log;
use Validator;
use View;

class Post extends FireliteModel {
	
	/**
	 * This array contains all rules and messages used by the post validation function
	 * 
	 * @var array 
	 */
	public $validation = array(
		'rules' => array(
			'template_id' => 'required|integer|exists:templates,id',
			'name' => 'required|alpha_dash|between:1,255',
			'title' => 'between:0,255',
		),
		'messages' => array(
			'template_id' => 'You must specify a valid template for the post to use',
			'name' => 'Name must be numbers, letters hyphens and underscores only, (only the root node can contain a slash) max length is 255',
			'title' => 'Title should be text only and no longer than 255 characters (recommended is less than 64)',
		)
	);
	
	/**
	 *
	 * @return Belongs_To
	 */
	public function template(){
		return $this->belongs_to('FireliteTemplate', 'template_id');
	}
	
	/**
	 *
	 * @return Has_Many
	 */
	public function fields(){
		return $this->has_many('FirelitePostField', 'post_id');
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
			
			$postField = new FirelitePostField(array(
				'post_id' => $this->id,
				'template_field_id' => $template_field->id,
				'data_id' => $datatype->id
			));
			$this->fields()->insert($postField);
			$postField->datatype()->attach($datatype);
			return $postField->save();
		}
		
		Log::firelite('Template ' . $this->template->name . ' does not allow field ' . $name);
		return false;
	}
	
	
	/**
	 * check if a post has the named field
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
	 * Render the post using it's template's view
	 * 
	 * @return View
	 */
	public function render(){
		
		$response = Event::until('firelite.post.render:before', array($this));
		
		if ( !is_null( $response ) ){
			return $response;
		}
		
		$tree = FireliteTree::find(Firelite::config('default_tree', 1));
		
		$data = array(
			'tree' => $tree,
			'post' => $this
		);
		
		$render_res = View::make( Firelite::getTemplateView( $this->template ), $data );
		Event::fire('firelite.post.render:after', array($this, $render_res));
		return $render_res;
		
	}
	
	/**
	 * 
	 * @param Boolean $recursive 
	 */
	public function save($recursive = true){
		$res =  (boolean)parent::save();
		
		if ( $recursive === true && $res === true ){

			
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
		return $this->attributes['name'];
	}
	
	/**
	 * 
	 * @param string $name
	 */
	public function set_name($value){
		$this->attributes['name'] = $value;
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
	 * 
	 * @return string
	 */
	public function isPublished(){
		return $this->attributes['published'];
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public function publish(){
		$this->attributes['published'] = 1;
	}
	
	/**
	 * 
	 * @param string $value
	 */
	public function unpublish(){
		$this->attributes['published'] = 0;
	}
	
	
	/**
	 * This function will try and map all values in $data to properties of the post
	 * or fields that the post owns.  It is intended to be used with the post plugin for editing
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
	
	/**
	 * 
	 * @param string $input
	 * @param array $rules_override
	 * @param array $messages_override
	 * @return Validator|true
	 */
	public function validate($input, $rules_override = array(), $messages_override = array()){
		return parent::validate($input, $rules_override, $messages_override);
	}

	
	/**
	 * Install the Post NodeType
	 * 
	 * @return boolean 
	 */
	static public function firelite_install(){
		//handle any post specific stuff here
		//create posts table
		if ( !Schema::exists( 'posts' ) ){
			Schema::create( 'posts', function($table) {
				if ( false ) {
					$table = new Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'template_id');
				$table->string( 'title', 255 );
				$table->string( 'name', 255 )->unique();
				$table->boolean('published');

				$table->timestamps();
			} );
			
		}
		
		//create post field data table
		if ( !Schema::exists( 'post_fields' ) ){
			Schema::create( 'post_fields', function($table) {
				if ( false ) {
					$table = new Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'post_id');
				$table->integer( 'template_field_id');
				$table->integer( 'data_id' );
				$table->timestamps();

				$table->index( array('post_id', 'template_field_id', 'data_id'), 'index_post_templatefield_data' );
			} );
		}
		
		if ( !Schema::exists( 'postfield_largetexts' ) ) {
			Schema::create( 'postfield_largetexts', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'postfield_id')->unique();
				$table->integer( 'largetext_id')->unique();
				$table->timestamps();

				$table->index( array( 'postfield_id', 'largetext_id' ), 'postfield_datatype_index' );
			} );
		}
		
		if ( !Schema::exists( 'postfield_simpletexts' ) ) {
			Schema::create( 'postfield_simpletexts', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'postfield_id')->unique();
				$table->integer( 'simpletext_id')->unique();
				$table->timestamps();

				$table->index( array( 'postfield_id', 'simpletext_id' ), 'postfield_datatype_index' );
			} );
		}
		

		return true;
	}
	
	/**
	 * Uninstall the Post NodeType
	 * 
	 * @param integer $version
	 * @return boolean 
	 */
	static public function firelite_uninstall( $version ){
		if ( $version > static::firelite_version() ){
			return false;
		}
		
		Schema::drop('posts');
		Schema::drop('post_fields');
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
		return 'Post represents a blog post';
	}
	
}