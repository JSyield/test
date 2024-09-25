<?php

namespace Alexr\Settings;

use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Models\SettingCustomized;

class SocialChannel extends SettingCustomized
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'social_channels';
	public static $pivot_tenant_field = 'restaurant_id';

	public static $custom_component = 'EditSocialChannels';

	public static function label()
	{
		return __eva('Social Channels');
	}

	public static function getId($tenantId)
	{
		$socialChannels = SocialChannel::where(self::$pivot_tenant_field, $tenantId)->first();

		if ($socialChannels) {
			return $socialChannels->id;
		}

		// Create if does not exists yet
		$socialChannels = SocialChannel::create([
			'restaurant_id' => $tenantId,
			'meta_value' => ['channels' => [
				'url_website' => 'https://mydomain.com/reserve',
				'items' => []
			]],
		]);

		return $socialChannels->id;
	}

	public static function getItems(AppSettingsRequest $request, $tenantId)
	{
		$socialChannelsId = SocialChannel::getId($tenantId);
		$socialChannels = SocialChannel::find($socialChannelsId);

		return $socialChannels->channels;
	}
}
