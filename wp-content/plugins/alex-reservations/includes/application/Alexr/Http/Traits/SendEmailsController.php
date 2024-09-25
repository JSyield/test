<?php

namespace Alexr\Http\Traits;

use Alexr\Models\Booking;
use Evavel\Http\Request\Request;

trait SendEmailsController {

	/**
	 * Send email with custom Subject/Content
	 * @param Request $request
	 *
	 * @return void
	 */
	public function emailCustom(Request $request)
	{
		// @todo authorization

		$bookingId = intval($request->bookingId);
		$subject = $request->subject;

		// Para poder hacer esto debe venir bien codificado desde javascript
		$content = base64_decode($request->content);

		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found.')]);
		}

		$booking->sendEmailCustom($subject, $content);

		return $this->response([ 'success' => true, 'message' => __('Done!') ] );
		//wp_send_json_success(['message' => __('Done!')]);
	}

	/**
	 * Send email for specific status
	 * @param Request $request
	 *
	 * @return void
	 */
	public function emailStatus(Request $request)
	{
		$bookingId = intval($request->bookingId);
		$status = $request->status;

		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found.')]);
		}

		// Language in the customer language
		$lang = $booking->language;

		$source = 'status-changed';
		if (isset($request->params['source']) && $request->params['source'] == 'reply-modal') {
			$source = 'reply-modal';
		}

		switch ($status){
			case 'pending':
				$booking->sendEmailPending($lang);
				break;
			case 'booked':
				$booking->sendEmailBooked($lang);
				break;
			case 'confirmed':
				$booking->sendEmailConfirmed($lang);
				break;
			case 'denied':
				$booking->sendEmailDenied($lang);
				break;
			case 'cancelled':
				$booking->sendEmailCancelled($lang);
				break;
			case 'no-show':
				$booking->sendEmailNoShow($lang);
				break;
			case 'finished':
				if ($source == 'status-changed') {
					$spend = intval($request->spend);
					$booking->spend = $spend;
					$booking->save();
					if ($request->with_email == 'yes') {
						$booking->sendEmailFinished($lang);
					}
				}
				else if ($source == 'reply-modal') {
					$booking->sendEmailFinished($lang);
				}

			default:
				break;
		}

		return $this->response([ 'success' => true, 'message' => __('Done!') ] );
	}

}
