<?php

namespace Alexr\Models\Traits;

use Alexr\Settings\WidgetMessage;

trait ReturnBookingMessages {

	protected function parseMessageTags($message)
	{
		// @TODO use tags in the message
		$tags = [
			'{booking_details}' => '', // Details are provided by the javascript in the widget
			//'{booking_details_no_duration}' => '<style>.custom-booking-duration{display:none !Important;}</style>' // Hide the duration
		];

		// Booking details is Name, Phone, Email

		foreach ($tags as $tag => $replace) {
			$message = str_replace($tag, $replace, $message);
		}

		return $message;
	}

	public function getServiceCustomMessage($template, $lang = 'en')
	{
		$service = $this->service;
		if (!$service) return null;

		$widget_custom = $service->widget_custom;
		if (!$widget_custom) return null;

		// Check is enabled overwrite general
		if (!isset($widget_custom['overwrite_general'])) return null;
		$overrite_general = $widget_custom['overwrite_general'];
		if ($overrite_general != '1' && $overrite_general != 1 && $overrite_general != 'true' && $overrite_general !== true) {
			return null;
		}

		// Check is enabled overwrite messages
		if (!isset($widget_custom['overwrite_messages'])) return null;
		$overrite_messages = $widget_custom['overwrite_messages'];
		if ($overrite_messages != '1' && $overrite_messages != 1 && $overrite_messages != 'true' && $overrite_messages !== true) {
			return null;
		}

		if (!isset($widget_custom[$template])) return null;

		$template = json_decode($widget_custom[$template], true);

		if (isset($template['content_' . $lang])) return $template['content_' . $lang];
		if (isset($template['content_en'])) return $template['content_en'];

		return null;
	}

	protected function getMessageTemplate($template = 'message_pending', $lang = 'en')
	{
		// Can be overwritten by the shift setting
		$message_b64 = $this->getServiceCustomMessage($template, $lang);
		if (!$message_b64){
			$w_message = WidgetMessage::where('restaurant_id', $this->restaurant_id)->first();
			$message_b64 = $w_message->{$template}['content_'.$lang];
		}

		$message = base64_decode($message_b64);
		$message = $this->parseMessageTags($message);

		return $message;
	}

	public function messagePending($lang = 'en')
	{
		return $this->getMessageTemplate('message_pending', $lang);
	}

	public function messageBooked($lang = 'en')
	{
		return $this->getMessageTemplate('message_booked', $lang);
	}
}
