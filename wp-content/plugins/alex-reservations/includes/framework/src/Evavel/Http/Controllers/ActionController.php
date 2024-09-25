<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\ActionRequest;
use Evavel\Http\Request\Request;
use Evavel\Resources\Resource;

class ActionController extends Controller
{
    public function index(Request $request)
    {
        $resource = $request->newResource();

        return $this->response([
            'actions' => $this->availableActions($request, $resource)
        ]);
    }

	// Receive the request and pass to the Action handler
    public function store(ActionRequest $request)
    {
		$request->validateFields();

		return $request->action()->handleRequest($request);


		// Ejemplo de validacion
		/*$errors = [
			'message' => 'Invalid Data',
			'errors' => [
				'status' => ['This field is required']
			]
		];
		return $this->response($errors, 422);

		return $this->response(['message' => 'DONE']);*/
    }

    public function availableActions(Request $request, Resource $resource)
    {
        switch ($request->display) {
            case 'index':
                $method = 'availableActionsOnIndex';
                break;
            case 'detail':
                $method = 'availableActionsOnDetail';
                break;
            default:
                $method = 'availableActions';
        }

        return $resource->{$method}($request);
    }
}
