<?php
/**
 * Lottie block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Lottie block.
 */
class Lottie extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'lottie';

	/**
	 * Render callback.
	 *
	 * @return string
	 */
	public function build_html( $content ) {
		$play_on = $this->get_attribute( 'playOn', 'auto' );
		if ( 'auto' !== $play_on ) {
			$content = str_replace( '<lottie-player', "<lottie-player {$play_on}", $this->content );
		}
		return $content;
	}
}
