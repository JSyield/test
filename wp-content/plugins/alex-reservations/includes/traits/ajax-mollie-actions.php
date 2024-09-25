<?php

trait Alexr_ajax_mollie_actions
{
	protected function getMolliePaymentArgs($booking)
	{
		if (!class_exists('\Alexr\Payments\GatewayMollie')) return null;

		$gateway = new \Alexr\Payments\GatewayMollie($booking->restaurant_id);
		if (!$gateway->enabled) return null;

		return [
			'id' => 'mollie',
			'title' => $gateway->title,
			'description' => $gateway->description,
			'can_capture_card' => \Alexr\Payments\GatewayMollie::$hasCaptureCards
		];
	}

	public function mollie_create()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		if ( ! $this->verify_nonce($restaurant_id)  && ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		if (!class_exists('Alexr\Payments\GatewayMollie')) {
			wp_send_json_error(['error' => __eva('Need the Mollie addon enabled.')]);
		}

		$booking = \Alexr\Models\Booking::find($booking_id);
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking')]);
		}

		$amount = floatval($_REQUEST['amount']);
		$gateway = new \Alexr\Payments\GatewayMollie($restaurant_id);
		$gateway_payment = $gateway->create_gateway_payment($booking, $amount);

		if (!$gateway_payment) {
			wp_send_json_error(['error' => __eva('ERROR creating gateway payment')]);
		}

		if (!$gateway_payment['success']) {
			wp_send_json_error(['error' => $gateway_payment['error']]);
		}

		$payment = $gateway->create_payment($booking_id, $amount, $gateway_payment['data']);

		wp_send_json_success(['payment' => $payment, 'redirect' => $gateway_payment['redirect']]);
	}

	public function mollie_fetch()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$uuid = sanitize_text_field($_REQUEST['uuid']);

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		$booking = \Alexr\Models\Booking::where('uuid', $uuid)->first();
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking.')]);
		}

		$payment = \Alexr\Models\Payment::where('booking_id', $booking->id)->last();

		if (!$payment) {
			wp_send_json_error(['error' => __eva('Invalid payment.')]);
		}

		try {
			$settings = $payment->settings;
			$data = $settings['mollie'];
			$redirect = $data['_links']['checkout']['href'];;
		}
		catch(Exception $e) {
			wp_send_json_error(['error' => __eva('Invalid payment data.')]);
		}

		wp_send_json_success(['redirect' => $redirect]);
	}
}
