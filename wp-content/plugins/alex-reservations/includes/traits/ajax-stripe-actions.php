<?php

use Alexr\Enums\PaymentStatus;

trait Alexr_ajax_stripe_actions
{
	protected function getStripePaymentArgs($booking)
	{
		if (!class_exists('\Alexr\Payments\GatewayStripe')) return null;

		$stripeCheckout = new \Alexr\Payments\GatewayStripe($booking->restaurant_id);
		if (!$stripeCheckout->enabled) return null;

		$public_key = $stripeCheckout->getPublicKey();
		if (!$public_key) return null;

		return [
			'id' => 'stripe',
			'title' => $stripeCheckout->title,
			'description' => $stripeCheckout->description,
			'public_key' => $public_key,
			'can_capture_card' => \Alexr\Payments\GatewayStripe::$hasCaptureCards
		];
	}

	/**
	 * For payments and card-on-file
	 * Create Stripe intent clientSecret
	 * @return void
	 */
	public function stripe_create()
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

		if (!class_exists('Alexr\Payments\GatewayStripe')) {
			wp_send_json_error(['error' => __eva('Need the Stripe addon enabled.')]);
		}

		$booking = \Alexr\Models\Booking::find($booking_id);
		if (!$booking) {
			wp_send_json_error(['error' => __eva('No booking found.')]);
		}

		try {
			// Check if payment already exists
			$payment = $booking->payment;

			if ($payment) {
				wp_send_json_success([
					'clientSecret' => $payment->stripeClientSecret,
					'status' => $payment->status
				]);
			}

			$gateway = new Alexr\Payments\GatewayStripe($restaurant_id);


			// CAPTURE CARD ??????????
			//------------------------------------------
			$status = false;
			if ($is_capture_card) {
				$data = $gateway->create_capture_payment($restaurant_id, $booking_id);
				$payment_model = $data[0];
				$status = $payment_model->status;
				$paymentIntent = $data[1];
				$clientSecret = $paymentIntent->client_secret;
			}
			else {
				$data = $gateway->create_payment($restaurant_id, $booking_id);
				$payment_model = $data[0];
				$status = $payment_model->status;
				$paymentIntent = $data[1];
				$clientSecret = $paymentIntent->client_secret;
			}

			wp_send_json_success([
				'clientSecret' => $clientSecret,
				//'intentType' => $paymentIntent instanceof \Stripe\SetupIntent ? 'SetupIntent' : 'PaymentIntent',
				'status' => $status
			]);
		}
		catch(\Exception $e) {
			wp_send_json_error(['error' => $e->getMessage()]);
		}
	}

	public function stripe_payment_confirmed()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);
		$is_card_capture = sanitize_text_field($_REQUEST['is_card_capture']);
		$is_card_capture = ($is_card_capture == 'yes' ? true : false);

		if ( ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		$booking_uuid = sanitize_text_field($_REQUEST['uuid']);
		$booking = \Alexr\Models\Booking::where('uuid', $booking_uuid)->first();

		if (!$booking){
			wp_send_json_error(['error' => __eva('Invalid booking')]);
		}

		// Check the payment rule status defined, can be PENDING or can be BOOKED
		$booking->status = $booking->paymentStatus;
		$booking->save();

		// Update payment status
		$payment_intent = sanitize_text_field($_REQUEST['payment_intent']);
		$payment_status = sanitize_text_field($_REQUEST['payment_status']);
		$payment_status_main = $payment_status;
		$payment_method = sanitize_text_field($_REQUEST['payment_method']);

		$payment = \Alexr\Models\Payment::where('stripe_payment_intent_id',$payment_intent)->first();

		if ($payment)
		{
			if ($is_card_capture)
			{
				if ( str_contains($payment->status,'_preauth') ) {
					$payment_status_main = $payment_status_main.'_preauth';
				}
				else {
					$payment_status_main = $payment_status_main.'_capture';
				}
			}

			$payment->status = $payment_status_main;
			$payment->stripe_payment_status = $payment_status;
			$payment->stripe_payment_method = $payment_method;
			$payment->save();
		}

		$message = $this->sendBookingNotifications($booking);

		wp_send_json_success([
			'booking' => [
				'status' => $booking->status,
				'status_label' => \Alexr\Enums\BookingStatus::label($booking->status)
			]
		]);
	}

	public function get_payment_details()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		$restaurant = \Alexr\Models\Restaurant::find($restaurant_id);
		if (!$restaurant){
			wp_send_json_error([
				'message' => 'Wrong restaurant'
			]);
		}

		$booking_uuid = sanitize_text_field($_REQUEST['uuid']);

		if (!$booking_uuid) {
			wp_send_json_error(['error' => __eva('Booking not found.')]);
		}

		$booking = \Alexr\Models\Booking::where('uuid', $booking_uuid)->first();
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Booking not found.')]);
		}

		$service_id = $booking->shift_event_id;
		$service = alexr_get_service($service_id);
		if (!$service) {
			wp_send_json_error(['message' => __eva('Service not found.')]);
		}

		$payment = $this->gatewayPaymentDetails($service, $booking);

		wp_send_json_success(['payment' => $payment]);

	}

	public function get_stripe_receipt()
	{
		$restaurant_id = sanitize_text_field($_REQUEST['restaurantId']);

		if ( ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		$stripe_payment_intent_id = sanitize_text_field($_REQUEST['stripe_payment_intent_id']);

		$stripe = new \Alexr\Payments\GatewayStripe($restaurant_id);
		$result = $stripe->getPaymentIntent($stripe_payment_intent_id);

		try {
			$receipt_url = $result->charges->data[0]->receipt_url;

			if (!$receipt_url) {
				wp_send_json_error(['error' => __eva('No receipt attached.')]);
			}
		}
		catch (Exception $e) {
			wp_send_json_error(['error' => __eva('Invalid receipt url.')]);
		}



		wp_send_json_success(['receipt_url' => $receipt_url]);
	}


	// NO LO USO
	// Capturing the credit card. YA NO LO USO.. es antiguo sistema de stripe
	/*public function stripe_save_token()
	{
		$restaurant_id = intval($_REQUEST['restaurantId']);
		$booking_id = intval($_REQUEST['booking_id']);

		if ( ! $this->verify_nonce($restaurant_id)  && ! $this->verify_nonce('stripe-'.$restaurant_id) ) {
			wp_send_json_error(['error' => __eva('Invalid nonce. Try to refresh the page.')]);
		}

		if (!defined('ALEXR_PRO_VERSION')) {
			wp_send_json_error(['error' => __eva('Need the PRO plugin enabled.')]);
		}

		if (!class_exists('Alexr\Payments\GatewayStripe')) {
			wp_send_json_error(['error' => __eva('Need the Stripe addon enabled.')]);
		}

		$booking = \Alexr\Models\Booking::find($booking_id);
		if (!$booking) {
			wp_send_json_error(['error' => __eva('Booking not found.')]);
		}

		$stripe_token = sanitize_text_field($_REQUEST['stripe_token']);
		$stripe_token = str_replace('\"', '"', $stripe_token);

		$booking->stripeCardToken = $stripe_token;

		$payment = \Alexr\Models\Payment::where('booking_id', $booking_id)->last();
		if ($payment) {
			$payment->status = \Alexr\Enums\PaymentStatus::SUCCEEDED_CAPTURE;
		}

		$booking->status = $booking->paymentStatus;
		$booking->save();

		wp_send_json_success([]);
	}*/
}
