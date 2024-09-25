<?php

namespace Alexr\Models;

use Alexr\Enums\PaymentStatus;
use Alexr\Models\Traits\HasSettings;
use Alexr\Payments\GatewayMercadopago;
use Alexr\Payments\GatewayStripe;
use Evavel\Models\Model;
use Evavel\Support\Str;

class Payment extends Model {
	use HasSettings;

	public static $table_name = 'payments';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	public static function booted() {
		static::creating( function ( $model ) {
			$model->uuid = Str::uuid();
		} );
	}

	public function restaurant() {
		return $this->belongsTo( Restaurant::class );
	}

	public function booking() {
		return $this->belongsTo( Booking::class );
	}

	public function getSettingsAttribute() {
		$data = json_decode( $this->attributes['settings'], true );
		if ( ! is_array( $data ) ) {
			return [];
		}

		return $data;
	}

	public function getRedsysDataFormattedAttribute() {
		$skip_keys = [ 'DS_MERCHANT_URLOK', 'DS_MERCHANT_URLKO', 'DS_MERCHANT_MERCHANTURL' ];

		$settings = $this->settings;

		if ( isset( $settings['redsys']['vars'] ) ) {
			$vars    = $settings['redsys']['vars'];
			$message = "<div>";
			foreach ( $vars as $key => $value ) {
				if ( ! in_array( $key, $skip_keys ) ) {
					$message .= "<div>{$key}: {$value}</div>";
				}
			}
			$message .= "</div>";

			return $message;
		}

		return null;
	}

	public function getPaypalDataFormattedAttribute() {
		$settings = $this->settings;

		if ( isset( $settings['paypal'] ) ) {
			$is_validated = false;
			$data         = $settings['paypal'];

			if ( isset( $settings['paypal']['validate'] ) ) {
				$data         = $settings['paypal']['validate'];
				$is_validated = true;
			}
			$message = "<div>";
			$message .= "<div>ID: {$data['id']}</div>";
			$message .= "<div>Status: {$data['status']}</div>";
			$message .= "<div>Purchase: {$data['purchase_units'][0]['amount']['currency_code']} {$data['purchase_units'][0]['amount']['value']}</div>";
			$message .= "<div>Payer: {$data['payer']['name']['given_name']} {$data['payer']['name']['surname']}</div>";
			$message .= "<div>Payer: {$data['payer']['email_address']}</div>";
			$message .= "<div>Payee: {$data['purchase_units'][0]['payee']['email_address']}</div>";
			$message .= "<div>Time: {$data['create_time']}</div>";
			$message .= "<div><a style='color:blue' href='{$data['links'][0]['href']}' target='_blank'>Paypal >> </a></div>";

			if ( $is_validated ) {
				ob_start();
				echo '<pre>';
				print_r( $data );
				echo '</pre>';
				$message .= "<div style='font-size: 12px; border: 1px solid black; padding: 10px; max-width: 480px; max-height: 400px; overflow-y: scroll'>" . ob_get_clean() . "</div>";
			}

			$message .= "</div>";

			return $message;
		}

		return null;
	}

	public function getMercadopagoDataFormattedAttribute() {
		$settings = $this->settings;

		if ( isset( $settings['mercadopago'] ) ) {
			$pago_id = false;
			$data    = $settings['mercadopago'];

			if ( isset( $settings['mercadopago_result'] ) ) {
				$data    = $settings['mercadopago_result'];
				$pago_id = $data['payment_id'];
			}

			if ( $pago_id ) {
				$gateway             = new GatewayMercadopago( $this->restaurant_id );
				$mercadopago_payment = $gateway->get_pago( $pago_id );
				$data                = json_decode( json_encode( $mercadopago_payment ), true );
			}

			$message = "";
			ob_start();
			echo '<pre>';
			print_r( $data );
			echo '</pre>';
			$message .= "<div style='font-size: 12px; border: 1px solid black; padding: 10px; max-width: 480px; max-height: 400px; overflow-y: scroll'>" . ob_get_clean() . "</div>";

			return $message;
		}

		return null;
	}

	public function getMollieDataFormattedAttribute() {
		$settings = $this->settings;

		if ( ! isset( $settings['mollie'] ) ) {
			return null;
		}

		$mollie_id = $settings['mollie']['id'];

		$gateway        = new \Alexr\Payments\GatewayMollie( $this->restaurant_id );
		$mollie_payment = $gateway->get_mollie_payment_from_id( $mollie_id );
		$data           = json_decode( json_encode( $mollie_payment ), true );

		$message = "";
		ob_start();
		echo '<pre>';
		print_r( $data );
		echo '</pre>';
		$message .= "<div style='font-size: 12px; border: 1px solid black; padding: 10px; max-width: 480px; max-height: 400px; overflow-y: scroll'>" . ob_get_clean() . "</div>";

		return $message;
	}

	// To try payment from view booking I am using the same intent secret
	public function getStripeClientSecretAttribute() {
		return $this->get_setting( 'stripe_client_secret' );
	}

	public function hasReceipt() {
		return $this->payment_type == 'stripe' && $this->status == PaymentStatus::SUCCEEDED;
	}

	public function canChargeAfter() {
		return $this->payment_type == 'stripe' && $this->status == PaymentStatus::SUCCEEDED_CAPTURE;
	}

	public function getCustomerPaymentMethods() {
		try {
			if ( $this->payment_type == 'stripe' ) {
				$customer_id = $this->booking->customer->stripeId;
				$gateway     = new GatewayStripe( $this->restaurant_id );
				$list        = $gateway->getCustomerPaymentMethods( $customer_id );

				return $list;
			}
		} catch ( \Exception $e ) {
			return [];
		}

		return [];
	}

	// Charge customer
	public function chargeCustomerWithPaymentMethodId( $payment_method_id ) {
		try {
			if ( $this->payment_type == 'stripe' ) {
				$customer_id = $this->booking->customer->stripeId;
				$gateway     = new GatewayStripe( $this->restaurant_id );
				$result      = $gateway->chargeCustomer( $this->booking->id, $customer_id, $this->amount, $this->currency, $payment_method_id );

				return $result;
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return null;
	}

	public function chargeCustomerPreauthorized()
	{
		try {
			if ( $this->payment_type == 'stripe' ) {
				$customer_id = $this->booking->customer->stripeId;
				$gateway     = new GatewayStripe( $this->restaurant_id );
				$result      = $gateway->capturePreauthorizedPayment( $this->stripe_payment_intent_id, $this->booking->id, $customer_id, $this->amount, $this->currency );

				return $result;
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return null;
	}


	public function toCustomArray() {
		return self::toDataArray($this);
	}

	/**
	 * Used for mybooking view
	 * @param $payment
	 *
	 * @return array
	 */
	public static function toDataArray($payment) {
		if (!$payment) return [];
		return [
			'status' => $payment->status,
			'id' => $payment->id,
			'restaurant_id' => $payment->restaurant_id,
			'booking_id' => $payment->booking_id,
			'amount' => $payment->amount,
			'payment_type' => $payment->payment_type,
			'currency' => $payment->currency,
			'status' => $payment->status,
			'stripe_payment_status' => $payment->stripe_payment_status,
			'stripe_payment_intent_id' => $payment->stripe_payment_intent_id
		];
	}
}
