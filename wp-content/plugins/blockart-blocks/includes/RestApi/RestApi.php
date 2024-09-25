<?php

/**
 * BlockArt Rest API.
 *
 * @package BlockArt
 */

namespace BlockArt\RestApi;

defined( 'ABSPATH' ) || exit;

use BlockArt\Traits\Singleton;

/**
 * BlockArt Rest API.
 */
class RestApi {


	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'rest_api_init', array( $this, 'on_rest_api_init' ) );
	}

	/**
	 * On rest api init.
	 *
	 * @return void
	 */
	public function on_rest_api_init() {
		$this->register_rest_routes();
	}

	/**
	 * Register rest routes.
	 *
	 * @return void
	 */
	private function register_rest_routes() {
		$controllers = $this->get_controllers();
		foreach ( $controllers as $controller ) {
			$controller = new $controller();
			$controller->register_routes();
		}
	}

	/**
	 * Get controllers.
	 *
	 * @return array
	 */
	public function get_controllers() {
		return apply_filters(
			'blockart_get_rest_api_controllers',
			[
				'BlockArt\RestApi\Controllers\LibraryDataController',
				'BlockArt\RestApi\Controllers\ImageImportController',
				'BlockArt\RestApi\Controllers\RegenerateAssetsController',
				'BlockArt\RestApi\Controllers\SettingsController',
				'BlockArt\RestApi\Controllers\ChangelogController',
				'BlockArt\RestApi\Controllers\VersionControlController',
			]
		);
	}
}
