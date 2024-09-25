<?php

trait Alexr_ajax_paypal_actions
{
	protected function getPaypalPaymentArgs($booking)
	{
		if (!class_exists('\Alexr\Payments\GatewayPaypal')) return null;

		$gateway = new \Alexr\Payments\GatewayPaypal($booking->restaurant_id);
		if (!$gateway->enabled) return null;

		$exchange = floatval(str_replace(',', '.', $gateway->currency_exchange));
		if (!$exchange || $exchange == 0) {
			$exchange = 1.0;
		}

		return [
			'id' => 'paypal',
			'title' => $gateway->title,
			'description' => $gateway->description,
			'currency' => $gateway->currency,
			'exchange' => $exchange,
			'can_capture_card' => \Alexr\Payments\GatewayPaypal::$hasCaptureCards
		];
	}

	public function paypal_create()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		if ( ! $this->verify_nonce($restaurant_id)  && ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		$gateway = new \Alexr\Payments\GatewayPaypal($restaurant_id);
		$amount = floatval($_REQUEST['amount']);
		$amount = intval( 100.0 * $amount);
		$booking = \Alexr\Models\Booking::find($booking_id);

		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking')]);
		}

		$orderData = sanitize_text_field($_REQUEST['orderData']);
		$orderData = json_decode(str_replace('\"', '"', $orderData), true);
		//ray($orderData);

		// Validate
		$orderDataValidate = null;
		if (isset($orderData['id'])) {
			$gateway = new \Alexr\Payments\GatewayPaypal($restaurant_id);
			$orderDataValidate = $gateway->validate($orderData['id']);
			//ray($orderDataValidate);
		}

		// If completed create payment and update booking
		$status = \Alexr\Enums\PaymentStatus::PENDING;
		if (isset($orderData['status'])
		    && $orderData['status'] == 'COMPLETED'
		    && isset($orderDataValidate['status'])
		    && $orderDataValidate['status'] == 'COMPLETED')
		{
			$status = \Alexr\Enums\PaymentStatus::SUCCEEDED;

			// Save the booking status
			$booking->status = $booking->paymentStatus;
			$booking->save();
			$booking->sendBookingEmail();
			$booking->sendSmsNotification();
		}

		$data = [
			'order' => $orderData,
			'validate' => $orderDataValidate
		];

		$payment = $gateway->create_payment($booking_id, $amount, $data, $status);

		wp_send_json_success(['payment' => $payment, 'redirect' => alexr_view_booking_url($booking) ]);
	}

	public function paypal_fetch()
	{
		$uuid = sanitize_text_field($_REQUEST['uuid']);
		$booking = \Alexr\Models\Booking::where('uuid', $uuid)->first();
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Invalid booking.')]);
		}

		$data = $this->getPaypalPaymentArgs($booking);
		$data['amount'] = intval($booking->amount) / 100.0;

		wp_send_json_success(['payment' => $data]);
	}

	// NO lo estoy usando todavia. NO esta probado.
	public function paypal_save_card()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);
		$amount = floatval($_REQUEST['amount']);
		$amount = intval( 100.0 * $amount);

		if (!$this->verify_nonce($restaurant_id) && !$this->verify_nonce('paypal-' . $restaurant_id)) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		$gateway = new \Alexr\Payments\GatewayPaypal($restaurant_id);

		$paypalData = json_decode(stripslashes($_REQUEST['paypalData']), true);

		if (!$paypalData) {
			wp_send_json_error(['error' => __eva('Invalid PayPal data.')]);
		}

		$result = $gateway->save_card($booking_id, $paypalData, $amount);

		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error(['error' => $result['message']]);
		}
	}

	// No lo uso todavia. Ni lo he probado.
	public function paypal_pre_auth()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		if (!$this->verify_nonce($restaurant_id) && !$this->verify_nonce('paypal-' . $restaurant_id)) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		$gateway = new \Alexr\Payments\GatewayPaypal($restaurant_id);
		$amount = floatval($_REQUEST['amount']);
		$amount = intval(100.0 * $amount);
		$paypalData = json_decode(stripslashes($_REQUEST['paypalData']), true);

		if (!$paypalData) {
			wp_send_json_error(['error' => __eva('Invalid PayPal data.')]);
		}

		$result = $gateway->create_pre_authorization($booking_id, $amount, $paypalData);

		if ($result['success']) {
			wp_send_json_success($result);
		} else {
			wp_send_json_error(['error' => $result['message']]);
		}
	}
}
