<?php

// For example will be: SRR_Booking
function evavel_model_prefix()
{
    return evavel_config('app.class_model_prefix');
}

// For example will be: SRR_Resource_Booking
function evavel_resource_prefix()
{
    return evavel_config('app.class_resource_prefix');
}

function evavel_setting_prefix()
{
	return evavel_config('app.class_setting_prefix');
}

function evavel_resource_to_model_class($resource_class)
{
	return str_replace('Resources', 'Models', $resource_class);
	//return str_replace('_Resource', '', $resource_class);
}
