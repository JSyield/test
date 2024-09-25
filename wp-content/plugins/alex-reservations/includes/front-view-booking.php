<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP Admin
 *
 * @since 1.0.0
 */
class ALEXR_View_booking {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct( ) {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'template_redirect', array( $this, 'redirect_view' ) );
	}

	public function redirect_view() {

		if (isset($_GET[ALEXR_GET_VIEW_BOOKING])) {

			$uuid = sanitize_text_field($_GET[ALEXR_GET_VIEW_BOOKING]);

			$booking = \Alexr\Models\Booking::where('uuid',$uuid)
                ->where('status', '!=', \Alexr\Enums\BookingStatus::DELETED)
                ->first();

			if ($booking) {
				if (defined('ALEXR_PRO_VERSION')){
					ob_start();
					include ALEXR_PRO_PLUGIN_DIR.'includes-pro/dashboard/templates/view-booking/booking.php';
					die(ob_get_clean());
				}
				else {
					ob_start();
					include ALEXR_PLUGIN_DIR.'includes/dashboard/templates/view-booking/booking.php';
					die(ob_get_clean());
				}
			}
			else {
				wp_redirect('/');
			}
		}
	}

}

new ALEXR_View_booking();
