<?php

namespace Evavel\Http\Request\Traits;

trait ManageSettings
{
	public function settingName()
	{
		return $this->extract(['settingName'], null);
	}

	public function settingId()
	{
		return $this->extract(['model_id', 'setting_id', 'settingId'], false, 'intval');
	}
}
