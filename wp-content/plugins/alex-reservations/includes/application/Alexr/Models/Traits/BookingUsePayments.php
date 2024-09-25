<?php

namespace Alexr\Models\Traits;

use Alexr\Enums\BookingStatus;
use Alexr\Enums\CurrencyType;
use Alexr\Enums\PaymentStatus;
use Alexr\Models\Payment;
use Alexr\Payments\GatewayStripe;
use MailPoet\Exception;

trait BookingUsePayments
{
	public function getServiceAttribute() {
		$service_id = $this->shift_event_id;
		$service = alexr_get_service($service_id);
		return $service;
	}

	public function getPaymentRuleAttribute() {
		$service = $this->service;
		if ($service){
			return $service->getPaymentRule($this);
		}
		return null;
	}

	public function getPaymentStatusAttribute() {
		$paymentRule = $this->paymentRule;
		if ($paymentRule && is_array($paymentRule) && isset($paymentRule['status'])){
			return $paymentRule['status'];
		}
		return BookingStatus::BOOKED;
	}

	// Get last payment
	public function getPaymentAttribute()
	{
		return Payment::where('booking_id', $this->id)->last();
	}

	// Solo devuelve el pago succeed
	public function getPaymentSuccessAttribute()
	{
		$payment =  $this->payments->filter(
			function($item){
				return $item->status == PaymentStatus::SUCCEEDED || $item->status == PaymentStatus::SUCCEEDED_CAPTURE;
			}
		)->first();

		if ($payment) return $payment;

		return null;
	}

	public function getPaidAmountAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return 0;

		return intval($payment->amount) / 100.0;
	}

	public function getPaidAmountFormattedAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return '';

		$amount = intval($payment->amount) / 100.0;

		return CurrencyType::symbolFor($payment->currency).$amount;
	}


	public function getPaymentReceiptLinkAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return '';

		// Only Stripe has receipt link
		if ($payment->payment_type != 'stripe') return null;

		$stripe_payment_intent_id = $payment->stripe_payment_intent_id;

		if (GatewayStripe::isCardOnFileIntent($stripe_payment_intent_id)) {
			return null;
		}

		$stripe = new \Alexr\Payments\GatewayStripe($this->restaurant_id);
		$result = $stripe->getPaymentIntent($stripe_payment_intent_id);

		//try {
		//	return $result->charges->data[0]->receipt_url;
		//}
		try {
			if (isset($result->charges) &&
			    isset($result->charges->data) &&
			    is_array($result->charges->data) &&
			    !empty($result->charges->data) &&
			    isset($result->charges->data[0]->receipt_url)) {
				return $result->charges->data[0]->receipt_url;
			}
			return null;
		}
		catch (Exception $e)
		{
			return null;
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	public function getIsPaidAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return false;

		return $payment->status == PaymentStatus::SUCCEEDED;
	}

	public function getIsCardCapturedAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return false;

		return $payment->status == PaymentStatus::SUCCEEDED_CAPTURE;
	}

	public function getIsForCapturingCardAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return false;

		return in_array($payment->status, [
			PaymentStatus::PENDING_CAPTURE,
			PaymentStatus::SUCCEEDED_CAPTURE,
			PaymentStatus::CANCELLED_CAPTURE,
			PaymentStatus::PENDING_PREAUTH,
			PaymentStatus::SUCCEEDED_PREAUTH,
			PaymentStatus::CANCELLED_PREAUTH,
		]);
	}

	public function getIsPreauthConfirmedAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return false;

		return in_array($payment->status, [
			PaymentStatus::SUCCEEDED_PREAUTH,
		]);
	}

	public function getStripeReceiptLinkAttribute()
	{
		$payment = $this->payment;
		if (!$payment) return null;

		if (!class_exists('\Alexr\Payments\GatewayStripe')){
			return null;
		}

		$stripe_payment_intent_id = $payment->stripe_payment_intent_id;

		$stripe = new \Alexr\Payments\GatewayStripe($this->restaurant_id);
		$result = $stripe->getPaymentIntent($stripe_payment_intent_id);

		try {
			$receipt_url = $result->charges->data[0]->receipt_url;
			return $receipt_url;
		}
		catch (Exception $e) {
			return null;
		}
	}

	// Card capture to pay later. NO LO USO es old Stripe
	//----------------------------------------------

	// Get stripe token captured for card intent
	public function getStripeCardTokenAttribute()
	{
		try {
			$token = $this->get_setting('stripe_card_token');
			if (is_string($token)) {
				return json_decode($token, true);
			}
			return $token;
		} catch (\Exception $e) {
			return null;
		}
	}

	// Get stripe token captured for card intent
	public function setStripeCardTokenAttribute($value)
	{
		$this->set_setting('stripe_card_token', $value);
	}

}
