<?php
/**
 * Map block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Map block.
 */
class Map extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'map';

	/**
	 * Get map options.
	 *
	 * @return array
	 */
	protected function get_map_options() {
		$marker          = $this->get_attribute( 'marker', array() );
		$has_marker      = $marker['enable'] ?? false;
		$has_marker_icon = $has_marker && ( $marker['icon'] ?? false );
		$coordinates     = $this->get_attribute(
			'coordinates',
			array(
				'lat' => 27.700769,
				'lng' => 85.30014,
			)
		);
		$map_options     = array(
			'map'        => array(
				'center'            => $coordinates,
				'zoom'              => $this->get_attribute( 'zoom', 10 ),
				'fullscreenControl' => $this->get_attribute( 'fullscreenControl', true ),
				'mapTypeControl'    => $this->get_attribute( 'mapTypeControl', true ),
				'streetViewControl' => $this->get_attribute( 'streetViewControl', true ),
				'zoomControl'       => $this->get_attribute( 'zoomControl', true ),
				'draggable'         => $this->get_attribute( 'draggable', true ),
			),
			'marker'     => $has_marker ? array(
				'position'  => $coordinates,
				'title'     => implode(
					', ',
					array_values( $coordinates )
				),
				'clickable' => false,
			) : null,
			'markerIcon' => $has_marker_icon ? blockart_get_icon(
				$marker['icon'],
				false,
				array(
					'size' => $this->get_attribute( 'markerIconSize', 32 ),
					'fill' => $this->get_attribute( 'markerIconColor' ),
				)
			) : null,
		);

		return $map_options;
	}

	/**
	 * Build html.
	 *
	 * @return string
	 */
	public function build_html( $content ) {
		if ( blockart_is_rest_request() ) {
			return $content;
		}

		$address     = $this->get_attribute( 'address', 'Kathmandu' );
		$map_height  = $this->get_attribute( 'mapHeight' );
		$language    = $this->get_attribute( 'language', 'en' );
		$zoom        = $this->get_attribute( 'zoom', 10 );
		$api_key     = blockart_get_setting( 'integrations.google-maps-embed-api-key', '' );
		$has_api_key = (bool) $api_key;

		if ( $has_api_key ) {
			wp_enqueue_script( 'blockart-google-maps' );
		}

		$google_maps_url = add_query_arg(
			array(
				'q'      => rawurlencode( $address ),
				'hl'     => $language,
				'z'      => $zoom,
				't'      => 'm',
				'output' => 'embed',
				'iwloc'  => 'near',
			),
			'https://maps.google.com/maps'
		);
		ob_start();
		?>
		<?php if ( $has_api_key ) : ?>
			<script>
			var _blockart_map_<?php echo esc_js( $this->get_attribute( 'clientId', '', true ) ); ?> =
						<?php echo wp_json_encode( $this->get_map_options() ); ?>;
			</script>
					<?php endif; ?>
			<div <?php $this->build_html_attributes( true ); ?>>
					<?php if ( ! $has_api_key ) : ?>
				<div class="blockart-map-iframe">
					<iframe src="<?php echo esc_url( $google_maps_url ); ?>" width="100%"
						height="<?php echo esc_attr( $map_height ); ?>" style="border:none;"></iframe>
				</div>
				<?php endif; ?>
			</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get html attrs.
	 *
	 * @return array
	 */
	protected function get_html_attrs() {
		$api_key    = blockart_get_setting( 'integrations.google-maps-embed-api-key', '' );
		$map_height = $this->get_attribute( 'mapHeight' );

		return [
			'data-map' => $api_key ? "_blockart_map_{$this->get_attribute( 'clientId', '' )}" : null,
			'style'    => $api_key ? "height: {$map_height}px" : null,
		];
	}
}
