<?php

namespace Evavel\Http\Controllers;

use Evavel\Http\Request\DestroyBulkRequest;
use Evavel\Query\Query;


class ResourceDestroyBulkController  extends Controller
{
	// @TODO pending to implement authorizations like in ResourceUpdateBulkController when needed

	public function handle(DestroyBulkRequest $request, $resourceName)
	{
		$data = $request->data;
		$items = $request->items;
		$modelClass = $request->modelClass();
		$table = $modelClass::$table_name;

		// COuld be items is inside the data structure
		if (!$items) {
			if ($data && isset($data['items'])){
				$items = $data['items'];
			} else {
				return $this->response(null);
			}
		}

		Query::table($table)
		     ->whereIn('id', $items)
		     ->delete();

		return $this->response($items);
	}
}
