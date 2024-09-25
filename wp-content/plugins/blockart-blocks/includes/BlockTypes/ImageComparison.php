<?php
/**
 * Image Comparison block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Image Comparison block.
 */
class ImageComparison extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'image-comparison';


	public function build_html( $content ) {
		if ( blockart_is_rest_request() ) {
			return $content;
		}

		$comparison_slider_class_map = array(
			'default'     => 'blockart-image-comparison-slider-default',
			'button-only' => 'blockart-image-comparison-slider-button-only',
			'hide-slider' => 'blockart-image-comparison-slider-hide',
		);

		$figure_attrs = array(
			'class' => $this->cn(
				"blockart-image-comparison-image-container b-dics orientation-{$this->get_attribute( 'orientation', 'horizontal' )}",
				array(
					'show-label' => $this->get_attribute( 'enableLabel', false ),
					'hide-label' => ! $this->get_attribute( 'enableLabel', false ),
				),
				$comparison_slider_class_map[ $this->get_attribute( 'comparisonSlider', 'default' ) ] ?? ''
			),
		);

		$before_image_attrs = array(
			'alt'   => $this->get_attribute( 'beforeImageText', '' ),
			'class' => 'blockart-image-comparison-image blockart-image-comparison-image-before',
			'src'   => $this->get_attribute( 'beforeImage.url', '' ),
		);

		$after_image_attrs = array(
			'alt'   => $this->get_attribute( 'afterImageText', '' ),
			'class' => 'blockart-image-comparison-image blockart-image-comparison-image-after',
			'src'   => $this->get_attribute( 'afterImage.url', '' ),
		);

		wp_enqueue_style( 'blockart-dics' );

		ob_start();
		?>
		<div <?php $this->build_html_attributes( true ); ?>>
		<figure <?php blockart_build_html_attrs( $figure_attrs, true ); ?>>
				<img <?php blockart_build_html_attrs( $before_image_attrs, true ); ?>>
				<img <?php blockart_build_html_attrs( $after_image_attrs, true ); ?>>
		</figure>
	</div>
		<?php
		return ob_get_clean();
	}
}
