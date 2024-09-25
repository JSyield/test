<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\UpdateBulkRequest;
use Evavel\Query\Query;

class ResourceUpdateBulkController extends Controller
{
	public static $authorizations = [];

	public function handle(UpdateBulkRequest $request, $resourceName)
	{
		$items = $request->items;
		$modelClass = $request->modelClass();

		$resource=$modelClass;

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

		// Update each model
		foreach ($items as $item){
			$modelItem = $modelClass::find($item['id']);
			foreach ($item as $key => $value) {
				if ($key != 'id') {
					$modelItem->{$key} = $value;
					//$modelItem->attributes[$key] = $value;
				}
			}
			$modelItem->save();
		}

		return $this->response($request->items);
	}
}
