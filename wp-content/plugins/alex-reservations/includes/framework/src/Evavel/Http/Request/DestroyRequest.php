<?php

namespace Evavel\Http\Request;

use Evavel\Facades\Gate;

class DestroyRequest extends Request
{
	public function authorize()
	{
		$modelClass = $this->modelClass();

		foreach($this->resources() as $id){
			if (Gate::denies('delete', [$modelClass::withId($id)])){
				evavel_403();
			}
		}
	}
}
