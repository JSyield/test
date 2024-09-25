<?php

namespace Alexr\Models\Traits;

use Alexr\Enums\Countries;
use Alexr\Settings\EmailTemplate;
use Alexr\Settings\SmsTemplate;
use Alexr\Sms\SmsManager;
//use Carbon\Carbon;
use Alexr\Mail\MailManager;

trait SendBookingSms {

	public $smsManager;

	public function sendSmsTemplate($template = 'booking_pending', $lang = 'en', $type = 'sms', $type_id = null)
	{
		// Check if phone is valid
		$to_phone = $this->dial_code.$this->phone;
		$to_phone = alexr_clean_phone_number($to_phone);
		if (!alexr_is_valid_phone($to_phone)) return false;

		$smsTemplate = SmsTemplate::where('restaurant_id', $this->restaurant_id)->first();

		// Check if enabled
		$enable = $smsTemplate->{$template.'_enable'};
		if ($enable !== 1) return false;

		$content_b64 = $smsTemplate->{$template}['content_'.$lang];
		$content = base64_decode($content_b64);

		$content = $this->parseSmsTags($content);

		return $this->sendSmsAndStore($to_phone, $content);
	}

	public function parseSmsTags($message)
	{
		$language = $this->language;
		if (!$language) {
			$language = $this->restaurant->language;
		}

		// Date formatted
		$date_format = $this->getDateFormat();

		/*$date_formatted = Carbon::createFromFormat('Y-m-d', $this->date)
		                        ->locale($language)
		                        ->translatedFormat($date_format);*/

		$date_formatted = evavel_date_createFromFormatTranslate('Y-m-d', $this->date, $language, $date_format);

		// Current date formatted
		//$current_date_formatted = evavel_now_timezone($this->restaurant->timezone)
		//	->locale($language)
		//	->translatedFormat($date_format);

		$current_date_formatted = evavel_date_translate(
			evavel_now_timezone_formatted($this->restaurant->timezone, $date_format)
			, $language
		);

		$mybooking_link = evavel_view_booking_url($this->uuid);
		$site_link = evavel_site_url();

		$restaurant_link = $this->restaurant->link_web;
		$restaurant_facebook = $this->restaurant->link_facebook;
		$restaurant_instagram = $this->restaurant->link_instagram;

		$tags = [
			'{restaurant}'  => $this->restaurant->name,
			'{restaurant_phone}'  => $this->restaurant->dial_code.' '.$this->restaurant->phone,
			'{name}'        => $this->name,
			'{email}'       => $this->email,
			'{phone}'       => '('.$this->dial_code . ')' . $this->phone,
			'{party}'       => $this->party,
			'{time}'        => $this->getTimeFormatted(),
			'{end_time}'    => $this->getEndTimeFormatted(),
			'{duration}'    => $this->toDuration($this->duration),
			'{date}'        => $date_formatted,
			'{current_date}'=> $current_date_formatted,
			'{reservation_number}' => $this->uuid,
			'{mybooking}'   => $mybooking_link,
			'{site_link}'   => $site_link,
			'{restaurant_link}' => $restaurant_link,
			'{restaurant_facebook}' => $restaurant_facebook,
			'{restaurant_instagram}' => $restaurant_instagram,

		];

		foreach ($tags as $tag => $replace) {
			if ($replace == null) $replace = '';
			$message = str_replace($tag, $replace, $message);
		}

		return $message;
	}


	public function sendSmsAndStore($to_phone, $content, $type = 'sms', $type_id = null)
	{
		if (!$this->smsManager){
			$this->smsManager = new SmsManager($this->restaurant_id);
		}

		try {
			$result = $this->smsManager->send_message($to_phone, 'sms', $content);
		} catch (\Exception $e) {
			$result = $e->getMessage();
		}

		$this->storeSmsSent($to_phone, 'SMS', $content, $result, $type, $type_id);

		return $result;
	}

	// Customer SMS
	public function sendSmsPending($lang = 'en')
	{
		$this->sendSmsTemplate('booking_pending', $lang);
	}

	public function sendSmsPendingPayment($lang = 'en')
	{
		$this->sendSmsTemplate('booking_pending_payment', $lang, 'sms_pending_payment');
	}

	public function sendSmsBooked($lang = 'en')
	{
		return $this->sendSmsTemplate('booking_booked', $lang);
	}

	public function sendSmsDenied($lang = 'en')
	{
		$this->sendSmsTemplate('booking_denied', $lang);
	}

	public function sendSmsCancelled($lang = 'en')
	{
		$this->sendSmsTemplate('booking_cancelled', $lang);
	}

	public function sendSmsNoShow($lang = 'en')
	{
		$this->sendSmsTemplate('booking_no_show', $lang);
	}

	public function sendSmsFinished($lang = 'en')
	{
		$this->sendSmsTemplate('booking_finished', $lang);
	}

	public function sendSmsCustom($content)
	{
		$to_phone = $this->dial_code.$this->phone;
		$to_phone = alexr_clean_phone_number($to_phone);
		if (!alexr_is_valid_phone($to_phone)) return false;

		return $this->sendSmsAndStore($to_phone, $content);
	}
}
