<?php

function alexr_get_user_meta($restaurant_id, $user_id, $meta_key, $default = null)
{
	$item = \Alexr\Models\UserMeta::table('user_meta')
              ->where('restaurant_id', $restaurant_id)
               ->where('user_id', $user_id)
               ->where('meta_key', $meta_key)
               ->first();
	if ($item) {
		return $item->meta_value;
	}
	return $default;
}

function alexr_set_user_meta($restaurant_id, $user_id, $meta_key, $meta_value)
{
	$item = \Alexr\Models\UserMeta::where('restaurant_id', $restaurant_id)
              ->where('user_id', $user_id)
              ->where('meta_key', $meta_key)
              ->first();

	if ($item)
	{
		$item->meta_value = $meta_value;
		$item->save();
	} else {
		$user = \Alexr\Models\User::find($user_id);
		if (!$user) {
			return;
		}
		\Alexr\Models\UserMeta::create([
			'restaurant_id' => $restaurant_id,
			'user_id' => $user_id,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value
		]);
	}
}

function alexr_delete_user_meta($restaurant_id, $user_id, $meta_key)
{
	\Alexr\Models\UserMeta::where('restaurant_id', $restaurant_id)
	                      ->where('user_id', $user_id)
	                      ->where('meta_key', $meta_key)
	                      ->delete();
}
