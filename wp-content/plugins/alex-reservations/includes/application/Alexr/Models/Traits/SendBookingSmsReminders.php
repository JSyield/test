<?php

namespace Alexr\Models\Traits;

use Alexr\Settings\Event;
use Alexr\Settings\Shift;
use Alexr\Settings\SmsReminder;
use Alexr\Sms\SmsManager;

trait SendBookingSmsReminders {

	public $smsManager;

	public function sendSmsReminder( $reminder_id, $lang = 'en' )
	{
		// Check if phone is valid
		$to_phone = $this->dial_code.$this->phone;

		$to_phone = alexr_clean_phone_number($to_phone);
		if (!alexr_is_valid_phone($to_phone)) return false;

		// Check reminder really exists
		$restaurant_id = $this->restaurant_id;

		$reminder = SmsReminder::where('restaurant_id', $restaurant_id)
		                       ->where('id', $reminder_id)
		                       ->first();

		if (!$reminder) return false;

		// Check reminder is active
		$enable = $reminder->sms_active;
		if (!$enable) return false;

		// Check reminder is for shifts or events
		$for_shifts =  $reminder->for_shifts == 1;
		$for_events = $reminder->for_events == 1;

		// Check service exists and is used by this reminder
		$service_id = $this->shift_event_id;
		$service = Shift::where('restaurant_id', $restaurant_id)->where('id', $service_id)->first();
		if ($service) {
			if (!$for_shifts) return false;
		} else {
			$service = Event::where('restaurant_id', $restaurant_id)->where('id', $service_id)->first();
			if ($service) {
				if (!$for_events) return false;
			} else {
				return false; // Service not exists
			}
		}

		$content_b64 = $reminder->sms_reminder['content_'.$lang];
		$content = base64_decode($content_b64);
		$content = $this->parseSmsTags($content);

		return $this->sendSmsAndStore($to_phone, $content, 'sms_reminder', $reminder->id);
	}
}
