<?php

class Firelite_Create_Node_Types {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'nodetypes', function($table) {
			if ( false ) {
				$table = new Laravel\Database\Schema\Table( $table );
			}
			$table->increments( 'id' );
			$table->string( 'name', 100 )->unique();
			$table->string( 'description', 255 );
			$table->integer( 'version');
			$table->timestamps();
		} );
		
		
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('nodetypes');
	}

}