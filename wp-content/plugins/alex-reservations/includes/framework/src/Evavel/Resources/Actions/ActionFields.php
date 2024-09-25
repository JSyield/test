<?php

namespace Evavel\Resources\Actions;

use Evavel\Models\Collections\Collection;

class ActionFields extends Collection
{
	public function __get($label)
	{
		if (isset($this->items[$label])){
			return $this->items[$label];
		}

		return null;
	}
}
