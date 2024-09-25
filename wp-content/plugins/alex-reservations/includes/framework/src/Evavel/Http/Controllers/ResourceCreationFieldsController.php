<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\CreateRequest;
use Evavel\Http\Request\Request;

class ResourceCreationFieldsController extends Controller
{
    public function index(CreateRequest $request, $resourceName)
    {
        $resource = $request->newResource();

        $response = [
            'panels' => $resource->availablePanels($request),
            'fields' => $resource->creationFields($request),
        ];

        return $this->response($response);
    }
}
