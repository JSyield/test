<?php

namespace Evavel\Resources\Traits;

use Evavel\Facades\Gate;
use Evavel\Http\Request\Request;

trait Authorizable
{
	public function authorizedToViewAny(Request $request)
	{
		return Gate::allows('viewAny', $request->modelClass());
	}

	public function authorizedToView(Request $request)
	{
		return Gate::allows('view', $this->model);
	}

	public function authorizedToUpdate(Request $request)
	{
		return Gate::allows('update', $this->model);
	}

	public function authorizedToCreate(Request $request)
	{
		return true;
	}

	public function authorizedToDelete(Request $request)
	{
		return Gate::allows('delete', $this->model);
	}

}
