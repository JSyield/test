<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Alexr\Settings\Traits\LoadEmailTemplates;
use Evavel\Models\SettingListing;

class SmsReminder extends SettingListing
{
	use LoadEmailTemplates;

	public $is_email = false;
	public $is_sms = true;

	const FOLDER = 'sms-reminders';

	public static $meta_key = 'sms_reminder';
	public static $table_name = 'restaurant_setting';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-sms-reminder';

	public $templates;

	protected $casts = [
		'sms_active' => 'boolean',
	];

	public static function description() {
		return __eva('When the reservation is approaching, make sure to send reminders to your clients.');
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function fields() {

		return [
			[
				'attribute' => 'for_shifts',
				'stacked'   => true,
				'style'     => 'display: inline-block; width: 10%; vertical-align: top;',
				'name'      => __eva( 'Use For Shifts' ),
				'component' => 'boolean-field',
				'type'      => 'switch',
				'value'     => $this->for_shifts
			],
			[
				'attribute' => 'for_events',
				'stacked'   => true,
				'style'     => 'display: inline-block; width: 10%; vertical-align: top;',
				'name'      => __eva( 'Use For Events' ),
				'component' => 'boolean-field',
				'type'      => 'switch',
				'value'     => $this->for_events
			],
			[
				'attribute'  => 'name',
				'stacked'    => true,
				//'style' => 'display: inline-block; width: 80%;',
				'inputClass' => 'form-input-bordered-highlight',
				'name'       => __eva( 'Name' ),
				'helpText'   => __eva( 'Use this field to identity your reminder' ),
				'component'  => 'text-field',
				'value'      => $this->name
			],
			[
				'attribute' => 'sms_active',
				'stacked'   => true,
				'style'     => 'display: inline-block; width: 20%; vertical-align: top;',
				'name'      => __eva( 'Send this SMS' ),
				'component' => 'boolean-field',
				'type'      => 'switch',
				'value'     => $this->sms_active
			],
			[
				'attribute' => 'sms_time',
				'stacked'   => true,
				'style'     => 'display: inline-block; width: 50%; vertical-align: top;',
				'name'      => __eva( 'When should be sent' ),
				'component' => 'select-field',
				'options'   => $this->getListOfTimes(),
				'value'     => $this->sms_time
			],
			[
				'attribute' => 'sms_reminder',
				'stacked' => true,
				'name' => __eva('Sms reminder'),
				'component' => 'group-sms-languages-field',
				'subfields' => ['content'],
				'previewButton' => true,
				'helpText' => __eva('Send this SMS as a reminder to the customer'),
				'help' => [
					'content' => __eva('SMS content.')
				],
				'template' => $this->get_template_email_for('reminder'),
				'value' => $this->valueWithLanguagesFor('sms_reminder'),
				'tags' => $this->tagsEmailForCustomer(),
			],
		];
	}

	protected function getListOfTimes()
	{
		$options = [
			['label' => __eva('1 hour before'), 'value' => 3600],
		];

		for ($i = 2; $i <= 48; $i++) {
			$label = $i . ' ' .__eva('hours before');
			if ($i == 24) {
				$label .= ' ' . __eva('(1 day before)');
			} else if ($i == 36) {
				$label .= ' ' . __eva('(1.5 days before)');
			} else if ($i == 48) {
				$label .= ' ' . __eva('(2 days before)');
			}
			$options[] = ['label' => $label, 'value' => 3600 * $i];
		}

		for($i = 3; $i <= 30; $i++) {
			$value = $i * 24 * 3600;
			$label = str_replace('{x}', $i, __eva('{x} days before'));
			$options[] = ['label' => $label, 'value' => $value];
		}

		return $options;
	}

	public function defaultValue()
	{
		return [
			'name' => __('SMS Reminder'),
			'for_shifts' => true,
			'for_events' => true,
			'sms_active' => true,
			'sms_time' => 7200,
			'sms_reminder' => $this->get_template_email_for('reminder')
		];
	}

	protected function load_templates_email()
	{
		if ($this->templates) return;

		$languages = evavel_languages_allowed();

		$templates = [];
		foreach ($languages as $lang => $label)
		{
			$file = ALEXR_DIR_TEMPLATES_SMS_REMINDERS.$lang.'.json';

			$json_decoded = [
				'reminder' => [
					'content' => 'Email content'
				]
			];

			$templates[$lang] = $this->replaceByTemplateFiles($lang, $json_decoded);
		}

		$templates = $this->transformTemplates($templates);

		// Convert to Base64 all values because javascript will decode them
		$templates = alexr_convertArrayToBase64($templates);

		$this->templates = $templates;
	}

	public function tagsEmailForCustomer()
	{
		$list = [
			'restaurant' => __eva('Restaurant name'),
			'restaurant_phone' => __eva('Restaurant phone'),
			'name' => __eva('Customer name'),
			'email' => __eva('Customer email'),
			'phone' => __eva('Customer phone'),
			'party' => __eva('Booking number of guests'),
			'date' => __eva('Booking date'),
			'time' => __eva('Booking time'),
			'end_time' => __eva('Booking end time'),
			'duration' => __eva('Booking duration'),

			'current_date' => __eva('Current date of the email sent'),
			'mybooking' => __eva('Link to view the booking'),
			'reservation_number' => __eva('Reservation number'),

			'site_link' => __eva('Website link'),
			'restaurant_link' => __eva('Restaurant link'),
			'restaurant_facebook' => __eva('Restaurant Facebook'),
			'restaurant_instagram' => __eva('Restaurant Instagram'),
		];

		$final_list = [];

		foreach($list as $key => $text) {
			$final_list['{'.$key.'}'] = $text;
		}

		return $final_list;
	}
}
