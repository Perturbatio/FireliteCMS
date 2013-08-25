<?php

class Firelite_Modify_Nodes_Add_Nav_Properties {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nodes', function($table){
			$table->string( 'link_text', 255 );
			$table->string( 'link_title', 64 );
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nodes', function($table){
			$table->drop_column('link_text');
			$table->drop_column('link_title');
		});
	}

}