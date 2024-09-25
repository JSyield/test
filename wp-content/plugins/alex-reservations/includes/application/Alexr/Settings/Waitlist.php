<?php

namespace Alexr\Settings;

use Alexr\Settings\Traits\HasTimeOptions;
use Evavel\Models\Setting;
use Evavel\Models\SettingListing;

class Waitlist extends SettingListing
{
	use HasTimeOptions;

	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'waitlist';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-waitlist';

	protected $casts = [
		'active' => 'boolean'
	];

	function defaultValue() {
		return [
			'active' => true,
			'name'   => 'Waitlist',
			'display_name' => 'Waitlist',
			'type' => 'recurring',
			'covers' => 10,
			'days_of_week' => [
				'sun' => true,
				'mon' => true,
				'tue' => true,
				'wed' => true,
				'thu' => true,
				'fri' => true,
				'sat' => true
			],
			'start_date' => evavel_date_now()->addDays(1)->format('Y-m-d'),
			'end_date' => evavel_date_now()->addDays(1)->format('Y-m-d'),
			'start_time' => 21600,
			'end_time' => 82800,
			'notes' => ''
		];
	}

	public function fields() {
		return [
			'left' => $this->fieldsLeft(),
			'right' => $this->fieldsRight()
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
				'name' => __eva('Name'),
				'component' => 'text-field',
				'value' => $this->name,
				//'helpText' => __eva('Internal name for your identification')
			],
			[
				'attribute' => 'display_name',
				'stacked' => true,
				'name' => __eva('Display Name'),
				'component' => 'text-field',
				'value' => $this->name,
				'helpText' => __eva('This name will displayed at the reservation form')
			],
			[
				'attribute' => 'notes',
				'stacked' => true,
				'name' => __eva('Notes'),
				'component' => 'textarea-field',
				'value' => $this->notes,
			]
		];
	}

	function fieldsRight() {

		return [
			[
				'attribute' => 'type',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Availability'),
				'component' => 'select-field',
				'value' => $this->type,
				'options' => [
					['label' => __eva('Recurring'), 'value' => 'recurring'],
					['label' => __eva('One Time'), 'value' => 'onetime']
				]
			],
			[
				'attribute' => 'covers',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Capacity by Covers'),
				'component' => 'text-field',
				'value' => $this->covers,
				//'helpText' => __eva('Internal name for your identification')
			],
			[
				'attribute' => 'days_of_week',
				'stacked' => true,
				'name' => __eva('Days of week'),
				'component' => 'checkboxes-field',
				'hideWhen' => [
					'attribute' => 'type',
					'values' => ['onetime']
				],
				'options' => [
					['label' => __eva('Sun'), 'value' => 'sun'],
					['label' => __eva('Mon'), 'value' => 'mon'],
					['label' => __eva('Tue'), 'value' => 'tue'],
					['label' => __eva('Wed'), 'value' => 'wed'],
					['label' => __eva('Thu'), 'value' => 'thu'],
					['label' => __eva('Fri'), 'value' => 'fri'],
					['label' => __eva('Sat'), 'value' => 'sat'],
				],
				'mode' => 'inline',
				'value' => evavel_json_encode($this->days_of_week)
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
		];
	}

	// @todo: pending check overlaps of dates
	public function validate() {
		return [];
		return ['error' => 'Dates are overlapping with Hours ---!'];
	}
}
