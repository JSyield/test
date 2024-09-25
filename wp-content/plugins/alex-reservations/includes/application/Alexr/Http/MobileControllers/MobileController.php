<?php

namespace Alexr\Http\MobileControllers;

use Alexr\Http\Controllers\BTagsController;
use Alexr\Http\Controllers\CTagsController;
use Alexr\Http\Controllers\FloorPlanController;
use Evavel\Http\Controllers\AppSettingsController;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Http\Request\Request;

class MobileController extends Controller {

	// Desde el mobil concentro todas las peticiones para que vaya mas rapido
	public function getAllData(AppSettingsRequest $request)
	{
		// Shifts
		// Events
		// Floorplan
		// Btags
		// Ctags
		// ClosedDays

		$data = [
			'shifts' => [],
			'events' => [],
			'btags' => (new BTagsController())->index($request),
			'ctags' => (new CTagsController())->index($request),
			'floorplan' => (new FloorPlanController())->index($request),
			'closed_days' => ['data' => [ 'items' => [] ] ]
		];


		$request->params['controller'] = "AppSettingsController@items";

		$request->params['settingName'] = "shifts";
		$request->params['settingClass'] = '\Alexr\Settings\Shift';
		$data['shifts'] = (new AppSettingsController($request))->items($request);

		$request->params['settingName'] = "events";
		$request->params['settingClass'] = '\Alexr\Settings\Event';
		$data['events'] = (new AppSettingsController($request))->items($request);

		$request->params['settingName'] = "closed_days";
		$request->params['settingClass'] = '\Alexr\Settings\ClosedDay';
		$data['closed_days'] = (new AppSettingsController($request))->items($request);

		return $this->response($data);
	}
}
