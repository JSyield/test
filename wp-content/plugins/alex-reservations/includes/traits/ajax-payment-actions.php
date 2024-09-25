<?php

trait Alexr_ajax_payment_actions {

	public function isPaymentNeeded($service, $booking)
	{
		$payment = $service->getPaymentAmountForBooking($booking);

		return $payment > 0;
	}

	public function gatewayPaymentDetails($service, $booking)
	{
		$restaurant = $booking->restaurant;
		$payment = $service->getPaymentAmountForBooking($booking);

		if ($payment > 0)
		{
			// Token needed for return url for gateways
			$token = \Evavel\Support\Str::upper(\Evavel\Support\Str::random(12));
			$booking->gateway_token = $token;
			$booking->save();

			$payment_gateways = [];

			// Stripe
			$stripe_args = $this->getStripePaymentArgs($booking);
			if ($stripe_args) {
				$payment_gateways['stripe'] = $stripe_args;
			}

			// Redsys
			$redsys_args = $this->getRedsysPaymentArgs($booking, $payment);
			if ($redsys_args) {
				$payment_gateways['redsys'] = $redsys_args;
			}

			// Paypal
			$paypal_args = $this->getPaypalPaymentArgs($booking);
			if ($paypal_args) {
				$payment_gateways['paypal'] = $paypal_args;
			}

			// MercadoPago
			$mercadopago_args = $this->getMercadopagoPaymentArgs($booking);
			if ($mercadopago_args) {
				$payment_gateways['mercadopago'] = $mercadopago_args;
			}

			// Mollie
			$mollie_args = $this->getMolliePaymentArgs($booking);
			if ($mollie_args) {
				$payment_gateways['mollie'] = $mollie_args;
			}

			// Square
			$square_args = $this->getSquarePaymentArgs($booking);
			if ($square_args) {
				$payment_gateways['square'] = $square_args;
			}

			// Save amount required inside the booking
			if ($payment > 0) {
				$booking->amount = intval(100 * $payment);
				$booking->save();
			}

			return [
				'payment_gateways' => $payment_gateways,
				'required' => $payment > 0,
				// Define is the gateway has the option to capture cards
				'is_capture_card' => $service->isPaymentToCapture(),
				'amount' => $payment,
				'currency' => strtolower($restaurant->currency),
				'currency_symbol' => \Alexr\Enums\CurrencyType::symbolFor($restaurant->currency),
				'message' => $service->getPaymentMessage(),
				'return_url' => add_query_arg(['payment' => 'confirm'],evavel_view_booking_url($booking->uuid)),
			];
		}

		return false;
	}

	public function webhook()
	{
		// @TODO para mercadopago
		die();
	}
}
