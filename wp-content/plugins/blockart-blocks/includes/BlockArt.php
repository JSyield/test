<?php
/**
 * BlockArt plugin main class.
 *
 * @since 1.0.0
 * @package BlockArt
 */

namespace BlockArt;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use BlockArt\RestApi\RestApi;
use BlockArt\Traits\Singleton;

/**
 * BlockArt setup.
 *
 * Include and initialize necessary files and classes for the plugin.
 *
 * @since   1.0.0
 */
final class BlockArt {

	use Singleton;

	/**
	 * Plugin Constructor.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function __construct() {
		Activation::init();
		Deactivation::init();
		Update::init();
		RestApi::init();
		Admin::init();
		Review::init();
		Blocks::init();
		ScriptStyle::init();
		BetaTester::init();
		MaintenanceMode::init();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'after_wp_init' ), 0 );
		add_filter( 'upload_mimes', array( $this, 'upload_custom_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'check_filetype_and_ext' ), 10, 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_global_styles' ) );
	}

	public function enqueue_global_styles() {
		$global_styles = blockart_generate_global_styles();
		$global_styles->enqueue_fonts();
		$global_styles->enqueue();
	}

	/**
	 * Initialize BlockArt when WordPress initializes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function after_wp_init() {
		/**
		 * BlockArt before init.
		 *
		 * @since 1.0.0
		 */
		do_action( 'blockart_before_init' );
		$this->update_plugin_version();
		$this->load_text_domain();
		/**
		 * BlockArt init.
		 *
		 * Fires after BlockArt has loaded.
		 *
		 * @since 1.0.0
		 */
		do_action( 'blockart_init' );
	}

	/**
	 * Update the plugin version.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function update_plugin_version() {
		$blockart_version = get_option( '_blockart_version', '' );
		do_action( 'blockart_version_update', BLOCKART_VERSION, $blockart_version );
		update_option( '_blockart_version', BLOCKART_VERSION );
	}

	/**
	 * Load plugin text domain.
	 */
	private function load_text_domain() {
		load_plugin_textdomain( 'blockart', false, plugin_basename( BLOCKART_PLUGIN_DIR ) . '/languages' );
	}

	/**
	 * Add custom mimes as supported format in the uploader.
	 *
	 * @param array $mimes Supported mime types.
	 */
	public function upload_custom_mimes( $mimes ) {
		$mimes['json'] = 'application/json';
		$mimes['svg']  = 'image/svg+xml';

		return $mimes;
	}

	/**
	 * Return valid filetype array for lottie json uploads.
	 *
	 * @param array  $value Filetype array.
	 * @param string $file Original file.
	 * @param string $filename Filename.
	 * @param array  $mimes Mimes array.
	 * @param string $real_mime Real mime type.
	 * @return array
	 */
	public function check_filetype_and_ext( $value, $file, $filename, $mimes, $real_mime ) {

		$wp_filetype = wp_check_filetype( $filename, $mimes );
		$ext         = $wp_filetype['ext'];
		$type        = $wp_filetype['type'];

		if ( ( 'json' !== $ext || 'application/json' !== $type || 'text/plain' !== $real_mime ) &&
		( 'svg' !== $ext || 'image/svg+xml' !== $type ) ) {
			return $value;
		}

		$value['ext']             = $wp_filetype['ext'];
		$value['type']            = $wp_filetype['type'];
		$value['proper_filename'] = $filename;

		return $value;
	}
}
