<?php
namespace Firelite\Models\Datatypes;

use FireliteBasedatatype;
use FireliteSchema as Schema;
use Form;
use Input;
use Firelite;

class Simpletext extends FireliteBasedatatype {
	
	
	/**
	 *
	 * @var String
	 */
	static public $table = 'datatype_simpletexts';
	
	static public $edit_as_datatype = 'Simpletext';
	
	static protected $storage_class = 'FireliteStorageSimpletext';
	
	
	/**
	 * 
	 * @return Relationships\Has_One
	 */
	public function storage(){
		return $this->has_one(static::$storage_class, 'datatype_simpletext_id');
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
				'datatype_simpletext_id' => $this->id,
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
	 * Install the Simpletext Datatype
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
		if ( !Schema::exists( 'storage_simpletexts' ) ) {
			Schema::create( 'storage_simpletexts', function($table) {
				$table->increments( 'id' );
				$table->integer( 'datatype_simpletext_id')->unique();
				$table->string( 'value', 255 );
				$table->timestamps();

				$table->index( array( 'id', 'datatype_simpletext_id', 'value' ), 'id_datatype_value_index' );
			} );
		}
				

		//create a tracker (pivot) table for the datatype to page field
		//this is not entirely ideal since a datatype should not be linked to more than one field
		//but this table theoretically allows it
		//if we were to store the page field id in the datatype, then the table could
		//not be used for anything other than pages (without things getting messy)
		if ( !Schema::exists( 'pagefield_simpletexts' ) ) {
			Schema::create( 'pagefield_simpletexts', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'pagefield_id')->unique();
				$table->integer( 'simpletext_id')->unique();
				$table->timestamps();

				$table->index( array( 'pagefield_id', 'simpletext_id' ), 'pagefield_datatype_index' );
			} );
		}
		
		return true;
	}
	
	/**
	 * Uninstall the Simpletext Datatype
	 * 
	 * @param integer $version
	 * @return boolean 
	 */
	static public function firelite_uninstall( $version ){
		if ( $version > static::firelite_version() ){
			return false;
		}
		
		Schema::drop('datatype_simpletexts');
		Schema::drop('storage_simpletexts');
		
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
		return 'Single line text (255 characters max)';
	}
	
	public function __toString(){
		return $this->getValue();
	}
	
}