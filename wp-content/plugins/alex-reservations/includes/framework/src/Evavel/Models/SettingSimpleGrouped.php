<?php

namespace Evavel\Models;

// List of items (left sidebar) + fields for each item
use Evavel\Http\Request\AppSettingsRequest;

class SettingSimpleGrouped extends Setting
{
	public static function configuration(AppSettingsRequest $request)
	{
		return [
			'mode' => 'SettingSimpleGrouped',
		];
	}
}
