<?php

namespace Alexr\Http\Traits;

use Alexr\Enums\PaymentStatus;
use Alexr\Models\Booking;
use Alexr\Models\Payment;
use Alexr\Payments\GatewayStripe;
use Evavel\Http\Request\Request;

trait BookingsUsePaymentsController
{
	public function getReceipt(Request $request)
	{
		$bookingId = intval($request->bookingId);
		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found!')]);
		}

		if (!$booking->gateway || $booking->gateway == 'stripe') {
			if (!class_exists('\Alexr\Payments\GatewayStripe')){
				return $this->response(['success' => false, 'error' => __eva('Module not enabled!')]);
			}

			return $this->response([
				'success' => true,
				'url' =>  $booking->paymentReceiptLink
			]);
		}

		else if (in_array($booking->gateway, ['redsys', 'paypal', 'mercadopago', 'mollie']))
		{
			$payment = $booking->payment;

			if (!$payment) {
				return $this->response([
					'success' => true,
					'message' =>  __eva('Payment not found.')
				]);
			}

			$function = $booking->gateway."DataFormatted";
			$message = $payment->{$function};

			if (!$message) {
				$message = __eva('NO DATA');
			}

			return $this->response([
				'success' => true,
				'message' =>  $message
			]);
		}

		return $this->response([
			'success' => false,
			'error' =>  'NOT IMPLEMENTED YET'
		]);
	}

	/**
	 * Data for the popup from the dashboard
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function getPaymentData(Request $request)
	{
		$bookingId = intval($request->bookingId);
		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found!')]);
		}

		$link_view_booking =  alexr_view_booking_url($booking);

		$payment = $booking->payment;
		$payment_status = $payment->status;

		$label_status = PaymentStatus::labels()[$payment_status];
		$amount = number_format(floatval($payment->amount) / 100, 2);

		$list_payment_methods = [];
		$canChargeAfter = $payment->canChargeAfter();
		if ($canChargeAfter) {
			$list_payment_methods = $payment->getCustomerPaymentMethods();
		}
		$isPreauthorized = $payment->status == PaymentStatus::SUCCEEDED_PREAUTH;

		// @TODO Ahora lo pongo todo de golpe, debo hacer un template para cada pasarela
		ob_start();
		echo '<a class="text-indigo-700 mt-4 font-bold" target="_blank" href="'.$link_view_booking.'">'.__eva('Customer View').'</a>';
		echo '<div>'.$label_status.' '.$amount.' '.$payment->currency.'</div>';
		//echo '<pre style="font-size: 12px">'; print_r($payment->attributes); echo '</pre>';
		$html = ob_get_clean();


		return $this->response([
			'success' => true,
			'payment' => $payment,
			'message' =>  $html,
			'has_receipt' => $payment->hasReceipt(),
			'can_charge_after' => $canChargeAfter,
			'list_payment_methods' => $list_payment_methods,
			'is_preauthorized' => $isPreauthorized
		]);
	}

	public function chargePreauth(Request $request)
	{
		$bookingId = intval($request->bookingId);
		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found!')]);
		}

		// Model Payment
		$payment_id = intval($request->payment_id);
		$payment = Payment::find($payment_id);
		if (!$payment) {
			return $this->response(['success' => false, 'error' => __eva('Payment not found!')]);
		}

		if ($payment->payment_type == 'stripe') {

			//return $this->response(['success' => false, 'error' => __eva('AHORA LO PREPARO')]);
			$result_payment = $payment->chargeCustomerPreauthorized();

			if (!$result_payment) {
				return $this->response(['success' => false, 'error' => __eva('Error charging with').' '.$payment->payment_type]);
			}

			return $this->response([
				'success' => true,
				'result' => is_array($result_payment) ? $result_payment : $result_payment->toArray()
			]);

		}

		return $this->response(['success' => false, 'error' => __eva('Charge not implemented for').' '.$payment->payment_type]);
	}

	public function chargeCard(Request $request)
	{
		$bookingId = intval($request->bookingId);
		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found!')]);
		}

		// Model Payment
		$payment_id = intval($request->payment_id);
		$payment = Payment::find($payment_id);
		if (!$payment) {
			return $this->response(['success' => false, 'error' => __eva('Payment not found!')]);
		}

		// Card id
		$payment_method_id = $request->payment_method_id;

		if ($payment->payment_type == 'stripe') {

			$result_payment = $payment->chargeCustomerWithPaymentMethodId($payment_method_id);

			if (!$result_payment) {
				return $this->response(['success' => false, 'error' => __eva('Error charging with').' '.$payment->payment_type]);
			}

			return $this->response([
				'success' => true,
				'result' => is_array($result_payment) ? $result_payment : $result_payment->toArray()
			]);
		}

		return $this->response(['success' => false, 'error' => __eva('Charge not implemented for').' '.$payment->payment_type]);
	}
}
