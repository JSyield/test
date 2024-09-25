<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\LensIndexRequest;
use Evavel\Http\Request\Request;

class LensFilterController extends Controller
{
	public function index(LensIndexRequest $request)
	{
		return $this->response($request->lensObject()->availableFilters($request));
	}
}
