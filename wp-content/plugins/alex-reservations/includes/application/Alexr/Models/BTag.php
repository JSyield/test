<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class BTag extends Model
{
	public static $table_name = 'btags';
	public static $pivot_tenant_field = 'restaurant_id';

	protected $casts = [
		'id' => 'int',
		'group_id' => 'int',
		'is_deletable' => 'int'
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function group()
	{
		return $this->belongsTo(BTagGroup::class, 'group_id');
	}

}
