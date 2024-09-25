<?php

namespace Evavel\Resources\Fields;

use Evavel\Http\Request\Request;

class Image extends Field
{
	public $component = 'image-upload-field';

	public function options($arr)
	{
		$this->withMeta(['options' => $arr]);

		return $this;
	}
}
