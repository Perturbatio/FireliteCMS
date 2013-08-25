<?php
namespace Firelite\Models\Datatypes;

use \FireliteBasedatatype;
use FireliteSchema as Schema;
use Form;
use Input;
use Firelite;
/**
 * The base image class 
 */
class Simpleimage extends FireliteBasedatatype {
	
	
	/**
	 *
	 * @var String
	 */
	static public $table = 'datatype_simpleimages';
	
	static public $edit_as_datatype = 'Simpleimage';
	
	static protected $storage_class = 'FireliteStorageSimpleimage';
	
	
	/**
	 * 
	 * @return Relationships\Has_One
	 */
	public function storage(){
		return $this->has_one(static::$storage_class, 'datatype_simpleimage_id');
	}
	
	/**
	 *
	 * @return string 
	 */
	public function getValue(){
		return $this->storage->value;
	}
	
	/**
	 * 
	 * @param string $value 
	 */
	public function setValue( $value ){
		
		$value = (string)$value;
		$this->_update_storage($value);
	}
	
	/**
	 * 
	 * @param boolean $recursive 
	 * @return boolean
	 */
	public function save($recursive = true){
		$save_res = parent::save();
		
		return $save_res;
	}
	
	protected function _create_storage($value = ''){
		if (!$this->exists){
			$this->save();
		}
		if (!$this->storage){
			$storage = new static::$storage_class( array(
				'datatype_simpleimage_id' => $this->id,
				'value' => $value) 
			);
			$this->storage()->insert( $storage );
		}
	}
	
	protected function _update_storage($value){
		if (!$this->storage){
			$this->_create_storage($value);
		} else {
			$storage = $this->storage;
			$storage->value = $value;
			$storage->save();
		}
	}
	
	/**
	 * Install the Simpleimage Datatype
	 * 
	 * @return boolean 
	 */
	static public function firelite_install(){
		//create datatype table
		
		if ( !Schema::exists( static::$table ) ) {
			Schema::create( static::$table, function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				
				$table->timestamps();
			} );
		} else {
			echo 'Schema ' . static::$table . ' already exists!' . CRLF;
			//return false;
		}
		
		//check if the data storage table exists
		if ( !Schema::exists( 'storage_simpleimages' ) ) {
			Schema::create( 'storage_simpleimages', function($table) {
				$table->increments( 'id' );
				$table->integer( 'datatype_simpleimage_id')->unique();
				$table->string( 'value', 255 );
				$table->timestamps();

				$table->index( array( 'id', 'datatype_simpleimage_id', 'value' ), 'id_datatype_value_index' );
			} );
		}
				
		//TODO: remove this tracker since we really shouldn't assume that pages will be the ones containing the image
		//create a tracker (pivot) table for the datatype to page field
		//this is not entirely ideal since a datatype should not be linked to more than one field
		//but this table theoretically allows it
		//if we were to store the page field id in the datatype, then the table could
		//not be used for anything other than pages (without things getting messy)
		if ( !Schema::exists( 'pagefield_simpleimages' ) ) {
			Schema::create( 'pagefield_simpleimages', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'pagefield_id')->unique();
				$table->integer( 'simpleimage_id')->unique();
				$table->timestamps();

				$table->index( array( 'pagefield_id', 'simpleimage_id' ), 'pagefield_datatype_index' );
			} );
		}
		
		if ( !Schema::exists( 'postfield_simpleimages' ) ) {
			Schema::create( 'postfield_simpleimages', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'postfield_id')->unique();
				$table->integer( 'simpleimage_id')->unique();
				$table->timestamps();

				$table->index( array( 'postfield_id', 'simpleimage_id' ), 'postfield_datatype_index' );
			} );
		}
		
		return true;
	}
	
	/**
	 * Uninstall the Simpleimage Datatype
	 * 
	 * @param integer $version
	 * @return boolean 
	 */
	static public function firelite_uninstall( $version ){
		if ( $version > static::firelite_version() ){
			return false;
		}
		
		Schema::drop('datatype_simpleimages');
		Schema::drop('storage_simpleimages');
		
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
	 * @return string 
	 */
	static public function firelite_description(){
		return 'A basic image URL';
	}
	
	public function __toString(){
		return $this->getValue();
	}
	
}