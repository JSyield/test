<?php

$uuid = sanitize_text_field($_GET[ALEXR_GET_VIEW_BOOKING]);

$js_url = ALEXR_PLUGIN_URL . 'assets/viewbooking-mix/';
$css_url = ALEXR_PLUGIN_URL . 'assets/viewbooking-mix/';
$widget_version = ALEXR_VERSION;

if (defined('ALEXR_PRO_PLUGIN_URL')) {
	$js_url = ALEXR_PRO_PLUGIN_URL . 'assets/viewbooking-mix/';
	$css_url = ALEXR_PRO_PLUGIN_URL . 'assets/viewbooking-mix/';
}

if (defined('ALEXR_PRO_VERSION')) {
	$widget_version = ALEXR_PRO_VERSION;
}

$assets_url = ALEXR_PLUGIN_URL.'assets/';

wp_enqueue_style( 'view-booking-css', $css_url.'index.css', array(), $widget_version );

wp_enqueue_script( 'view-booking-main', $js_url . 'main.js', array(), $widget_version, true );

wp_localize_script('view-booking-main', 'rr_config', alexr_get_config());
wp_localize_script('view-booking-main', 'rr_translations', alexr_get_translations());

$booking = \Alexr\Models\Booking::where('uuid',$uuid)
    ->where('status', '!=', \Alexr\Enums\BookingStatus::DELETED)
    ->first();

$args = [
	'nonce' => evavel_create_nonce('booking-'.$uuid),
	'statuses_allowed_to_cancel' => [
		\Alexr\Enums\BookingStatus::SELECTED,
		\Alexr\Enums\BookingStatus::PENDING_PAYMENT,
		\Alexr\Enums\BookingStatus::PENDING,
		\Alexr\Enums\BookingStatus::BOOKED
	],
	'statuses_with_red_background' => [
		\Alexr\Enums\BookingStatus::CANCELLED,
		\Alexr\Enums\BookingStatus::PENDING_PAYMENT,
		\Alexr\Enums\BookingStatus::DENIED,
		\Alexr\Enums\BookingStatus::NO_SHOW
	],
	'booking' => \Alexr\Models\Booking::toDataArray($booking),
	'booking_layout' => $booking->viewLayout,
	'restaurant' => $booking->restaurant->toArray(),
	'date_formats' => alexr_config('app.date_formats'),
	'calendar_links' => $booking->getCalendarLinks(),
];

wp_localize_script('view-booking-main', 'rr_booking', $args);

alexr_limit_hooks_to_one();

?>


<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div id="app"></div>
    <?php
        wp_footer();
    ?>
</body>
</html>
