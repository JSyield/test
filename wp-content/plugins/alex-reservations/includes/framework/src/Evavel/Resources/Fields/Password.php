<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;

class Password  extends Field
{
	public $component = 'password-field';

	public function toJsonSerialize()
	{
		return array_merge(parent::toJsonSerialize(),
			['value' => '']
		);
	}
}
