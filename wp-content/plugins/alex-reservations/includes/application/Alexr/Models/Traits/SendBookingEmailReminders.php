<?php

namespace Alexr\Models\Traits;

use Alexr\Settings\EmailReminder;
use Alexr\Settings\Event;
use Alexr\Settings\Shift;
use Alexr\Mail\MailManager;

trait SendBookingEmailReminders {

	public function sendEmailReminder( $reminder_id, $lang = 'en' )
	{
		// Check reminder really exists
		$restaurant_id = $this->restaurant_id;

		$reminder = EmailReminder::where('restaurant_id', $restaurant_id)
		                         ->where('id', $reminder_id)
		                         ->first();

		if (!$reminder) return false;

		// Check reminder is active
		$enable = $reminder->email_active;
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

		// Subject & Content
		$subject_b64 = $reminder->email_reminder['subject_'.$lang];
		$content_b64 = $reminder->email_reminder['content_'.$lang];

		$subject = base64_decode($subject_b64);
		$content = base64_decode($content_b64);

		$subject = $this->parseEmailTags($subject);
		$content = $this->parseEmailTags($content);

		// Booking user email
		$to = $this->email;

		if ($this->mailManager == null) {
			$this->mailManager = new MailManager($this->restaurant_id);
		}

		$result = $this->mailManager->send_email($to, $subject, $content);

		// Store the reminder as email_reminder type with the id of the reminder
		$this->storeEmailSent($to, $subject, $content, $result, 'email_reminder', $reminder_id);

		return $result;
	}
}
