<?php

return [
    //'class_model_prefix' => 'SRR_',
    //'class_resource_prefix' => 'SRR_Resource_',

	'class_model_prefix' => '\\Alexr\\Models\\',
    'class_resource_prefix' => '\\Alexr\\Resources\\',
	'class_setting_prefix' => '\\Alexr\\Settings\\',

    'tenant' => 'restaurants',
    'resources' => [
		'bookings',
	    'customers',
	    'restaurants',
	    'users',
	    'floors',
	    'areas',
	    'tables',
	    'combinations',
	    'ctaggroups',
	    'users'
    ],
	// https://www.science.co.il/language/Codes.php
    'languages' => [
        'en' => __eva('English'),
        'es' => __eva('Spanish'),
	    'de' => __eva('German'),
	    'da' => __eva('Danish'),
	    'fr' => __eva('French'),
	    'nl' => __eva('Dutch'),
	    'it' => __eva('Italian'),
        'pt' => __eva('Portuguese'),
	    'no' => __eva('Norwegian'),
        'sv' => __eva('Swedish'),
        'fi' => __eva('Finnish'),
        'pl' => __eva('Polish'),
        'el' => __eva('Greek'),
	    'tr' => __eva('Turkish'),
	    'hr' => __eva('Croatian'),
	    'cs' => __eva('Czech'),
	    'hu' => __eva('Hungarian'),
	    'ro' => __eva('Romanian'),
        'hi' => __eva('Hindi'),
        'zh' => __eva('Chinese'),
	    'ja' => __eva('Japanese'),
	    'ar' => __eva('Arabic'),
    ],

	'providers' => [
		\Alexr\Providers\AppServiceProvider::class,
		\Alexr\Providers\EventServiceProvider::class,
		\Alexr\Providers\AuthServiceProvider::class
	],

	// luxon
	'date_formats' => [
		'dmy' => 'dd LLLL yyyy',
		'mdy' => 'LLLL dd yyyy'
	],
	'date_formats_carbon' => [
		'dmy' => 'j F Y',
		'mdy' => 'F j Y'
	],
];
