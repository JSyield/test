<?php

namespace Alexr\Http\Controllers;

use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Alexr\Mail\MailManager;
use PHPMailer\PHPMailer\Exception;

class SendTestEmailController extends Controller
{
	public function index(Request $request)
	{
		$restaurantId = $request->tenantId();
		$to = $request->test_email_to;
		$subject = $request->test_email_subject;
		$message = $request->test_email_message;


		try {
			$mm = new MailManager($restaurantId);
			$result = $mm->send_email_phpmailer($to, $subject, $message, false, true);

			if (isset($result['error'])) {
				$message = $result['error'] . ' ' . $result['debug_log'];
				throw new \Exception($message);
			}

			//$mm = new MailManager($restaurantId);
			//$mm->send_email($to, $subject, $message);

			wp_send_json_success();

		}
		catch (\Exception $e) {
			wp_send_json_error(['error' => $e->getMessage()]);
		}

	}
}
