<?php

namespace Alexr\Settings;

use Alexr\Models\Restaurant;
use Evavel\Models\SettingSimple;

class GoogleReserve extends SettingSimple
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'google_reserve';
	public static $pivot_tenant_field = 'restaurant_id';

	const URL_GOOGLE_MAP = 'https://alexreservations.com/map2';

	protected $casts = [
		'active' => 'boolean',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	function settingName()
	{
		return __eva('Google Reserve');
	}

	function defaultValue()
	{
		$restaurant = $this->restaurant;

		return [
			"name"              => $this->name != null ? $this->name : $restaurant->name,
			"telephone"         => $this->telephone,
			"email"             => $this->email != null ? $this->email : $restaurant->email,
			"url"               => $this->url,
			//"category"          => $this->category,
			"language"          => $this->language != null ? $this->language : $restaurant->language,
			"latitude"          => $this->latitude != null ? $this->latitude : $restaurant->latitude,
			"longitude"         => $this->longitude != null ? $this->longitude : $restaurant->longitude,
			"place_id"          => $this->place_id,
			"street_address"    => $this->street_address != null ? $this->street_address : $restaurant->address,
			"locality"          => $this->locality,
            "region"            => $this->region,
            "country"           => $this->country,
            "postal_code"       => $this->postal_code != null ? $this->postal_code : $restaurant->postal_code,
			"action_url"        => $this->action_url,
			"submit_request"    => $this->submit_request
		];
	}

	function fields()
	{
		$restaurant = $this->restaurant;

		$fields = [
			[
				'attribute' => 'name',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 66%;',
				'name' => __eva('Restaurant Name').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->name != null ? $this->name : $restaurant->name,
			],
			[
				'attribute' => 'url',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Website URL').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->url,
				'placeholder' => "www.partnerwebsite.com"
			],
			[
				'attribute' => 'action_url',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Website URL reservation form').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->action_url,
				'placeholder' => "www.partnerwebsite.com/reservation",
				'helpText' => __eva('The reservation page cannot be the home page. Has to be a different page or a subdomain.')
			],

			/*[
				'attribute' => 'category',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Category').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->category,
				'placeholder' => "restaurant"
			],*/
			[
				'attribute' => 'telephone',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Telephone').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->telephone,
				'placeholder' => "+1-650-123-4567"
			],
			[
				'attribute' => 'email',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Email').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->email != null ? $this->email : $restaurant->email,
				'placeholder' => "restaurant@domain.com"
			],
			[
				'attribute' => 'language',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Language').' *',
				'component' => 'select-language',
				'value' => $this->language,
			],
			[
				'attribute' => 'googlemap',
				'stacked' => true,
				'style' => 'display: inline-block; width: 100%;',
				'name' => __eva('Map'),
				'component' => 'google-map',
				'url' => self::URL_GOOGLE_MAP,
				'value' => [
					'latitude' => $this->latitude != null ? $this->latitude : $restaurant->latitude,
					'longitude' =>$this->longitude != null ? $this->longitude : $restaurant->longitude
				],
			],
			[
				'attribute' => 'latitude',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Latitude').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->latitude != null ? $this->latitude : $restaurant->latitude,
				'placeholder' => "37.422113",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'longitude',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Longitude').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->longitude != null ? $this->longitude : $restaurant->longitude,
				'placeholder' => "-122.084041",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'place_id',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Google Place ID').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->place_id,
				'placeholder' => "",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'street_address',
				'stacked' => true,
				'style' => 'display: inline-block; width: 70%;',
				'name' => __eva('Street Address').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->street_address != null ? $this->street_address : $restaurant->address,
				'placeholder' => "1170 Bordeaux Dr Building 3",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'postal_code',
				'stacked' => true,
				'style' => 'display: inline-block; width: 30%;',
				'name' => __eva('Postal Code').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' => $this->postal_code != null ? $this->postal_code : $restaurant->postal_code,
				'placeholder' => "94089",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'country',
				'stacked' => true,
				'style' => 'display: inline-block; width: 40%;',
				'name' => __eva('Country').' *',
				'component' => 'country-field',
				'type' => 'text',
				'value' =>$this->country,
				'placeholder' => "US",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'region',
				'stacked' => true,
				'style' => 'display: inline-block; width: 30%;',
				'name' => __eva('Province/State').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' =>$this->region,
				'placeholder' => "CA",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'locality',
				'stacked' => true,
				'style' => 'display: inline-block; width: 30%;',
				'name' => __eva('Locality').' *',
				'component' => 'text-field',
				'type' => 'text',
				'value' =>$this->locality,
				'placeholder' => "Sunnyvale",
				'helpText' => __eva('(*auto filled by map selection)')
			],
			[
				'attribute' => 'submit_request',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 30%;',
				'name' => __eva('Submit to Google'),
				'component' => 'google-map-submit',
				'type' => 'text',
				'value' =>$this->submit_request,
				'placeholder' => "",
				'helpText' => __eva('(*auto filled by map selection)')
			]

		];

		return $fields;
	}
}
