<?php

namespace Alexr\Http\Traits;

use Alexr\Models\Booking;
use Evavel\Http\Request\Request;

trait SendSmsController {

	/**
	 * Send email with custom message
	 * @param Request $request
	 *
	 * @return void
	 */
	public function smsCustom(Request $request)
	{
		$bookingId = intval($request->bookingId);
		$message = $request->message;

		// Para poder hacer esto debe venir bien codificado desde javascript
		$message = base64_decode($message);

		$booking = Booking::find($bookingId);
		if (!$booking){
			return $this->response(['success' => false, 'error' => __eva('Booking not found.')]);
		}

		$booking->sendSmsCustom($message);

		return $this->response([ 'success' => true, 'message' => __('Done!') ] );
	}

	/**
	 * Send sms for specific status
	 * @param Request $request
	 *
	 * @return void
	 */
	public function smsStatus(Request $request)
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
				$booking->sendSmsPending($lang);
				break;
			case 'booked':
				$booking->sendSmsBooked($lang);
				break;
			case 'denied':
				$booking->sendSmsDenied($lang);
				break;
			case 'cancelled':
				$booking->sendSmsCancelled($lang);
				break;
			case 'no-show':
				$booking->sendSmsNoShow($lang);
				break;
			case 'finished':
				if ($source == 'status-changed') {
					$spend = intval($request->spend);
					$booking->spend = $spend;
					$booking->save();
					if ($request->with_sms == 'yes') {
						$booking->sendSmsFinished($lang);
					}
				}
				else if ($source == 'reply-modal') {
					$booking->sendSmsFinished($lang);
				}

			default:
				break;
		}

		return $this->response([ 'success' => true, 'message' => __('Done!') ] );
	}
}
