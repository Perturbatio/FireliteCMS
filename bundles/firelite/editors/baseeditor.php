<?php namespace Firelite\Editors;
//TODO: assess whether this is needed since static methods cannot be abstract
/**
 * 
 */
abstract class BaseEditor {
	static public function render($name, $data, $params = array()){
		return 'No editor';
	}
}