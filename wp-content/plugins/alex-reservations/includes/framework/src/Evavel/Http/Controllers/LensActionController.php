<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\LensIndexRequest;
use Evavel\Http\Request\Request;

class LensActionController  extends Controller
{
	public function index(LensIndexRequest $request)
	{
		$resource = $request->lensObject();


		return $this->response([
			'actions' => $resource->actions($request)
		]);
	}
}
