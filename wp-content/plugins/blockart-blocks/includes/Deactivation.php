<?php
/**
 * Deactivation class.
 *
 * @package BlockArt
 * @since 1.0.0
 */

namespace BlockArt;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use BlockArt\Traits\Singleton;

/**
 * Deactivation class.
 */
class Deactivation {

	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		register_deactivation_hook( BLOCKART_PLUGIN_FILE, array( __CLASS__, 'on_deactivate' ) );
		register_uninstall_hook( BLOCKART_PLUGIN_FILE, array( __CLASS__, 'on_uninstall' ) );
	}

	/**
	 * Callback for plugin deactivation hook.
	 *
	 * @since 1.0.0
	 */
	public static function on_deactivate() {}

	/**
	 * Callback for plugin uninstall hook.
	 *
	 * @since 1.0.0
	 */
	public static function on_uninstall() {
		if ( ! apply_filters( 'blockart_remove_data_on_uninstall', false ) ) {
			return;
		}

		global $wpdb;

		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%_blockart_%';" );
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%_blockart_%';" );

		$filesystem = blockart_get_filesystem();

		if ( $filesystem && $filesystem->is_dir( BLOCKART_UPLOAD_DIR ) ) {
			$filesystem->delete( BLOCKART_UPLOAD_DIR, true );
		}

		wp_cache_flush();
	}
}
