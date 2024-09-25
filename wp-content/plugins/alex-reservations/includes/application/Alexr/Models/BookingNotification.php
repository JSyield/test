<?php

namespace Alexr\Models;

use Alexr\Enums\BookingStatus;
use Evavel\Models\Model;

class BookingNotification extends Model {

	public static $table_name = 'booking_notifications';
	public static $pivot_tenant_field = 'restaurant_id';

	public $casts = [
		'payload' => 'json'
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function booking()
	{
		return $this->belongsTo(Booking::class);
	}

	public function toArray() {

		if (isset($this->payload['status'])) {
			$status_label = BookingStatus::listing()[$this->payload['status']];
		} else {
			$status_label = '-';
		}

		return [
			'id' => $this->id,
			'type' => $this->type,
			'date_created' => $this->date_created,
			'date_modified' => $this->date_modified,
			'payload' => $this->payload,
			'status_label' => $status_label
		];
	}
}
