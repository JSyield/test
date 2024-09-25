<?php
/**
 * Update class.
 *
 * @package BlockArt
 */

namespace BlockArt;

defined( 'ABSPATH' ) || exit;

use BlockArt\Traits\Singleton;

/**
 * Update class.
 */
class Update {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	protected function __construct() {
		add_action( 'blockart_version_update', array( $this, 'on_update' ), 10, 2 );
	}

	/**
	 * On update.
	 *
	 * @param string $new_version Current version.
	 * @param string $old_version Old version.
	 * d previous version.
	 *
	 * @return void
	 */
	public function on_update( string $new_version, string $old_version ) {
		if ( version_compare( $old_version, '2.0.0', '<' ) ) {
			$this->update_to_2_0_0();
		}
		if ( version_compare( $old_version, '2.0.0.1', '<' ) ) {
			$this->update_to_2_0_0_1();
		}
		if ( version_compare( $old_version, '2.0.7', '<' ) ) {
			$this->update_to_2_0_7();
		}
		if ( version_compare( $old_version, '2.0.9', '<' ) ) {
			$this->update_to_2_0_9();
		}
		if ( version_compare( $old_version, '2.1.0', '<' ) ) {
			$this->update_to_2_1_0();
		}
	}

	/**
	 * Update to version 2.0.0.
	 *
	 * @return void
	 */
	private function update_to_2_0_0() {
		global $wpdb;

		// Delete old meta keys.
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_blockart_css' OR meta_key = '_blockart_active'" );

		// Delete old options.
		delete_option( '_blockart_widget_css' );
	}

	/**
	 * Update to version 2.0.0.1.
	 *
	 * @return void
	 */
	private function update_to_2_0_0_1() {
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_blockart_blocks_css'" );
		delete_option( '_blockart_blocks_css' );
	}

	/**
	 * Update to version 2.0.7.
	 *
	 * @return void
	 */
	private function update_to_2_0_7() {
		$setting              = blockart_get_setting();
		$old_css_print_method = get_option( '_blockart_dynamic_css_print_method', 'internal-css' );
		if ( 'external-css-file' === $old_css_print_method ) {
			$setting->set( 'asset-generation.external-file', true );
			$setting->save();
		}
		delete_option( '_blockart_dynamic_css_print_method' );
	}

	/**
	 * Update to version 2.0.9.
	 *
	 * @return void
	 */
	private function update_to_2_0_9() {
		Setting::read();

		foreach ( [ 'modal', 'icon', 'icon-list' ] as $block ) {
			if ( ! Setting::get( "blocks.$block", true ) ) {
				Setting::set( "blocks.$block", true );
			}
		}
		Setting::save();
	}

	/**
	 * Update to 2.1.0.
	 *
	 * @return void
	 */
	private function update_to_2_1_0() {
		delete_transient( '_blockart_library_data' );
	}
}
