<?php
namespace Firelite\Models\Datatypes;

use FireliteBasedatatype;
use FireliteSchema as Schema;
use Form;
use Input;
use Firelite;

class Datetime extends FireliteBasedatatype {
	
	
	/**
	 *
	 * @var String
	 */
	static public $table = 'datatype_datetimes';
	
	static public $edit_as_datatype = 'Datetime';
	
	static protected $storage_class = 'FireliteStorageDatetime';
	
	
	/**
	 * 
	 * @return Relationships\Has_One
	 */
	public function storage(){
		return $this->has_one(static::$storage_class, 'datatype_datetime_id');
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
				'datatype_datetime_id' => $this->id,
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
		if ( !Schema::exists( 'storage_datetimes' ) ) {
			Schema::create( 'storage_datetimes', function($table) {
				$table->increments( 'id' );
				$table->integer( 'datatype_datetime_id')->unique();
				$table->date('value');
				$table->timestamps();

				$table->index( array( 'id', 'datatype_datetime_id', 'value' ), 'id_datatype_value_index' );
			} );
		}
				

		//create a tracker (pivot) table for the datatype to post field
		//this is not entirely ideal since a datatype should not be linked to more than one field
		//but this table theoretically allows it
		//if we were to store the post field id in the datatype, then the table could
		//not be used for anything other than posts (without things getting messy)
		if ( !Schema::exists( 'postfield_datetimes' ) ) {
			Schema::create( 'postfield_datetimes', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->integer( 'postfield_id')->unique();
				$table->integer( 'datetime_id')->unique();
				$table->timestamps();

				$table->index( array( 'postfield_id', 'datetime_id' ), 'postfield_datatype_index' );
			} );
		}
		
		
		if ( !Schema::exists( 'datatype_datetimes' ) ) {
			Schema::create( 'datatype_datetimes', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );
				$table->timestamps();

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
		
		if ( !Schema::exists( 'datatype_datetimes' ) ) {
			Schema::drop('datatype_datetimes');
		}
		if ( !Schema::exists( 'storage_datetimes' ) ) {
			Schema::drop('storage_datetimes');
		}
	
		
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
		return 'Datetime';
	}
	
	public function __toString(){
		return $this->getValue();
	}
	
}