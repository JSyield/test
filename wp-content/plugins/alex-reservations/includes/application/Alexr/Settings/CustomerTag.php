<?php

namespace Alexr\Settings;

use Alexr\Models\CTag;
use Alexr\Models\CTagGroup;
use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Models\SettingCustomized;

class CustomerTag extends SettingCustomized
{
	public static $custom_component = 'EditTags';

	public static function label()
	{
		return __eva('Tags for customers');
	}

	public static function getItems(AppSettingsRequest $request, $tenantId)
	{
		$tags = CTag::where('restaurant_id', $tenantId)
		            ->orderBy('ordering', 'ASC')
		            ->get()->toArray();

		$groups = CTagGroup::where('restaurant_id', $tenantId)
		                   ->orderBy('ordering', 'ASC')
		                   ->get()->toArray();

		return [
			'predefined' => alexr_config_tags($tenantId, 'customers'),
			'groups' => $groups,
			'tags' => $tags,
		];
	}
}
