<?php

class Firelite_Create_User_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('firelite_users', function($table) {
			$table->increments('id');
			$table->string('username', 255)->unique();
			$table->string('password', 255);
			$table->timestamps();
			$table->index( array( 'id', 'username' ), 'index_id_username' );
			
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('firelite_users');
	}

}