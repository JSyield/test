<?php
/**
 * Tabs block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Tabs block.
 */
class Tabs extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'tabs';

	/**
	 * Build html.
	 *
	 * @param string    $content Block content.
	 * @return string
	 */
	public function build_html( $content ) {
		if ( ! blockart_is_rest_request() ) {
			$classes_to_replace = array();
			$initial_open_tab   = absint( $attributes['initialOpenTab'] ?? 1 );

			if ( $initial_open_tab > count( $this->block->inner_blocks ) ) {
				$initial_open_tab = 1;
			}
			foreach ( $this->block->inner_blocks as $i => $inner_block ) {
				if ( 'blockart/tab-titles' === $inner_block->name ) {
					continue;
				}
				$client_id = $inner_block->parsed_block['attrs']['clientId'] ?? '';
				$classes_to_replace[ "blockart-tab-{$client_id}" ] = "blockart-tab-{$client_id} blockart-tab-{$i}" . ( $i === $initial_open_tab ? ' is-active' : '' );
			}
			$content = str_replace( array_keys( $classes_to_replace ), array_values( $classes_to_replace ), $content );
			$content = str_replace( "blockart-tabs-trigger-{$initial_open_tab}", "blockart-tabs-trigger-{$initial_open_tab} is-active", $content );
		}

		return $content;
	}
}
