<?php
/**
 * Abstract block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract block.
 */
abstract class AbstractBlock {

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'blockart';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = '';

	/**
	 * Attributes.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Block content.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Block instance.
	 *
	 * @var \WP_Block
	 */
	protected $block;

	/**
	 * Constructor.
	 *
	 * @param string $block_name Block name.
	 */
	public function __construct( $block_name = '' ) {
		$this->block_name = empty( $block_name ) ? $this->block_name : $block_name;
		$this->register();
	}

	/**
	 * Register.
	 *
	 * @return void
	 */
	protected function register() {
		if ( empty( $this->block_name ) ) {
			_doing_it_wrong( __CLASS__, esc_html__( 'Block name is not set.', 'blockart' ), '2.0.7.3' );
			return;
		}

		$metadata = $this->get_metadata_base_dir() . "/$this->block_name/block.json";

		if ( ! file_exists( $metadata ) ) {
			_doing_it_wrong(
				__CLASS__,
				/* Translators: 1: Block name */
				esc_html( sprintf( __( 'Metadata file for %s block does not exist.', 'blockart' ), $this->block_name ) ),
				'2.0.7.3'
			);
			return;
		}

		register_block_type_from_metadata(
			$metadata,
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Get base metadata path.
	 *
	 * @return string
	 */
	protected function get_metadata_base_dir() {
		return apply_filters( 'blockart_get_metadata_base_dir', BLOCKART_PLUGIN_DIR . '/dist', $this->block_name );
	}

	/**
	 * Get block type.
	 *
	 * @return string
	 */
	protected function get_block_type() {
		return "$this->namespace/$this->block_name";
	}

	/**
	 * Render callback.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content Block content.
	 * @param \WP_Block $block Block object.
	 *
	 * @return string
	 */
	public function render( $attributes, $content, $block ) {
		$this->attributes = $attributes;
		$this->block      = $block;
		$this->content    = $content;

		$content = apply_filters(
			"blockart_{$this->block_name}_content",
			$this->build_html( $this->content ),
			$this
		);

		return $content;
	}

	/**
	 * Get attribute.
	 *
	 * @param string $attribute_key
	 * @param mixed $default
	 * @param boolean $sanitize
	 * @return mixed
	 */
	public function get_attribute( $attribute_key, $default_value = null, $sanitize = false ) {
		$attribute = blockart_array_get( $this->attributes, $attribute_key, $default_value );
		return $sanitize ? preg_replace( '/[^a-zA-Z0-9_-]/', '', $attribute ) : $attribute;
	}

	/**
	 * Build classnames.
	 *
	 * Usage cn( 'a', 'b', [ 'c' => true, 'd' => false ], [ 'e' => [ 'f' => true ], 'g' => [ 'h' => false ] ] );
	 *
	 * @return string|null
	 */
	public function cn() {
		$to_string = function ( $val ) use ( &$to_string ) {
			$str = '';

			if ( is_string( $val ) || is_numeric( $val ) ) {
				$str .= $val;
			} elseif ( is_array( $val ) && blockart_array_is_assoc( $val ) ) {
				foreach ( $val as $y => $value ) {
					if ( $value ) {
						$str && ( $str .= ' ' );
						$str .= $y;
					}
				}
			} elseif ( is_array( $val ) ) {
				$len = count( $val );
				for ( $k = 0; $k < $len; $k++ ) {
					if ( $val[ $k ] ) {
						$y = $to_string( $val[ $k ] );
						if ( $y ) {
							$str && ( $str .= ' ' );
							$str .= $y;
						}
					}
				}
			}

			return $str;
		};

		$i   = 0;
		$str = '';
		$len = func_num_args();

		for ( $i = 0; $i < $len; $i++ ) {
			$tmp = func_get_arg( $i );
			if ( $tmp ) {
				$x = $to_string( $tmp );
				if ( $x ) {
					$str && ( $str .= ' ' );
					$str .= $x;
				}
			}
		}

		return empty( $str ) ? null : $str;
	}

	/**
	 * Build html.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function build_html( $content ) {
		return $content;
	}

	/**openIcon
	 * Get html attrs.
	 *
	 * @return array
	 */
	protected function get_default_html_attrs() {
		return [
			'id'    => $this->get_attribute( 'cssID', '', true ),
			'class' => $this->cn(
				"blockart-$this->block_name blockart-$this->block_name-{$this->get_attribute('clientId', '', true)}",
				$this->get_attribute( 'className', '' ),
			),
		];
	}

	/**
	 * Get html attributes.
	 *
	 * @return array
	 */
	protected function get_html_attrs() {
		return array();
	}

	/**
	 * Build html attributes.
	 *
	 * @param boolean $echo_attrs
	 * @return string
	 */
	protected function build_html_attributes( $echo_attrs = false ) {
		$attrs = wp_parse_args( $this->get_html_attrs(), $this->get_default_html_attrs() );
		return blockart_build_html_attrs( $attrs, $echo_attrs );
	}
}
