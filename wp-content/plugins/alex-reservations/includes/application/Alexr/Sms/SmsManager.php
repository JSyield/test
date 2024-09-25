<?php

namespace Alexr\Sms;

use Alexr\Settings\EmailConfig;
use MailPoet\Exception;
use Twilio\Rest\Client;

class SmsManager {

	public $config;

	public function __construct( $restaurant_id )
	{
		// Load Twilio
		require_once ALEXR_PRO_PLUGIN_DIR . 'vendors/twilio/vendor/autoload.php';

		$this->config = EmailConfig::where('restaurant_id', $restaurant_id)->get()->first();
	}

	public function is_configured() {

		if (!$this->config) return false;
		if (!$this->config->use_sms) return false;

		$sms_sid = $this->config->twilio_sid;
		$sms_token = $this->config->twilio_token;
		$sms_phone = $this->config->twilio_phone;

		if ( empty($sms_sid) ) return false;
		if (empty($sms_token)) return false;
		if (empty($sms_phone)) return false;

		return true;
	}

	public function send_test_message($to_phone, $type = 'sms')
	{
		$to_phone = alexr_clean_phone_number($to_phone);
		if (!alexr_is_valid_phone($to_phone)) return false;
		//throw new \Exception($to_phone);

		if (!$this->is_configured()) {
			throw new \Exception(__eva('SMS not configured.'));
		}

		if (!defined('ALEXR_PRO_PLUGIN_DIR')) {
			throw new \Exception(__eva('You need to activate the PRO plugin.'));
		}

		$sms_sid = $this->config->twilio_sid;
		$sms_token = $this->config->twilio_token;
		$sms_phone = $this->config->twilio_phone;
		$whatsapp_phone = $this->config->twilio_whatsapp_phone;

		$from_phone = $sms_phone;
		$message = 'This is a test message!';

		if ($type == 'whatsapp') {
			$from_phone = $whatsapp_phone;
			if (!preg_match('#whatsapp:#', $from_phone)){
				$from_phone = 'whatsapp:'.$whatsapp_phone;
			}
			if (!preg_match('#whatsapp:#', $to_phone)) {
				$to_phone = 'whatsapp:'.$to_phone;
			}
			$message = 'Your appointment is coming up on -THIS IS A TEST-';
		}

		$client = new Client($sms_sid, $sms_token);

		$result = $client->messages->create(
			$to_phone,
			array(
				'from' => $from_phone,
				'body' => $message
			)
		);

		//ray('SMS MESSAGE from ' . $from_phone . ' to phone ' . $to_phone);
		//ray($message);
		//ray($result);

		return true;
	}

	public function send_message($to_phone = false, $type = 'sms', $message = '')
	{
		$to_phone = alexr_clean_phone_number($to_phone);
		if (!alexr_is_valid_phone($to_phone)) return false;

		if (!$this->is_configured()) {
			throw new \Exception(__eva('SMS not configured.'));
		}

		if (!defined('ALEXR_PRO_PLUGIN_DIR')) {
			throw new \Exception(__eva('You need to activate the PRO plugin.'));
		}

		$sms_sid = $this->config->twilio_sid;
		$sms_token = $this->config->twilio_token;
		$sms_phone = $this->config->twilio_phone;

		$from_phone = $sms_phone;

		$client = new Client($sms_sid, $sms_token);

		// @TODO - Not storing the result data
		$result = $client->messages->create(
			$to_phone,
			array(
				'from' => $from_phone,
				'body' => $message
			)
		);


		return true;
	}
}
