<?php

namespace Alexr\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class SupportController extends Controller
{
	public function send(Request $request)
	{
		$user = Eva::make('user');
		$params = $request->params;

		$email = $request->email;
		$message = $request->message;

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return $this->response(['success' => false, 'error' => __eva('Email not valid.')]);
		}

		if (empty($message)) {
			return $this->response(['success' => false, 'error' => __eva('Message is empty.')]);
		}


		$message = '<h3>'.$request->email.'</h3><div>'.$message.'</div>';
		$subject = "Support from ".$request->email;

		add_filter( 'wp_mail_content_type', function(){ return 'text/html'; }, 99999 );

		$response = wp_mail('support@alexreservations.com', $subject, $message);

		if ($response){
			return $this->response(['success' => true, 'message' => __eva('Thank you! Our support team will contact you.')]);
		} else {
			return $this->response(['success' => false, 'error' => __eva('Error sending your message. Review your WP email configuraiton.')]);
		}


	}
}
