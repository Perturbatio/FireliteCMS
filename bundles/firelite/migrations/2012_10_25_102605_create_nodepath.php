<?php

class Firelite_Create_Nodepath {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'nodes', function($table) {
			if ( false ) {
				$table = new Laravel\Database\Schema\Table( $table );
			}
			$table->string('path', 1000);
		} );
		
		DB::query('ALTER TABLE `nodes` ADD INDEX ( `path` ) ');
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nodes', function($table){
			$table->drop_index('index_path');
			$table->drop_column('path');
		});
	}

}