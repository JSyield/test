<?php
/**
 * ImageGallery block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * ImageGallery block.
 */
class ImageGallery extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'image-gallery';

	/**
	 * Build html.
	 *
	 * @param string $html Html.
	 *
	 * @return string
	 */
	public function build_html( $content ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $content;
		}
		$layout_type = $this->get_attribute( 'layoutType', 'grid' );
		$images      = $this->get_attribute( 'images', [] );
		if ( empty( $images ) ) {
			return '';
		}
		$columns         = $this->get_attribute( 'columns', 2 );
		$on_click_image  = $this->get_attribute( 'onClickImage', '' );
		$wrapper_classes = $this->cn(
			$layout_type ? "blockart-$layout_type" : '',
			$columns && $layout_type ? "blockart-$layout_type-$columns" : '',
			$on_click_image ? "blockart-$on_click_image" : '',
		);

		$swiper_data = array(
			'slidesPerView' => $this->get_attribute( 'perPage', 1 ),
			'loop'          => $this->get_attribute( 'loop', false ),
			'navigation'    => $this->get_attribute( 'arrows', false ),
			'pagination'    => $this->get_attribute( 'pagination', false ),
			'autoplay'      => $this->get_attribute( 'autoplay', false ),
			'speed'         => $this->get_attribute( 'speed', 800 ),
		);

		$swiper_data_attr = [
			'data-swiper' => wp_json_encode(
				[
					'slidesPerView' => $this->get_attribute( 'perPage', 1 ),
					'loop'          => $this->get_attribute( 'loop', false ),
					'navigation'    => $this->get_attribute( 'arrows', false ),
					'pagination'    => $this->get_attribute( 'pagination', false ),
					'autoplay'      => $this->get_attribute( 'autoplay', true ),
					'speed'         => $this->get_attribute( 'speed', 800 ),
					'imgHeight'     => $this->get_attribute( 'imgHeight' ),
					'imgWidth'      => $this->get_attribute( 'imgWidth' ),
					'imgGap'        => $this->get_attribute( 'imgGap' ),
					'interval'      => $this->get_attribute( 'interval', false ),
				]
			),
			'class'       => $this->cn(
				$layout_type ? "blockart-$layout_type" : '',
				$columns && $layout_type ? "blockart-$layout_type-$columns" : '',
				$on_click_image ? "blockart-$on_click_image" : '',
			),
		];
		ob_start();
		?>
		<div <?php $this->build_html_attributes( true ); ?>>
			<div <?php blockart_build_html_attrs( $swiper_data_attr, true ); ?>>
			<?php
			switch ( $layout_type ) {
				case 'carousel':
					?>
					<div class="swiper carousel-swiper">
						<div class="swiper-wrapper">
						<?php $this->render_image_gallery( true ); ?>
						</div>
						<div class="swiper-button-next"></div>
						<div class="swiper-button-prev"></div>
						<div class="swiper-pagination"></div>
					</div>
					<?php
					break;
				case 'thumbnail-carousel':
					?>
						<div class="swiper main-swiper">
							<div class="swiper-wrapper">
							<?php $this->render_image_gallery( true ); ?>
							</div>
						</div>
						<div class="swiper thumbnail-swiper">
							<div class="swiper-wrapper">
							<?php $this->render_image_gallery( true ); ?>
							</div>
							<div class="swiper-button-next"></div>
							<div class="swiper-button-prev"></div>
							<div class="swiper-pagination"></div>
						</div>
						<?php
					break;
				default:
					$this->render_image_gallery();
			}
			?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function render_image_gallery( $is_carousel = false ) {
		$images             = $this->get_attribute( 'images', [] );
		$enable_caption     = $this->get_attribute( 'enableCaption', false );
		$caption_layout     = $this->get_attribute( 'captionLayout', 'overlay' );
		$caption_visibility = $this->get_attribute( 'captionVisibility', 'show-on-hover' );
		$caption_position   = $this->get_attribute( 'captionPosition', 'center-center' );
		$caption_texts      = $this->get_attribute( 'captionText', [] );
		?>
		<?php
		foreach ( $images as $i => $image ) :
			$caption_wrapper_class = $this->cn(
				'blockart-caption-wrapper',
				[
					"blockart-$caption_layout"     => ! ! $caption_layout,
					"blockart-$caption_visibility" => ! ! $caption_visibility,
					"blockart-$caption_position"   => ! ! $caption_position,
				]
			);
			?>
			<?php $is_carousel && print( '<div class="swiper-slide">' ); ?>
				<div class="blockart-image-wrapper">
					<figure class="blockart-image">
						<img src="<?php echo esc_url( $image['url'] ?? '' ); ?>" alt="<?php echo esc_url( $image['alt'] ?? '' ); ?>">
						<?php if ( $enable_caption ) : ?>
							<div class="<?php echo esc_attr( $caption_wrapper_class ); ?>">
								<figcaption><?php echo esc_html( $caption_texts[ $i ] ?? __( 'No caption', 'blockart' ) ); ?></figcaption>
							</div>
						<?php endif; ?>
					</figure>
				</div>
			<?php $is_carousel && print( '</div>' ); ?>
			<?php
		endforeach;
	}
}
