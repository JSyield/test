<?php

return [
	'panels' => [
		'loginpage'   => __eva('Login page'),
		'dashboard' => __eva('Dashboard'),
		'authentication' => __eva('Authentication'),
		//'mobile' => __eva('Mobile'),
		'saas' => __eva('SAAS')
	],

	'settings' => [
		'loginpage'   => [
			[
				'key'       => 'header',
				'label'     => __eva('Login page'),
				'type'      => 'header',
			],
			[
				'key'       => 'help',
				'label'     => __eva('Customize the style of the login page.'),
				'type'      => 'help',
			],
			[
				'key'       => 'login_language',
				'label'     => __eva('Language'),
				'help'      => false,
				'type'      => 'select',
				'options'   => evavel_languages_all(),
				'placeholder' => __eva('Select language')
			],
			[
				'key'       => 'login_logo',
				'label'     => __eva('Logo'),
				'help'      => __eva('Logo for the custom login page'),
				'type'      => 'image', // component ImageSetting from ./application/fields/Form/ImageField
				//'type'      => 'image-upload-file',
				'placeholder' => 'Some placeholder here',
				'options' => [
					'accept'    => 'image/png, image/jpeg',
					'maxWidth'  => 750,
					'maxHeight' => 250,
					'resize' => true
				],
			],
			[
				'key'       => 'login_back_color',
				'label'     => __eva('Background color'),
				'help'      => __eva('Leave it empty for default background'),
				'type'      => 'color',
				'placeholder' => ''
			],
			[
				'key'       => 'login_text_color',
				'label'     => __eva('Text color'),
				'help'      => __eva('For title and content'),
				'type'      => 'color',
				'placeholder' => ''
			],
			[
				'key'       => 'login_page_title',
				'label'     => __eva('Page title'),
				'help'      => false,
				'type'      => 'text',
				'placeholder' => ''
			],
			[
				'key'       => 'login_page_desc_1',
				'label'     => __eva('Text below title'),
				'help'      => false,
				'type'      => 'textarea',
				'placeholder' => ''
			],
			[
				'key'       => 'login_page_desc_2',
				'label'     => __eva('Text below form'),
				'help'      => false,
				'type'      => 'textarea',
				'placeholder' => ''
			],
			/*[
				'key'       => 'general_3',
				'label'     => 'General 3',
				'help'      => 'Help general 3',
				'type'      => 'textarea',
				'placeholder' => 'Some placeholder here'
			],
			[
				'key'       => 'general_4',
				'label'     => 'General 4',
				'help'      => 'Help general 4',
				'type'      => 'toggle',
				'placeholder' => 'Some placeholder here'
			],
			[
				'key'       => 'general_5',
				'label'     => 'General 5',
				'help'      => 'Help general 5',
				'type'      => 'select',
				'options'   => [
					'option_1' => 'Option 1',
					'option_2' => 'Option 2',
					'option_3' => 'Option 3'
				],
			],
			[
				'key'       => 'general_6',
				'label'     => 'General 6',
				'help'      => 'Help general 6',
				'type'      => 'multiselect',
				'options'   => [
					'option_1' => 'Option 1',
					'option_2' => 'Option 2',
					'option_3' => 'Option 3'
				],
			],*/
		],
		'dashboard' => [
			[
				'key'       => 'header',
				'label'     => __eva('Dashboard page'),
				'type'      => 'header',
			],
			[
				'key'       => 'dashboard_page_title',
				'label'     => __eva('Page title'),
				'help'      => false,
				'type'      => 'text',
				'placeholder' => 'My restaurant name'
			],
			[
				'key'       => 'dashboard_page_favicon',
				'label'     => __eva('Favicon url'),
				'help'      => __eva('32x32 png format'),
				'type'      => 'text',
				'placeholder' => 'https://'
			],
			[
				'key'       => 'dashboard_menu_message',
				'label'     => __eva('Sidebar menu message'),
				'help'      => __eva('Put some message in the sidebar menu at the bottom'),
				'type'      => 'text',
				'placeholder' => 'With love from XYZ'
			],
			[
				'key'       => 'dashboard_menu_message_link',
				'label'     => __eva('Link for sidebar menu message'),
				'help'      => __eva('Leave it empty for no link'),
				'type'      => 'text',
				'placeholder' => 'https://'
			],
		],
		'authentication' => [
			[
				'key'       => 'header',
				'label'     => __eva('Authentication'),
				'type'      => 'header',
			],
			[
				'key'       => 'help',
				'label'     => __eva('When user logs in using email and pin code a token will be generated valid for one year.')
				               .'<br>'.__eva('This token will auto-login the user during this period without the need of entering email/pin again.')
								.'<br>'.__eva('Enabled by default. You can disable it here.')
								.'<br>'.__eva('Clearing the tokens will force users to login again when WP cookies have expired.')
				,
				'type'      => 'help',
			],
			[
				'key'       => 'auth_token_disable',
				'label'     => __eva('Disable auth code'),
				'help'      => false,
				'type'      => 'toggle',
				'options'   => [
					'inactive' => __eva('Disabled'),
					'active' => __eva('Enabled'),
				],
				'placeholder' => __eva('Select option')
			],
			[
				'key'       => 'auth_token_clear',
				'label'     => __eva('Clear tokens'),
				'help'      => false,
				'type'      => 'tokens',
			],
		],
		'mobile' => [
			[
				'key'       => 'header',
				'label'     => __eva('Mobile dashboard'),
				'type'      => 'header',
			],
			[
				'key'       => 'help',
				'label'     => __eva('The interface for desktop and mobile is different.')
				               .'<br>'.__eva('The system will detect the screen width and will load the appropiate dashboard.')
				               .'<br>'.__eva('Legacy dashboard: the first version is loading the full dashboard with a responsive interface, which makes it slower when loading on mobile.')
				               .'<br>'.__eva('The current version has a separate dashboard to make it faster when loading from a mobile.')
				,
				'type'      => 'help',
			],
			[
				'key'       => 'dashboard_use_new_mobile_app',
				'label'     => __eva('Mobile dashboard'),
				'help'      => __eva('By default desktop and mobile dashboards are different. This will make mobile app to load faster. You can disable it and load the legacy desktop-mobile version.'),
				//'type'      => 'toggle',
				'type'      => 'select',
				'options'   => [
					'active' => __eva('Load separate dashboards (new)'),
					'inactive' => __eva('Load desktop dashboard (legacy)'),
				],
				'placeholder' => __eva('Select option')
			],
		],
		'saas' => [
			[
				'key'       => 'header',
				'label'     => __eva('SAAS settings'),
				'type'      => 'header',
			],
			[
				'key'       => 'help',
				'label'     => __eva('Customize some settings to make it work for a SAAS solution.'),
				'type'      => 'help',
			],
			[
				'key'       => 'subheader',
				'label'     => __eva('Login page'),
				'sublabel'  => __eva('If you do not want to use the default AR login page, you can use your own login page.'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'login_page_redirect',
				'label'     => __eva('Redirect login page to another page'),
				'help'      => false,
				'type'      => 'select',
				'options'   => [
					'yes' => __eva('YES. User will login from a different link'),
					'no' => __eva('NO. Use default login page from AlexReservations'),
				],
				'placeholder' => __eva('Select option')
			],
			[
				'key'       => 'login_page_redirect_link',
				'label'     => __eva('Custom login page'),
				'help'      => __eva('Only if you have selected YES'),
				'type'      => 'text',
				'placeholder' => 'https://...'
			],
			[
				'key'       => 'redirect_to_dashboard',
				'label'     => __eva('Redirect to dashboard after login'),
				'help'      => false,
				'type'      => 'select',
				'options'   => [
					'no' => __eva('NO. Let WordPress manage the redirection'),
					'yes' => __eva('YES. Redirect after login for restaurant managers')
				],
				'placeholder' => __eva('Select option')
			],


			[
				'key'       => 'subheader',
				'label'     => __eva('Dashboard main menu'),
				'sublabel'  => __eva('The menu menu (top-left logo) has a back link to WP admin. You can change this link.'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'redirect_to_wp',
				'label'     => __eva('Redirect from the left menu (restaurant managers)'),
				'help'      => false,
				'type'      => 'select',
				'options'   => [
					'yes' => __eva('YES. Redirect to a custom link'),
					'no' => __eva('NO. No link is needed.'),

				],
				'placeholder' => __eva('Select option')
			],
			[
				'key'       => 'redirect_to_wp_custom_url',
				'label'     => __eva('Custom URL to redirect (hidden menu at the top)'),
				'help'      => __eva('Use this if you have selected "Redirect to a custom link"'),
				'type'      => 'text',
				'placeholder' => 'https://...'
			],
			[
				'key'       => 'redirect_to_button_title',
				'label'     => __eva('Redirect button tittle (hidden menu at the top)'),
				'help'      => __eva('Use this if you have selected "Redirect to a custom link"'),
				'type'      => 'text',
				'placeholder' => 'Back to'
			],


			[
				'key'       => 'subheader',
				'label'     => __eva('Initial view'),
				'sublabel'  => __eva('You can select which view will be loaded first when launching the dashboard.'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'first_page_to_show',
				'label'     => __eva('First page that should be loaded when launching the dashboard'),
				'help'      => false,
				'type'      => 'select',
				'options'   => [
					'/' => __eva('Default home page'),
					'current:bookings-list-date' => __eva('Reservations List view'),
					'current:bookings-calendar-month' => __eva('Reservations Monthly view'),
				],
				'placeholder' => __eva('Select option')
			],


			[
				'key'       => 'subheader',
				'label'     => __eva('Top-Left logo'),
				'sublabel'  => __eva('Put your own logo here.'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'logo_custom_image_url',
				'label'     => __eva('Logo image url (top-left icon)'),
				'help'      => __eva('Leave it empty to keep the default logo'),
				'type'      => 'text',
				'placeholder' => 'https://'
			],


			[
				'key'       => 'subheader',
				'label'     => __eva('Events menu'),
				'sublabel'  => __eva('You can enable the events settings in the sidebar menu with icons'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'saas_enable_events_menu',
				'label'     => __eva('Enable events in the sidebar menu'),
				'help'      => false,
				'type'      => 'toggle',
			],
			[
				'key'       => 'subheader',
				'label'     => __eva('Info windows'),
				'sublabel'  => __eva('Enable this option to hide all info icons and windows in the dashboard'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'saas_hide_info_windows',
				'label'     => __eva('Hide info windows'),
				'help'      => false,
				'type'      => 'toggle',
			],


			[
				'key'       => 'subheader',
				'label'     => __eva('BASIC version'),
				'sublabel'  => __eva('The basic version shows a custom message for specific modules not active. Change to the link to your own page.'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'saas_custom_link_module_not_available',
				'label'     => __eva('Custom link when module is not available'),
				'help'      => __eva('Leave it empty to keep the default link to https://alexreservations.com'),
				'type'      => 'text',
				'placeholder' => 'https://'
			],


			[
				'key'       => 'subheader',
				'label'     => __eva('Reservation form'),
				'sublabel'  => __eva('You can add your logo/link to the bottom of the reservation form.'),
				'type'      => 'subheader',
			],
			[
				'key'       => 'saas_widget_use_custom_logo',
				'label'     => __eva('Widget. Allow custom logo & link'),
				'help'      => false,
				'type'      => 'toggle',
			],
			[
				'key'       => 'saas_widget_logo_image',
				'label'     => __eva('Widget. Logo image url'),
				'help'      => __eva('Image url'),
				'type'      => 'text',
				'placeholder' => 'https://'
			],
			[
				'key'       => 'saas_widget_logo_link',
				'label'     => __eva('Widget. Logo link'),
				'help'      => __eva('Logo link'),
				'type'      => 'text',
				'placeholder' => 'https://'
			],
		]
	],
];
