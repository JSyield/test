<?php
/**
 * AdminMenu class.
 *
 * @package BlockArt
 * @since 1.0.0
 */

namespace BlockArt;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use BlockArt\Traits\Singleton;

/**
 * Admin class.
 */
class Admin {

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
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'init_menus' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		add_filter( 'update_footer', array( $this, 'admin_footer_version' ), 11 );
		add_action( 'in_admin_header', array( $this, 'hide_admin_notices' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
	}

	/**
	 * Init menus.
	 *
	 * @since 1.0.0
	 */
	public function init_menus() {
		$blockart_page = add_menu_page(
			esc_html__( 'BlockArt', 'blockart' ),
			esc_html__( 'BlockArt', 'blockart' ),
			'manage_options',
			'blockart',
			array( $this, 'markup' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 22H2V2h20zM3 21h18V3H3z" fill="#fff"/><path d="M13.46 10l-1.39-5-1.39 5zm.92 3H9.77l-1 4.46V19h6.4v-1.52z" fill="#fff" fill-rule="evenodd"/></svg>' ) // phpcs:ignore
		);

		$submenus = $this->get_submenus();

		uasort(
			$submenus,
			function ( $a, $b ) {
				if ( $a['position'] === $b['position'] ) {
					return 0;
				}
				return ( $a['position'] < $b['position'] ) ? -1 : 1;
			}
		);

		foreach ( $submenus as $slug => $submenu ) {
			add_submenu_page(
				$submenu['parent_slug'],
				$submenu['page_title'],
				$submenu['menu_title'],
				$submenu['capability'],
				'blockart#/' . $slug,
				$submenu['callback'],
				$submenu['position']
			);
		}

		add_action( "admin_print_scripts-$blockart_page", array( $this, 'enqueue' ) );
		remove_submenu_page( 'blockart', 'blockart' );
	}

	/**
	 * Get submenus.
	 *
	 * @return array
	 */
	private function get_submenus() {
		$submenus = [
			'dashboard' => [
				'page_title' => __( 'Dashboard', 'blockart' ),
				'menu_title' => __( 'Dashboard', 'blockart' ),
				'position'   => 10,
			],
			'blocks'    => [
				'page_title' => __( 'Blocks', 'blockart' ),
				'menu_title' => __( 'Blocks', 'blockart' ),
				'position'   => 20,
			],
			'products'  => [
				'page_title' => __( 'Products', 'blockart' ),
				'menu_title' => __( 'Products', 'blockart' ),
				'position'   => 30,
			],
			'settings'  => [
				'page_title' => __( 'Settings', 'blockart' ),
				'menu_title' => __( 'Settings', 'blockart' ),
				'position'   => 40,
			],
			'help'      => [
				'page_title' => __( 'Help', 'blockart' ),
				'menu_title' => __( 'Help', 'blockart' ),
				'position'   => 50,
			],
		];

		$submenus = apply_filters( 'blockart_admin_submenus', $submenus );
		$submenus = array_map(
			function ( $submenu ) {
				return wp_parse_args(
					$submenu,
					array(
						'page_title'  => '',
						'menu_title'  => '',
						'parent_slug' => 'blockart',
						'capability'  => 'manage_options',
						'position'    => 1000,
						'callback'    => [ $this, 'markup' ],
					)
				);
			},
			$submenus
		);

		return $submenus;
	}

	/**
	 * Markup.
	 *
	 * @since 1.0.0
	 */
	public function markup() {
		echo '<div id="blockart"></div>';
	}

	/**
	 * Enqueue.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		wp_enqueue_script( 'blockart-admin' );
	}

	/**
	 * Change admin footer text on BlockArt page.
	 *
	 * @param string $text Admin footer text.
	 *
	 * @return string Admin footer text.
	 */
	public function admin_footer_text( string $text ): string {
		if ( 'toplevel_page_blockart' !== get_current_screen()->id ) {
			return $text;
		}

		return __( 'Thank you for creating with BlockArt Blocks.', 'blockart' );
	}

	/**
	 * Override WordPress version with plugin version.
	 *
	 * @param string $version Version text.
	 *
	 * @return string Version text.
	 */
	public function admin_footer_version( string $version ): string {
		return 'toplevel_page_blockart' !== get_current_screen()->id ? $version : __( 'Version ', 'blockart' ) . BLOCKART_VERSION;
	}

	/**
	 * Redirecting user to dashboard page.
	 */
	public function admin_redirects() {
		if ( get_option( '_blockart_activation_redirect' ) && apply_filters( 'blockart_activation_redirect', true ) ) {
			update_option( '_blockart_activation_redirect', false );
			wp_safe_redirect( admin_url( 'index.php?page=blockart#/getting-started' ) );
			exit;
		}
	}

	/**
	 * Hide admin notices from BlockArt admin pages.
	 *
	 * @since 1.0.0
	 */
	public function hide_admin_notices() {

		// Bail if we're not on a BlockArt screen or page.
		if ( empty( $_REQUEST['page'] ) || false === strpos( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ), 'blockart' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		global $wp_filter;
		$ignore_notices = apply_filters( 'blockart_ignore_hide_admin_notices', array() );

		foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $wp_notice ) {
			if ( empty( $wp_filter[ $wp_notice ] ) ) {
				continue;
			}

			$hook_callbacks = $wp_filter[ $wp_notice ]->callbacks;

			if ( empty( $hook_callbacks ) || ! is_array( $hook_callbacks ) ) {
				continue;
			}

			foreach ( $hook_callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $callback ) {
					if ( ! empty( $name ) && in_array( $name, $ignore_notices, true ) ) {
						continue;
					}
					if (
						! empty( $callback['function'] ) &&
						! is_a( $callback['function'], '\Closure' ) &&
						isset( $callback['function'][0], $callback['function'][1] ) &&
						is_object( $callback['function'][0] ) &&
						in_array( $callback['function'][1], $ignore_notices, true )
					) {
						continue;
					}
					unset( $wp_filter[ $wp_notice ]->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}
}
