<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\CreateRequest;
use Evavel\Query\Query;

class ResourceCreateController extends Controller
{
	public static $authorizations = [];

    public function handle(CreateRequest $request, $resourceName)
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
	    if ($permission_name){
		    $user = Eva::make('user');
		    if (!$user->canEdit($permission_name, $request->tenantId())) {
			    return $this->response([ 'success' => false, 'error' => $permission_message]);
		    }
	    }

        $resource::validateForCreation($request);

        $model = $resource::fill($request, $resource::newModel());

		$tenantId = $request->tenantId();

		// Add tenant field
		if ($resourceName != evavel_tenant_resource()) {
			$tenant_field = evavel_tenant_field();
			$model->{$tenant_field} = $tenantId;
		}

        $model->save();

		// If tags are coming with the group, then attach them too
	    $tags = $request->tags;
		if ($tags){
			$model->createAndAttachTags($tags);
		}

        return $this->response([
            'redirect' => "/t/{$tenantId}/resources/{$resourceName}/{$model->id}",
            'model' => $model
        ]);

    }
}
