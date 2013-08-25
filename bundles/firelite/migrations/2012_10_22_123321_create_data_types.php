<?php

class Firelite_Create_Data_Types {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'datatypes', function($table) {
			if ( false ) {
				$table = new Laravel\Database\Schema\Table( $table );
			}
			$table->increments( 'id' );
			$table->string( 'name', 64 )->unique();
			$table->string( 'description', 255 );
			$table->integer( 'version' );
			$table->timestamps();
			
			$table->index( array( 'name', 'version' ), 'index_name_version' );
			
		} );
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('datatypes');
	}

}