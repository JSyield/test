<?php

return [

	'user_roles' => [
		'administrator' => 1,
		'owner' => 2,
		'employee' => 3,
		'customer' => 4
	],

	'panels' => [
		'general'   => [ 'label' => __eva('General'), 'roles_can_edit' => [1, 2, 3] ],
		'schedules'   => [ 'label' => __eva('Schedules'), 'roles_can_edit' => [1, 2, 3] ],
		'capacity'   => [ 'label' => __eva('Capacity'), 'roles_can_edit' => [1, 2] ],
		'emails'   => [ 'label' => __eva('Emails'), 'roles_can_edit' => [1, 2] ],
	],

	'panels_old' => [
		'general'   => __eva('General'),
		'schedules' => __eva('Schedules'),
		'capacity'  => __eva('Capacity'),
		'emails' => __eva('Emails')
	],

	'settings' => [
		'general'   => [
			[
				'key'       => 'header',
				'label'     => 'GENERAL',
				'type'      => 'header',
			],
			[
				'key'       => 'help',
				'label'     => 'Some <strong>help</strong> here',
				'type'      => 'help',
			],
			[
				'key'       => 'general_1',
				'label'     => 'General 1',
				'help'      => 'Help general 1',
				'type'      => 'text',
				'placeholder' => 'Some placeholder here'
			],
			[
				'key'       => 'general_2',
				'label'     => 'General 2',
				'help'      => 'Help general 2',
				'type'      => 'text',
				'placeholder' => 'Some placeholder here'
			],
			[
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
			],
			[
				'key'       => 'general_7',
				'label'     => 'General 7',
				'help'      => 'Help general 7',
				'type'      => 'datetime',
				'placeholder' => ''
			],
		],
		'schedules' => [
			[
				'key'       => 'schedules_1',
				'label'     => 'Schedules 1',
				'help'      => 'Help schedules 1',
				'type'      => 'text',
				'placeholder' => 'Some placeholder here'
			],
		],
		'capacity'  => [
			[
				'key'       => 'capacity_1',
				'label'     => 'Capacity 1',
				'help'      => 'Help capacity 1',
				'type'      => 'text',
				'placeholder' => 'Some placeholder here'
			],
		],
		'emails'  => [
			[
				'key'       => 'emails1',
				'label'     => 'Emails 1',
				'help'      => 'Help emails 1',
				'type'      => 'text',
				'placeholder' => 'Some placeholder here'
			],
		]
	],
];
