<?php

namespace Evavel\Resources\Traits;

use Evavel\Http\Request\Request;

trait ResolvesLenses
{
	public function availableLenses(Request $request)
	{
		return $this->resolveLenses($request);
	}

	public function resolveLenses(Request $request)
	{
		return $this->lenses($request);
	}
}
