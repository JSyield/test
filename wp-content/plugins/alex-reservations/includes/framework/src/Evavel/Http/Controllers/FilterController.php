<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\Request;

class FilterController extends Controller
{
    public function index(Request $request)
    {
        return $this->response($request->newResource()->availableFilters($request));
    }
}
