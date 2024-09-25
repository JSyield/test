<?php

trait Alexr_ajax_square_actions
{
	protected function getSquarePaymentArgs($booking)
	{
		if (!class_exists('\Alexr\Payments\GatewaySquare')) return null;

		$gateway = new \Alexr\Payments\GatewaySquare($booking->restaurant_id);
		if (!$gateway->enabled) return null;

		return [
			'id' => 'square',
			'title' => $gateway->title,
			'description' => $gateway->description,
			'can_capture_card' => \Alexr\Payments\GatewaySquare::$hasCaptureCards
		];
	}

	public function square_create()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		if ( ! $this->verify_nonce($restaurant_id)  && ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		if (!class_exists('Alexr\Payments\GatewaySquare')) {
			wp_send_json_error(['error' => __eva('Need the Square addon enabled.')]);
		}

		$booking = \Alexr\Models\Booking::find($booking_id);
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking')]);
		}

		$amount = floatval($_REQUEST['amount']);
		$gateway = new \Alexr\Payments\GatewaySquare($restaurant_id);

		$gateway_payment = $gateway->create_gateway_payment($booking, $amount);

		if (!$gateway_payment['success']) {
			ob_start();
			echo '<pre style="font-size:12px">'; print_r($gateway_payment['data']); echo '</pre>';
			$error = ob_get_clean();
			wp_send_json_error([ 'error' => $error]);
		}

		// Viene ya como array
		$data = $gateway_payment['data'];

		$payment = $gateway->create_payment($booking_id, $amount, $data);

		$redirect = $data['payment_link']['long_url'];

		wp_send_json_success(['payment' => $payment, 'redirect' => $redirect]);
	}

	public function square_fetch()
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
			$data = $settings['square'];
			$redirect = $data['payment_link']['long_url'];
		}
		catch(Exception $e) {
			wp_send_json_error(['error' => __eva('Invalid payment data.')]);
		}

		wp_send_json_success(['redirect' => $redirect]);
	}
}
