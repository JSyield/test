<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\DestroyRequest;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class ResourceDestroyController extends Controller
{
	public static $authorizations = [];

    public function handle(DestroyRequest $request, $resourceName)
    {
        // customers resource -> user model
	    $model = $request->modelClass();
	    $resource = $model;

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


        $table = $model::$table_name;

        Query::table($table)
            ->whereIn('id', $request->resources())
            ->delete();

        return $this->response([ 'resources' => $request->resources() ]);
    }

}
