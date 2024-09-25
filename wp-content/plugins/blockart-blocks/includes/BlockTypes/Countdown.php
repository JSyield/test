<?php
/**
 * Countdown block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Paragraph block.
 */
class Countdown extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'countdown';

	/**
	 * Build html.
	 *
	 * @param string $content
	 * @return string
	 */
	public function build_html( $content ) {
		if ( ! blockart_is_rest_request() ) {
			$date            = $this->get_attribute( 'date' );
			$timestamp       = $date['timestamp'] ?? '';
			$calculated_time = $this->calculate_time( $date );
			$content         = str_replace(
				array_keys( $calculated_time ),
				array_values( $calculated_time ),
				$content
			);
			$content         = preg_replace( '/<div/', "<div data-expiry-timestamp='{$timestamp}' ", $content, 1 );
		}
		return $content;
	}

	/**
	 * Calculate time difference with regards to current site timezone.
	 *
	 * @param int $start
	 * @param int $end
	 * @return array
	 */
	protected function calculate_time( $date ) {
		$result = array(
			'{{DAYS}}'    => '00',
			'{{HOURS}}'   => '00',
			'{{MINUTES}}' => '00',
			'{{SECONDS}}' => '00',
		);

		$diff = round( $date['timestamp'] / 1000 ) - current_datetime()->getTimestamp();

		if ( $diff < 0 ) {
			return $result;
		}

		$result['{{DAYS}}']    = sprintf( '%02d', floor( $diff / ( 24 * 60 * 60 ) ) );
		$result['{{HOURS}}']   = sprintf( '%02d', floor( $diff / ( 60 * 60 ) ) % 24 );
		$result['{{MINUTES}}'] = sprintf( '%02d', floor( $diff / 60 ) % 60 );
		$result['{{SECONDS}}'] = sprintf( '%02d', $diff % 60 );

		return $result;
	}
}
