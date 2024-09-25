<?php
/**
 * BlockArt Blocks.
 *
 * Manages all the blocks & block categories.
 * Manages the blocks that need to be prepared for CSS generation.
 *
 * @since 1.0.0
 * @package BlockArt
 */

namespace BlockArt;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use BlockArt\BlockTypes\Image;
use BlockArt\BlockTypes\ImageComparison;
use BlockArt\Traits\Singleton;
use BlockArt\BlockTypes\Button;
use BlockArt\BlockTypes\Column;
use BlockArt\BlockTypes\Buttons;
use BlockArt\BlockTypes\Slider;
use BlockArt\BlockTypes\Testimonial;
use BlockArt\BlockTypes\Heading;
use BlockArt\BlockTypes\Counter;
use BlockArt\BlockTypes\Section;
use BlockArt\BlockTypes\Spacing;
use BlockArt\BlockTypes\Team;
use BlockArt\BlockTypes\Info;
use BlockArt\BlockTypes\Paragraph;
use BlockArt\BlockTypes\FAQ;
use BlockArt\BlockTypes\FAQs;
use BlockArt\BlockTypes\SocialShare;
use BlockArt\BlockTypes\SocialInner;
use BlockArt\BlockTypes\Tabs;
use BlockArt\BlockTypes\Tab;
use BlockArt\BlockTypes\TabTitles;
use BlockArt\BlockTypes\TableOfContents;
use BlockArt\BlockTypes\Lottie;
use BlockArt\BlockTypes\Countdown;
use BlockArt\BlockTypes\Progress;
use BlockArt\BlockTypes\CallToAction;
use BlockArt\BlockTypes\Slide;
use BlockArt\BlockTypes\TestimonialSlide;
use BlockArt\BlockTypes\Map;
use BlockArt\BlockTypes\AbstractBlock;
use BlockArt\BlockTypes\Blockquote;
use BlockArt\BlockTypes\Timeline;
use BlockArt\BlockTypes\TimelineInner;
use BlockArt\BlockTypes\Notice;
use BlockArt\BlockTypes\ImageGallery;
use BlockArt\BlockTypes\PriceList;
use BlockArt\BlockTypes\PriceListChild;
use BlockArt\BlockTypes\Price;
use BlockArt\BlockTypes\IconList;
use BlockArt\BlockTypes\IconListItem;
use BlockArt\BlockTypes\Icon;
use BlockArt\BlockTypes\Modal;

/**
 * BlockArt Blocks.
 *
 * Manages all the blocks & block categories.
 * Manages the blocks that need to be prepared for CSS generation.
 *
 * @since 1.0.0
 */
class Blocks {

	use Singleton;

	/**
	 * Block styles.
	 *
	 * @var BlockStyles|null $block_styles
	 */
	private $block_styles;

	/**
	 * Blocks that need to be prepared for CSS generation.
	 *
	 * @var array $prepared_blocks
	 */
	private $prepared_blocks = array();

	/**
	 * Prepared widget blocks.
	 *
	 * @var array
	 */
	private $prepared_widget_blocks = array();

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
		$block_categories_hook   = version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ?
			'block_categories_all' :
			'block_categories';
		$preload_api_hook_handle = class_exists( 'WP_Block_Editor_Context' ) ? 'block_editor_rest_api_preload_paths' : 'block_editor_preload_paths';

		add_action( 'init', array( $this, 'register_block_types' ) );
		add_filter( $block_categories_hook, array( $this, 'block_categories' ), PHP_INT_MAX, 2 );
		add_filter( $preload_api_hook_handle, array( $this, 'preload_rest_api_path' ), 10, 2 );

		add_filter( 'pre_render_block', array( $this, 'maybe_prepare_blocks' ), 10, 3 );
		add_filter( 'wp_head', array( $this, 'maybe_prepare_blocks' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_blocks_styles' ), 100 );

		add_action( 'customize_save_after', array( $this, 'maybe_clear_widget_block_styles' ) );
		add_action( 'rest_after_save_widget', array( $this, 'maybe_clear_widget_block_styles' ) );
		add_action( 'after_switch_theme', array( $this, 'maybe_clear_block_styles_on_theme_switch' ), 10, 2 );
		add_action( 'save_post', array( $this, 'maybe_clear_block_styles' ), 10, 2 );
		add_action( 'delete_post', array( $this, 'maybe_clear_block_styles' ), 10, 2 );
		add_action( 'blockart_responsive_breakpoints_changed', array( $this, 'regenerate_block_styles' ) );
	}

	/**
	 * Preload REST api path.
	 *
	 * @param array                                   $paths Rest API paths.
	 * @param \WP_Block_Editor_Context|\WP_Post|mixed $context Current editor context.
	 *
	 * @return array
	 */
	public function preload_rest_api_path( array $paths, $context ): array {
		if (
			$context instanceof \WP_Post ||
			( isset( $context->name ) && in_array( $context->name, array( 'core/edit-site', 'core/edit-post' ), true ) )
		) {
			$paths[] = '/blockart/v1/library-data';
		}
		return $paths;
	}

	/**
	 * Prepare blocks for FSE themes.
	 */
	public function maybe_prepare_blocks() {
		if ( blockart_is_block_theme() && doing_filter( 'pre_render_block' ) ) {
			$args                    = func_get_args();
			$block                   = $args[1];
			$this->prepared_blocks[] = $block;
			return $args[0];
		}
		if ( ! blockart_is_block_theme() && doing_action( 'wp_head' ) ) {
			$this->prepared_blocks        = parse_blocks( get_the_content() );
			$this->prepared_widget_blocks = $this->get_widget_blocks();
		}
	}

	/**
	 * Enqueue blocks styles.
	 *
	 * @return void
	 */
	public function enqueue_blocks_styles() {
		$fonts = [];

		if ( ! blockart_is_block_theme() ) {
			$this->prepared_widget_blocks = blockart_process_blocks( $this->prepared_widget_blocks );
			$widget_styles                = blockart_generate_blocks_styles( $this->prepared_widget_blocks, 'widgets' );
			$fonts                        = $widget_styles->get_fonts();
			$widget_styles->enqueue();

			if ( empty( $this->prepared_blocks ) ) {
				$widget_styles->enqueue_fonts();
			}
		}

		if ( ! empty( $this->prepared_blocks ) ) {
			$this->prepared_blocks = blockart_process_blocks( $this->prepared_blocks );
			$styles                = blockart_generate_blocks_styles( $this->prepared_blocks );
			$styles->enqueue_fonts( $fonts );
			$styles->enqueue();
		}
	}

	/**
	 * Register block types.
	 *
	 * @return void
	 */
	public function register_block_types() {
		$block_types = $this->get_block_types();
		foreach ( $block_types as $block_type ) {
			new $block_type();
		}
	}

	/**
	 * Get block types.
	 *
	 * @return AbstractBlock[]
	 */
	private function get_block_types(): array {
		return apply_filters(
			'blockart_block_types',
			array(
				Button::class,
				Buttons::class,
				Slider::class,
				Heading::class,
				Counter::class,
				Paragraph::class,
				Column::class,
				Section::class,
				Spacing::class,
				Info::class,
				Image::class,
				FAQ::class,
				FAQs::class,
				SocialShare::class,
				SocialInner::class,
				Tabs::class,
				Tab::class,
				TabTitles::class,
				TableOfContents::class,
				Lottie::class,
				Team::class,
				Countdown::class,
				Blockquote::class,
				Timeline::class,
				TimelineInner::class,
				Notice::class,
				Progress::class,
				CallToAction::class,
				Slide::class,
				Testimonial::class,
				TestimonialSlide::class,
				Map::class,
				ImageGallery::class,
				IconList::class,
				IconListItem::class,
				Icon::class,
				Modal::class,
				ImageComparison::class,
				PriceList::class,
				PriceListChild::class,
				Price::class,
			)
		);
	}

	/**
	 * Add "BlockArt" category to the blocks listing in post edit screen.
	 *
	 * @param array $block_categories All registered block categories.
	 * @return array
	 * @since 1.0.0
	 */
	public function block_categories( array $block_categories ): array {
		return array_merge(
			array(
				array(
					'slug'  => 'blockart',
					'title' => esc_html__( 'BlockArt', 'blockart' ),
				),
			),
			$block_categories
		);
	}

	/**
	 * Clear cached widget styles when widget is updated.
	 *
	 * @return void
	 */
	public function maybe_clear_widget_block_styles() {
		$cached = get_option( '_blockart_blocks_css', array() );
		blockart_array_forget( $cached, 'widgets' );
		update_option( '_blockart_blocks_css', $cached );
	}

	/**
	 * Clear cached styles when theme is switched.
	 *
	 * If is block theme then clear all cached styles stored in options table.
	 * As block theme fully depends on blocks.
	 *
	 * @param string    $name string Theme name.
	 * @param \WP_Theme $theme Theme object.
	 * @return void
	 */
	public function maybe_clear_block_styles_on_theme_switch( string $name, \WP_Theme $theme ) {
		if ( $theme->is_block_theme() ) {
			delete_option( '_blockart_blocks_css' );
		}
	}

	/**
	 * Clear or update cached styles.
	 *
	 * @param int      $id Post ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function maybe_clear_block_styles( $id, \WP_Post $post ) {
		if ( doing_action( 'save_post' ) ) {
			// Don't make style for reusable blocks.
			if ( 'wp_block' === $post->post_type ) {
				return;
			}

			// Clear cached styles when template part or template is updated.
			if ( 'wp_template_part' === $post->post_type || 'wp_template' === $post->post_type ) {
				delete_option( '_blockart_blocks_css' );
				return;
			}

			delete_post_meta( $id, '_blockart_blocks_css' );
		}

		if ( doing_action( 'delete_post' ) ) {
			$filesystem = blockart_get_filesystem();
			if ( $filesystem ) {
				$css_files = $filesystem->dirlist( BLOCKART_UPLOAD_DIR );
				if ( ! empty( $css_files ) ) {
					foreach ( $css_files as $css_file ) {
						if ( false !== strpos( $css_file['name'], "ba-style-$id-" ) ) {
							$filesystem->delete( BLOCKART_UPLOAD_DIR . $css_file['name'] );
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * Get widget blocks.
	 *
	 * @return array
	 */
	private function get_widget_blocks() {
		return parse_blocks(
			array_reduce(
				get_option( 'widget_block', array() ),
				function ( $acc, $curr ) {
					if ( ! empty( $curr['content'] ) ) {
						$acc .= $curr['content'];
					}
					return $acc;
				},
				''
			)
		);
	}

	/**
	 * Regenerate block styles.
	 *
	 * @return void
	 */
	public function regenerate_block_styles() {
		delete_option( '_blockart_blocks_css' );
		delete_post_meta_by_key( '_blockart_blocks_css' );

		$filesystem = blockart_get_filesystem();

		if ( $filesystem ) {
			$filesystem->delete( BLOCKART_UPLOAD_DIR, true );
		}
	}
}
