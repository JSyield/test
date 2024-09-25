<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Area;
use Alexr\Models\Combination;
use Alexr\Models\Floor;
use Alexr\Models\Table;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;

class FloorPlanController extends Controller
{
	public function index(Request $request)
	{
		// @TODO authorization user

		$tenant = $request->tenantId();

		if (!$tenant){
			return $this->response([
				'floors' => [],
				'areas' => [],
				'tables' => [],
				'combinations' => []
			]);
		}

		//$floor = Floor::find(1)->toArray();
		//return $this->response([$floor]);

		// Asegurarse de que no hay mesas huerfanas de area
		Table::removeTablesAndCombinationsWithNoAreas($tenant);

		return $this->response(
			[
				'floors'        => Floor::where('restaurant_id', $tenant)->get()->toArray(),
				'areas'         => Area::where('restaurant_id', $tenant)->get()->toArray(),
				'tables'        => Table::where('restaurant_id', $tenant)->get()->toArray(),
				'combinations'  => Combination::where('restaurant_id', $tenant)->get()->toArray(),
			]
		);
	}

	public function areas(Request $request)
	{
		// @TODO authorization user

		$tenant = $request->tenantId();

		if (!$tenant){
			return $this->response([
				'areas' => [],
			]);
		}

		return $this->response([
			'areas'     => Area::where('restaurant_id', $tenant)->get()->toArray(),
			'tables'    => Table::where('restaurant_id', $tenant)->get()->toArray(),
		]);
	}

	/**
	 * Save the area canvas viewportTransform
	 *
	 * @param Request $request
	 *
	 * @return void
	 */
	public function canvas(Request $request)
	{
		// @TODO Authorization
		//ray($request->area);
		//ray($request->viewportTransform);

		$area = Area::find($request->area);
		if ($area) {
			$area->viewportTransform = $request->viewportTransform;
			$area->save();
		}

		return $this->response([]);
	}

	public function decoration(Request $request)
	{
		// @TODO Authorization
		//ray('POST DECORATION');
		//ray($request->decoration);

		$area = Area::find($request->area);

		if ($area) {
			$area->decoration = $request->decoration;
			$area->save();
		}

		return $this->response([]);
	}
}
