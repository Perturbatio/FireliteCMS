<?php
namespace Firelite\Database;

use Laravel\Database\Schema as LaravelSchema;
use \DB;

class Schema extends LaravelSchema {
	
	/**
	 * Determine if a table exists (this function is a little ugly, but it works)
	 * 
	 * @param type $table 
	 */
	static public function exists($table){
			
		try {
			$connection = DB::table($table)->connection;
			$res = $connection->query( "SHOW TABLES FROM {$connection->config[ 'database' ]} LIKE '{$table}'" );
			return count($res) > 0;
		} catch ( Exception $e ){
			
		}
		return false;
	}
}