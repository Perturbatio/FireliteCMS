<?php

class Firelite_Add_Editors_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'editors', function($table) {
			if ( false ) {
				$table = new Laravel\Database\Schema\Table( $table );
			}
			$table->increments( 'id' );
			$table->string( 'name', 100 )->unique();
			$table->string( 'description', 255 );
			$table->boolean('enabled');
			$table->integer( 'version');
			$table->timestamps();
			
			$table->index( array( 'name', 'enabled', 'version' ), 'index_name_enabled_version' );
		} );
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('editors');
	}

}