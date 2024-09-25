<?php

namespace Evavel\Http;


use Evavel\Container\EvaContainer;
use Evavel\Providers\EventServiceProvider;

class RegisterRoutes {

    public static $namespace = EVAVEL_WPJSON_NAMESPACE;
	public static $routesRegisterd = false;

    public function __construct()
    {
		// Will be called for each plugin using the framework so be sure it is called only once
		if (self::$routesRegisterd) return;
		self::$routesRegisterd = true;

        $this->hooks();
    }

    public function hooks() {
        add_action( 'rest_api_init', array( $this, 'register_endpoint_eloquent' ) );
    }

    public function register_endpoint_eloquent() {

		// Load routes
        $routes = require EVAVEL_FRAMEWORK . 'routes/routes.php';

        foreach($routes as $route) {
            register_rest_route( static::$namespace, $route->getRoute(), array(
                array(
                    'methods'         => $route->method,
                    'callback'        => array( $this, 'resolve_route' ),
                    'permission_callback' => array( $this, 'permission_callback' ),
	                //'permission_callback' => __return_true,
                    'args'            => array(
                        'controller' =>  array(
                            'default' => $route->getController()
                        ),
                        'context' => array(
                            'default' => $route->getContext()
                        )
                    ),
                ),
            ) );
        }

    }

    public function resolve_route($WP_request)
    {
        return (new ResolveRoute($WP_request))->handle();
    }

	public function permission_callback()
	{
		return true;
	}

}



