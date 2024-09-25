<?php

namespace Evavel\Http;

use Evavel\Eva;
use Evavel\Http\Middleware\SanitizeMiddleware;
use Evavel\Http\Request\Interfaces\AuthorizeWhenResolved;
use Evavel\Http\Request\Request;
use Evavel\Container\EvaContainer;
use Evavel\Http\Controllers;
use Evavel\Pipeline\Pipeline;

class ResolveRoute {

    /**
     * \WP_REST_Request
     */
    protected $WP_request;

	/**
	 * @var Request
	 */
    protected $request;

	/**
	 * Create Request from WP request received
	 *
	 * @param \WP_REST_Request $WP_request
	 */
	/*public function __construct_test($WP_request)
	{
		//ray('RESOLVE ROUTE');
		wp_set_current_user(4);
		$this->WP_request = $WP_request;
		$this->request = new Request($WP_request);

		Eva::bind('request', $this->request);
	}*/

    public function __construct($WP_request)
    {
		//ray('RESOLVE ROUTE');

	    // Init user (WP Cookie)
	    $this->autoLoginUser();

	    if (!is_user_logged_in()) {
		    evavel_login();
			exit();
	    }

	    // Verify nonce ++++++++++++++++
	    if (!defined('EVAVEL_SKIP_ROUTE_NONCE_CHECK') || !EVAVEL_SKIP_ROUTE_NONCE_CHECK)
	    {
		    $nonce = $WP_request->get_header('nonce');
		    if (!$nonce) {
			    $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : null;
		    }
		    if (!evavel_verify_nonce($nonce, EVAVEL_NONCE)) {
			    evavel_login();
		    }
	    }

		// Init request
        $this->WP_request = $WP_request;
        $this->request = new Request($WP_request);

	    Eva::bind('request', $this->request);
    }

	/**
	 * Use the WP COOKIE to login the user
	 *
	 * @return false|int
	 */
	public function autoLoginUser()
	{
		// Hacer auto login con el codigo
		/*$auth_token = $_SERVER['HTTP_AUTHORIZATION'];
		if ($auth_token == 'ar123456') {
			//ray('Con auth token');
			wp_set_current_user(4);
		}*/


		if (!isset($_COOKIE[ LOGGED_IN_COOKIE ])) return false;

		$user_id = wp_validate_auth_cookie( $_COOKIE[ LOGGED_IN_COOKIE ], 'logged_in' );

		if ($user_id){
			wp_set_current_user($user_id);
		}

		//ray($user_id);

		return $user_id;
	}

	/**
	 * Register some bindings needed
	 *
	 * @return void
	 */
	protected function bindings()
	{

		// Bind the application user, could be null
		//Eva::bind('user', $this->getApplicationUser());
	}


	/**
	 * Handle the request,
	 * extract params to call the controller
	 *
	 * @return bool|mixed|null
	 */
    public function handle()
    {
        // OLD code - try force VITE devtools
        //if (!EVAVEL_USE_VITE_DEVTOOLS){
            //$this->autoLoginUser();
            //if (!is_user_logged_in()) return wp_redirect('/');
        //} else {
        //    $wp_user_id = EVAVEL_USE_VITE_DEVTOOLS_WP_USER;
        //    wp_set_current_user($wp_user_id);
        //}

        // Extract main parameters
	    // controller, context, resourceName, resourceId
        //$params = $this->WP_request->get_params();
		$params = $this->request->params;
		//ray($params);

        // Find the controller
        $the_controller = $this->guessControllerAndMethod($params['controller']);
        if (empty($the_controller)) return null;
        $controller_class = $the_controller[0];
        $method_class = $the_controller[1];

        // Create the controller
        $controller = new $controller_class($controller_class);

        // Dependency Injection
        // Resolve the parameters of the method to prepare them
        $params_for_method = $this->resolveMethodParams($controller_class, $method_class);

        // Call the controller method
        return $controller->$method_class(...$params_for_method);
    }

	/**
	 * Extract the controller and method to be called
	 *
	 * @param $string
	 *
	 * @return array|null
	 */
    protected function guessControllerAndMethod($string)
    {
        $split = explode('@', $string);
        $controller_class = $split[0];
	    $method_class = $split[1];

		// Try the class directly
		if (class_exists($controller_class)) {
			return [$controller_class, $method_class];
		}

		// Try with framework controllers
        $controller_class = "Evavel\Http\Controllers\\".$controller_class;
	    if (class_exists($controller_class)) {
		    return [$controller_class, $method_class];
	    }

        return null;
    }

	/**
	 * Resolve parameters needed using reflection
	 *
	 * @param string $controller_class
	 * @param string $method
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
    protected function resolveMethodParams($controller_class, $method)
    {
        $resolved = [];

        // Extract params type
        $reflection = new \ReflectionClass($controller_class);
        $ref_method = $reflection->getMethod($method);
        $ref_params = $ref_method->getParameters();

	    // Resolve params
	    foreach ($ref_params as $ref_param) {

		    // Simple parameter extract
		    //if (is_null($ref_param->getClass())) { deprecated
		    if (is_null($ref_param->getType())) {
			    $resolved[] = $this->request->{$ref_param->name}();
		    }
		    // Class parameter -> IndexRequest, DetailRequest
		    // Instantiate with wp request
		    else {
			    //$class_name = $ref_param->getClass()->name; deprecated
			    $type = $ref_param->getType();
			    $class_name = $type->getName();

			    // IndexRequest...
			    //$resolved[] = new $class_name($this->WP_request);
			    $instance = new $class_name($this->request);
			    $resolved[]= $instance;
			    if ($instance instanceof AuthorizeWhenResolved) {
				    $instance->authorize();
			    }
		    }
	    }

        return $resolved;
    }

}
