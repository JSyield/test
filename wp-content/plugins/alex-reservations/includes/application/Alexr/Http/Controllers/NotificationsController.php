<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\Notification;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class NotificationsController extends Controller
{
	public static $number_to_load = 5;

	/**
	 * Mark a notification as read
	 * @param Request $request
	 *
	 * @return void
	 */
	public function read(Request $request)
	{
		// @TODO AUTHORIZATION user
		$tenantId = $request->tenantId();
		$notificationId = $request->notificationId;

		$notification = Notification::find($notificationId);

		if (!$notification) {
			wp_send_json_error(['error', __eva('Notification not found')]);
		}

		$notification->read_at = evavel_now();
		$notification->save();

		wp_send_json_success();
	}

	/**
	 * Mark a notification as unread
	 * @param Request $request
	 *
	 * @return void
	 */
	public function unread(Request $request)
	{
		// @TODO AUTHORIZATION user
		$tenantId = $request->tenantId();
		$notificationId = $request->notificationId;

		$notification = Notification::find($notificationId);

		if (!$notification) {
			wp_send_json_error(['error', __eva('Notification not found')]);
		}

		$notification->read_at = null;
		$notification->save();

		return $this->response(['success' => true]);
	}


	public function markAllRead(Request $request)
	{
		$tenantId = $request->tenantId();

		$user = Eva::make('user');

		Notification::where('notifiable_type', 'like', '%User%')
		            ->where('notifiable_id', $user->id)
		            ->where('restaurant_id', $tenantId)
					->whereIsNull('read_at')
		            ->update([
						'read_at' => evavel_now()
		            ]);

		return $this->response(['success' => true]);
	}

	/**
	 * Load most recent notifications
	 * This is called from AppHeartBeatController
	 *
	 * @param Request $request
	 * @param $tenantId
	 *
	 * @return mixed
	 */
	public function loadRecent(Request $request, $tenantId = null)
	{
		$user = Eva::make('user');
		//ray($user);
		//ray(evavel_escape_className(get_class($user)));

		// Notifications not read for the user logged-in
		//Query::setDebug(true);

		$notifications = Notification::where('notifiable_type', 'like', '%User%')
	                         ->where('notifiable_id', $user->id)
							 ->where('restaurant_id', $tenantId)
	                         ->orderBy('id', 'DESC')
	                         ->limit(self::$number_to_load)
	                         ->get()->toArray();

		return $notifications;
	}

	// Load the next notifications
	public function loadMore(Request $request)
	{
		$tenantId = $request->tenantId();
		$notificationId = $request->notificationId;

		$user = Eva::make('user');

		// Notifications not read for the user logged-in
		$notifications = Notification::where('notifiable_type', 'like', '%User%')
             ->where('notifiable_id', $user->id)
			 ->where('restaurant_id', $tenantId)
			 ->where('id', '<', $notificationId)
             ->orderBy('id', 'DESC')
             ->limit(self::$number_to_load)
             ->get()->toArray();

		return $this->response([
			'success' => true,
			'notifications' => $notifications
		]);
	}
}
