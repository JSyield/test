<?php

namespace Alexr\Models;

use Evavel\Models\Model;
use Evavel\Support\Str;

class DailyNotification extends Model
{
	public static $table_name = 'daily_notifications';
	public static $pivot_tenant_field = 'restaurant_id';

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public static function booted()
	{
		static::creating(function($notification){
			$notification->uuid = Str::uuid('dn');
		});
	}
}
