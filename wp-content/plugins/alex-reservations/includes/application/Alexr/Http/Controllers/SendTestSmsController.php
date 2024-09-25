<?php

namespace Alexr\Http\Controllers;

use Alexr\Sms\SmsManager;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class SendTestSmsController extends Controller
{
	public function sendSms(Request $request)
	{
		$this->send($request, 'sms');
	}

	public function sendWhatsapp(Request $request)
	{
		$this->send($request, 'whatsapp');
	}

	public function send(Request $request, $type = 'sms')
	{
		$restaurantId = $request->tenantId();
		$to_phone = $request->test_sms_phone;

		try {
			$sms = new SmsManager($restaurantId);
			$result = $sms->send_test_message($to_phone, $type);

			if (isset($result['error'])) {
				$message = $result['error'] . ' ' . $result['debug_log'];
				throw new \Exception($message);
			}

			wp_send_json_success();
		}
		catch (\Exception $e) {
			wp_send_json_error(['error' => $e->getMessage()]);
		}
	}
}
