<?php

namespace Evavel\Http\Controllers;


use Evavel\Eva;
use Evavel\Facades\Gate;
use Evavel\Http\Request\IndexRequest;

class ResourceIndexController extends Controller
{
    public function handle(IndexRequest $request, $resourceName)
    {
        if (!$this->validate(['resourceName' => $resourceName])) {
            return $this->response(['response_code' => '404', 'error' => 'Invalid resource']);
        }

		if (Gate::denies('viewAny', $request->modelClass())){
			evavel_403();
		}

        $resourceClass = $request->resourceClass();

        $data = $request->searchIndex();

        $response = [
            'label' => $resourceClass::label(),
            'label_singular' => $resourceClass::labelSingular(),
            'resources' => $data['resources'],
            'total' => $data['total'],
            'per_page' => $data['perPage'],
            'current_page' => $data['currentPage'],
            'total_pages' => $data['total_pages']
        ];

        return $this->response($response);
    }

}
