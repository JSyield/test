<?php

// Example of how to configure the Application file config/app.php

return [
	//'class_model_prefix' => 'SRR_',
	//'class_resource_prefix' => 'SRR_Resource_',
	'class_model_prefix' => '\\Alexr\\Models\\',
	'class_resource_prefix' => '\\Alexr\\Resources\\',
	'class_setting_prefix' => '\\Alexr\\Settings\\',
	'tenant' => 'restaurants',
	'resources' => [ 'bookings', 'restaurants', 'users', ],
	'languages' => [
		'es' => 'Spanish',
		'en' => 'English'
	],
	'providers' => []
];
