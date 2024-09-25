<?php

namespace Alexr\Models\Traits;

use Alexr\Models\BookingNotification;

trait StoreEmailSent {

	protected function storeEmailSent($to = '', $subject = '', $content = '', $result = null, $type = 'email', $type_id = null) {

		$payload = [
			'status' => $this->status,
			'to' => $to,
			'subject' => base64_encode($subject),
			'content' => base64_encode($content),
			'result' => $result
		];

		$notification = BookingNotification::create([
			'restaurant_id' => $this->restaurant->id,
			'booking_id' => $this->id,
			'type' => $type,
			'type_id' => $type_id,
			'payload' => $payload
		]);

		$notification->save();

	}
}
