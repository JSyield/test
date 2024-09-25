<?php

namespace Evavel\Models;

use Evavel\Http\Request\AppSettingsRequest;

class SettingListing extends Setting
{
	public static function configuration(AppSettingsRequest $request)
	{
		return [
			'mode' => 'SettingListing'
		];
	}
}
