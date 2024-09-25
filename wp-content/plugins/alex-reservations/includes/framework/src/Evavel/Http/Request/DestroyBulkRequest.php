<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;

class DestroyBulkRequest  extends Request {

	public function authorize() {

		//@todo: IMPLEMENT destroyAny permission
		if (Gate::denies('destroyAny', $this->resourceName())){
			evavel_403();
		}
	}
}
