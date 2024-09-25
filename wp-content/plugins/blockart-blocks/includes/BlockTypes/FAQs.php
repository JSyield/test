<?php
/**
 * FAQs block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * FAQs block.
 */
class FAQs extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'faqs';

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
		if ( blockart_is_rest_request() ) {
			return $content;
		}

		if ( '1' === $attributes['enableSchema'] ) {
			$faq_data = $this->get_faq_data( $content );

			if ( $faq_data ) {
				$schema_json = $this->get_schema_json( $faq_data );
				$schema_html = '<script type="application/ld+json">' . wp_json_encode( $schema_json ) . '</script>';

				$content .= $schema_html;
			}
		}

		return $content;
	}

	/**
	 * Extracts questions and answers from the content and return them as array pairs.
	 *
	 * @param string $content Block Content.
	 * @return array
	 */
	protected function get_faq_data( $content = '' ) {
		$faq_data = array();

		if ( ! empty( $content ) ) {
			$pattern = '/<div class="blockart-faq-question">(.*?)<\/div>.*?<div class="blockart-faq-content">(.*?)<\/div>/s';
			preg_match_all( $pattern, $content, $matches );

			if ( isset( $matches[1] ) && isset( $matches[2] ) ) {
				return array_map(
					function ( $question, $answer ) {
						return array( $question, $answer );
					},
					$matches[1],
					$matches[2]
				);
			}
		}

		return $faq_data;
	}

	/**
	 * Generate and return the schema array with necessary values.
	 *
	 * @param [array] $faq_data FAQ Data.
	 * @return array
	 */
	protected function get_schema_json( $faq_data ) {
		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array(),
		);

		if ( $faq_data ) {
			foreach ( $faq_data as [$question, $answer] ) {
				array_push(
					$schema['mainEntity'],
					array(
						'@type'          => 'Question',
						'name'           => $question,
						'acceptedAnswer' => array(
							'@type' => 'Answer',
							'text'  => $answer,
						),
					)
				);
			}
		}

		return $schema;
	}
}
