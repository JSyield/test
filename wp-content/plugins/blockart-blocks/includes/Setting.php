<?php
/**
 * Setting API class.
 *
 * @package BlockArt
 */

namespace BlockArt;

defined( 'ABSPATH' ) || exit;

/**
 * Setting class.
 */
class Setting {

	/**
	 * Default data.
	 *
	 * @var array
	 */
	private static $data = array(
		'blocks'           => array(
			'section'          => true,
			'heading'          => true,
			'paragraph'        => true,
			'button'           => true,
			'image'            => true,
			'spacing'          => true,
			'socials'          => true,
			'tabs'             => true,
			'toc'              => true,
			'counter'          => true,
			'lottie'           => true,
			'team'             => true,
			'countdown'        => true,
			'info'             => true,
			'blockquote'       => true,
			'timeline'         => true,
			'notice'           => true,
			'progress'         => true,
			'call-to-action'   => true,
			'slider'           => true,
			'map'              => true,
			'testimonial'      => true,
			'faq'              => true,
			'modal'            => true,
			'icon'             => true,
			'icon-list'        => true,
			'image-comparison' => true,
		),
		'editor'           => array(
			'section-width'          => 1170,
			'editor-blocks-spacing'  => 24,
			'design-library'         => true,
			'responsive-breakpoints' => array(
				'tablet' => 992,
				'mobile' => 768,
			),
			'copy-paste-styles'      => true,
			'auto-collapse-panels'   => true,
		),
		'performance'      => array(
			'local-google-fonts'        => false,
			'preload-local-fonts'       => false,
			'allow-only-selected-fonts' => false,
			'allowed-fonts'             => array(),
		),
		'asset-generation' => array(
			'external-file' => false,
		),
		'version-control'  => array(
			'beta-tester' => false,
		),
		'maintenance-mode' => array(
			'mode'             => 'none',
			'maintenance-page' => null,
		),
		'integrations'     => array(
			'google-maps-embed-api-key' => '',
		),
		'global-styles'    => '',
	);

	/**
	 * Sanitize callbacks.
	 *
	 * @var array
	 */
	private static $sanitize_callbacks = array(
		'blocks'           => array(
			'section'         => 'blockart_string_to_bool',
			'heading'         => 'blockart_string_to_bool',
			'paragraph'       => 'blockart_string_to_bool',
			'button'          => 'blockart_string_to_bool',
			'image'           => 'blockart_string_to_bool',
			'spacing'         => 'blockart_string_to_bool',
			'socials'         => 'blockart_string_to_bool',
			'tabs'            => 'blockart_string_to_bool',
			'toc'             => 'blockart_string_to_bool',
			'counter'         => 'blockart_string_to_bool',
			'lottie'          => 'blockart_string_to_bool',
			'team'            => 'blockart_string_to_bool',
			'countdown'       => 'blockart_string_to_bool',
			'info'            => 'blockart_string_to_bool',
			'blockquote'      => 'blockart_string_to_bool',
			'timeline'        => 'blockart_string_to_bool',
			'notice'          => 'blockart_string_to_bool',
			'progress'        => 'blockart_string_to_bool',
			'call-to-action'  => 'blockart_string_to_bool',
			'slider'          => 'blockart_string_to_bool',
			'map'             => 'blockart_string_to_bool',
			'testimonial'     => 'blockart_string_to_bool',
			'imageComparison' => 'blockart_string_to_bool',
			'faq'             => 'blockart_string_to_bool',
		),
		'editor'           => array(
			'section-width'          => 'absint',
			'editor-blocks-spacing'  => 'absint',
			'design-library'         => 'blockart_string_to_bool',
			'copy-paste-styles'      => 'blockart_string_to_bool',
			'auto-collapse-panels'   => 'blockart_string_to_bool',
			'responsive-breakpoints' => array(
				'tablet' => 'absint',
				'mobile' => 'absint',
			),
		),
		'performance'      => array(
			'local-google-fonts'        => 'blockart_string_to_bool',
			'preload-local-fonts'       => 'blockart_string_to_bool',
			'allow-only-selected-fonts' => 'blockart_string_to_bool',
		),
		'asset-generation' => array(
			'external-file' => 'blockart_string_to_bool',
		),
		'version-control'  => array(

			'beta-tester' => 'blockart_string_to_bool',
		),
		'integrations'     => array(
			'google-maps-embed-api-key' => 'sanitize_text_field',
		),
		'maintenance-mode' => array(
			'mode'             => 'sanitize_text_field',
			'maintenance-page' => array(
				'id'    => 'absint',
				'title' => 'sanitize_text_field',
			),
		),
	);

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public static function read() {
		self::set_default_global_styles();
		$settings   = get_option( '_blockart_settings', self::$data );
		self::$data = blockart_parse_args( $settings, self::$data );
		return self::$data;
	}

	/**
	 * Get all data.
	 *
	 * @return array
	 */
	public static function all() {
		return self::read();
	}

	/**
	 * Get setting.
	 *
	 * @param string $key
	 * @param mixed $default_value
	 * @return mixed
	 */
	public static function get( $key, $default_value = null ) {
		self::read();
		return blockart_array_get( self::$data, $key, $default_value );
	}

	/**
	 * Set multiple data.
	 *
	 * @param array $data
	 * @return void
	 */
	public static function set_data( $data ) {
		$data = blockart_array_dot( $data );
		array_walk(
			$data,
			function ( &$value, $key ) {
				$value = self::sanitize( $key, $value );
			}
		);
		$data       = blockart_array_undot( $data );
		self::$data = blockart_parse_args( $data, self::$data );
	}

	/**
	 * Set default global styles.
	 *
	 * @return void
	 */
	private static function set_default_global_styles() {
		$styles                      = array(
			'colors'       => array(
				array(
					'id'    => 'primary',
					'name'  => 'Primary',
					'value' => '#2563eb',
				),
				array(
					'id'    => 'secondary',
					'name'  => 'Secondary',
					'value' => '#54595F',
				),
				array(
					'id'    => 'text',
					'name'  => 'Text',
					'value' => '#7A7A7A',
				),
				array(
					'id'    => 'accent',
					'name'  => 'Accent',
					'value' => '#61CE70',
				),
			),
			'typographies' => array(
				array(
					'id'    => 'primary',
					'name'  => 'Primary',
					'value' => array(
						'fontFamily' => 'Default',
						'weight'     => 600,
					),
				),
				array(
					'id'    => 'secondary',
					'name'  => 'Secondary',
					'value' => array(
						'fontFamily' => 'Default',
						'weight'     => 400,
					),
				),
				array(
					'id'    => 'text',
					'name'  => 'Text',
					'value' => array(
						'fontFamily' => 'Default',
						'weight'     => 600,
					),
				),
				array(
					'id'    => 'accent',
					'name'  => 'Accent',
					'value' => array(
						'fontFamily' => 'Default',
						'weight'     => 500,
					),
				),
			),
		);
		self::$data['global-styles'] = wp_json_encode( $styles );
	}

	/**
	 * Set setting data.
	 *
	 * @param string $key Key to set.
	 * @param mixed $value Value to set.
	 * @return void
	 */
	public static function set( $key, $value ) {
		$value      = self::sanitize( $key, $value );
		self::$data = blockart_array_set( self::$data, $key, $value );
	}

	/**
	 * Sanitize value.
	 *
	 * @param string $key Key to sanitize.
	 * @param mixed $value Value to sanitize.
	 * @return mixed Sanitized value.
	 */
	private static function sanitize( $key, $value ) {
		$sanitize_callback = blockart_array_get(
			(array) apply_filters(
				'blockart_setting_sanitize_callbacks',
				self::$sanitize_callbacks
			),
			$key
		);
		if ( is_callable( $sanitize_callback ) || ( is_string( $sanitize_callback ) && function_exists( $sanitize_callback ) ) ) {
			return call_user_func_array( $sanitize_callback, array( $value ) );
		}
		return $value;
	}

	/**
	 * Save setting data after sanitization.
	 *
	 * @return void
	 */
	public static function save() {
		self::watch_responsive_breakpoints();
		update_option( '_blockart_settings', self::$data );
	}

	/**
	 * Watch responsive breakpoints.
	 *
	 * @return void
	 */
	private static function watch_responsive_breakpoints() {
		$new_breakpoints = blockart_array_get( self::$data, 'editor.responsive-breakpoints', array() );
		$old_breakpoints = wp_parse_args(
			blockart_array_get( get_option( '_blockart_settings', array() ), 'editor.responsive-breakpoints', array() ),
			blockart_array_get( self::$data, 'editor.responsive-breakpoints', array() )
		);

		ksort( $new_breakpoints );
		ksort( $old_breakpoints );

		if ( $new_breakpoints !== $old_breakpoints ) {
			do_action( 'blockart_responsive_breakpoints_changed', $new_breakpoints, $old_breakpoints );
		}
	}
}
