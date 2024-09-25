<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Alexr\Settings\Traits\LoadEmailTemplates;
use Evavel\Models\SettingListing;

class EmailReminder extends SettingListing
{
	use LoadEmailTemplates;

	public $is_email = true;
	public $is_sms = false;

	// Where email templates are stored
	const FOLDER = 'email-reminders';

	public static $meta_key = 'email_reminder';
	public static $table_name = 'restaurant_setting';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-email-reminder';

	public $templates;

	protected $casts = [
		'email_active' => 'boolean',
	];

	public static function description() {
		return __eva('When the reservation is approaching, make sure to send reminders to your clients.');
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function fields()
	{
		return [
			[
				'attribute' => 'for_shifts',
				'stacked' => true,
				'style' => 'display: inline-block; width: 10%; vertical-align: top;',
				'name' => __eva('Use For Shifts'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->for_shifts
			],
			[
				'attribute' => 'for_events',
				'stacked' => true,
				'style' => 'display: inline-block; width: 10%; vertical-align: top;',
				'name' => __eva('Use For Events'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->for_events
			],
			[
				'attribute' => 'name',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 80%;',
				'inputClass' => 'form-input-bordered-highlight',
				'name' => __eva('Name'),
				'helpText' => __eva('Use this field to identity your reminder'),
				'component' => 'text-field',
				'value' => $this->name
			],

			[
				'attribute' => 'email_active',
				'stacked' => true,
				'style' => 'display: inline-block; width: 20%; vertical-align: top;',
				'name' => __eva('Send this email'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->email_active
			],
			[
				'attribute' => 'email_time',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%; vertical-align: top;',
				'name' => __eva('When should be sent'),
				'component' => 'select-field',
				'options' => $this->getListOfTimes(),
				'value' => $this->email_time
			],
			[
				'attribute' => 'email_reminder',
				'stacked' => true,
				'name' => __eva('Email reminder'),
				'component' => 'group-email-languages-field',
				'subfields' => ['subject', 'content'],
				'previewButton' => true,
				'helpText' => __eva('Send this email as a reminder to the customer'),
				'help' => [
					'subject' => __eva('Email subject.'),
					'content' => __eva('Email content.')
				],
				'template' => $this->get_template_email_for('reminder'),
				'value' => $this->valueWithLanguagesFor('email_reminder'),
				'tags' => $this->tagsEmailForCustomer(),
			],
			/*[
				'attribute' => 'sms_active',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 30%; vertical-align: top;',
				'name' => __eva('SMS active'),
				'component' => 'boolean-field',
				'type' => 'switch',
				'value' => $this->sms_active
			]*/
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
			'name' => __('Reminder'),
			'for_shifts' => true,
			'for_events' => true,
			'email_active' => true,
			'email_time' => 7200,
			'email_reminder' => $this->get_template_email_for('reminder')
			//'sms_active' => true
		];
	}

	protected function load_templates_email()
	{
		if ($this->templates) return;

		$languages = evavel_languages_allowed();

		$templates = [];
		foreach ($languages as $lang => $label)
		{
			$file = ALEXR_DIR_TEMPLATES_EMAIL_REMINDERS.$lang.'.json';

			$json_decoded = [
				'reminder' => [
					'subject' => 'Email reminder',
					'content' => 'Email content'
				]
			];

			$templates[$lang] = $this->replaceByTemplateFiles($lang, $json_decoded);
		}

		// Convert from object to array
		//$templates = evavel_json_decode(evavel_json_encode($templates), true);
		// nltobr
		$templates = $this->transformTemplates($templates);

		// Convert to Base64 all values because javascript will decode them
		$templates = alexr_convertArrayToBase64($templates);

		$this->templates = $templates;
	}


	public function tagsEmailForCustomer()
	{
		$pro = '<span style="color:red">PRO: </span>';
		if (defined('ALEXR_PRO_VERSION')){ $pro = ''; }

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
			'area_table' => __eva('Area/Table selected by user'),

			'message' => __eva('Booking message'),
			'tags' => __eva('Booking tags selected'),

			'current_date' => __eva('Current date of the email sent'),

			'mybooking' => __eva('Button to view the booking'),
			'mybooking_link' => __eva('Link to view the booking'),

			'reservation_number' => __eva('Reservation number'),

			'note_from_us' => __eva('Restaurant - Note from us'),
			'reservation_policy' => __eva('Restaurant - Reservation policy'),

			'add_to_calendar' => $pro.__eva('Add to calendar logos'),
			'add_to_calendar_text' => $pro.__eva('Add to calendar text'),

			'social' => $pro.__eva('Social logo links'),
			'social_text' => $pro.__eva('Social text links'),


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
