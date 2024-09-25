<?php
/**
 * Setting controller.
 *
 * @package BlockArt
 */

namespace BlockArt\RestApi\Controllers;

use BlockArt\Setting;

defined( 'ABSPATH' ) || exit;

/**
 * Setting controller.
 */
class SettingsController extends \WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string The namespace of this controller's route.
	 */
	protected $namespace = 'blockart/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string The base of this controller's route.
	 */
	protected $rest_base = 'settings';

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
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema(),
				),
			)
		);
	}

	/**
	 * Create a single item.
	 *
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		try {
			Setting::read();
			Setting::set_data( $request->get_params() );
			Setting::save();
			return rest_ensure_response( Setting::all() );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'rest_setting_create_error', $e->getMessage(), array( 'status' => 500 ) );
		}
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return true|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You are not allowed to access this resource.', 'blockart' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You are not allowed to access this resource.', 'blockart' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

	/**
	 * Get items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ): \WP_REST_Response {
		return new \WP_REST_Response( Setting::all(), 200 );
	}

	/**
	 * Get item schema
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'setting',
			'type'       => 'object',
			'properties' => array(
				'rated'            => array(
					'type' => 'boolean',
				),
				'blocks'           => array(
					'type'        => 'object',
					'description' => __( 'Blocks', 'blockart' ),
					'properties'  => array(
						'section'           => array(
							'description' => __( 'Section block', 'blockart' ),
							'type'        => 'boolean',
						),
						'heading'           => array(
							'description' => __( 'Heading block', 'blockart' ),
							'type'        => 'boolean',
						),
						'paragraph'         => array(
							'description' => __( 'Paragraph block', 'blockart' ),
							'type'        => 'boolean',
						),
						'button'            => array(
							'description' => __( 'Button block', 'blockart' ),
							'type'        => 'boolean',
						),
						'image'             => array(
							'description' => __( 'Image block', 'blockart' ),
							'type'        => 'boolean',
						),
						'countdown'         => array(
							'description' => __( 'Countdown block', 'blockart' ),
							'type'        => 'boolean',
						),
						'counter'           => array(
							'description' => __( 'Counter block', 'blockart' ),
							'type'        => 'boolean',
						),
						'spacing'           => array(
							'description' => __( 'Spacing block', 'blockart' ),
							'type'        => 'boolean',
						),
						'info-box'          => array(
							'description' => __( 'Info box block', 'blockart' ),
							'type'        => 'boolean',
						),
						'lottie'            => array(
							'description' => __( 'Lottie animation block', 'blockart' ),
							'type'        => 'boolean',
						),
						'team'              => array(
							'description' => __( 'Team block', 'blockart' ),
							'type'        => 'boolean',
						),
						'table-of-contents' => array(
							'description' => __( 'Table of contents block', 'blockart' ),
							'type'        => 'boolean',
						),
						'tabs'              => array(
							'description' => __( 'Tabs block', 'blockart' ),
							'type'        => 'boolean',
						),
						'social-share'      => array(
							'description' => __( 'Social share block', 'blockart' ),
							'type'        => 'boolean',
						),
						'info'              => array(
							'description' => __( 'Info block', 'blockart' ),
							'type'        => 'boolean',
						),
						'blockquote'        => array(
							'description' => __( 'Blockquote block', 'blockart' ),
							'type'        => 'boolean',
						),
						'timeline'          => array(
							'description' => __( 'Timeline block', 'blockart' ),
							'type'        => 'boolean',
						),
						'notice'            => array(
							'description' => __( 'Notice block', 'blockart' ),
							'type'        => 'boolean',
						),
						'progress'          => array(
							'description' => __( 'Progress block', 'blockart' ),
							'type'        => 'boolean',
						),
						'call-to-action'    => array(
							'description' => __( 'Call to action block', 'blockart' ),
							'type'        => 'boolean',
						),
						'slider'            => array(
							'description' => __( 'Slider block', 'blockart' ),
							'type'        => 'boolean',
						),
						'map'               => array(
							'description' => __( 'Google maps block', 'blockart' ),
							'type'        => 'boolean',
						),
						'testimonial'       => array(
							'description' => __( 'Testimonial block', 'blockart' ),
							'type'        => 'boolean',
						),
						'faq'               => array(
							'description' => __( 'FAQ block', 'blockart' ),
              'type'        => 'boolean',
						'icon'              => array(
							'description' => __( 'Icon block', 'blockart' ),
							'type'        => 'boolean',
						),
						'icon-list'         => array(
							'description' => __( 'Icon list block', 'blockart' ),
							'type'        => 'boolean',
						),
						'modal'             => array(
							'description' => __( 'Modal block', 'blockart' ),
							'type'        => 'boolean',
						),
						'imageComparison'             => array(
							'description' => __( 'Image Comparison block', 'blockart' ),
							'type'        => 'boolean',
						),
					),
				),
				'editor'           => array(
					'type'        => 'object',
					'description' => __( 'Editor Options', 'blockart' ),
					'properties'  => array(
						'section-width'          => array(
							'type'        => 'integer',
							'description' => __( 'Default section max width', 'blockart' ),
						),
						'editor-blocks-spacing'  => array(
							'type'        => 'integer',
							'description' => __( 'Spacing between blocks in the block editor', 'blockart' ),
						),
						'design-library'         => array(
							'type'        => 'boolean',
							'description' => __( 'Collection of pre-made blocks', 'blockart' ),
						),
						'responsive-breakpoints' => array(
							'type'        => 'object',
							'description' => __( 'Responsive breakpoints', 'blockart' ),
							'properties'  => array(
								'tablet' => array(
									'type'        => 'integer',
									'description' => __( 'Tablet breakpoint', 'blockart' ),
								),
								'mobile' => array(
									'type'        => 'integer',
									'description' => __( 'Mobile breakpoint', 'blockart' ),
								),
							),
						),
						'copy-paste-styles'      => array(
							'type'        => 'boolean',
							'description' => __( 'Copy paste style for blocks', 'blockart' ),
						),
						'auto-collapse-panels'   => array(
							'type'        => 'boolean',
							'description' => __( 'Panels behavior similar to accordion. Open one at a time', 'blockart' ),
						),
					),
				),
				'performance'      => array(
					'type'        => 'object',
					'description' => __( 'Performance', 'blockart' ),
					'properties'  => array(
						'local-google-fonts'        => array(
							'type'        => 'boolean',
							'description' => __( 'Load google fonts locally', 'blockart' ),
						),
						'preload-local-fonts'       => array(
							'type'        => 'boolean',
							'description' => __( 'Preload local fonts', 'blockart' ),
						),
						'allow-only-selected-fonts' => array(
							'type'        => 'boolean',
							'description' => __( 'Allow only selected fonts', 'blockart' ),
						),

						'allowed-fonts'             => array(
							'type'        => 'array',
							'description' => __( 'Allowed fonts', 'blockart' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'           => array(
										'type' => 'string',
									),
									'category'     => array(
										'type' => 'string',
									),
									'defSubset'    => array(
										'type' => 'string',
									),
									'family'       => array(
										'type' => 'string',
									),
									'label'        => array(
										'type' => 'string',
									),
									'value'        => array(
										'type' => 'string',
									),
									'lastModified' => array(
										'type' => 'string',
									),
									'popularity'   => array(
										'type' => 'number',
									),
									'version'      => array(
										'type' => 'string',
									),
									'subsets'      => array(
										'type'  => 'array',
										'items' => array(
											'type' => 'string',
										),
									),
									'variants'     => array(
										'type'  => 'array',
										'items' => array(
											'type' => 'string',
										),
									),
								),
							),
						),
					),
				),
				'asset-generation' => array(
					'type'        => 'object',
					'description' => __( 'Asset generation', 'blockart' ),
					'properties'  => array(
						'external-file' => array(
							'type'        => 'boolean',
							'description' => __( 'File generation', 'blockart' ),
						),
					),
				),
				'version-control'  => array(
					'type'        => 'object',
					'description' => __( 'Version control', 'blockart' ),
					'properties'  => array(
						'beta-tester' => array(
							'type'        => 'boolean',
							'description' => __( 'Beta tester', 'blockart' ),
						),
					),
				),
				'integrations'     => array(
					'type'        => 'object',
					'description' => __( 'Third party integrations', 'blockart' ),
					'properties'  => array(
						'google-maps-embed-api-key' => array(
							'type'        => 'string',
							'description' => __( 'Google maps embed api key', 'blockart' ),
						),
					),
				),
				'maintenance-mode' => array(
					'type'        => 'object',
					'description' => __( 'Maintenance mode', 'blockart' ),
					'properties'  => array(
						'maintenance-mode' => array(
							'type'        => 'boolean',
							'description' => __( 'Enable or disable maintenance mode', 'blockart' ),
						),
						'maintenance-page' => array(
							'oneOf' => array(
								array(
									'type'        => 'object',
									'description' => __( 'Maintenance mode page data.', 'blockart' ),
									'properties'  => array(
										'id'    => array(
											'type' => 'number',
										),
										'title' => array(
											'type' => 'string',
										),
									),
								),
								array(
									'type' => 'null',
								),
							),
						),
					),
				),
			),
		),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
