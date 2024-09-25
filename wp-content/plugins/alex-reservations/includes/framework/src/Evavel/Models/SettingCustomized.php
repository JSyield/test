<?php

namespace Evavel\Models;

use Evavel\Http\Request\AppSettingsRequest;

class SettingCustomized extends Setting
{
	public static $custom_component = 'CustomComponentName';

	public static function configuration(AppSettingsRequest $request)
	{
		return [
			'mode' => 'SettingCustomized',
			'component' => static::$custom_component
		];
	}

	public static function getId($tenantId)
	{
		return null;
	}
}
