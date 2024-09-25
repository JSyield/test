<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* For admins to update booking status */
class ALEXR_Front_Edit_booking
{
	public function __construct( ) {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'template_redirect', array( $this, 'redirect_view' ) );
	}

	public function redirect_view()
	{
		if (isset($_GET[ALEXR_EDIT_VIEW_BOOKING])) {

			$uuid = sanitize_text_field($_GET[ALEXR_EDIT_VIEW_BOOKING]);

			$booking = \Alexr\Models\Booking::where('uuid',$uuid)
			                                ->where('status', '!=', \Alexr\Enums\BookingStatus::DELETED)
			                                ->first();

			if ($booking) {
				if (defined('ALEXR_PRO_VERSION')) {
					ob_start();
					include ALEXR_PRO_PLUGIN_DIR.'includes-pro/dashboard/templates/edit-booking/booking.php';
					die(ob_get_clean());
				}
			}
			else {
				wp_redirect('/');
			}
		}
	}
}

new ALEXR_Front_Edit_booking();
