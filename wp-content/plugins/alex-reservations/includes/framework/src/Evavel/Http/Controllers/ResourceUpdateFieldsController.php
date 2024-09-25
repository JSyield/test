<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\Request;
use Evavel\Http\Request\UpdateRequest;

class ResourceUpdateFieldsController extends Controller
{
    // Send fields to update
    public function index(UpdateRequest $request, $resourceName, $resourceId)
    {
        if (!$this->validate(['resourceName' => $resourceName])) {
            return $this->response(['response_code' => '404', 'error' => 'Invalid resource']);
        }

        // Create the resource with the model
        $resource = $request->queryResource();

        if ($resource == null){
            return $this->response(['response_code' => '404', 'error' => 'Not found']);
        }

        $response = [
            'title' => $resource->title(),
            'panels' => $resource->availablePanels($request),
            'fields' => $resource->updateFields($request),
        ];

        return $this->response($response);
    }
}
