<?php
/**
 * 
 */
class TestNode extends PHPUnit_Framework_TestCase {
	
	/**
	 *  
	 */
	public function setUp(){
		Bundle::start('firelite');
	}
	
	/**
	 * Test that the FireliteNode alias exists
	 *
	 * @return void
	 */
	public function testFireliteNodeClassAlias(){
		$this->assertTrue( class_exists( 'FireliteNode' ) );
	}
	
	/**
	 * Test that the FireliteNode alias is an instance of Node
	 *
	 * @return void
	 */
	public function testFirelistNodeClassIsANodeInstance(){
		$node = new FireliteNode();
		$this->assertTrue( is_a($node, 'Firelite\\Models\\Node'));
	}

}