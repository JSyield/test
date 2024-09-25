<?php

namespace Evavel\Http\Request;

class LensIndexRequest extends IndexRequest
{
	public function lensObject()
	{
		return evavel_collect($this->availableLenses())->first(function($lens){
			return $this->lens == $lens->uriKey();
		});
	}

	public function availableLenses()
	{
		return $this->newResource()->availableLenses($this);
	}

	public function applyLensQuery($query)
	{
		if (get_class($this) != LensIndexRequest::class)
		{
			return $query;
		}

		if ($this->viaResource() !== evavel_config('app.tenant'))
		{
			$query = $this->lensObject()->query($this, $query);
		}

		return $query;
	}
}
