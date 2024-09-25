<?php

namespace Alexr\Models;

use Evavel\Models\Model;

class Area extends Model
{
	public static $table_name = 'areas';
	public static $table_meta = false;
	public static $pivot_tenant_field = 'restaurant_id';

	protected $visible = [ 'id', 'name', 'floor_id', 'active',
		'priority', 'ordering', 'bookable_staff', 'bookable_online', 'image_url'
	];
	protected $appends = [
		//'canvas',
		'viewportTransform',
		'decoration'
	];

	protected $casts = [
		'id' => 'int',
		'active' => 'bool',
		'floor_id' => 'int',
		'ordering' => 'int',
		'priority' => 'int',
		'bookable_staff' => 'boolean',
		'bookable_online' => 'boolean',
		'settings' => 'json'
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function floor()
	{
		return $this->belongsTo(Floor::class);
	}

	public function tables()
	{
		return $this->hasMany(Table::class);
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

	public function getViewportTransformAttribute()
	{
		$settings = $this->settings;

		if (isset($this->settings['viewportTransform'])){
			$viewport = $this->settings['viewportTransform'];
			if (is_string($viewport)){
				$viewport = explode(',', $viewport);
			}
			return $viewport;
		}
		return [];
	}

	public function setViewportTransformAttribute($value)
	{
		$settings = $this->settings;
		if (!is_array($settings)){
			$settings = [];
		}
		$settings['viewportTransform'] = $value ;

		$this->attributes['settings'] = $settings;
	}

	public function getDecorationAttribute()
	{
		$settings = $this->settings;
		$decoration = $this->settings['decoration'];
		if (is_string($decoration) && $decoration != ''){
			$decoration = json_decode($decoration, true);
		} else {
			$decoration = [];
		}
		return $decoration;
	}

	public function setDecorationAttribute($value)
	{
		$settings = $this->settings;
		if (!is_array($settings)){
			$settings = [];
		}
		$settings['decoration'] = ($value != '[]' ? $value : '');

		$this->attributes['settings'] = $settings;
	}
}
