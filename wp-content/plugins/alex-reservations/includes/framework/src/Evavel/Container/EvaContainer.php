<?php
namespace Evavel\Container;

use Evavel\Providers\ServiceProvider;


class EvaContainer
{
    private static $singleton;

    public $jsonVariables = [];

    protected $bindings = [];

	public static function __callStatic( $name, $arguments )
	{
		if (method_exists(EvaContainer::class, $name)) {
			$container = EvaContainer::singleton();
			return $container->{$name}(...$arguments);
		}
		return null;
	}

    public static function singleton() {
        if ( !isset( self::$singleton ) && !( self::$singleton instanceof EvaContainer ) ) {
            self::$singleton = new EvaContainer;
            self::$singleton->boot();
        }
        return self::$singleton;
    }

    public function boot() {
        $this->addConfig([
            'axios_timeout' => 20000,
        ]);
    }

    public function addConfig($values)
    {
        $this->jsonVariables = array_merge($this->jsonVariables, $values);
    }

    public function configJson()
    {
		// Hook from WP
        $jsonVariables = apply_filters('srr_dashboard_config', $this->jsonVariables);

        return evavel_json_encode($jsonVariables);
    }

	public function config()
	{
		$jsonVariables = apply_filters('srr_dashboard_config', $this->jsonVariables);

		return $jsonVariables;
	}

    public function bind($name, $object) {
        if (!isset($this->bindings[$name])){
            $this->bindings[$name] = $object;
        }
    }

	public function make($name)
	{
		return $this->resolve($name);
	}

    public function resolve($name) {

		// Key of the binding could be a direct class
        // if (is_string($name) && class_exists($name))
	    if (!isset($this->bindings[$name]) && is_string($name) && class_exists($name))
		{
			return new $name;
		}

		if (!isset($this->bindings[$name])) return null;

		// Get the binding
		$result = $this->bindings[$name];

		// Is a class name
		if (is_string($result) && class_exists($result))
		{
		    return new $result;
	    }

		// Is a closure
		if (is_callable($result)) {
			return $result();
		}

		// Just the object, string, ...
		return $result;
    }

    public function registerProviders( $providers )
    {

        if (is_array($providers)){
            foreach($providers as $provider){
                $this->registerProvider($provider);
            }
        }
    }

	public function bootProviders( $providers )
	{
		if (is_array($providers)){
			foreach($providers as $provider){
				$this->bootProvider($provider);
			}
		}
	}

    public function registerProvider( $provider )
    {
        if (is_subclass_of($provider, ServiceProvider::class)){
            $provider->register();
        }
    }

	public function bootProvider( $provider )
	{
		if (is_subclass_of($provider, ServiceProvider::class)){
			$provider->boot();
		}
	}


}
