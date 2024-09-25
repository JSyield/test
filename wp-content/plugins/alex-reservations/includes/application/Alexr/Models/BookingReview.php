<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class BookingReview extends Model {

	public static $table_name = 'booking_reviews';
	public static $pivot_tenant_field = 'restaurant_id';

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function booking()
	{
		return $this->belongsTo(Booking::class);
	}

	public function toArray()
	{
		return [
			'booking_id' => $this->booking_id,
			'score1' => $this->score1,
			'score2' => $this->score2,
			'score3' => $this->score3,
			'score4' => $this->score4,
			'score5' => $this->score5,
			'feedback' => $this->feedback,
			'date_created' => $this->date_created
		];
	}
}
