<?php
/**
 * BlockStyles.
 *
 * @since 2.0.7.3
 * @package BlockArt
 */

namespace BlockArt;

use BlockArt\Abstracts\Styles;

defined( 'ABSPATH' ) || exit;

/**
 * Class BlockStyles.
 */
class BlockStyles extends Styles {

	/**
	 * Array of blocks data.
	 *
	 * @var array Array of blocks data.
	 */
	protected $blocks;

	/**
	 * Has old button markup.
	 *
	 * @var bool Has old button markup.
	 */
	protected $has_old_button_markup;

	/**
	 * Constructor.
	 *
	 * @param array           $blocks Block data.
	 * @param null|int|string $id Current page or template id.
	 * @param bool            $force_generate Force generate styles.
	 */
	public function __construct( array &$blocks, $id = null, bool $force_generate = false ) {
		$this->id             = $id ?? blockart_get_the_id();
		$this->force_generate = $force_generate;
		$this->blocks         = &$blocks;

		if ( empty( $this->blocks ) || empty( $this->id ) ) {
			return;
		}

		$this->has_old_button_markup = $this->has_old_button_markup();

		$this->maybe_generate();
	}

	/**
	 * Make styles.
	 *
	 * @return void
	 */
	protected function make_styles() {
		if ( empty( $this->css ) ) {
			return;
		}

		$styles            = $this->make_all_device_styles();
		$tablet_breakpoint = blockart_get_setting( 'editor.responsive-breakpoints.tablet', $this->tablet_breakpoint ) . 'px';
		$mobile_breakpoint = blockart_get_setting( 'editor.responsive-breakpoints.mobile', $this->mobile_breakpoint ) . 'px';

		$this->styles  = $styles['desktop'];
		$this->styles .= $this->make_media_query_styles( $styles['tablet'], "(max-width: $tablet_breakpoint)" );
		$this->styles .= $this->make_media_query_styles( $styles['mobile'], "(max-width: $mobile_breakpoint)" );
		$this->styles .= $this->make_media_query_styles(
			$styles['tablet_only'],
			"(min-width: $mobile_breakpoint) and (max-width: $tablet_breakpoint)"
		);
		$this->styles .= $this->make_media_query_styles( $styles['desktop_only'], "(min-width: $tablet_breakpoint)" );

		unset( $this->css );
	}

	/**
	 * Make all device styles.
	 *
	 * @return array
	 */
	protected function make_all_device_styles() {
		$styles = array(
			'desktop'      => '',
			'tablet'       => '',
			'mobile'       => '',
			'tablet_only'  => '',
			'desktop_only' => '',
		);

		foreach ( $this->css as $device => $css ) {
			$css    = blockart_array_combine_keys( $css );
			$result = '';

			array_walk(
				$css,
				function ( $declarations, $selector ) use ( &$result ) {
					$styles = '';
					foreach ( $declarations as $property => $value ) {
						$styles .= "$property:$value;";
					}
					$result .= "$selector{{$styles}}";
				}
			);

			$styles[ $device ] .= $result;
		}

		return $styles;
	}

	/**
	 * Make media query styles.
	 *
	 * @param string $styles CSS declarations.
	 * @param string $media_query Media query.
	 *
	 * @return string
	 */
	protected function make_media_query_styles( string $styles, string $media_query ) {
		if ( empty( $styles ) ) {
			return '';
		}
		return "@media $media_query{{$styles}}";
	}

	/**
	 * Generator.
	 *
	 * @return \Generator
	 */
	protected function generator() {
		foreach ( $this->blocks as $block ) {
			yield $block;
		}
	}

	/**
	 * Generate.
	 *
	 * @return void
	 */
	protected function generate() {
		foreach ( $this->generator() as $block ) {
			$namespace = $block['blockName'];
			$attrs     = $block['attrs'];

			if ( $this->has_old_button_markup ) {
				$namespace = 'blockart/button' === $namespace ? 'blockart/button-inner' : $namespace;
			}

			$name = explode( '/', $namespace )[1];

			$attrs_def = $this->get_attribute_def( $namespace );

			if ( ! $attrs_def || ! isset( $attrs['clientId'] ) ) {
				continue;
			}

			if ( ! $this->has_old_button_markup ) {
				$name = 'button-inner' === $name ? 'button' : ( 'button' === $name ? 'buttons' : $name );
			}

			$wrapper_class = '.blockart-' . $name . '-' . $attrs['clientId'];

			foreach ( $attrs_def as $setting_id => $data ) {
				$styles_def = $data['style'] ?? false;

				if ( empty( $styles_def ) ) {
					continue;
				}

				$value = $this->get_setting_value( $data, $attrs[ $setting_id ] ?? null );

				if ( empty( $value ) ) {
					continue;
				}

				$this->css = blockart_parse_args(
					$this->css,
					$this->generate_style_by_prop( $value, $styles_def, $attrs, $attrs_def, $wrapper_class )
				);
			}
		}
		unset( $this->blocks );
	}

	/**
	 * Get setting value.
	 *
	 * @param mixed $data Setting definition.
	 * @param mixed $value Setting value.
	 *
	 * @return mixed
	 */
	protected function get_setting_value( $data, $value ) {
		$default = blockart_array_get( $data, 'default' );
		return $value ?? $default;
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function generate_style_by_prop( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		if ( isset( $value['border'] ) ) {
			unset( $value['border'] );
			$callback = array( $this, 'border' );
		} elseif ( isset( $value['background'] ) ) {
			unset( $value['background'] );
			$callback = array( $this, 'background' );
		} elseif ( isset( $value['typography'] ) ) {
			unset( $value['typography'] );
			$this->generate_fonts( $value );
			$callback = array( $this, 'typography' );
		} elseif ( isset( $value['dimension'] ) ) {
			unset( $value['dimension'] );
			$callback = array( $this, 'dimension' );
		} elseif ( isset( $value['boxShadow'] ) ) {
			unset( $value['boxShadow'] );
			$callback = [ $this, 'box_shadow' ];
		} elseif ( isset( $value['topSeparator'] ) || isset( $value['bottomSeparator'] ) ) {
			unset( $value['topSeparator'] );
			unset( $value['bottomSeparator'] );
			$callback = [ $this, 'separator' ];
		} else {
			$callback = array( $this, 'general' );
		}
		return call_user_func_array( $callback, array( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) );
	}

	/**
	 * Generate fonts from blocks.
	 *
	 * @param array $value Setting value.
	 *
	 * @return void
	 */
	protected function generate_fonts( array $value ) {
		if ( ! isset( $value['family'] ) || 'default' === strtolower( $value['family'] ) ) {
			return;
		}

		$weight = (string) ( $value['weight'] ?? '400' );
		$family = $value['family'];

		if ( ! isset( $this->fonts[ $family ] ) ) {
			$this->fonts[ $family ] = array( $weight );
		} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
			if ( ! in_array( $weight, $this->fonts[ $family ], true ) ) {
				$this->fonts[ $family ][] = $weight;
			}
		}
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function border( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = array();

		if ( empty( $value ) ) {
			return $css;
		}

		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}
			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );
			$style    = blockart_array_get( $value, 'type', 'none' );

			if ( 'none' !== $style ) {
				$css['desktop'][ $selector ]['border-style'] = $style;

				if ( isset( $value['color'] ) ) {
					$css['desktop'][ $selector ]['border-color'] = $value['color'];
				}

				$this->process_border_size_styles( $value, $selector, $css );
			}
			$this->process_border_radius_styles( $value, $selector, $css );
		}

		return $css;
	}

	/**
	 * Process border size styles.
	 *
	 * @param array  $value Setting value.
	 * @param string $selector CSS selector.
	 * @param array  $css CSS.
	 *
	 * @return void
	 */
	protected function process_border_size_styles( $value, $selector, &$css ) {
		if ( ! isset( $value['size'] ) ) {
			return;
		}
		foreach ( $this->devices as $device ) {
			$device_val = blockart_array_get( $value, "size.$device", array() );
			$val        = $this->sides_style( $device_val );
			if ( false !== $val ) {
				$css[ $device ][ $selector ]['border-width'] = $val;
			}
		}
	}

	/**
	 * Process border radius styles.
	 *
	 * @param array  $value Setting value.
	 * @param string $selector CSS selector.
	 * @param array  $css CSS.
	 *
	 * @return void
	 */
	protected function process_border_radius_styles( $value, $selector, &$css ) {
		if ( ! isset( $value['radius'] ) ) {
			return;
		}
		foreach ( $this->devices as $device ) {
			$device_val = blockart_array_get( $value, "radius.$device", array() );
			$val        = $this->sides_style( $device_val );
			if ( false !== $val ) {
				$css[ $device ][ $selector ]['border-radius'] = $val;
			}
		}
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function dimension( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = array(
			'desktop' => array(),
			'tablet'  => array(),
			'mobile'  => array(),
		);

		if ( empty( $value ) ) {
			return $css;
		}

		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}
			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );

			if ( blockart_array_has_any( $value, $this->devices ) ) {
				foreach ( $this->devices as $device ) {
					$val = $this->sides_style( $value[ $device ] ?? array() );
					if ( false === $val ) {
						continue;
					}
					$css[ $device ] = blockart_parse_args(
						$css[ $device ],
						$this->parse_css_string(
							str_replace( '{{VALUE}}', $val, $selector )
						)
					);
				}
			} else {
				$val = $this->sides_style( $value );
				if ( false !== $val ) {
					$css['desktop'] = blockart_parse_args(
						$css['desktop'],
						$this->parse_css_string(
							str_replace( '{{VALUE}}', $val, $selector )
						)
					);
				}
			}
		}

		return $css;
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function background( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = array();

		if ( empty( $value ) ) {
			return $css;
		}

		$type = blockart_array_get( $value, 'type', 'color' );

		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}
			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}
			$selector = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );

			if ( 'gradient' === $type && isset( $value['gradient'] ) ) {
				$css['desktop'][ $selector ]['background'] = $value['gradient'];
				continue;
			}

			if ( isset( $value['color'] ) ) {
				$css['desktop'][ $selector ]['background-color'] = $value['color'];
			}

			$url = blockart_array_get( $value, 'image.image.url' );
			$url = $url ?? blockart_array_get( $value, 'image.image.local' );
			$url = $url ?? blockart_array_get( $value, 'image.image.external' );

			if ( empty( $url ) ) {
				continue;
			}

			$css['desktop'][ $selector ]['background-image'] = "url($url)";

			$attachment = blockart_array_get( $value, 'image.attachment', 'default' );

			if ( 'default' !== $attachment ) {
				$css['desktop'][ $selector ]['background-attachment'] = $attachment;
			}

			$this->process_responsive_background_styles( $value, $selector, $css );
		}

		return $css;
	}

	/**
	 * Process responsive background styles.
	 *
	 * @param array  $value Setting value.
	 * @param string $selector CSS selector.
	 * @param array  $css CSS.
	 *
	 * @return void
	 */
	protected function process_responsive_background_styles( $value, $selector, &$css ) {
		foreach ( $this->devices as $device ) {
			$position = blockart_array_get( $value, "image.position.$device", 'default' );
			$repeat   = blockart_array_get( $value, "image.repeat.$device", 'default' );
			$size     = blockart_array_get( $value, "image.size.$device", 'default' );
			if ( 'default' !== $position ) {
				$css[ $device ][ $selector ]['background-position'] = $position;
			}
			if ( 'default' !== $repeat ) {
				$css[ $device ][ $selector ]['background-repeat'] = $repeat;
			}
			if ( 'default' !== $size || 'custom' !== $size ) {
				$css[ $device ][ $selector ]['background-size'] = $size;
			}
			if ( 'custom' === $size ) {
				$key         = 'customSize' . ucfirst( $device );
				$custom_size = blockart_array_get( $value, "image.$key.value" );
				$custom_unit = blockart_array_get( $value, "image.$key.unit", 'px' );
				if ( $custom_size ) {
					$css[ $device ][ $selector ]['background-size'] = "$custom_size$custom_unit auto";
				}
			}
		}
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function box_shadow( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = array();

		if ( ! isset( $value['enable'] ) || ! $value['enable'] ) {
			return $css;
		}

		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}
			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector     = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );
			$position     = blockart_array_get( $value, 'position', 'outline' );
			$horizontal_x = blockart_array_get( $value, 'horizontalX', '0' );
			$vertical_y   = blockart_array_get( $value, 'verticalY', '0' );
			$blur         = blockart_array_get( $value, 'blur', '10' );
			$spread       = blockart_array_get( $value, 'spread', '0' );
			$color        = blockart_array_get( $value, 'color', 'rgba(0,0,0, 0.5)' );

			$css['desktop'][ $selector ]['box-shadow'] = sprintf(
				'%s%spx %spx %spx %spx %s',
				'inset' === $position ? 'inset ' : '',
				$horizontal_x,
				$vertical_y,
				$blur,
				$spread,
				$color
			);
		}

		return $css;
	}

	/**
	 * Generate separator css.
	 *
	 * @param array $value Separator value.
	 * @param array $styles_def Styles definition.
	 * @param array $attrs Saved attributes.
	 * @param array $attrs_def Attributes definition.
	 * @param string $wrapper_class Wrapper class.
	 * @return void
	 */
	protected function separator( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = [];

		if ( ! isset( $value['enable'] ) || ! $value['enable'] ) {
			return $css;
		}

		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}
			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector     = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );
			$fill         = blockart_array_get( $value, 'color', '#fff' );
			$height       = blockart_array_get( $value, 'height', 100 );
			$width        = blockart_array_get( $value, 'width', 1 );
			$shadow_color = blockart_array_get( $value, 'shadow_color', '#fff' );
			$horizontal_x = blockart_array_get( $value, 'horizontalX', 0 );
			$vertical_y   = blockart_array_get( $value, 'verticalY', 0 );
			$blur         = blockart_array_get( $value, 'blur', 0 );

			$css['desktop'][ $selector ]['fill']      = $fill;
			$css['desktop'][ $selector ]['height']    = $height . 'px';
			$css['desktop'][ $selector ]['transform'] = 'scaleX(' . $width . ')';
			$css['desktop'][ $selector ]['filter']    = 'drop-shadow(' . $horizontal_x . 'px ' . $vertical_y . 'px ' . $blur . 'px ' . $shadow_color . ')';
		}

		return $css;
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function typography( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = array();
		if ( empty( $value ) ) {
			return $css;
		}
		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}

			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector   = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );
			$family     = blockart_array_get( $value, 'family', 'Default' );
			$weight     = blockart_array_get( $value, 'weight' );
			$transform  = blockart_array_get( $value, 'transform', 'default' );
			$decoration = blockart_array_get( $value, 'decoration', 'default' );
			$font_style = blockart_array_get( $value, 'fontStyle', 'default' );

			if ( 'default' !== strtolower( $family ) ) {
				$css['desktop'][ $selector ]['font-family'] = $family;
			}
			if ( $weight ) {
				$css['desktop'][ $selector ]['font-weight'] = $weight;
			}
			if ( 'default' !== strtolower( $transform ) ) {
				$css['desktop'][ $selector ]['text-transform'] = $transform;
			}
			if ( 'default' !== strtolower( $decoration ) ) {
				$css['desktop'][ $selector ]['text-decoration'] = $decoration;
			}
			if ( 'default' !== strtolower( $font_style ) ) {
				$css['desktop'][ $selector ]['font-style'] = $font_style;
			}

			$this->process_responsive_typography_styles( $value, $selector, $css );
		}
		return $css;
	}

	/**
	 * Process responsive typography styles.
	 *
	 * @param array  $value Setting value.
	 * @param string $selector CSS selector.
	 * @param array  $css CSS.
	 *
	 * @return void
	 */
	protected function process_responsive_typography_styles( $value, $selector, &$css ) {
		foreach ( $this->devices as $device ) {
			$size                = blockart_array_get( $value, "size.$device.value", false );
			$size_unit           = blockart_array_get( $value, "size.$device.unit", 'px' );
			$line_height         = blockart_array_get( $value, "lineHeight.$device.value", false );
			$line_height_unit    = blockart_array_get( $value, "lineHeight.$device.unit", 'px' );
			$letter_spacing      = blockart_array_get( $value, "letterSpacing.$device.value", false );
			$letter_spacing_unit = blockart_array_get( $value, "letterSpacing.$device.unit", 'px' );
			if ( $size ) {
				$css[ $device ][ $selector ]['font-size'] = $size . $size_unit;
			}
			if ( $line_height ) {
				$css[ $device ][ $selector ]['line-height'] = $line_height . $line_height_unit;
			}
			if ( $letter_spacing ) {
				$css[ $device ][ $selector ]['letter-spacing'] = $letter_spacing . $letter_spacing_unit;
			}
		}
	}

	/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function general( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = array(
			'desktop'      => array(),
			'tablet'       => array(),
			'mobile'       => array(),
			'tablet_only'  => array(),
			'desktop_only' => array(),
		);

		if ( empty( $value ) ) {
			return $css;
		}
		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? array(), $attrs, $attrs_def ) ) {
				continue;
			}

			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );

			if ( false === strpos( $selector, '{{VALUE}}' ) ) {
				$this->process_non_value_selector( $selector, $css );
				continue;
			}

			if ( ! $value ) {
				continue;
			}

			$this->process_value_selector( $value, $selector, $css );
		}

		return $css;
	}

		/**
	 * Generate style by prop.
	 *
	 * @param mixed  $value Setting value.
	 * @param array  $styles_def Styles definition.
	 * @param array  $attrs Block attributes.
	 * @param array  $attrs_def Block attributes def.
	 * @param string $wrapper_class Wrapper id.
	 *
	 * @return array
	 */
	protected function list_gap( $value, $styles_def, $attrs, $attrs_def, $wrapper_class ) {
		$css = [];

		if ( ! isset( $value['enable'] ) || ! $value['enable'] ) {
			return $css;
		}

		foreach ( $styles_def as $style_def ) {
			if ( ! $this->check_condition( $style_def['condition'] ?? [], $attrs, $attrs_def ) ) {
				continue;
			}
			if ( ! isset( $style_def['selector'] ) ) {
				continue;
			}

			$selector = str_replace( '{{WRAPPER}}', $wrapper_class, $style_def['selector'] );

			// Retrieve the slider value for padding (divided by 2)
			$slider_value = blockart_array_get( $value, 'sliderValue', '0' );

			// Calculate the padding value based on the slider value
			$padding_value = $slider_value / 2;

			// Generate CSS rules for padding for the selector
			$css['desktop'][ $selector ]['padding-top']    = $padding_value . 'px';
			$css['desktop'][ $selector ]['padding-bottom'] = $padding_value . 'px';
		}

		return $css;
	}

	/**
	 * This function processes CSS selectors and assigns them to specific CSS arrays based on their media
	 * query properties.
	 *
	 * @param string $selector The CSS selector to be processed.
	 * @param array  $css  is an associative array that contains different CSS styles for different screen
	 * sizes. The keys of the array are 'mobile', 'tablet', 'desktop', 'tablet_only', and 'desktop_only'.
	 * The values of the array are CSS declarations in string format.
	 */
	protected function process_non_value_selector( $selector, &$css ) {
		if ( false !== strpos( $selector, '@media' ) ) {
			$this->process_media_query_styles( $selector, $css );
		} else {
			$css['desktop'] = blockart_parse_args( $css['mobile'], $this->parse_css_string( $selector ) );
		}
	}

	/**
	 * Process media query styles.
	 *
	 * @param mixed $selector CSS selector.
	 * @param mixed $css Process css.
	 *
	 * @return void
	 */
	protected function process_media_query_styles( $selector, &$css ) {
		preg_match( '/@media\s*\((.*?)\)\s*{(.*?)}/', $selector, $matches );

		$desktop_only = 'min-width:62em';
		$tablet_only  = 'min-width:48em) and (max-width:62em';
		$mobile       = 'max-width:48em';
		$tablet       = 'max-width:62em';

		if ( empty( $matches[1] ) || empty( $matches[2] ) ) {
			return;
		}

		$media_query  = $matches[1];
		$declarations = $matches[2];
		$declarations = substr( $declarations, -1 ) !== '}' ? $declarations . '}' : $declarations;
		$declarations = $this->parse_css_string( $declarations );

		if ( strpos( $media_query, $tablet_only ) !== false ) {
			$css['tablet_only'] = blockart_parse_args( $css['tablet_only'], $declarations );
			return;
		}
		if ( strpos( $media_query, $desktop_only ) !== false ) {
			$css['desktop_only'] = blockart_parse_args( $css['desktop_only'], $declarations );
			return;
		}
		if ( strpos( $media_query, $mobile ) !== false ) {
			$css['mobile'] = blockart_parse_args( $css['mobile'], $declarations );
			return;
		}
		if ( strpos( $media_query, $tablet ) !== false ) {
			$css['tablet'] = blockart_parse_args( $css['tablet'], $declarations );
		}
	}


	/**
	 * The method processes a value selector for CSS styling, taking into account different devices and
	 * units.
	 *
	 * @param mixed  $value The value to be processed. It can be a string or an array containing values for different devices.
	 * @param string $selector The CSS selector to apply the value to.
	 * @param array  $css is a reference to an array that contains CSS styles for different devices (desktop,
	 * tablet, mobile). The function updates this array with new styles based on the value and selector
	 * parameters.
	 */
	protected function process_value_selector( $value, $selector, &$css ) {
		if ( is_array( $value ) ) {
			if ( count( array_intersect( array_keys( $value ), array( 'desktop', 'tablet', 'mobile' ) ) ) > 0 ) {
				foreach ( $this->devices as $device ) {
					if ( ! isset( $value[ $device ] ) ) {
						continue;
					}
					if ( is_array( $value[ $device ] ) ) {
						if ( isset( $value[ $device ]['value'] ) ) {
							$unit           = $value[ $device ]['unit'] ?? 'px';
							$temp           = str_replace( '{{VALUE}}', "{$value[ $device ]['value']}$unit", $selector );
							$css[ $device ] = blockart_parse_args( $css[ $device ], $this->parse_css_string( $temp ) );
						}
					} else {
						$css[ $device ] = blockart_parse_args(
							$css[ $device ],
							$this->parse_css_string( str_replace( '{{VALUE}}', $value[ $device ], $selector ) )
						);
					}
				}
			} elseif ( isset( $value['value'] ) ) {
				$unit           = $value['unit'] ?? 'px';
				$css['desktop'] = blockart_parse_args(
					$css['desktop'],
					$this->parse_css_string( str_replace( '{{VALUE}}', "{$value['value']}$unit", $selector ) )
				);
			}
		} elseif ( '' !== $value ) {
			$css['desktop'] = blockart_parse_args(
				$css['desktop'],
				$this->parse_css_string( str_replace( '{{VALUE}}', $value, $selector ) )
			);
		}
	}

	/**
	 * Parses a CSS string and returns an array of selectors and their corresponding
	 * properties.
	 *
	 * @param string $css_string A string containing CSS rules and declarations.
	 *
	 * @return array An array containing parsed CSS rules, where each selector is a key and its
	 * corresponding properties are stored as an array.
	 */
	protected function parse_css_string( string $css_string ): array {
		$start = microtime( true );
		$css   = array();
		$rules = explode( '}', $css_string );
		foreach ( $rules as $rule ) {
			$parts = explode( '{', $rule, 2 );
			if ( ! isset( $parts[1] ) ) {
				continue;
			}
			$selector   = trim( $parts[0] );
			$properties = array();
			$pairs      = explode( ';', trim( $parts[1] ) );
			foreach ( $pairs as $pair ) {
				$pos = strpos( $pair, ':' );
				if ( false === $pos ) {
					continue;
				}
				$prop                = trim( substr( $pair, 0, $pos ) );
				$val                 = trim( substr( $pair, $pos + 1 ) );
				$properties[ $prop ] = $val;
			}
			$css[ $selector ] = $properties;
		}
		return $css;
	}

	/**
	 * Side styles.
	 *
	 * @param mixed $value Setting value.
	 *
	 * @return string
	 */
	protected function sides_style( $value ) {
		if ( blockart_array_has_any( $value, array( 'top', 'right', 'bottom', 'left' ) ) ) {
			$unit                = blockart_array_get( $value, 'unit', 'px' );
			$top                 = blockart_array_get( $value, 'top', 0 );
			$right               = blockart_array_get( $value, 'right', 0 );
			$bottom              = blockart_array_get( $value, 'bottom', 0 );
			$left                = blockart_array_get( $value, 'left', 0 );
			$is_equal            = $top === $right && $right === $bottom && $bottom === $left;
			$is_top_bottom_equal = $top === $bottom;
			$is_left_right_equal = $left === $right;

			if ( $is_equal ) {
				return "$top$unit";
			}

			if ( $is_top_bottom_equal && $is_left_right_equal ) {
				return "$top$unit $left$unit";
			}

			return "$top$unit $right$unit $bottom$unit $left$unit";
		}

		return false;
	}

	/**
	 * Get attribute definition.
	 *
	 * @param mixed $block_namespace Block namespace.
	 * @return array|bool
	 */
	protected function get_attribute_def( $block_namespace ) {
		$attribute_def = \WP_Block_Type_Registry::get_instance()
		->get_all_registered()[ $block_namespace ]->attributes ?? false;

		if ( $attribute_def && 'blockart/section' === $block_namespace ) {
			$attribute_def['width']['default']['desktop']['value'] = blockart_get_setting( 'editor.section-width', 1170 );
		}
		return $attribute_def;
	}

	/**
	 * Get saved styles.
	 *
	 * @return array|false
	 */
	protected function get_saved_styles() {
		return is_int( $this->id ) ?
		get_post_meta( $this->id, '_blockart_blocks_css', true ) :
		get_option( '_blockart_blocks_css', array() )[ $this->id ] ?? [];
	}

	/**
	 * Updates the styles data.
	 */
	protected function update_styles() {
		$styles_data = array(
			'filename'   => $this->filename,
			'fonts'      => $this->fonts,
			'stylesheet' => $this->styles,
		);

		if ( is_int( $this->id ) ) {
			update_post_meta( $this->id, '_blockart_blocks_css', $styles_data );
			return;
		}

		$saved              = get_option( '_blockart_blocks_css', array() );
		$saved[ $this->id ] = $styles_data;

		update_option( '_blockart_blocks_css', $saved );
	}

	/**
	 * Check if has old button markup.
	 *
	 * @return bool
	 */
	protected function has_old_button_markup() {
		foreach ( $this->blocks as $block ) {
			if ( 'blockart/button' === $block['blockName'] && empty( $block['innerBlocks'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check condition.
	 *
	 * @param array $conditions Conditions to check.
	 * @param array $attrs Block attributes.
	 * @param array $attrs_def Block attributes definition.
	 *
	 * @return boolean
	 */
	protected function check_condition( $conditions, $attrs, $attrs_def ) {
		$result = true;

		if ( empty( $conditions ) ) {
			return $result;
		}

		foreach ( $conditions as $condition ) {
			$previous = $result;
			$key      = $condition['key'];
			$value    = blockart_array_get( $attrs, $key );
			$value    = $value ?? blockart_array_get( $attrs_def, "$key.default", false );

			if ( ! $value ) {
				continue;
			}

			$relation              = blockart_array_get( $condition, 'relation', '' );
			$condition_value       = blockart_array_get( $condition, 'value', '' );
			$is_equal_relation     = '==' === $relation || '===' === $relation;
			$is_non_equal_relation = '!=' === $relation || '!==' === $relation;

			if ( $is_equal_relation ) {
				if ( is_scalar( $condition_value ) ) {
					$result = $value === $condition_value;
				} else {
					$result = in_array( $value, $condition_value, true );
				}
			} elseif ( $is_non_equal_relation ) {
				if ( is_scalar( $condition_value ) ) {
					$result = $value !== $condition_value;
				} else {
					$result = ! in_array( $value, $condition_value, true );
				}
			}

			if ( ! $previous ) {
				$result = false;
			}
		}

		return $result;
	}
}
