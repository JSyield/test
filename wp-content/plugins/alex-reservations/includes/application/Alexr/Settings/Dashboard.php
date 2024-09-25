<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Evavel\Models\SettingSimple;

class Dashboard extends SettingSimple {

	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'dashboard';
	public static $pivot_tenant_field = 'restaurant_id';

	protected $casts = [
		//'active' => 'boolean',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	function settingName()
	{
		return __eva('Dashboard');
	}

	public function addToUpdateResponse($response)
	{
		$response['forceReload'] = true;
		return $response;
	}

	public function fields()
	{
		$fields = [
			[
				'attribute' => 'time_slider_expire_time',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Time slider - reset time'),
				'helpText' => __eva('The time slider on the floor plan will reset automatically after being manually adjusted.'),
				'component' => 'select-field',
				'options' => [
					['label' => __eva('in 5 seconds'), 'value' => 5],
					['label' => __eva('in 10 seconds'), 'value' => 10],
					['label' => __eva('in 20 seconds'), 'value' => 20],
					['label' => __eva('in 30 seconds'), 'value' => 30],
					['label' => __eva('in 1 minute'), 'value' => 60],
					['label' => __eva('in 5 minutes'), 'value' => 300],
					['label' => __eva('in 15 minutes'), 'value' => 900],
					['label' => __eva('in 30 minutes'), 'value' => 1800],
					['label' => __eva('in 60 minutes'), 'value' => 3600],
				],
				'value' => $this->time_slider_expire_time,
			],
			[
				'attribute' => 'resource_timeline',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 69%;',
				'name' => __eva('Timeline views: Start/End time'),
				//'helpText' => __eva('To be used inside the reservation widget and emails'),
				'component' => 'first-last-seating-field',
				'value' => [
					'timeline_start' => $this->timeline_start ? $this->timeline_start : 10*3600,
					'timeline_end' =>  $this->timeline_end ? $this->timeline_end : 86400 + 7200,
				],
				'options' => [
					'start_time' => 3600 * 4,
					'end_time' => 30 * 3600,
					'step' => 3600,
					'maxLowerVal' => 22 * 3600,
					'minUpperVal' => 24 * 3600
				],
			],
		];

		return $fields;
	}
}
