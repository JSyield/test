<?php
/**
 * BetaTester class.
 *
 * @since 2.0.7.3
 * @package BlockArt
 */
namespace BlockArt;

defined( 'ABSPATH' ) || exit;

use BlockArt\Traits\Singleton;

/**
 * BetaTester class
 */
class BetaTester {

	use Singleton;

	/**
	 * Hashed transient key.
	 *
	 * @var string
	 */
	protected $hashed_transient_key;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		if ( ! $this->is_beta_tester() ) {
			return;
		}
		$this->set_hashed_transient_key();
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	protected function init_hooks() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_beta_version' ) );
	}

	/**
	 * Is beta tester.
	 *
	 * @return boolean
	 */
	protected function is_beta_tester() {
		return blockart_get_setting( 'version-control.beta-tester', false );
	}

	/**
	 * Set hashed transient key.
	 *
	 * @return void
	 */
	protected function set_hashed_transient_key() {
		$this->hashed_transient_key = md5( '_blockart_beta_tester' );
	}

	/**
	 * Check for beta version.
	 *
	 * @param object $transient_data Plugins updates transient data.
	 * @return object
	 */
	public function check_for_beta_version( $transient_data ) {
		if ( empty( $transient_data->checked ) ) {
			return $transient_data;
		}

		delete_site_transient( $this->hashed_transient_key );

		$plugin_slug  = basename( BLOCKART_PLUGIN_FILE, '.php' );
		$beta_version = $this->retrieve_beta_version();

		if ( empty( $beta_version ) || version_compare( $beta_version, BLOCKART_VERSION, '<=' ) ) {
			return $transient_data;
		}

		$transient_data->response[ plugin_basename( BLOCKART_PLUGIN_FILE ) ] = (object) array(
			'plugin'      => $plugin_slug,
			'slug'        => $plugin_slug,
			'new_version' => $beta_version,
			'url'         => 'https://wpblockart.com',
			'package'     => sprintf( 'https://downloads.wordpress.org/plugin/blockart-blocks.%s.zip', $beta_version ),
		);

		return $transient_data;
	}

	/**
	 * Retrieve beta version if available.
	 *
	 * @return string
	 */
	protected function retrieve_beta_version() {
		$beta_version = get_site_transient( $this->hashed_transient_key );

		if ( ! empty( $beta_version ) ) {
			return $beta_version;
		}

		$response = wp_remote_get( 'https://plugins.svn.wordpress.org/blockart-blocks/trunk/readme.txt' );
		$response = wp_remote_retrieve_body( $response );
		if ( ! empty( $response ) ) {
			preg_match( '/Beta tag: (.*)/i', $response, $matches );
			if ( isset( $matches[1] ) ) {
				$beta_version = $matches[1];
			}
			set_site_transient( $this->hashed_transient_key, $beta_version, 6 * HOUR_IN_SECONDS );
		}

		return $beta_version;
	}
}