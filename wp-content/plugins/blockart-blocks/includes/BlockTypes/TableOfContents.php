<?php
/**
 * TableOfContents block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * TableOfContents block.
 */
class TableOfContents extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'table-of-contents';

	/**
	 * Get html attrs.
	 *
	 * @return array
	 */
	protected function get_default_html_attrs() {
		return [
			'id'             => $this->get_attribute( 'cssID' ),
			'class'          => $this->cn(
				"blockart-toc blockart-toc-{$this->get_attribute( 'clientId', '', true )}",
				"has-marker-{$this->get_attribute( 'marker', 'bullet' )}",
				$this->get_attribute( 'className', '' ),
			),
			'data-collapsed' => $this->get_attribute( 'collapsible', false ) ? ( $this->get_attribute( 'initiallyCollapsed', false ) ? 'true' : 'false' ) : null,
			'data-toc'       => "_blockart_toc_{$this->get_attribute('clientId', '')}",
		];
	}

	/**
	 * Build html.
	 *
	 * @param string $html Html.
	 *
	 * @return string
	 */
	public function build_html( $content ) {
		if ( blockart_is_rest_request() ) {
			return $content;
		}

		$headings = $this->extract_headings_from_content();

		ob_start();
		?>
		<script>var _blockart_toc_<?php echo esc_js( $this->get_attribute( 'clientId', '', true ) ); ?> = <?php echo wp_json_encode( $headings ); ?>;</script>
		<div <?php $this->build_html_attributes( true ); ?>>
			<div class="blockart-toc-header">
				<div class="blockart-toc-title <?php echo esc_attr( $this->get_attribute( 'titleTypography' )['_className'] ?? '' ); ?>"><?php echo esc_html( $this->get_attribute( 'headingTitle', '' ) ); ?></div>
				<?php if ( $this->get_attribute( 'collapsible', false ) ) : ?>
						<button class="blockart-toc-toggle <?php echo esc_attr( $this->get_attribute( 'typography' )['_className'] ?? '' ); ?>" type="button">
							<?php
							if ( 'svg' !== $this->get_attribute( 'iconType', '' ) ) {
								?>
								<span class="blockart-toc-open-icon"><?php esc_html_e( 'Hide', 'blockart' ); ?></span>
								<span class="blockart-toc-close-icon"><?php esc_html_e( 'Show', 'blockart' ); ?></span>
								<?php
							} else {
								blockart_get_icon(
									$this->get_attribute( 'openIcon', '' ),
									true,
									array(
										'class' => 'blockart-toc-open-icon',
									)
								);
								?>
								<?php
								blockart_get_icon(
									$this->get_attribute( 'closeIcon', '' ),
									true,
									array(
										'class' => 'blockart-toc-close-icon',
									)
								);
							}
							?>
						</button>
				<?php endif; ?>
			</div>
			<div class="blockart-toc-body">
				<?php if ( $headings ) : ?>
					<?php $this->headings_list_html( $this->transform_single_level_headings_to_nested( $headings ) ); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'Begin adding Headings to create a table of contents.', 'blockart' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Headings list HTML.
	 *
	 * @param array $headings Nested array of headings.
	 * @return void
	 */
	protected function headings_list_html( $headings ) {
		?>
		<ul class="blockart-toc-list">
			<?php foreach ( $headings as $heading ) : ?>
				<li class="blockart-toc-list-item">
					<a class="<?php echo esc_attr( $this->get_attribute( 'listTypography' )['_className'] ?? '' ); ?>" href="<?php echo '#' . esc_attr( isset( $heading['id'] ) ? "{$heading['id']}" : blockart_string_to_kebab( $heading['content'] ) ); ?>">
						<?php echo esc_html( $heading['content'] ); ?>
						<?php if ( isset( $heading['children'] ) ) : ?>
							<?php $this->headings_list_html( $heading['children'] ); ?>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Extract headings from content.
	 *
	 * @param array $allowed_headings Allowed headings from block attribute.
	 * @return array
	 */
	protected function extract_headings_from_content( $content = '' ) {
		$content          = empty( $content ) ? get_the_content() : $content;
		$allowed_headings = $this->get_attribute( 'headings', array() );
		if (
			empty( $content ) ||
			empty( $allowed_headings )
		) {
			return false;
		}

		preg_match_all( '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $content, $matches );

		if ( empty( $matches[0] ) ) {
			return false;
		}

		return array_filter(
			array_map(
				function ( $heading ) {
						preg_match( '/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $heading, $heading_matches );
					if ( count( $heading_matches ) !== 3 || empty( $heading_matches[2] ) ) {
						return false;
					}
						return array(
							'content' => wp_strip_all_tags( $heading_matches[2] ),
							'level'   => intval( $heading_matches[1] ),
							'id'      => blockart_string_to_kebab( $heading_matches[2] ),
						);
				},
				$matches[0]
			)
		);
	}

	/**
	 * Get nested headings level.
	 *
	 * @param array $headings Single level array of headings.
	 * @param integer $position
	 * @return array
	 */
	protected function transform_single_level_headings_to_nested( $headings, $position = 0 ) {
		$result = array();
		$length = count( $headings );
		for ( $i = 0; $i < $length; $i++ ) {
			$heading = $headings[ $i ];
			if ( $heading['level'] === $headings[0]['level'] ) {
				$end   = $i + 1;
				$count = count( $headings );
				while (
					$end < $count &&
					$headings[ $end ]['level'] > $heading['level']
				) {
					++$end;
				}
				$heading['position'] = $position + $i;
				$heading['children'] = $end > ( $i + 1 ) ?
				$this->transform_single_level_headings_to_nested(
					array_slice( $headings, $i + 1, $end - ( $i - 1 ) ),
					$position + $i
				) : null;
				$result[]            = $heading;
				$i                   = $end - 1;
			}
		}
		return $result;
	}
}
