<?php

class Firelite_Add_Published_To_Nodes {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nodes', function($table){
			$table->boolean('published');
			$table->index(array('id','name','published'),'index_node_published');
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
			$table->drop_index('index_node_published');
			$table->drop_column('published');
		});
	}

}