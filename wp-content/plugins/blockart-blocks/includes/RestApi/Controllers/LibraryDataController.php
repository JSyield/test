<?php
/**
 * BlockArt Library Data Controller.
 *
 * @package BlockArt
 */

namespace BlockArt\RestApi\Controllers;

defined( 'ABSPATH' ) || exit;

/**
 * LibraryData controller.
 */
class LibraryDataController extends \WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'blockart/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base = 'library-data';

	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'refresh' => array(
							'default'           => false,
							'sanitize_callback' => 'rest_sanitize_boolean',
							'required'          => false,
						),
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return true|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You are not allowed to access this resource.', 'blockart' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

	/**
	 * Get library data.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		$refresh = $request->get_param( 'refresh' ) ?? false;
		$data    = $this->fetch( $refresh );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$response = $this->prepare_items_for_response( $data, $request );

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Prepare items for response.
	 *
	 * @param array $data
	 * @param \WP_Request $request
	 * @return array
	 */
	protected function prepare_items_for_response( $data, $request ) {
		$categorizer = function( $data_to_categorize ) {
			$result = array();
			foreach ( $data_to_categorize as $item ) {
				if ( ! empty( $item['children'] ) ) {
					array_walk(
						$item['children'],
						function ( &$v ) use ( $item ) {
							$v['slug'] = $item['post_name'] . ':' . $v['post_name'];
						}
					);
				}
				$item['slug'] = $item['post_name'];
				foreach ( $item['category'] ?? [] as $cat ) {
					if ( isset( $result[ $cat['slug'] ] ) ) {
						++$result[ $cat['slug'] ]['count'];
						$result[ $cat['slug'] ]['items'][] = $item;
						continue;
					}
					$result[ $cat['slug'] ] = array(
						'name'  => $cat['name'],
						'slug'  => $cat['slug'],
						'count' => 1,
						'items' => array( $item ),
					);
				}
			}
			return $result;
		};

		return array(
			'categorized_sections'  => $categorizer( $data['sections'] ),
			'categorized_templates' => $categorizer( $data['templates'] ),
		);
	}

	/**
	 * Fetch library data.
	 *
	 * @param boolean $force
	 * @return array
	 */
	protected function fetch( $force = false ) {
		if ( $force ) {
			delete_transient( '_blockart_library_data' );
		}

		$data = get_transient( '_blockart_library_data' );

		if ( empty( $data ) ) {
			$response = wp_remote_get(
				'https://wpblockart.com/wp-json/blockart-library/v1/all',
				array(
					'timeout' => 120,
				)
			);

			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				return new \WP_Error(
					'rest_forbidden',
					esc_html__( 'You are not allowed to access this resource.', 'blockart' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}

			$data = wp_remote_retrieve_body( $response );
			$data = json_decode( $data, true );

			set_transient( '_blockart_library_data', $data, WEEK_IN_SECONDS );
		}

		return $data;
	}
}
