<?php

class Firelite_Create_Nodes_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'nodes', function($table) {
			if ( false ) {
				$table = new Laravel\Database\Schema\Table( $table );
			}
			$table->increments( 'id' );
			
			$table->string('name', 255);
			
			$table->integer('lft');
			$table->integer('rgt');
			$table->integer('tree_id');
			$table->integer('parent_node_id');
			$table->integer('nodetype_id');
			
			
			$table->timestamps();
			
			$table->index('name');
			$table->index('parent_node_id');
			$table->index('nodetype_id');
			$table->index('lft','rgt','name');
			$table->unique(array('tree_id', 'name', 'parent_node_id') );
			
		} );
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('nodes');
	}

}