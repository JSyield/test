<?php

return [
	'navigation_left' => [
		[
			'name' => 'WP',
			'name_mobile' => '<< WP',
			'href' => ALEXR_SITE_URL.'/wp-admin',
			'icon' => 'arrow-circle-left-icon',
			'current' => false
		],
	],
	'navigation_roles' => [
		'user' => [
			'home',
			'profile',
			'logout',
		],
		'administrator' => [
			'home',
			'profile',
			'restaurants',
			'users',
			'roles',
			//'customize',
			'settings_app',
			'logout',
			'separation',
			'update-popup',
			'license-popup',
			'translate',
			//'support' pendiente

			//'license',
			//'bookings',

			// All settings are now grouped in app_settings (for each tenant)
			//'settings_tenant', // Old way for defining tenant settings
			//'settings_app', // Old way for defining General settings
			//'settings_user', // Old way for defining User settings
			//'calendar',
			//'floorplanfull',
			//'floorplan',
			//'app_settings',
			//'bookings_floorplan',
			],
		//'owner' => ['home','restaurants', 'bookings', 'users', 'settings_tenant', 'settings_user', 'calendar', 'floorplan'],
		//'employee' => ['home', 'bookings', 'settings_tenant', 'settings_user', 'calendar', 'floorplan'],
		//'customer' => ['settings_user'],
		//'default' => ['home']
	],

	'navigation_items' => [
		'separation' => [
			'name' => null,
			'href' => null
		],
		'logout' => [
			'name' => __eva('Logout'),
			'name_mobile' => __eva('Logout'),
			'to' => '/logout',
			'icon' => 'arrow-circle-left-icon',
			'current' => 'logout'
		],
		/*'license' => [
			'name' => __eva('License Manager'),
			'name_mobile' => __eva('License'),
			'to' => '/app/license',
			'icon' => 'key-icon',
			'current' => 'app-license'
		],*/
		'license-popup' => [
			'name' => __eva('License'),
			'name_mobile' => __eva('License'),
			'type' => 'notification',
			'to' => 'show-license-view',
			'icon' => 'key-icon',
			'current' => 'app-license-popup'
		],
		'update-popup' => [
			'name' => __eva('Check for Updates'),
			'name_mobile' => __eva('Check for Updates'),
			'type' => 'notification',
			'to' => 'show-update-view',
			'icon' => 'cloud-download-icon',
			'current' => 'app-update-popup'
		],
		'support' => [
			'name' => __eva('Support'),
			'name_mobile' => __eva('Support'),
			'to' => '/app/support',
			'icon' => 'user-circle-icon',
			'current' => 'app-support'
		],
		'translate' => [
			'name' => __eva('Translate'),
			'name_mobile' => __eva('Translate'),
			'to' => '/app/translate',
			'icon' => 'map-icon',
			'current' => 'app-translate'
		],
		'customize' => [
			'name' => __eva('Customize'),
			'name_mobile' => __eva('Customize'),
			'to' => '/app/customize',
			'icon' => 'map-icon',
			'current' => 'app-customize'
		],
		'home' => [
			'name' => __eva('Home'),
			'name_mobile' => __eva('Home'),
			'to' => '/',
			'icon' => 'home-icon',
			'current' => 'home'
		],
		'profile' => [
			'name' => __eva('Profile'),
			'name_mobile' => __eva('Profile'),
			'to' => '/app/profile',
			'icon' => 'user-circle-icon',
			'current' => 'app-profile'
		],
		'restaurants' => [
			'name' => __eva('Restaurants'),
			'name_mobile' => __eva('Restaurants'),
			'to' => '/app/restaurants',
			'icon' => 'map-icon',
			'current' => 'app-restaurants'
		],
		'users' => [
			'name' => __eva('Users'),
			'name_mobile' => __eva('Users'),
			'to' => '/app/users',
			'icon' => 'users-icon',
			'current' => 'app-users'
		],
		'roles' => [
			'name' => __eva('Roles'),
			'name_mobile' => __eva('Roles'),
			'to' => '/app/roles',
			'icon' => 'user-icon',
			'current' => 'app-roles'
		],

		'restaurants_OLD' => [
			'name' => 'Restaurants',
			'name_mobile' => 'Restaurants',
			'to' => '/t/{tenantId}/resources/restaurants',
			'icon' => 'clipboard-list-icon',
			'current' => 'restaurants'
		],
		'users_OLD' => [
			'name' => 'Users',
			'name_mobile' => 'Users',
			'to' => '/t/{tenantId}/resources/users',
			'icon' => 'users-icon',
			'current' => 'users'
		],
		'bookings' => [
			'name' => 'Bookings',
			'name_mobile' => 'Bookings',
			'to' => '/t/{tenantId}/resources/bookings',
			'icon' => 'clipboard-list-icon',
			'current' => 'bookings'
		],
		'settings_tenant' => [
			'name' => 'Settings',
			'name_mobile' => 'Settings',
			'to' => '/t/{tenantId}/resources/restaurants/settings',
			'icon' => 'adjustments-icon',
			'current' => 'settings'
		],
		'settings_app' => [
			'name' => 'Settings',
			'name_mobile' => 'Settings',
			'to' => '/app/settings',
			'icon' => 'adjustments-icon',
			'current' => 'app-settings'
		],
		'settings_user' => [
			'name' => 'User',
			'name_mobile' => 'User',
			'to' => '/user/settings',
			'icon' => 'adjustments-icon',
			'current' => 'user-settings'
		],
		'calendar' => [
			'name' => 'Calendar',
			'name_mobile' => 'Calendar',
			'to' => '/t/{tenantId}/calendar',
			'icon' => 'adjustments-icon',
			'current' => 'calendar'
		],
		'floorplan' => [
			'name' => 'FloorPlan',
			'name_mobile' => 'FloorPlan',
			'to' => '/t/{tenantId}/floorplan',
			'icon' => 'adjustments-icon',
			'current' => 'floorplan'
		],
		'floorplanfull' => [
			'name' => 'FloorPlanFull',
			'name_mobile' => 'FloorPlanFull',
			'to' => '/t/{tenantId}/floorplanfull',
			'icon' => 'adjustments-icon',
			'current' => 'floorplanfull'
		],
		'app_settings' => [
			'name' => 'AppSettings',
			'name_mobile' => 'AppSettings',
			'to' => '/t/{tenantId}/app/settings/all',
			'icon' => 'adjustments-icon',
			'current' => 'app-settings-name'
		],
		'bookings_floorplan' => [
			'name' => 'Bookings Floor',
			'name_mobile' => 'Bookings Floor',
			'to' => '/t/{tenantId}/bookings/floorplan',
			'icon' => 'adjustments-icon',
			'current' => 'bookings-floorplan'
		]
	],

	'navigation_user' => [
		[
			'name' => 'Profile',
			'name_mobile' => 'Profile',
			'to' => '/user/settings',
			'current' => 'user-settings'
		],
		[
			'name' => 'Settings',
			'href' => '#'
		],
		[
			'name' => 'Sign Out',
			'href' => '#'
		],
	],

	'navigation_sidebar_icons' => [
		'top' => [
			[
				'name' => 'List',
				'component' => 'IconList',
				'current' => 'bookings-list-date',
				'siblings' => ['bookings-list', 'bookings-list-date', 'bookings-list-date-date'],
				'to' => '/t/{tenantId}/bookings/list'
			],
			[
				'name' => 'List Pending',
				'component' => 'IconListPending',
				'current' => 'bookings-pending',
				'siblings' => ['bookings-pending'],
				'to' => '/t/{tenantId}/bookings-pending'
			],
			[
				'name' => 'Floorplan',
				'component' => 'IconTableChairs', //'IconFloorPlan2',
				'current' => 'bookings-floorplan',
			],
			[
				'name' => 'Calendar',
				'component' => 'IconCalendar',
				'current' => 'bookings-calendar-month',
				'to' => '/t/{tenantId}/bookings/calendar'
			],
			/*[
				'name' => 'Timeline',
				'component' => 'IconTimeline',
				'current' => 'calendar',
			],*/
			[
				'name' => 'Customers',
				'component' => 'IconPerson', //'UsersIcon',
				'isHeroIcon' => true,
				'current' => 'customers-list',
				'to' => '/t/{tenantId}/customers/list'
			],
			[
				'name' => 'Statistics',
				'component' => 'IconChart', //'IconTimeline',
				'current' => 'statistics',
				'siblings' => ['statistics', 'statistics-yearmonth']
			],
			/*[
				'name' => 'Reporting',
				'component' => 'IconTimeline',
				'current' => 'reporting',
			],*/
		],
		'bottom' => [
			[
				'name' => 'Editor',
				'component' => 'IconTablesChairs', //'IconFloorPlan',
				'current' => 'floorplan',
			],
			[
				'name' => 'Settings',
				'component' => 'IconSettings', //'CogIcon',
				'isHeroIcon' => true,
				'current' => 'app-settings-name',
				'to' => '/t/{tenantId}/app/settings/general'
			],
		]
	],
];
