<?php

namespace Alexr\Settings;

use Alexr\Enums\BookingType;
use Alexr\Settings\Traits\HasTimeOptions;
//use Carbon\Carbon;
use Evavel\Models\Setting;
use Evavel\Models\SettingListing;

class BlockedHour extends SettingListing
{
	use HasTimeOptions;

	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'blocked_hour';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-blocked-hour';

	protected $casts = [
		'active' => 'boolean'
	];

	function defaultValue() {
		return [
			'active' => true,
			'name' => 'Blocked hours',
			'start_date' => evavel_date_now()->addDays(1)->format('Y-m-d'),
			'end_date' => evavel_date_now()->addDays(1)->format('Y-m-d'),
			'start_time' => 21600,
			'end_time' => 82800,
			'block_type' => [
				BookingType::ONLINE => false,
				BookingType::INHOUSE => false
			],
		];
	}

	public function fields() {
		return [
			'left' => $this->fieldsLeft(),
			'right' => []
		];
	}

	function fieldsLeft() {
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
			[
				'attribute' => 'start_date',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Start Date'),
				'component' => 'date-field',
				'value' => $this->start_date
			],
			[
				'attribute' => 'start_time',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Start Time'),
				'component' => 'select-field',
				'value' => $this->start_time,
				'options' => $this->listOfHours()
			],
			[
				'attribute' => 'end_date',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('End Date'),
				'component' => 'date-field',
				'value' => $this->end_date
			],
			[
				'attribute' => 'end_time',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('End Time'),
				'component' => 'select-field',
				'value' => $this->end_time,
				'options' => $this->listOfHours()
			],
			[
				'attribute' => 'block_type',
				'stacked' => true,
				'name' => __eva('Block Type'),
				'component' => 'checkboxes-field',
				'options' => [
					['label' => __eva('Online'), 'value' => BookingType::ONLINE],
					['label' => __eva('In House'), 'value' => BookingType::INHOUSE],
				],
				'mode' => 'inline',
				'value' => evavel_json_encode($this->block_type)
			],
		];
	}

	// @todo: pending check overlaps of dates
	public function validate() {
		return [];
		return ['error' => 'Dates are overlapping with Hours ---!'];
	}
}
