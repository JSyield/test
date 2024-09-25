<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\UpdateRequest;
use Evavel\Query\Query;

class ResourceUpdateController extends Controller
{
	public static $authorizations = [];

    public function handle(UpdateRequest $request, $resourceName, $resourceId)
    {
		$resource = $request->resourceClass();

	    // Auhorization
	    $permission_name = false;
	    $permission_message = false;

		// Extended function to check permissions
		$data = ['permission_name' => $permission_name, 'permission_message' => $permission_message];
		foreach(self::$authorizations as $func) {
			$data = $func[0]->{$func[1]}($resource, $data);
		}
	    $permission_name = $data['permission_name'];
		$permission_message = $data['permission_message'];

		// Check the appropiate permission
	    if ($permission_name)
		{
		    $user = Eva::make('user');
		    if (!$user->canEdit($permission_name, $request->tenantId())) {
			    return $this->response([ 'success' => false, 'error' => $permission_message]);
		    }
	    }

	    //return $this->response(['error' => 'NO SE PUEDE']);

	    //ray($request);
        $resource::validateForUpdate($request);

        $model = $request->fetchModel();

	    // @todo: Si el campo no aparece en fields para UPDATE entonces no se guarda
	    // Esto es malo para hacer un update de un campo sobre la marcha
	    // como el campo 'shape' desde FloorOrganizer
	    $model = $resource::fillForUpdate($request, $model);

		//Query::setDebug(true);
        $model->save();

		if (evavel_tenant_resource() != $resourceName){
			$field_tenant = evavel_config('app.tenant');
			$field_tenant = evavel_singular($field_tenant).'_id';
			$tenantId = $model->{$field_tenant};
		} else {
			$tenantId = $request->tenantId();
		}

        $response = [
            'id' => $resourceId,
            'resource' => $model->toArray(),
            'redirect' => "/t/{$tenantId}/resources/{$resourceName}/{$resourceId}"
        ];

        return $this->response($response);

        //return $this->response($response, 422);
        //return $this->response($response, 409);
        //return $this->response404($response);
    }
}
