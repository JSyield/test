<?php

namespace Evavel\Models;

// One setting with a list of fields
use Evavel\Http\Request\AppSettingsRequest;

class SettingSimple extends Setting
{
	public static function configuration(AppSettingsRequest $request)
	{
		return [
			'mode' => 'SettingSimple'
		];
	}
}
