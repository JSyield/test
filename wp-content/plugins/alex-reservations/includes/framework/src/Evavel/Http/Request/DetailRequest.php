<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;
use Evavel\Query\Query;

class DetailRequest extends Request
{
	public function authorize()
	{
		if (Gate::denies('view', [$this->fetchModel()])){
			evavel_403();
		}
	}
}
