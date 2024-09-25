<?php

namespace Alexr\Http\Controllers;

use Alexr\Models\DailyNotification;
use Alexr\Models\UserMeta;
use Evavel\Eva;
use Evavel\Http\Controllers\Controller;
use Evavel\Http\Request\Request;
use Evavel\Query\Query;

class DailyNotificationsController extends Controller
{
	public function index(Request $request)
	{
		$tenantId = $request->tenantId();
		$date = $request->date;

		$list = DailyNotification::where('restaurant_id', $tenantId)
		                         ->where('date', $date)
		                         ->with('user')
		                         ->orderBy('date_modified', 'ASC')
		                         ->get()
		                         ->toArray();

		// I only want the name of the user, not the rest of the data
		$list_clean = [];
		foreach($list as $item) {
			$list_clean[] = $this->simplifyNotification($item);
		}

		$user = Eva::make('user');

		$count_from_others = 0;
		foreach($list_clean as $item) {
			if ($user->id != $item['user_id']) {
				$count_from_others++;
			}
		}

		return $this->response([
			'success' => true,
			'notifications' => $list_clean,
			'count' => [
				'total' => count($list_clean), // Total notifications
				'from_others' => $count_from_others, // From the current user
				'read' => alexr_get_user_meta($tenantId, $user->id, 'daily_notifications_read_'.$date, 0), // Unread from other users
			]
		]);
	}

	protected function simplifyNotification($item)
	{
		$item_arr = $item->toArray();
		$name = false;
		if ($item_arr['user']) {
			$name = $item_arr['user']->name;
		}
		$item_arr['user'] = ['name' => $name];
		return $item_arr;
	}

	public function save(Request $request)
	{
		$tenantId = $request->tenantId();
		$date     = $request->date;

		$message = sanitize_textarea_field($request->message);
		$user = Eva::make('user');

		DailyNotification::create([
			'restaurant_id' => $tenantId,
			'date' => $date,
			'user_id' => $user->id,
			'message' => $message
		]);

		return $this->response([ 'success' => true ]);
	}

	public function delete(Request $request)
	{
		$tenantId = $request->tenantId();
		$uuid = $request->uuid;

		$notification = DailyNotification::where('restaurant_id', $tenantId)->where('uuid', $uuid)->delete();

		return $this->response([ 'success' => true ]);
	}

	public function update(Request $request)
	{
		$tenantId = $request->tenantId();
		$uuid = $request->uuid;
		$message = sanitize_textarea_field($request->message);

		$notification = DailyNotification::where('restaurant_id', $tenantId)->where('uuid', $uuid)->first();

		$notification->message = $message;
		$notification->save();

		// Tengo que reducir en uno los mensajes leidos para cada usuairo
		$this->recalculate($tenantId, $notification->date);

		return $this->response([ 'success' => true ]);
	}

	// I have to reduce the number of read messages for each user that is not current user
	protected function recalculate($tenantId, $date)
	{
		$user = Eva::make('user');
		if (!$user) return;

		$items = UserMeta::where('restaurant_id', $tenantId)
			->where('user_id', '!=', $user->id)
			->where('meta_key', 'daily_notifications_read_'.$date)
			->get();

		foreach($items as $item) {
			$value = $item->meta_value -1;
			if ($value < 0) $value = 0;

			$item->meta_value = $value;
			$item->save();
		}

	}

	public function readAll(Request $request)
	{
		$tenantId = $request->tenantId();
		$date     = $request->date;
		$user = Eva::make('user');

		$count_from_others = DailyNotification::where('restaurant_id', $tenantId)
			->where('date', $date)
			->where('user_id', '!=', $user->id)
			->get()
			->count();

		alexr_set_user_meta($tenantId, $user->id, 'daily_notifications_read_'.$date, $count_from_others);

		return $this->response([ 'success' => true ]);
	}
}
