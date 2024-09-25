<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Evavel\Models\SettingSimple;

// Esto no es un setting del restaurante, no debo usarlo
class Profile extends SettingSimple
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'profile';
	public static $pivot_tenant_field = 'restaurant_id';

	protected $casts = [
		//'active' => 'boolean',
	];

	public function user()
	{
		return $this->belongsTo(Restaurant::class);
	}

	function settingName()
	{
		return __eva('My Profile');
	}

	function defaultValue()
	{
		return [
			'active' => true,
			'name' => 'My Profile'
		];
	}

	public function fields()
	{
		return [
			[
				'attribute' => 'active',
				'stacked' => true,
				'style' => 'display: inline-block; width: 20%;',
				'name' => __eva('Active'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->active
			],
			[
				'attribute' => 'name',
				'stacked' => true,
				'style' => 'display: inline-block; width: 80%;',
				'name' => __eva('Internal Name'),
				'component' => 'text-field',
				'value' => $this->name
			],
		];
	}

	public function validate()
	{
		return [];
	}
}
