<?php

trait Alexr_ajax_redsys_actions {

	protected function getRedsysPaymentArgs($booking, $amount)
	{
		if (!class_exists('\Alexr\Payments\GatewayRedsys')) return null;

		$gateway = new \Alexr\Payments\GatewayRedsys($booking->restaurant_id);
		if (!$gateway->enabled) return null;

		return [
			'id' => 'redsys',
			'title' => $gateway->title,
			'description' => $gateway->description,
			'can_capture_card' => \Alexr\Payments\GatewayRedsys::$hasCaptureCards
		];
	}

	public function redsys_create()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		$is_capture_card = sanitize_text_field($_REQUEST['is_capture_card']);
		$is_capture_card = $is_capture_card == 'yes';

		if ( ! $this->verify_nonce($restaurant_id)  && ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		if (!class_exists('Alexr\Payments\GatewayRedsys')) {
			wp_send_json_error(['error' => __eva('Need the Redsys addon enabled.')]);
		}

		$booking = \Alexr\Models\Booking::find($booking_id);

		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking')]);
		}

		$gateway = new \Alexr\Payments\GatewayRedsys($restaurant_id);
		$amount = floatval($_REQUEST['amount']);

		// No se detecta error hasta que se envia a la pagina de pago
		// Capture Card ???
		if ($is_capture_card && \Alexr\Payments\GatewayRedsys::$hasCaptureCards){
			$params = $gateway->get_redsys_args($booking, $amount, null, $is_capture_card);
		} else {
			$params = $gateway->get_redsys_args($booking, $amount);
		}


		$raw_vars = $gateway->get_raw_vars();
		$action = $gateway->get_url();

		$data = ['vars' => $raw_vars, 'params' => $params, 'action' => $action];
		$payment = $gateway->create_payment($booking_id, $data);

		wp_send_json_success(['payment' => $payment, 'params' => $params, 'action' => $action]);
	}

	// Get the data used for the request
	public function redsys_fetch()
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
			$data = $settings['redsys'];
			$params = $data['params'];
			$action = $data['action'];
		} catch(Exception $e) {
			wp_send_json_error(['error' => __eva('Invalid payment data.')]);
		}

		wp_send_json_success(['params' => $params, 'action' => $action]);
	}
}
