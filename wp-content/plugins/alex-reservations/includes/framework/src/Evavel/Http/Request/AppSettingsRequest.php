<?php

namespace Evavel\Http\Request;

use Evavel\Support\Str;

class AppSettingsRequest  extends Request
{
	public function settingClass()
	{
		//Str::studly()
		return evavel_setting_prefix().Str::studly(evavel_singular($this->settingName()));
	}
}
