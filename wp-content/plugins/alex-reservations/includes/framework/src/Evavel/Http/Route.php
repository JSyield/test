<?php

namespace Evavel\Http;

class Route {

    public $method;
    protected $route;
    protected $controller_method;
    protected $args = [];
    protected $format = [];

    public function __construct($route, $controller_method) {
        $this->route = $route;
        $this->controller_method = $controller_method;
        $this->args['controller'] = $controller_method;
    }

    public static function get( $route, $controller_method ) {
        $instance = new static($route, $controller_method);
        $instance->method = 'GET';
        return $instance;
    }

    public static function post( $route, $controller_method ) {
        $instance = new static($route, $controller_method);
        $instance->method = 'POST';
        return $instance;
    }

    public static function delete( $route, $controller_method ) {
        $instance = new static($route, $controller_method);
        $instance->method = 'DELETE';
        return $instance;
    }

    public function context($mode) {
        $this->args['context'] = $mode;
        return $this;
    }

    public function format($format) {
        $this->format = array_merge($this->format, $format);
        return $this;
    }

    // Prepare for WP
    public function getRoute() {
        if (empty($this->format)) {
            $this->addDefaultFormats();
        }

        foreach ($this->format as $key => $format){
            $this->route = str_replace($key, '(?P<'.str_replace(':','',$key).'>'.$format.'+)', $this->route);
        }
        return $this->route;
    }

    protected function addDefaultFormats() {

		$formats = [
			['#:resourceName#', ':resourceName', '.'],
			['#:resourceId#', ':resourceId', '[\d]'],
			['#:field#', ':field', '.'],
			['#:lens#', ':lens', '.'],
			['#:settingName#', ':settingName', '.'],
			['#:settingId#', ':settingId', '[\d]'],
			['#:date#', ':date', '.'],
			['#:range#', ':range', '.'],
			['#:bookingId#', ':bookingId', '[\d]'],
			['#:notificationId#', ':notificationId', '[\d]'],
			['#:status#', ':status', '.'],
			['#:customerId#', ':customerId', '[\d]'],
			['#:customers#', ':customers', '.'],
			['#:userId#', ':userId', '[\d]'],
			['#:lang#', ':lang', '.'],
			['#:attribute#', ':attribute', '.'],
			['#:status#', ':status', '.'],
			['#:view#', ':view', '.'],
			['#:uuid#', ':uuid', '.'],
			['#:option#', ':option', '.']
		];

		$app_formats = evavel_app_routes_params_formats();
		foreach($app_formats as $format){
			$formats[] = $format;
		}

		foreach($formats as $format) {
			if (preg_match($format[0], $this->route)){
				$this->format([$format[1] => $format[2]]);
			}
		}
    }

    public function getContext() {
        return $this->args['context'];
    }

    public function getController() {
        return $this->args['controller'];
    }

}

