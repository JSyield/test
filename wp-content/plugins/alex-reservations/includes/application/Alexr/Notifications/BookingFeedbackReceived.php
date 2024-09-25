<?php

namespace Alexr\Notifications;

use Alexr\Enums\BookingStatus;
use Alexr\Enums\ReviewItems;
use Alexr\Models\Booking;
use Alexr\Models\User;
//use Carbon\Carbon;
use Evavel\Notifications\Notification;
use Evavel\Query\Query;

class BookingFeedbackReceived extends Notification
{
	public $booking;

	public function __construct(Booking $booking)
	{
		$this->booking = $booking;
	}

	public function toDatabase($user)
	{
		$data = parent::toDatabase($user);

		$data['data'] = [
			'icon' => 'info',
			'booking_id' => $this->booking->id,
			'status' => $this->booking->status,
			'status_label' => BookingStatus::label($this->booking->status),
			'review_id' => $this->booking->review->id,
			'title' => $this->title($user),
			'text' => $this->text($user),
			'feedback' => $this->feedback($user),
			'link' => $this->link($user)
		];

		$notification = \Alexr\Models\Notification::create($data);

		//Query::setDebug(true);

		$notification->save();
	}

	protected function title($user)
	{
		return __eva('REVIEW');
	}

	protected function text($user)
	{
		//$d_formatted = Carbon::createFromFormat('Y-m-d', $this->booking->date)
		//                     ->locale($this->booking->language)
		//                     ->translatedFormat('l j F Y');

		$d_formatted = evavel_date_createFromFormatTranslate('Y-m-d', $this->booking->date, $this->booking->language, 'l j F Y');

		$html = '<div><strong>' . $d_formatted . ' - ' . evavel_seconds_to_Hm($this->booking->time).'</strong></div>';
		$html .= '<p>'.$this->booking->name.' (' . $this->booking->party . ')</p>';

		$review = $this->booking->review;

		if ($review)
		{
			//$html .= '<div style=\"margin-left:10px;color:#05a6e8;\">';
			$html .= '<div class=\"pl-2\">';
			$keys = ['score1', 'score2', 'score3', 'score4', 'score5'];
			foreach($keys as $key) {
				$html .= '<p>'.ReviewItems::conceptForScore($key) . ': <strong>' . ReviewItems::labelForNumber($review->{$key}).'</strong></p>';
			}
			$html .= '</div>';
		}

		return $html;
	}

	protected function feedback($user)
	{
		$review = $this->booking->review;

		// base64 encoded
		if ($review) {
			return $review->feedback;
		}

		return '';
	}

	protected function link($user)
	{
		return '';
	}
}
