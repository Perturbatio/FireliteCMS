<?php namespace Firelite;
use Exception;
use Laravel\Asset as Laravel_Asset;
use Laravel\Asset_Container as Laravel_Asset_Container;
use \Bundle;
use \HTML;
use \URL;

class Asset extends \Laravel\Asset{


	/**
	 * Get an asset container instance.
	 *
	 * <code>
	 *		// Get the default asset container
	 *		$container = Asset::container();
	 *
	 *		// Get a named asset container
	 *		$container = Asset::container('footer');
	 * </code>
	 *
	 * @param  string            $container
	 * @return Asset_Container
	 */
	public static function container($container = 'default')
	{
		if ( ! isset(static::$containers[$container]))
		{
			static::$containers[$container] = new Asset_Container($container);
		}

		return static::$containers[$container];
	}

	/**
	 * Magic Method for calling methods on the default container.
	 *
	 * <code>
	 *		// Call the "styles" method on the default container
	 *		echo Asset::styles();
	 *
	 *		// Call the "add" method on the default container
	 *		Asset::add('jquery', 'js/jquery.js');
	 * </code>
	 */
	public static function __callStatic($method, $parameters)
	{
		return call_user_func_array(array(static::container(), $method), $parameters);
	}

}

class Asset_Container extends Laravel_Asset_Container {

	/**
	 * Add an asset to the container.
	 *
	 * The extension of the asset source will be used to determine the type of
	 * asset being registered (CSS or JavaScript). When using a non-standard
	 * extension, the style/script methods may be used to register assets.
	 * 
	 * Adding a handlebars template currently requires the name to begin with "handlebars-"
	 *
	 * <code>
	 *		// Add an asset to the container
	 *		Asset::container()->add('jquery', 'js/jquery.js');
	 *
	 *		// Add an asset that has dependencies on other assets
	 *		Asset::add('jquery', 'js/jquery.js', 'jquery-ui');
	 *
	 *		// Add an asset that should have attributes applied to its tags
	 *		Asset::add('jquery', 'js/jquery.js', null, array('defer'));
	 * </code>
	 *
	 * @param  string  $name
	 * @param  string  $source
	 * @param  array   $dependencies
	 * @param  array   $attributes
	 * @return void
	 */
	public function add( $name, $source, $dependencies = array(), $attributes = array() ) {
		//detect handlebars first, if none, pass through to standard detection
		if ( strpos( $name, 'handlebars-' ) === 0 ){
			$type = 'handlebar';//need to use singular to prevent conflicting with the handlebars function (which outputs all handlebars templates)
		} else {
			$type = (pathinfo( $source, PATHINFO_EXTENSION ) == 'css') ? 'style' : 'script';
		}
		return $this->$type( $name, $source, $dependencies, $attributes );
	}

	/**
	 *
	 * @param string $name
	 * @param string $source
	 * @param array $dependencies
	 * @param array $attributes 
	 */
	public function handlebar( $name, $source, $dependencies=array(), $attributes=array() ) {
		$this->register('handlebar', $name, $source, $dependencies, $attributes);

		return $this;
	}
	
	/**
	 * Get all of the registered Handlebars Template markup.
	 *
	 * @return  string
	 */
	public function handlebars()
	{
		return $this->group('handlebar');
	}
	

	/**
	 * Get the HTML link to a registered asset.
	 *
	 * @param  string  $group
	 * @param  string  $name
	 * @return string
	 */
	protected function asset($group, $name)
	{
		if ( ! isset($this->assets[$group][$name])) return '';

		$asset = $this->assets[$group][$name];
		// If the bundle source is not a complete URL, we will go ahead and prepend
		// the bundle's asset path to the source provided with the asset. This will
		// ensure that we attach the correct path to the asset.
		if ($group !== 'handlebar' || !View::exists( $asset['source'] )){//if it's not a handlebar asset OR it's not a valid view (prevents a / prefix being added to source
			if (filter_var($asset['source'], FILTER_VALIDATE_URL) === false)
			{
				$asset['source'] = $this->path($asset['source']);
			}
		}
		switch($group){
			case 'handlebar':
				$script_content = '';
				$result = '<script type="text/x-handlebars-template" id="' . $name . '"';
				
				if ( View::exists( $asset['source'] ) ){
					$script_content = PHP_EOL . render($asset['source']). PHP_EOL;
				} else {
					$url = URL::to_asset($asset['source']);
					
					$result .= ' src="'.$url.'" ';
				}
				$result .= HTML::attributes($asset['attributes']).'>' . $script_content . '</script>'.PHP_EOL;
			break;
			default:
				$result = HTML::$group($asset['source'], $asset['attributes']);
			break;
		}
		return $result;
	}
	

}
