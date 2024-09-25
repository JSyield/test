<?php

Use Evavel\Eva;
// Load All Providers

// Load framework providers
// Only APP providers should be loaded and
// should call the parent framework methods

// Will be called for each plugin using the framework so be sure it is called only once
global $EvaBootstrapped;
if ($EvaBootstrapped) return;
$EvaBootstrapped = true;

$providers = [];

// Check if alex reservations is used
if (function_exists('cplus_application_config')) {
	foreach(cplus_application_config('app.providers', []) as $provider){
		$providers[] = new $provider;
	}
}

Eva::registerProviders($providers);
Eva::bootProviders($providers);
