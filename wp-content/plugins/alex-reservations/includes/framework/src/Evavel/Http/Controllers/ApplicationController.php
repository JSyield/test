<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\Request;

class ApplicationController extends Controller
{
	/**
	 * Returns initial configuration for the application
	 * based on the user logged in
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle(Request $request)
	{
		$configurator = Eva::make('app-configurator');
		$config = $configurator === null ? [] : $configurator->getConfiguration();
		return $this->response($config);
	}
}
