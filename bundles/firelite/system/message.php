<?php namespace Firelite\System;

/**
 * Class Message
 *
 * @author KKelly
 */
class Message {
	/**
	 *
	 * @var string 
	 */
	public $text;
	/**
	 *
	 * @var type 
	 */
	public $type;
	
	//methods
	
	/**
	 * Constructor for the Message class
	 *
	 */
	public function __construct( $text, $type ) {
		$this->text = $text;
		$this->type = $type;
	}
	
	public function __toString(){
		return $this->text;
	}
	
}
