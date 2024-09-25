<?php

namespace Alexr\Settings;

use Alexr\Settings\Traits\LoadEmailTemplates;
use Evavel\Models\SettingSimple;
use Evavel\Models\SettingSimpleGrouped;
//use function DI\value;

class EmailTemplate extends SettingSimpleGrouped
{
	use LoadEmailTemplates;

	public $is_email = true;
	public $is_sms = false;

	const FOLDER = 'email';

	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'email_templates';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-email-template';

	public $templates;

	function settingName()
	{
		return __eva('Email Templates');
	}

	/**
	 * Default values if setting not generated yet
	 *
	 * @return array
	 */
	function defaultValue()
	{
		$this->load_templates_email();

		$list = [
			'booking_pending' => $this->get_template_email_for('booking_pending'),
			'booking_booked' => $this->get_template_email_for('booking_booked'),
			'booking_confirmed' => $this->get_template_email_for('booking_confirmed'),
			'booking_modified' => $this->get_template_email_for('booking_modified'),
			'booking_denied' => $this->get_template_email_for('booking_denied'),
			'booking_cancelled' => $this->get_template_email_for('booking_cancelled'),
			'booking_no_show' => $this->get_template_email_for('booking_no_show'),
			'booking_finished' => $this->get_template_email_for('booking_finished'),
			'booking_pending_payment' => $this->get_template_email_for('booking_pending_payment'),

			'booking_pending_admin' => $this->get_template_email_for('booking_pending_admin'),
			'booking_booked_admin' => $this->get_template_email_for('booking_booked_admin'),
			'booking_confirmed_admin' => $this->get_template_email_for('booking_confirmed_admin'),
			'booking_cancelled_admin' => $this->get_template_email_for('booking_cancelled_admin'),

			'booking_pending_enable' => 1,
			'booking_booked_enable' => 1,
			'booking_confirmed_enable' => 1,
			'booking_modified_enable' => 1,
			'booking_denied_enable' => 1,
			'booking_cancelled_enable' => 1,
			'booking_no_show_enable' => 1,
			'booking_finished_enable' => 1,
			'booking_pending_payment_enable' => 1,

			'booking_pending_admin_enable' => 1,
			'booking_booked_admin_enable' => 1,
			'booking_confirmed_admin_enable' => 1,
			'booking_cancelled_admin_enable' => 1,
		];

		return $list;
	}

	/**
	 * List of items to navigate
	 *
	 * @return array[]
	 */
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
				'label' => __eva('Booking modified (Guest)'),
				'slug' => 'booking_modified'
			],
			[
				'label' => __eva('User re-confirmed (Guest)'),
				'slug' => 'booking_confirmed'
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
			[
				'label' => __eva('Received pending (Admin)'),
				'slug' => 'booking_pending_admin'
			],
			[
				'label' => __eva('Received confirmed (Admin)'),
				'slug' => 'booking_booked_admin'
			],
			[
				'label' => __eva('Received User re-confirmed (Admin)'),
				'slug' => 'booking_confirmed_admin'
			],
			[
				'label' => __eva('Received cancelled (Admin)'),
				'slug' => 'booking_cancelled_admin'
			],
			[
				'label' => __eva('Received modified (Admin)'),
				'slug' => 'booking_modified_admin'
			],
		];
	}

	/**
	 * Fields for the admin editor
	 *
	 * @return array
	 */
	public function fields()
	{
		$this->load_templates_email();

		return [
			'booking_pending' => [
				[
					'attribute' => 'booking_pending_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_pending_enable,
				],
				[
					'attribute' => 'booking_pending',
					'stacked' => true,
					'name' => __eva('Booking Pending'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_pending'),
					'helpText' => __eva('Email sent to the guest when the reservation is still pending confirmation.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_pending'),
					'tags' => $this->tagsEmailForCustomer(),
					'tags_payment' => $this->tagsForPayments()

				]
			],
			'booking_pending_payment' => [
				[
					'attribute' => 'booking_pending_payment_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_pending_payment_enable,
				],
				[
					'attribute' => 'booking_pending_payment',
					'stacked' => true,
					'name' => __eva('Booking Pending Payment'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_pending_payment'),
					'helpText' => __eva('Email sent to the customer is has not completed the payment.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_pending_payment'),
					'tags' => $this->tagsEmailForCustomer()
				]
			],
			'booking_booked' => [
				[
					'attribute' => 'booking_booked_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_booked_enable,
				],
				[
					'attribute' => 'booking_booked',
					'stacked' => true,
					'name' => __eva('Booking Confirmed'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_booked'),
					'helpText' => __eva('Email sent to the guest when the reservation has been confirmed by the restaurant.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_booked'),
					'tags' => $this->tagsEmailForCustomer(),
					'tags_payment' => $this->tagsForPayments()
				],

			],
			'booking_confirmed' => [
				[
					'attribute' => 'booking_confirmed_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_confirmed_enable,
				],
				[
					'attribute' => 'booking_confirmed',
					'stacked' => true,
					'name' => __eva('Booking Confirmed by User'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_confirmed'),
					'helpText' => __eva('Email sent to the guest when they have reconfirmed their attendance.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_confirmed'),
					'tags' => $this->tagsEmailForCustomer(),
					'tags_payment' => $this->tagsForPayments()
				],

			],
			'booking_modified' => [
				[
					'attribute' => 'booking_modified_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_modified_enable,
				],
				[
					'attribute' => 'booking_modified',
					'stacked' => true,
					'name' => __eva('Booking Modified by User'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_modified'),
					'helpText' => __eva('Email sent to the guest after has modified the reservation.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_modified'),
					'tags' => $this->tagsEmailForCustomer(),
					'tags_payment' => $this->tagsForPayments()
				],

			],
			'booking_denied' => [
				[
					'attribute' => 'booking_denied_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_denied_enable,
				],
				[
					'attribute' => 'booking_denied',
					'stacked' => true,
					'name' => __eva('Booking Denied'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_denied'),
					'helpText' => __eva('Email sent to the guest when the reservation has been rejected by the restaurant.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_denied'),
					'tags' => $this->tagsEmailForCustomer()
				]
			],
			'booking_cancelled' => [
				[
					'attribute' => 'booking_cancelled_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_cancelled_enable,
				],
				[
					'attribute' => 'booking_cancelled',
					'stacked' => true,
					'name' => __eva('Booking Cancelled'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_cancelled'),
					'helpText' => __eva('Email sent to the guest has cancelled the booking.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_cancelled'),
					'tags' => $this->tagsEmailForCustomer()
				]
			],
			'booking_no_show' => [
				[
					'attribute' => 'booking_no_show_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_no_show_enable,
				],
				[
					'attribute' => 'booking_no_show',
					'stacked' => true,
					'name' => __eva('Booking No show'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_no_show'),
					'helpText' => __eva('Email sent to the guest has not shown up.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_no_show'),
					'tags' => $this->tagsEmailForCustomer()
				]
			],
			'booking_finished' => [
				[
					'attribute' => 'booking_finished_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the user.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_finished_enable,
				],
				[
					'attribute' => 'booking_finished',
					'stacked' => true,
					'name' => __eva('Booking Finished'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_finished'),
					'helpText' => __eva('Email sent to the guest to ask for a review.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_finished'),
					'tags' => $this->tagsEmailForCustomer()
				]
			],
			'booking_pending_admin' => [
				[
					'attribute' => 'booking_pending_admin_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the administrator.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_pending_admin_enable,
				],
				[
					'attribute' => 'booking_pending_admin_email',
					'stacked' => true,
					'name' => __eva('Email address that will receive this notification'),
					'component' => 'text-field',
					'helpText' => __eva('You can enter 1 or more emails (separated by commas).'),
					'value' => $this->booking_pending_admin_email
				],
				[
					'attribute' => 'booking_pending_admin',
					'stacked' => true,
					'name' => __eva('Booking Pending (admin)'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_pending_admin'),
					'helpText' => __eva('Email sent to the email when a new pending reservation has been received.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_pending_admin'),
					'tags' => $this->tagsEmailForAdministrator()
				]
			],
			'booking_booked_admin' => [
				[
					'attribute' => 'booking_booked_admin_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the administrator.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_booked_admin_enable,
				],
				[
					'attribute' => 'booking_booked_admin_email',
					'stacked' => true,
					'name' => __eva('Email address that will receive this notification'),
					'component' => 'text-field',
					'helpText' => __eva('You can enter 1 or more emails (separated by commas).'),
					'value' => $this->booking_booked_admin_email
				],
				[
					'attribute' => 'booking_booked_admin',
					'stacked' => true,
					'name' => __eva('Booking Confirmed (admin)'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_booked_admin'),
					'helpText' => __eva('Email sent to the email when a new reservation has been confirmed.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_booked_admin'),
					'tags' => $this->tagsEmailForAdministrator()
				]
			],
			'booking_confirmed_admin' => [
				[
					'attribute' => 'booking_confirmed_admin_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the administrator.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_confirmed_admin_enable,
				],
				[
					'attribute' => 'booking_confirmed_admin_email',
					'stacked' => true,
					'name' => __eva('Email address that will receive this notification'),
					'component' => 'text-field',
					'helpText' => __eva('You can enter 1 or more emails (separated by commas).'),
					'value' => $this->booking_confirmed_admin_email
				],
				[
					'attribute' => 'booking_confirmed_admin',
					'stacked' => true,
					'name' => __eva('Booking User Confirmed (admin)'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_confirmed_admin'),
					'helpText' => __eva('Email sent to the email when a new reservation has been confirmed by the guest.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_confirmed_admin'),
					'tags' => $this->tagsEmailForAdministrator()
				]
			],
			'booking_cancelled_admin' => [
				[
					'attribute' => 'booking_cancelled_admin_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the administrator.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_cancelled_admin_enable,
				],
				[
					'attribute' => 'booking_cancelled_admin_email',
					'stacked' => true,
					'name' => __eva('Email address that will receive this notification'),
					'component' => 'text-field',
					'helpText' => __eva('You can enter 1 or more emails (separated by commas).'),
					'value' => $this->booking_cancelled_admin_email
				],
				[
					'attribute' => 'booking_cancelled_admin',
					'stacked' => true,
					'name' => __eva('Booking Cancelled (admin)'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_cancelled_admin'),
					'helpText' => __eva('Email sent to the email when the user has cancelled the reservation.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_cancelled_admin'),
					'tags' => $this->tagsEmailForAdministrator()
				]
			],
			'booking_modified_admin' => [
				[
					'attribute' => 'booking_modified_admin_enable',
					'stacked' => true,
					'name' => __eva('Active'),
					'helpText' => __eva('Activate this email to send it to the administrator.'),
					'component' => 'boolean-field',
					'type' => 'switch',
					'value' => $this->booking_modified_admin_enable,
				],
				[
					'attribute' => 'booking_modified_admin_email',
					'stacked' => true,
					'name' => __eva('Email address that will receive this notification'),
					'component' => 'text-field',
					'helpText' => __eva('You can enter 1 or more emails (separated by commas).'),
					'value' => $this->booking_modified_admin_email
				],
				[
					'attribute' => 'booking_modified_admin',
					'stacked' => true,
					'name' => __eva('Booking Modified (admin)'),
					'component' => 'group-email-languages-field',
					'subfields' => ['subject', 'content'],
					'previewButton' => true,
					'value' => $this->valueWithLanguagesFor('booking_modified_admin'),
					'helpText' => __eva('Email sent to the email when the user has modified the reservation.'),
					'help' => [
						'subject' => __eva('Email subject. You can use tags.'),
						'content' => __eva('Email content. You can use tags.')
					],
					'template' => $this->get_template_email_for('booking_modified_admin'),
					'tags' => $this->tagsEmailForAdministrator()
				]
			],
		];
	}


	public function tagsEmailForCustomer()
	{
		$pro = '<span style="color:red">PRO: </span>';

		if (defined('ALEXR_PRO_VERSION')){
			$pro = '';
		}

		$list = [
			'restaurant' => __eva('Restaurant name'),
			'restaurant_phone' => __eva('Restaurant phone'),
			'name' => __eva('Customer name'),
			'email' => __eva('Customer email'),
			'phone' => __eva('Customer phone'),

			'service' => __eva('Shift / Event name'),
			'party' => __eva('Booking number of guests'),
			'date' => __eva('Booking date'),
			'time' => __eva('Booking time'),
			'end_time' => __eva('Booking end time'),
			'duration' => __eva('Booking duration'),
			'area_table' => __eva('Area/Table selected by user'),

			'message' => $pro.__eva('Booking message'),
			'tags' => $pro.__eva('Booking tags selected'),

			'country' => __eva('Booking country'),
			'custom_fields' => $pro.__eva('Booking custom fields'),

			'current_date' => __eva('Current date of the email sent'),

			'mybooking' => __eva('Button to view the booking'),
			'mybooking_link' => __eva('Link to view the booking'),

			'reservation_number' => __eva('Reservation number'),

			'booking_modified' => $pro.__eva('Modified reservation data'),

			'note_from_us' => $pro.__eva('Restaurant - Note from us'),
			'reservation_policy' => $pro.__eva('Restaurant - Reservation policy'),

			'add_to_calendar' => $pro.__eva('Add to calendar logos'),
			'add_to_calendar_text' => $pro.__eva('Add to calendar text'),

			'social' => $pro.__eva('Social logo links'),
			'social_text' => $pro.__eva('Social text links'),

			'site_link' => __eva('Website link'),
			'restaurant_link' => __eva('Restaurant link'),
			'restaurant_facebook' => __eva('Restaurant Facebook'),
			'restaurant_instagram' => __eva('Restaurant Instagram'),

			'payment_amount' => $pro.__eva('Payment amount'),
			'payment_receipt' => $pro.__eva('Payment receipt link'),
		];

		//$list = $this->filter_pro($list);

		$final_list = [];

		foreach($list as $key => $text) {
			$final_list['{'.$key.'}'] = $text;
		}

		return $final_list;
	}

	protected function filter_pro($list)
	{
		if (defined('ALEXR_PRO_VERSION')) {
			return $list;
		}

		$pro = [
			'note_from_us',
			'reservation_policy',
			'add_to_calendar',
			'social',
			'update_button',
			'number_reservations',
			'booking_modified'
		];

		foreach ($pro as $key) {
			unset($list[$key]);
		}

		return $list;
	}

	public function tagsEmailForAdministrator()
	{
		$list = [
			'restaurant' => __eva('Restaurant name'),
			'service' => __eva('Service name'),
			'name' => __eva('Customer name'),
			'email' => __eva('Customer email'),
			'phone' => __eva('Customer phone'),
			'party' => __eva('Booking number of guests'),
			'date' => __eva('Booking date'),
			'time' => __eva('Booking time'),
			'end_time' => __eva('Booking end time'),
			'duration' => __eva('Booking duration'),
			'message' => __eva('Booking message'),
			'tags' => __eva('Booking tags selected'),
			'country' => __eva('Booking country'),
			'custom_fields' => __eva('Booking custom fields'),
			'current_date' => __eva('Current date of the email sent'),
			'site_link' => __eva('Website link'),
			'restaurant_link' => __eva('Restaurant link'),
			'restaurant_facebook' => __eva('Restaurant Facebook'),
			'restaurant_instagram' => __eva('Restaurant Instagram'),

			'update_button' => __eva('Update Booking Status'),
			'number_reservations' => __eva('Show current number of reservations'),
			'booking_modified' => __eva('Show modified booking data if exists')
		];

		$list = $this->filter_pro($list);

		$final_list = [];

		foreach($list as $key => $text) {
			$final_list['{'.$key.'}'] = $text;
		}

		return $final_list;
	}



	/**
	 * Load json files for every language
	 * Before was using json, now uses a file html
	 * but to keep compatibility with old version I'm loading the json file first
	 * and then replacing it with the html template
	 * @return void
	 */
	protected function load_templates_email()
	{
		// Si ya estaban guardados en la DB entonces volver
		if ($this->templates) return;

		$languages = evavel_languages_allowed();

		$templates = [];
		foreach ($languages as $lang => $label)
		{
			$file = ALEXR_DIR_TEMPLATES_EMAIL.$lang.'.json';

			if (file_exists($file)){
				$json = file_get_contents($file);
				//$templates[$lang] = json_decode($json);
				// If there is a template file for the email then use it
				$templates[$lang] = $this->replaceByTemplateFiles($lang, json_decode($json, true));
			} else {
				$templates[$lang] = [];
			}
		}

		// Convert from object to array
		//$templates = evavel_json_decode(evavel_json_encode($templates), true);
		// nltobr
		$templates = $this->transformTemplates($templates);

		// Convert to Base64 all values because javascript will decode them
		$templates = alexr_convertArrayToBase64($templates);

		$this->templates = $templates;
	}

	public function tagsForPayments()
	{
		return '<div class="text-slate-400 dark:text-slate-100 text-sm">'.__eva('To include payment-related content, please insert it within these tags.').'</div>'.
		       '<div class="text-slate-400 dark:text-slate-100 text-sm">'.__eva('The content will be shown only if the booking has a payment attached.').'</div>'.
		       '<br>'.'<span class="inline-block ml-6 bg-slate-200 text-slate-700 p-2 rounded-md">[payment]</span>'.
		       '<br><div class="ml-6 mt-4">'.__eva('....content here...').'</div>'.
		       '<br>'.'<span class="inline-block ml-6 bg-slate-200 text-slate-700 p-2 rounded-md">[/payment]</span>';
	}

}
