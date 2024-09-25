<?php

namespace Alexr\Notifications;

use Alexr\Enums\BookingStatus;
use Alexr\Models\Booking;
use Evavel\Notifications\Notification;

class BookingConfirmedByUser extends Notification
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
			'icon' => 'success',
			'booking_id' => $this->booking->id,
			'status' => $this->booking->status,
			'status_label' => BookingStatus::label($this->booking->status),
			'title' => $this->title($user),
			'text' => $this->text($user),
			'link' => $this->link($user)
		];

		$notification = \Alexr\Models\Notification::create($data);
		$notification->save();
	}

	protected function title($user)
	{
		return __eva('BOOKING CONFIRMED BY USER');
	}

	protected function text($user)
	{
		$d_formatted = evavel_date_createFromFormatTranslate('Y-m-d',
			$this->booking->date,
			$this->booking->language,
			'l j F Y');

		// Cannot save :0, creates a problem when displaying at the front-end
		$hourminutes = evavel_seconds_to_Hm($this->booking->time);
		//$hourminutes = str_replace(':', '.', $hourminutes);

		$html = '<div><strong>' . $d_formatted . ' - ' . $hourminutes.'</strong></div>';
		$html .= '<p>'.$this->booking->first_name. ' ' . $this->booking->last_name . ' (' . $this->booking->party . ')</p>';

		return $html;
	}

	protected function link($user)
	{
		return [
			'name' => 'bookings-list-date',
			'params' => ['tenantId' => $this->booking->restaurant_id, 'yearmonthday' => $this->booking->date]
		];
	}
}
