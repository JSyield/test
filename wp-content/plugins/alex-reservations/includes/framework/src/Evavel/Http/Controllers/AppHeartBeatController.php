<?php

namespace Evavel\Http\Controllers;

use Evavel\Eva;
use Evavel\Http\Request\Request;

class AppHeartBeatController extends Controller {

	/**
	 * Send data with every heartbeat
	 *
	 * @param Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle(Request $request)
	{
		// Using this to load the recent notifications
		$user = Eva::make('user');
		if ($user->isInactive()){
			return $this->response([
				'success' => true,
				'user_active' => false,
			]);
		}

		$controller = evavel_new_notifications_controller();
		$notifications = $controller->loadRecent($request, $request->tenantId());

		return $this->response([
			'success' => true,
			'user_active' => true,
			'notifications' => $notifications
		]);
	}
}
