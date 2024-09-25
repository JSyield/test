<?php

namespace Alexr\Enums;

class PaymentStatus {

	const PENDING = "pending";
	const PROCESSING = "processing";
	const SUCCEEDED = "succeeded";
	const CANCELLED = "cancelled";

	const PENDING_CAPTURE = "pending_capture";
	const SUCCEEDED_CAPTURE = "succeeded_capture";
	const CANCELLED_CAPTURE = "cancelled_capture";

	const PENDING_PREAUTH = "pending_preauth";
	const SUCCEEDED_PREAUTH = "succeeded_preauth";
	const CANCELLED_PREAUTH = "cancelled_preauth";

	public static function labels() {
		return [
			PaymentStatus::PENDING => __eva('Pending Payment'),
			PaymentStatus::PROCESSING => __eva('Processing'),
			PaymentStatus::SUCCEEDED => __eva('Paid'),
			PaymentStatus::CANCELLED => __eva('Payment Cancelled'),

			PaymentStatus::PENDING_CAPTURE => __eva('Pending Getting Card'),
			PaymentStatus::SUCCEEDED_CAPTURE => __eva('Card Stored in Gateway'),
			PaymentStatus::CANCELLED_CAPTURE => __eva('Cancelled Getting Card'),

			self::PENDING_PREAUTH => __eva('Pending Pre-authorization'),
			self::SUCCEEDED_PREAUTH => __eva('Pre-authorized'),
			self::CANCELLED_PREAUTH => __eva('Cancelled Pre-authorization'),
		];
	}

	public static function listGateways()
	{
		return ['mercadopago', 'mollie', 'paypal', 'redsys', 'square', 'stripe'];
	}

	public static function gatewaysThatCanCaptureCards()
	{
		$list_to_check = self::listGateways();

		$canCapture = [];
		foreach($list_to_check as $name)
		{
			$className = 'Alexr\Payments\Gateway'.$name;
			if (class_exists($className))
			{
				if ($className::$hasCaptureCards)
				{
					$canCapture[] = $name;
				}
			}
		}
		return $canCapture;
	}

	public static function canCaptureCards($gateway) {
		return in_array($gateway, self::gatewaysThatCanCaptureCards());
	}

	public static function isCardCapture( $status ) {
		return in_array($status, [
			self::PENDING_CAPTURE,
			self::SUCCEEDED_CAPTURE,
			self::CANCELLED_CAPTURE
		]);
	}

	public static function isPreAuth($status) {
		return in_array($status, [
			self::PENDING_PREAUTH,
			self::SUCCEEDED_PREAUTH,
			self::CANCELLED_PREAUTH
		]);
	}

	public static function mapStripeStatus( $stripe_status ) {
		$map_status = [
			// Estados estándar de Stripe
			'requires_payment_method' => self::PENDING,
			'requires_confirmation' => self::PENDING,
			'requires_action' => self::PENDING,
			'processing' => self::PROCESSING,
			'requires_capture' => self::PENDING_CAPTURE,
			'canceled' => self::CANCELLED,
			'succeeded' => self::SUCCEEDED,

			// Estados adicionales para manejar casos específicos
			'payment_failed' => self::CANCELLED,
			'expired' => self::CANCELLED,

			// Estados específicos para SetupIntents (captura de tarjeta)
			'requires_payment_method_setup' => self::PENDING_CAPTURE,
			'requires_confirmation_setup' => self::PENDING_CAPTURE,
			'requires_action_setup' => self::PENDING_CAPTURE,
			'processing_setup' => self::PROCESSING,
			'succeeded_setup' => self::SUCCEEDED_CAPTURE,
			'canceled_setup' => self::CANCELLED_CAPTURE,

			// Estados para pre-autorización
			'requires_capture_preauth' => self::PENDING_PREAUTH,
			'succeeded_preauth' => self::SUCCEEDED_PREAUTH,
			'canceled_preauth' => self::CANCELLED_PREAUTH,
		];

		return isset($map_status[$stripe_status]) ? $map_status[$stripe_status] : PaymentStatus::PENDING;
	}

	// No los estoy usando pero pueden ser utiles

	public static function isPending($status) {
		return in_array($status, [
			self::PENDING,
			self::PENDING_CAPTURE,
			self::PENDING_PREAUTH
		]);
	}

	public static function isSucceeded($status) {
		return in_array($status, [
			self::SUCCEEDED,
			self::SUCCEEDED_CAPTURE,
			self::SUCCEEDED_PREAUTH
		]);
	}

	public static function isCancelled($status) {
		return in_array($status, [
			self::CANCELLED,
			self::CANCELLED_CAPTURE,
			self::CANCELLED_PREAUTH
		]);
	}

	public static function canBeCaptured($status) {
		return $status === self::SUCCEEDED_PREAUTH;
	}

	public static function canBeVoided($status) {
		return in_array($status, [
			self::PENDING_PREAUTH,
			self::SUCCEEDED_PREAUTH
		]);
	}
}
