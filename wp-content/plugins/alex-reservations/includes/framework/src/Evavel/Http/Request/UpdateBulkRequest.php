<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;

class UpdateBulkRequest extends Request
{
	public function authorize() {

		//@todo: IMPLEMENT updateAny permission
		if (Gate::denies('updateAny', $this->resourceName())){
			evavel_403();
		}
	}
}
