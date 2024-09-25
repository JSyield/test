<?php

namespace Alexr\Models;

use Alexr\Enums\BookingStatus;
use Alexr\Models\Traits\CustomerPaymentUtils;
use Alexr\Models\Traits\HasSettings;
use Evavel\Models\Model;
use Evavel\Support\Str;

class Customer extends Model
{
	use HasSettings;
	use CustomerPaymentUtils;

	public static $table_name = 'customers';
	public static $table_meta = 'customer_meta';
	public static $pivot_tenant_field = 'restaurant_id';

	public $casts = [
		'visits' => 'integer',
		'visits_imported' => 'integer',
		'spend' => 'integer',
		'spend_imported' => 'integer',
		'spend_per_visit' => 'integer',
		'spend_per_cover' => 'integer',
		'covers' => 'integer',
		'cancels' => 'integer',
		'no_shows' => 'integer'
	];

	public $appends = [
		'tagsList', 'isVip'
	];

	public static function booted()
	{
		static::creating(function($customer) {
			$customer->uuid = Str::uuid('cu');
			//$customer->calculateVisits();
		});
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function tags()
	{
		return $this->belongsToMany(CTag::class, 'customer_ctag', 'customer_id', 'ctag_id', 'id', 'id', 'ctags');
	}

	public function getTagsListAttribute()
	{
		return $this->tags->map( function($tag) {
			return intval($tag->id);
		})->toArray();
	}

	public function getTagsListNamesAttribute()
	{
		$names =  $this->tags->map( function($tag) { return $tag->name; })->toArray();
		return implode(', ', $names);
	}

	public function getIsVipAttribute()
	{
		$list = $this->tags->filter(function($tag){
			return $tag->group->is_vip;
		})->toArray();

		return count($list) > 0;
	}

	public function calculateVisits()
	{
		$bookings = Booking::where('customer_id', $this->id)->get();

		$last_date = null;

		$visits = 0;
		$spend = 0;
		$covers = 0;
		$cancels = 0;
		$no_shows = 0;
		$denied = 0;

		foreach($bookings as $booking) {

			$visits += 1;

			if ($booking->spend > 0) {
				$spend += $booking->spend;
				$covers += $booking->party;
			}

			if ($booking->date > $last_date) {
				$last_date = $booking->date;
			}

			if ($booking->status == BookingStatus::NO_SHOW){
				$no_shows += 1;
				$visits -= 1;
				$covers -= $booking->party;
				$spend -= $booking->spend;
			}
			else if ($booking->status == BookingStatus::CANCELLED) {
				$cancels += 1;
				$visits -= 1;
				$covers -= $booking->party;
				$spend -= $booking->spend;
			}
			else if ($booking->status == BookingStatus::DENIED) {
				$denied += 1;
				$visits -= 1;
				$covers -= $booking->party;
				$spend -= $booking->spend;
			}
		}

		$this->visits = $visits + intval($this->visits_imported);
		$this->covers = $covers;
		$this->spend = intval($spend) + intval($this->spend_imported);
		$this->spend_per_visit = $this->spend / ($this->visits > 0 ? $this->visits : 1);
		$this->spend_per_cover = $this->spend / ($this->covers > 0 ? $this->covers : 1);
		$this->last_visit = $last_date;
		$this->cancels = $cancels;
		$this->no_shows = $no_shows;

		$this->save();
	}

	/**
	 * Data for CSV exporting
	 * @return array
	 */
	public function toCsvArray($lang = 'en') {

		$phone = $this->dial_code.$this->phone;
		$phone = str_replace(["(",")","+","-","."], "", $phone);

		return [
			//'id' => $this->uuid,
			'name' => $this->name,
			'email' => $this->email,
			//'phone' => '('.$this->dial_code.')'.$this->phone,
			'phone' => $phone,
			'tags' => $this->tagsListNames
		];
	}
}
