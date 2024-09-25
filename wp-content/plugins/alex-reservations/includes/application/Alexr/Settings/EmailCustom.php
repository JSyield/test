<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Alexr\Settings\Traits\LoadEmailTemplates;
use Evavel\Models\SettingListing;

class EmailCustom extends SettingListing
{
	use LoadEmailTemplates;

	public $is_email = true;
	public $is_sms = false;

	const FOLDER = 'email';

	public static $meta_key = 'email_custom';
	public static $table_name = 'restaurant_setting';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-email-custom';

	public $templates;

	public static function description()
	{
		return __eva('Create customized emails to send to your customers. You can access them from the booking reply button.');
	}

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	public function fields()
	{
		return [
			[
				'attribute' => 'name',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 80%;',
				'inputClass' => 'form-input-bordered-highlight',
				'name' => __eva('Name'),
				'helpText' => __eva('Use this field to identity your email'),
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
				'attribute' => 'email_custom',
				'stacked' => true,
				'name' => __eva('Email'),
				'component' => 'group-email-languages-field',
				'subfields' => ['subject', 'content'],
				'previewButton' => true,
				'helpText' => __eva('Send this email to the customer'),
				'help' => [
					'subject' => __eva('Email subject.'),
					'content' => __eva('Email content.')
				],
				'template' => $this->get_template_email_for('custom'),
				'value' => $this->valueWithLanguagesFor('email_custom'),
				'tags' => $this->tagsEmailForCustomer(),
			],
		];
	}

	public function defaultValue()
	{
		return [
			'name' => __('Custom email'),
			'email_active' => true,
			'email_custom' => $this->get_template_email_for('custom')
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
				'custom' => [
					'subject' => 'Email subject',
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
