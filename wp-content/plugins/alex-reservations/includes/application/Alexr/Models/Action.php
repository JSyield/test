<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class Action extends Model
{
	public static $table_name = 'actions';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	protected $appends = [];

	protected $casts = [
		'original' => 'array',
		'changes' => 'array'
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}
}
