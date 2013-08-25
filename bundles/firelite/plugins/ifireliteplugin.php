<?php namespace Firelite\Plugins;

interface IFirelitePlugin {
	
	/**
	 *
	 * @return array 
	 */
	static public function getMainNav();
	
	/**
	 *
	 * @return boolean 
	 */
	static public function hasMainNav();
	
	/**
	 *
	 * @return array 
	 */
	static public function getSubNav();
	
	/**
	 *
	 * @return boolean 
	 */
	static public function hasSubNav();
	/*public function firelite_install();
	public function firelite_uninstall();
	public function firelite_version();
	public function firelite_description();
	public function firelite_activate();
	public function firelite_render();*/
}