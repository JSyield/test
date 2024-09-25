<?php

// Used for each restaurant
return [
	[
		'label' => 'General',
		'slug' => 'main',
		'submenu' => [
			['label' => 'Restaurant', 'settingName' => 'general', 'help_online' => ['restaurant-settings']],
			['label' => 'Dashboard', 'settingName' => 'dashboard']
			//['label' => 'Profile', 'settingName' => 'profile'],
		]
	],
	[
		'label' => 'Availability',
		'slug' => 'availability',
		'submenu' => [
			['label' => 'Shifts', 'settingName' => 'shifts', 'help_online' => ['shifts-general']],
			['label' => 'Events', 'settingName' => 'events', 'help_online' => ['shifts-general']],
			['label' => 'Closed Days', 'settingName' => 'closeddays', 'help_online' => ['settings-closed-days']],
			//['label' => 'Block Hours', 'settingName' => 'blocked_hours'],
			//['label' => 'Waitlists', 'settingName' => 'waitlists'],
		]
	],
	[
		'label' => 'Widget',
		'slug' => 'widget',
		'submenu' => [
			//['label' => 'Install', 'settingName' => 'widgetinstall'],
			['label' => 'Form', 'settingName' => 'widgetform', 'help_online' => ['widget-create',
				'widget-settings','widget-schedules','widget-available-slots',
				'widget-front-end','widget-page-builder'
				]],
			['label' => 'Messages', 'settingName' => 'widgetmessage', 'help_online' => ['widget-messages']],
			// pending ['label' => 'Booking', 'settingName' => 'widgetbooking'],
		]
	],
	[
		'label' => 'Notifications',
		'slug' => 'email',
		'submenu' => [
			['label' => 'Configuration', 'settingName' => 'email_config'],
			['label' => 'Emails', 'settingName' => 'email_templates', 'help_online' => ['settings-templates']],
			['label' => 'Custom emails', 'settingName' => 'email_custom'],
			['label' => 'Email reminders', 'settingName' => 'email_reminders', 'help_online' => ['settings-reminders']],
			['label' => 'SMS', 'settingName' => 'sms_templates', 'help_online' => ['sms-templates']],
			['label' => 'SMS reminders', 'settingName' => 'sms_reminders', 'help_online' => ['sms-reminders']],
		]
	],
	/*[
		'label' => 'Pre-payments',
		'slug' => 'prepayments',
		'submenu' => [
			['label' => 'Rules', 'settingName' => 'payrules'],
			['label' => 'Offers', 'settingName' => 'payoffers'],
		]
	],*/
	[
		'label' => 'Customers',
		'slug' => 'customers',
		'submenu' => [
			//['label' => 'Categories', 'settingName' => 'cgroups'],
			['label' => 'Tags', 'settingName' => 'customer_tags', 'help_online' => ['customers-tags-general']],
			//['label' => 'Feedback', 'settingName' => 'feedback'],
		]
	],
	[
		'label' => 'Bookings',
		'slug' => 'bookings',
		'submenu' => [
			//['label' => 'Categories', 'settingName' => 'bgroups'],
			['label' => 'Tags', 'settingName' => 'booking_tags', 'help_online' => ['bookings-tags-general']],
		]
	],

	[
		'label' => 'Services',
		'slug' => 'services',
		'submenu' => [
			//['label' => 'Google', 'settingName' => 'google'],
			//['label' => 'Facebook', 'settingName' => 'facebook'],
			['label' => 'Payments', 'settingName' => 'payments', 'help_online' => [
				'payments-settings', 'payments-shifts', 'payments-frontend',
				'payments-dashboard', 'payments-emails', 'payments-store-cards']],
			['label' => 'Social Channels', 'settingName' => 'social_channels', 'help_online' => 'social-channels'],
			//['label' => 'Google Reserve', 'settingName' => 'google_reserve']
		]
	],
];

