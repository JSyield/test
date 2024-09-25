<?php

namespace Alexr\Settings;

use Alexr\Models\BTagGroup;
use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Http\Request\Request;
use Evavel\Models\SettingCustomized;

class WidgetForm extends SettingCustomized
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'widget_form';
	public static $pivot_tenant_field = 'restaurant_id';

	public static $custom_component = 'WidgetForm';

	public function getMessageSlotsNotAvailable()
	{
		$message = $this->form_config['message_not_available'];
		if ($message != null && $message != 'null'){
			$message = base64_decode($message);
			if (strlen($message) < 10){
				$message = __eva('Unfortunately there is no availability for that date.');
			}
		}
		return $message;
	}

	public static function configuration(AppSettingsRequest $request)
	{
		$tenantId = $request->tenantId();

		return [
			'mode' => 'SettingCustomized',
			'component' => static::$custom_component,
			'ajaxurl' => evavel_ajaxurl(),
			'languages' => evavel_languages_as_options(),
			'nonce' => evavel_tenant_create_nonce($tenantId),
		];
	}

	public static function label()
	{
		return __eva('Reservation Form');
	}

	/**
	 * Get list of available widget forms for the restaurant
	 * @param $tenantId
	 *
	 * @return mixed
	 */
	public static function getListing($tenantId) {

		$list = WidgetForm::where('restaurant_id', $tenantId)
                ->get()
				->map(function($widget){
					return [
						'id' => $widget->id,
						'name' => $widget->name,
						'desc' => $widget->desc,
						'base_color' => $widget->form_config['base_color'],
						'text_color' => $widget->form_config['text_color'],
						'shifts_option' => $widget->form_config['shifts_option'],
						'events_option' => $widget->form_config['events_option'],
						'shifts' => $widget->form_config['shifts'],
						'events' => $widget->form_config['events'],
						];
				})->toArray();


		$shifts = Shift::where('restaurant_id', $tenantId)->orderBy('ordering', 'ASC')->get()->toArray();
		$events = Event::where('restaurant_id', $tenantId)->orderBy('ordering', 'ASC')->get()->toArray();

		return [
			'list' => $list,
			'shifts' => $shifts,
			'events' => $events
		];
	}

	/**
	 * List of items needed for the widget customization
	 *
	 * @param AppSettingsRequest|null $request
	 * @param $tenantId
	 * @param $widgetId
	 *
	 * @return array
	 */
	public static function getItems(AppSettingsRequest $request = null, $tenantId = null, $widgetId = null)
	{
		//ray($request->params);
		//ray($request->tenantId);
		//ray($request->widgetId);

		if (!$widgetId){
			$widgetId = $request->widgetId;
		}

		$setting = WidgetForm::where('restaurant_id', $tenantId)
			->where('id', $widgetId)
			->first();


		// Create a new widget form
		if (!$setting) {
			$setting = new WidgetForm;
			$field_tenant_id = evavel_tenant_field();
			$setting->{$field_tenant_id} = $tenantId;
			$setting->setupDefaultValues()->save();
		}

		$complete_form_config = self::secureNeededConfig($setting->form_config);
		$complete_form_fields = self::secureNeededFields($setting->form_fields);

		return [
			'id' => $setting->id,
			'name' => $setting->name,
			'desc' => $setting->desc,
			'form_config' => $complete_form_config, // Configure different parameters
			'form_fields' => $complete_form_fields, // The form fields only
			'bTagGroups' => BTagGroup::where('restaurant_id', $tenantId)->get()->toArray(),
			'shifts' => Shift::where('restaurant_id', $tenantId)->get()->toArray(),
			'events' => Event::where('restaurant_id', $tenantId)->get()->toArray(),
			'languages' => evavel_languages_allowed()
		];
	}


	public function defaultValue(){
		return [
			'name' => 'Custom wiget',
			'desc' => 'This widget can be customized',
			'form_fields' => self::defaultFields(),
			'form_config' => self::defaultConfig()
		];
	}

	/** Check that minimum fields required for the form are in the form,
		in case the fields have been saved before and I need to add more default fields,
		or remove some default fields
	 */
	public static function secureNeededFields($custom_fields)
	{
		$required_fields = self::defaultFields();

		$final_fields = [];

		// Complete the custom fields with the required fields attributes
		foreach ($custom_fields as $custom_field)
		{
			foreach($required_fields as $required_field){
				if ($required_field['attribute'] == $custom_field['attribute']){

					// Be sure it has all the attributes the default field has
					foreach($required_field as $key => $value)
					{
						if (!isset($custom_field[$key])){
							$custom_field[$key] = $value;
						}
						// Rewrite this key always
						if ($key == 'customizable'){
							$custom_field[$key] = $value;
						}
					}

				}
			}

			$final_fields[] = $custom_field;
		}

		// Now be sure I have added all the required fields
		foreach($required_fields as $required_field)
		{
			$found_required_field = false;

			foreach ($final_fields as $custom_field) {
				if ($required_field['attribute'] == $custom_field['attribute']){
					$found_required_field = true;
				}
			}

			if (!$found_required_field){
				$final_fields[] = $required_field;
			}
		}

		return $final_fields;
	}

	public static function secureNeededConfig($custom_configs)
	{
		$required_configs = self::defaultConfig();
		$final_configs = [];

		foreach($required_configs as $required_config_key => $required_config_value)
		{
			if (!isset($custom_configs[$required_config_key]) || empty($custom_configs[$required_config_key])){
				$final_configs[$required_config_key] = $required_config_value;
			} else {
				$final_configs[$required_config_key] = $custom_configs[$required_config_key];
			}
		}
		return $final_configs;
	}

	public static function defaultConfig()
	{
		return [
			'shifts_option' => 'all', // all, select
			'events_option' => 'all', // all, select
			'shifts' => [],
			'events' => [],
			'show_services_dropdown' => 'yes',
			'show_services_duration' => 'yes',

			'header_text' => '',
			'max_days_in_advance' => 90,
			'prevent_duplicate_bookings' => 'yes',
			'show_not_available_slots' => 'no',
			'message_not_available' => 'Unfortunately there is no availability for that date',
			//'booking_duration' => 3600,

			'base_color' => '#0284c7',
			'text_color' => '#f8fafc',
			'custom_css' => '',

			'link_terms_of_service' => '',
			'link_privacy_policy'   => '',
			//'link_gdpr_policy'      => '',

			// Not using these fields now, but I leave them here in case I decide to use them later
			'number_slots_display' => 4,
			'number_of_other_days_to_display' => 4,
			'number_slots_display_for_other_days' => 4,

			'language_default' => 'en',
			'languages_allowed' => ['en'] // languages allowed for the widget
		];
	}

	// These are for the customizer, not the front-end
	// For the front-end -> SearchBookingsController()->fieldsDefault
	/**
	 * Form fields to be used for the Widget
	 * @return array[]
	 */
	public static function defaultFields()
	{
		return [
			[
				'attribute' => 'first_name',
				'type' => 'default',
				'name' => __eva('First name'),
				'placeholder' => __eva('First name'),
				'visible' => true,
				'customizable' => [
					'placeholder',
					'name',
					'required_message',
					'help',
				],
				'required' => true,
				'required_message' => 'Enter your first name',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
			[
				'attribute' => 'last_name',
				'type' => 'default',
				'name' => __eva('Last name'),
				'placeholder' => __eva('Last name'),
				'visible' => true,
				'customizable' => [
					'placeholder',
					'name',
					'required_message',
					'help',
				],
				'required' => true,
				'required_message' => 'Enter your last name',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
			[
				'attribute' => 'email',
				'type' => 'default',
				'name' => __eva('Email'),
				'placeholder' => __eva('Email'),
				'visible' => true,
				'customizable' => [
					'placeholder',
					'name',
					'required_message',
					'help',
				],
				'required' => true,
				'required_message' => 'Enter a valid email',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
			/*[
				'attribute' => 'dial_code',
				'type' => 'default',
				'name' => 'Phone code',
				'visible' => true,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter your phone code',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],*/
			[
				'attribute' => 'phone_number',
				'fields' => ['dial_code', 'dial_code_country', 'phone'],
				'default_dial_code' => 0,
				'type' => 'default',
				'name' => __eva('Phone'),
				'placeholder' => __eva('Phone'),
				'visible' => true,
				'customizable' => [
					'placeholder',
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter a valid phone number',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
			[
				'attribute' => 'country_code',
				'type' => 'default',
				'name' => __eva('Country'),
				'visible' => true,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter your country',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
			/*[
				'attribute' => 'birthday',
				'type' => 'default',
				'name' => 'Birthday',
				'visible' => true,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter your birthday',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			]*/
			[
				'attribute' => 'tags',
				'type' => 'default',
				'name' => __eva('Dietary restrictions, Special occasion.'),
				'placeholder' => __eva('Click to add preferences'),
				'visible' => true,
				'customizable' => [
					'placeholder',
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter your preferences',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
				'availableGroups' => ''
			],
			/*[
				'attribute' => 'credit_card',
				'type' => 'default',
				'name' => __eva('Credit card fields.'),
				'visible' => false,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter your credit card',
				'help' => 'In the event of a no-show, we will apply a fee to your account',
				'editing' => false,
				'can_be_removed' => false,
				'availableGroups' => ''
			],*/
			[
				'attribute' => 'notes',
				'type' => 'default',
				'name' => __eva('Notes'),
				'placeholder' => __eva('Notes'),
				'visible' => true,
				'customizable' => [
					'placeholder',
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'Enter your custom notes',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
			[
				'attribute' => 'agree',
				'type' => 'default',
				'name' => __eva('I agree with the restaurant conditions'),
				'visible' => true,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'You need to agree the conditions',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],

			[
				'attribute' => 'agree_email_news',
				'type' => 'default',
				'name' => __eva('I accept to receive restaurant news via email'),
				'visible' => false,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'You need to agree the email marketing option',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],

			// @TODO enable this field when SMS is ready
			[
				'attribute' => 'agree_sms',
				'type' => 'default',
				'name' => __eva('I accept to receive SMS notifications'),
				'visible' => false,
				'customizable' => [
					'visible',
					'required',
					'name',
					'required_message',
					'help',
				],
				'required' => false,
				'required_message' => 'You need to agree the SMS reminders option',
				'help' => '',
				'editing' => false,
				'can_be_removed' => false,
			],
		];
	}

}
