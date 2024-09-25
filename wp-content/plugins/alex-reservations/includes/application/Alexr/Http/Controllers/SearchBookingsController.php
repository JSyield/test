<?php

namespace Alexr\Http\Controllers;

use Alexr\Settings\WidgetForm;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class SearchBookingsController  extends Controller
{
	// NOT USING THIS. it was an example
	/*
	public function search(Request $request)
	{
		// @todo authorization user when testing from the dashboard

		$tenant = $request->get('tenant');
		$guests = $request->get('guests');
		$date = $request->get('date');
		$time = $request->get('time');

		return $this->response(
			[
				'request' => [
					'tenant'    => $tenant,
					'guests'    => $guests,
					'date'      => $date,
					'time'      => $time
				],
				'resultDate' => [
					'date' => $date,
					'slots' => [
						['time' => 18000, 'name' => 'Dinner'],
						['time' => 21600, 'name' => 'Dinner'],
						['time' => 25200, 'name' => 'Dinner'],
						['time' => 28800, 'name' => 'Dinner'],
					]
				],
				'resultOtherDates' => [
					[
						'date' => '2022-10-01',
						'slots' => [
							['time' => 61200, 'name' => 'Shift name'],
							['time' => 61200+3600, 'name' => 'Shift name'],
							['time' => 61200+7200, 'name' => 'Shift name'],
							['time' => 61200+3*3600, 'name' => 'Shift name'],
						]
					],
					[
						'date' => '2022-10-02',
						'slots' => [
							['time' => 61200, 'name' => 'Shift name'],
							['time' => 61200+3600, 'name' => 'Shift name'],
							['time' => 61200+7200, 'name' => 'Shift name'],
							['time' => 61200+3*3600, 'name' => 'Shift name'],
						]
					],
					[
						'date' => '2022-10-03',
						'slots' => [
							['time' => 61200, 'name' => 'Shift name'],
							['time' => 61200+3600, 'name' => 'Shift name'],
							['time' => 61200+7200, 'name' => 'Shift name'],
							['time' => 61200+3*3600, 'name' => 'Shift name'],
						]
					],
				]
			]
		);
	}
	*/

	public function reserve(Request $request)
	{
		return $this->response([
			'success' => true,
			'error' => 'Your reservation could not be completed.',
			'seconds' => 300
		]);
	}

	// If tenantId is not null then it is a Fake request from ajax-actions.php
	public function fields(Request $request = null, $tenantId = null, $widgetId = null, $serviceId = null)
	{
		// Process default fields through the custom fields save for the restaurant
		// If no custom fields are saved yet then just use the default fields

		$is_fake_request = true;
		if (!$tenantId) {
			$tenantId = $request->tenantId();
			$is_fake_request = false;
		}
		if (!$widgetId && $request) {
			$widgetId = $request->widgetId;
		}

		// Get the custom fields from the setting
		$custom_fields = [];
		$default_fields = $this->fieldsDefault();

		$wform = WidgetForm::where('restaurant_id', $tenantId)
		                   ->where('id', $widgetId)->first();

		if ($wform) {
			$custom_fields = $wform->form_fields;
		} else {
			$custom_fields = $default_fields;
		}

		// Remove custom fields depending on the service

		// Remove invisible fields
		$fields_to_use = [];
		foreach($custom_fields as $custom_field){

			if (isset($custom_field['visible']) && $custom_field['visible'])
			{
				$add_this_field = true;

				$custom_field['placeholder'] = isset($custom_field['placeholder']) ? $custom_field['placeholder'] : $custom_field['name'] ;

				if ($custom_field['attribute'] == 'tags') {
					$custom_field['availableGroups'] = $custom_field['availableGroups'];
					$add_this_field = !empty($custom_field['availableGroups']);
				}

				// Remove if not included in the selected services for this field
				if (isset($custom_field['services']) && $custom_field['services'] == 'selected' && isset($custom_field['services_selected'])){
					if ( !in_array($serviceId, $custom_field['services_selected'])) {
						$add_this_field = false;
					}
				}

				if ($add_this_field){
					$fields_to_use[] = $custom_field;
				}
			}
		}

		// Complete with default parameters
		$fields_to_use_2 = [];
		foreach($fields_to_use as $field)
		{
			$found = false;

			foreach ($default_fields as $default_field)
			{
				if ($default_field['attribute'] == $field['attribute'])
				{
					$found = true;
					foreach($default_field as $key => $value)
					{
						// Need to overwrite the type
						$field['type'] = $default_field['type'];

						// And add extra keys
						if (!isset($field[$key])) {
							$field[$key] = $default_field[$key];
						}
					}
				}
			}

			// Custom fields can by text, textarea, select, checkbox, options
			if (!$found) {
				if ($field['type'] == 'text' || $field['type'] == 'textarea')
				{
					$field['value'] = '';
				}
				else if ($field['type'] == 'checkbox')
				{
					$field['value'] = false;
				}
				else if ($field['type'] == 'select')
				{
					$options = [];
					foreach ($field['options'] as $option){
						$options[] = ['label' => $option, 'value' => $option];
					}
					$field['options'] = $options;
					$field['value'] = $field['options'][0];
				}
				else if ($field['type'] == 'options')
				{
					$values = [];
					foreach($field['options'] as $option){
						$values[$option] = false;
					}
					$field['value'] = $values;
				}
			}

			$fields_to_use_2[] = $field;
		}

		//sray($fields_to_use_2);
		return $is_fake_request ? $fields_to_use_2 : $this->response( $fields_to_use_2 );
	}

	// If tenantId is not null then it is a Fake request from ajax-actions.php
	/*
	public function fields_OLD(Request $request = null, $tenantId = null, $widgetId = null)
	{
		// Process default fields through the custom fields save for the restaurant
		// If no custom fields are saved yet then just use the default fields

		$is_fake_request = true;
		if (!$tenantId) {
			$tenantId = $request->tenantId();
			$is_fake_request = false;
		}

		if (!$widgetId && $request) {
			$widgetId = $request->widgetId;
		}

		// Get the custom fields from the setting
		$custom_fields = [];
		$wform = WidgetForm::where('restaurant_id', $tenantId)
		                   ->where('id', $widgetId)->first();

		if ($wform) {
			$custom_fields = $wform->form_fields;
		}

		// Default fields defined for the form
		$default_fields = $this->fieldsDefault();

		// Compare both list to get the final fields to be displayed
		$fields_to_use = [];
		if (!empty($custom_fields))
		{
			foreach($default_fields as $field)
			{
				// Find the corresponding custom field
				$custom_field = false;
				foreach($custom_fields as $c_field){
					if ($field['attribute'] == $c_field['attribute']) {
						$custom_field = $c_field;
					}
				}

				if ($custom_field && $custom_field['visible'])
				{
					$add_field = true;

					// Replace these rows from the custom field
					$field['name'] = $custom_field['name'];
					$field['placeholder'] = isset($custom_field['placeholder']) ? $custom_field['placeholder'] : $custom_field['name'] ;
					$field['required'] = $custom_field['required'];
					$field['required_message'] = $custom_field['required_message'];
					$field['help'] = $custom_field['help'];

					if (isset($custom_field['default_dial_code'])) {
						$field['default_dial_code'] = $custom_field['default_dial_code'];
					}

					if ($field['attribute'] == 'tags') {
						$field['availableGroups'] = $custom_field['availableGroups'];
						$add_field = !empty($field['availableGroups']);
					}

					// and add to the list
					if ($add_field){
						$fields_to_use[] = $field;
					}
				}
			}
		}
		else {
			// All fields if there are no custom fields yet
			$fields_to_use = $default_fields;
		}

		ray($fields_to_use);
		return $is_fake_request ? $fields_to_use : $this->response( $fields_to_use );
	}
	*/

	public function fieldsDefault()
	{
		return [
			[
				'attribute' => 'first_name',
                'name' => __eva('First name'),
                'placeholder' => 'First name',
				'help' => '',
				'visible' => true,
				'required' =>  true,
                'required_message' =>  'Enter your first name',
                'type' => 'text',
				'value' => '',
                'style' => 'display: inline-block; vertical-align: top; width: 50%; padding-right: 5px;',
				'styleDependsOn' => 'last_name'
			],
			[
				'attribute' => 'last_name',
				'name' => __eva('Last name'),
				'placeholder' => 'Last name',
				'help' => '',
				'visible' => true,
				'required' =>  true,
				'required_message' =>  'Enter your last name',
				'type' => 'text',
				'value' => '',
				'style' => 'display: inline-block; vertical-align: top; width: 50%; padding-left: 5px;',
				'styleDependsOn' => 'first_name'
			],
			[
				'attribute' => 'email',
                'name' => __eva('Email'),
				'placeholder' => 'Email address',
				'help' => '',
				'visible' => true,
                'required' => true,
                'required_message' => 'Enter a valid email',
                'type' => 'email',
                'value' => ''
			],
			[
				'attribute' => 'dial_code',
				'name' => __eva('Phone code'),
				'placeholder' => 'Phone code',
				'help' => '',
				'visible' => true,
				'required' =>  false,
				'required_message' =>  '',
				'type' => 'select',
				'value' => '',
				'style' => 'display: inline-block; vertical-align: top; width: 50%; padding-right: 5px;',
				'styleDependsOn' => 'phone_number',
				'options' => [] // Managed in javascript
			],
			[
				'attribute' => 'phone_number',
				'name' => __eva('Phone number'),
				'placeholder' => 'Phone number',
				'help' => '',
				'visible' => true,
				'required' =>  false,
				'required_message' =>  '',
				'type' => 'phone',
				'value' => [
					'dial_code' => '',
					'dial_code_country' => '',
					'phone' => ''
				],
				'style' => 'display: inline-block; vertical-align: top; width: 50%; padding-left: 5px;',
				'styleDependsOn' => 'dial_code',
				'options' => [] // Managed in javascript
			],
			[
				'attribute' => 'country_code',
				'name' => __eva('Country'),
				'placeholder' => 'Country',
				'help' => '',
				'visible' => true,
				'required' =>  false,
				'required_message' =>  '',
				'type' => 'select',
				'value' => '',
				'style' => 'display: inline-block; vertical-align: top; width: 50%; padding-right: 5px;',
				'styleDependsOn' => 'birthday',
				'options' => [] // Managed in javascript
			],
			/*[
				'attribute' => 'birthday',
				'name' => 'Birthday',
				'placeholder' => 'mm/dd',
				'help' => 'Use the format mm/dd',
				'required' =>  false,
				'required_message' =>  '',
				'type' => 'text',
				'value' => '',
				'style' => 'display: inline-block; vertical-align: top; width: 50%; padding-left: 5px;',
				'styleDependsOn' => 'country_code',
				'options' => [] // Managed in javascript
			],*/
			[
				'attribute' => 'tags',
				'name' => __eva('Dietary restrictions, Special occasion.'),
				'help' => '',
				'stacked' => true,
				'visible' => true,
				'required' => false,
                'type' => 'select-tags-field',
                'styleMode' => 'widget',
                'url' => '/app/btags',
                //'helpText' => 'Help Text',
                'availableGroups' => [], // Only allow these tags
                'value' => null, //[4,5,6]
			],
			[
				'attribute' => 'notes',
				'name' => __eva('Reservation notes'),
				'help' => '',
				'visible' => true,
				'required' => false,
				'required_message' => '',
				'type' => 'textarea',
				'value' => ''
			],
			[
				'attribute' => 'agree',
				'name' => __eva('I agree with the restaurant conditions'),
				'help' => '',
				'visible' => true,
				'required' => true,
				'required_message' => 'You need to agree the conditions',
				'type' => 'checkbox',
				'value' => false
			],
			[
				'attribute' => 'agree_email_news',
				'name' => __eva('I agree with receiving email news'),
				'help' => '',
				'visible' => true,
				'required' => true,
				'required_message' => 'You need to agree the email marketing option',
				'type' => 'checkbox',
				'value' => false
			],
			[
				'attribute' => 'agree_sms',
				'name' => __eva('I agree with receiving SMS reminders'),
				'help' => '',
				'visible' => true,
				'required' => true,
				'required_message' => 'You need to agree the SMS reminders option',
				'type' => 'checkbox',
				'value' => false
			],
		];
	}
}
