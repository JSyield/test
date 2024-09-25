<?php

namespace Alexr\Settings;

use Alexr\Models\BTag;
use Alexr\Models\BTagGroup;
use Alexr\Models\Restaurant;
use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Models\SettingCustomized;

class BookingTag extends SettingCustomized
{
	public static $custom_component = 'EditTags';

	public static function label()
	{
		return __eva('Tags for bookings');
	}

	public static function getItems(AppSettingsRequest $request, $tenantId)
	{
		$tags = BTag::where('restaurant_id', $tenantId)
		            ->orderBy('ordering', 'ASC')
		            ->get()->toArray();

		$groups = BTagGroup::where('restaurant_id', $tenantId)
		                   ->orderBy('ordering', 'ASC')
		                   ->get()->toArray();

		$restaurant = Restaurant::find($tenantId);
		$language = $restaurant ? $restaurant->language : 'en';

		return [
			'predefined' => alexr_config_tags($tenantId, 'bookings'),
			'groups' => $groups,
			'tags' => $tags,
		];
	}
}
