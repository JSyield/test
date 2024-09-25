<?php

namespace Alexr\Settings;

use Alexr\Settings\Traits\LoadEmailTemplates;
use Evavel\Models\SettingSimpleGrouped;

class SmsTemplate extends SettingSimpleGrouped
{
	use LoadEmailTemplates;

	public $is_email = false;
	public $is_sms = true;

	const FOLDER = 'sms';

	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'sms_templates';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-email-template'; // @todo

	public $templates;

	function settingName()
	{
		return __eva('Sms Templates');
	}

	function getTemplateKeys()
	{
		$keys = [
			'booking_pending',
			'booking_pending_payment',
			'booking_booked',
			'booking_denied',
			'booking_cancelled',
			'booking_no_show',
			'booking_finished'
		];

		return $keys;
	}

	function defaultValue()
	{
		$this->load_templates_email();

		$list = [];

		$keys = $this->getTemplateKeys();

		foreach($keys as $key) {
			$list[$key] = $this->get_template_email_for($key);
			$list[$key.'_enable'] = 1;
		}

		return $list;
	}

	public function listItems()
	{
		return [
			[
				'label' => __eva('Booking pending (Guest)'),
				'slug' => 'booking_pending'
			],
			[
				'label' => __eva('Booking pending payment (Guest)'),
				'slug' => 'booking_pending_payment'
			],
			[
				'label' => __eva('Booking confirmed (Guest)'),
				'slug' => 'booking_booked'
			],
			[
				'label' => __eva('Booking denied (Guest)'),
				'slug' => 'booking_denied'
			],
			[
				'label' => __eva('Booking cancelled (Guest)'),
				'slug' => 'booking_cancelled'
			],
			[
				'label' => __eva('Booking no show (Guest)'),
				'slug' => 'booking_no_show'
			],
			[
				'label' => __eva('Booking finished (Guest)'),
				'slug' => 'booking_finished'
			],
		];
	}

	public function fields()
	{
		$keys = $this->getTemplateKeys();

		$list = [];

		$keys = [
			'booking_pending' => [__eva('Booking Pending'), __eva('SMS sent to the guest when the reservation is pending confirmation')],
			'booking_pending_payment' => [__eva('Booking Pending Payment'), __eva('SMS sent to the guest when the reservation is pending payment')],
			'booking_booked' => [__eva('Booking Confirmed'), __eva('SMS sent to the guest when the reservation has been confirmed by the restaurant')],
			'booking_denied' => [__eva('Booking Denied'), __eva('SMS sent to the guest when the reservation has been rejected')],
			'booking_cancelled' => [__eva('Booking Cancelled'), __eva('SMS sent to the guest has cancelled the booking')],
			'booking_no_show' => [__eva('Booking No-show'), __eva('SMS sent to the guest has not shown up')],
			'booking_finished' => [__eva('Booking Confirmed'), __eva('SMS sent to the guest to ask for a review')],
		];

		foreach ($keys as $key => $labels)
		{
			$list[$key] = [
				[
					'attribute' => $key.'_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this SMS to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->{$key.'_enable'},
				],
				[
					'attribute' => $key,
					'stacked' => true,
					'name' => $labels[0],
					'component' => 'group-sms-languages-field',
					'subfields' => ['content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor($key),
					'helpText' => $labels[1],
					'help' => [
						'content' => __eva('SMS message. You can use tags.')
					],
					'template' => $this->get_template_email_for($key),
					'tags' => $this->tagsEmailForCustomer(),
				]
			];
		}

		return $list;
	}

	protected function load_templates_email()
	{
		if ($this->templates) return;

		$languages = evavel_languages_allowed();

		$templates = [];
		foreach ($languages as $lang => $label)
		{
			// Have to load all templates
			$keys = $this->getTemplateKeys();

			foreach($keys as $key)
			{
				$file = ALEXR_DIR_TEMPLATES_SMS."{$lang}/{$key}.html";

				if (file_exists($file)){
					$content = file_get_contents($file);
				} else {
					$content = 'Write your SMS message';
				}

				$templates[$lang][$key] = [
					'content' => $content
				];
			}
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
