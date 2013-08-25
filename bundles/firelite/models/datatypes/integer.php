<?php
namespace Firelite\Models\Datatypes;

use FireliteBasedatatype;
use FireliteSchema as Schema;
use Form;
use Input;
use Firelite;

class Integer extends FireliteBasedatatype {
	
	static public $min = -2147483648;
	static public $max = 2147483647;
	
	/**
	 *
	 * @var String
	 */
	static public $table = 'datatype_integers';
	
	static public $edit_as_datatype = 'Integer';
	
	static protected $storage_class = 'FireliteStorageInteger';
	
	
	/**
	 * 
	 * @return Relationships\Has_One
	 */
	public function storage(){
		return $this->has_one(static::$storage_class, 'datatype_integer_id');
	}
	
	/**
	 * need to return a string, although this is an integer
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
		$value = (int)$value;
		if ($value >= static::$min and $value <= static::$max){
			$this->_update_storage($value);
		}
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
				'datatype_integer_id' => $this->id,
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
	 * Install the Integer Datatype
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
		if ( !Schema::exists( 'storage_integers' ) ) {
			Schema::create( 'storage_integers', function($table) {
				$table->increments( 'id' );
				$table->integer( 'datatype_integer_id')->unique();
				$table->integer( 'value' );
				$table->timestamps();

				$table->index( array( 'id', 'datatype_integer_id', 'value' ), 'id_datatype_value_index' );
			} );
		}
				

		//create a tracker (pivot) table for the datatype to page field
		//this is not entirely ideal since a datatype should not be linked to more than one field
		//but this table theoretically allows it
		//if we were to store the page field id in the datatype, then the table could
		//not be used for anything other than pages (without things getting messy)
		if ( !Schema::exists( 'pagefield_integers' ) ) {
			Schema::create( 'pagefield_integers', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'pagefield_id')->unique();
				$table->integer( 'integer_id')->unique();
				$table->timestamps();

				$table->index( array( 'pagefield_id', 'integer_id' ), 'pagefield_datatype_index' );
			} );
		}
		
		return true;
	}
	
	/**
	 * Uninstall the Integer Datatype
	 * 
	 * @param integer $version
	 * @return boolean 
	 */
	static public function firelite_uninstall( $version ){
		if ( $version > static::firelite_version() ){
			return false;
		}
		
		Schema::drop('datatype_integers');
		Schema::drop('storage_integers');
		
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
		return 'An integer value compatible with MySQL';
	}
	
	public function __toString(){
		return $this->getValue();
	}
	
}