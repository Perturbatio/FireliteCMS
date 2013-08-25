<?php

class Firelite_Create_Trees {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'trees', function($table) {
				if ( false ) {
					$table = new Laravel\Database\Schema\Table( $table );
				}
				$table->increments( 'id' );

				$table->string( 'name', 64 )->unique();
				$table->string( 'description', 255 );


				$table->timestamps();
			} );

		DB::table('trees')->insert(array(
			'name' => 'main_site',
			'description' => 'This is the default site tree',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('trees');
	}

}