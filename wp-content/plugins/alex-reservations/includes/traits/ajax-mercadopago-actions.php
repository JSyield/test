<?php

trait Alexr_ajax_mercadopago_actions
{
	protected function getMercadopagoPaymentArgs($booking)
	{
		if (!class_exists('\Alexr\Payments\GatewayMercadopago')) return null;

		$gateway = new \Alexr\Payments\GatewayMercadopago($booking->restaurant_id);
		if (!$gateway->enabled) return null;

		return [
			'id' => 'mercadopago',
			'title' => $gateway->title,
			'description' => $gateway->description,
			'can_capture_card' => \Alexr\Payments\GatewayMercadopago::$hasCaptureCards
		];
	}

	public function mercadopago_create()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		if ( ! $this->verify_nonce($restaurant_id)  && ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		if (!class_exists('Alexr\Payments\GatewayMercadopago')) {
			wp_send_json_error(['error' => __eva('Need the Mercadopago addon enabled.')]);
		}

		$booking = \Alexr\Models\Booking::find($booking_id);
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking')]);
		}

		$gateway = new \Alexr\Payments\GatewayMercadopago($restaurant_id);
		$amount = floatval($_REQUEST['amount']);
		$gateway_payment = $gateway->create_gateway_payment($booking, $amount);

		if (!$gateway_payment){
			wp_send_json_error(['error' => __eva('ERROR creating gateway payment')]);
		}

		if (!$gateway_payment['success']) {
			wp_send_json_error(['error' => $gateway_payment['error']]);
		}

		$payment = $gateway->create_payment($booking_id, $amount, $gateway_payment['data']);

		wp_send_json_success(['payment' => $payment, 'redirect' => $gateway_payment['redirect']]);
	}

	public function mercadopago_fetch()
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
			$data = $settings['mercadopago'];
			$redirect = $data['init_point'];
		}
		catch(Exception $e) {
			wp_send_json_error(['error' => __eva('Invalid payment data.')]);
		}

		wp_send_json_success(['redirect' => $redirect]);
	}
}
