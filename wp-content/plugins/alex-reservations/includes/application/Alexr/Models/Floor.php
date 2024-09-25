<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class Floor extends Model
{
	public static $table_name = 'floors';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	protected $visible = ['id', 'active', 'name'];
	protected $appends = ['canvas'];

	protected $casts = [
		'id' => 'int',
		'active' => 'bool',
		'ordering' => 'int',
		'priority' => 'int',
		'settings' => 'json',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function areas()
	{
		return $this->hasMany(Area::class);
	}

	public function getCanvasAttribute()
	{
		$settings = $this->settings;
		return isset($this->settings['canvas']) ? $this->settings['canvas'] : [];
	}

	public function setCanvasAttribute($value)
	{
		$settings = $this->settings;
		if (!is_array($settings)){
			$settings = [];
		}
		$settings['canvas'] = json_decode($value, true) ;

		$this->attributes['settings'] = $settings;
	}
}
