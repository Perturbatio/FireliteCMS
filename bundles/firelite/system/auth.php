<?php namespace Firelite\System;

use Config;
use Exception;
use FireliteAuthDriver;
use Laravel\Auth as LaravelAuth;
use Laravel\Auth\Drivers\Eloquent;
use Laravel\Auth\Drivers\Fluent;

class Auth extends LaravelAuth {

	/**
	 * Get an authentication driver instance.
	 *
	 * @param  string  $driver
	 * @return Driver
	 */
	public static function driver($driver = null)
	{
		if (is_null($driver)) $driver = Firelite::config('auth.driver');

		if ( ! isset(static::$drivers[$driver]))
		{
			static::$drivers[$driver] = static::factory($driver);
		}

		return static::$drivers[$driver];
	}

	/**
	 * Create a new authentication driver instance.
	 *
	 * @param  string  $driver
	 * @return Driver
	 */
	protected static function factory($driver)
	{
		if (isset(static::$registrar[$driver]))
		{
			$resolver = static::$registrar[$driver];

			return $resolver();
		}

		switch ($driver)
		{
			case 'fluent':
				return new Fluent(Config::get('auth.table'));

			case 'eloquent':
				return new Eloquent(Config::get('auth.model'));

			case 'firelite':
				return new FireliteAuthDriver(Firelite::config('auth.model'));
				
			default:
				throw new Exception("Auth driver {$driver} is not supported.");
		}
	}

}