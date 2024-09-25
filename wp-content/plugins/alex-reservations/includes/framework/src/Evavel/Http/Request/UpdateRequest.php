<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;
use Evavel\Query\Query;

class UpdateRequest extends Request
{
	public function authorize() {
		if (Gate::denies('update', [$this->fetchModel()])){
			evavel_403();
		}
	}

	public function updateRules()
    {
        $resource = $this->newResource();
        return $resource->updateRules($this);
    }

}
