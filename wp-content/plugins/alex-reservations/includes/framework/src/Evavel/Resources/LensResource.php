<?php

namespace Evavel\Resources;

use Evavel\Http\Request\LensIndexRequest;
use Evavel\Interfaces\ToJsonSerialize;
use Evavel\Resources\Traits\FillsFields;
use Evavel\Resources\Traits\PerformsValidation;
use Evavel\Resources\Traits\ResolvesFields;
use Evavel\Resources\Traits\ResolvesActions;
use Evavel\Resources\Traits\ResolvesFilters;
use Evavel\Support\Str;

abstract class LensResource implements ToJsonSerialize
{
	use ResolvesFields;
	use PerformsValidation;
	use FillsFields;
	use ResolvesFilters;
	use ResolvesActions;

	public $name;

	public $resource;

	public function name()
	{
		return $this->name != null ? $this->name : Str::humanize($this);
	}

	public function uriKey()
	{
		return Str::slug($this->name(), '-', null);
	}

	public function toJsonSerialize()
	{
		return [
			'name' => $this->name(),
			'uriKey' => $this->uriKey()
		];
	}

	public static function query(LensIndexRequest $request, $query){
		return $query;
	}
}
