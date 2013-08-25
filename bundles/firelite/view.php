<?php
namespace Firelite;

use \Event;

class View extends \Laravel\View {

	/**
	 * Overrides Laravel view to implement named view composing
	 * 
	 * Register a view composer with the Event class.
	 *
	 * <code>
	 *		// Register a composer for the "home.index" view
	 *		View::composer('home.index', function($view)
	 *		{
	 *			$view['title'] = 'Home';
	 *		});
	 * </code>
	 *
	 * @param  string|array  $views
	 * @param  Closure       $composer
	 * @return void
	 */
	public static function composer($views, $composer)
	{
		$views = (array) $views;

		foreach ($views as $view)
		{
			if (starts_with($view, 'name: ') and array_key_exists($name = substr($view, 6), static::$names))
			{
				$view = static::$names[$name];
			}
			Event::listen("laravel.composing: {$view}", $composer);
		}
	}
}