<?php

namespace Alexr\Models\Traits;

use Alexr\Models\BookingNotification;

trait StoreSmsSent {

	protected function storeSmsSent($to = '', $subject = '', $content = '', $result = null, $type = 'sms', $type_id = null) {

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

		// Update the status of the SMS
		if ($result === true) {
			if ($this->sms_status != 'ok'){
				$this->sms_status = 'ok';
				$this->save();
			}
		} else {
			if ($this->sms_status != 'error'){
				$this->sms_status = 'error';
				$this->save();
			}
		}

	}
}
