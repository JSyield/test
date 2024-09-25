<?php

namespace Alexr\Models;

use Evavel\Models\Model;
use Evavel\Support\Str;

class Notification extends Model
{
	public static $table_name = 'notifications';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	protected $casts = [
		'data' => 'json',
	];

	public static function booted()
	{
		static::creating(function($model) {
			$model->uuid = Str::uuid();
		});
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}
}
