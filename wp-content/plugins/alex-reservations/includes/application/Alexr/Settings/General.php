<?php

namespace Alexr\Settings;

use Alexr\Enums\CurrencyType;
use Alexr\Models\Restaurant;
use Alexr\Settings\Traits\HasTimeOptions;
use Evavel\Models\SettingSimple;

class General extends SettingSimple
{
	use HasTimeOptions;

	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'general';
	public static $pivot_tenant_field = 'restaurant_id';

	protected $casts = [
		//'active' => 'boolean',
	];

	public function restaurant()
	{
		return $this->belongsTo(Restaurant::class);
	}

	function settingName()
	{
		return __eva('Restaurant settings');
	}

	function defaultValue()
	{
		$restaurant = $this->restaurant;

		// Fields starting with resource_ are saved in the restaurant table and not in the restaurant_setting table
		return [
			'resource_name' => $restaurant->name,
			'resource_email' => $restaurant->email,
			'resource_timezone' => $restaurant->timezone,
			'resource_language' => $restaurant->language,
			'resource_currency' => $restaurant->currency,
			'resource_date_format' => $restaurant->date_format,
			'resource_time_format' => $restaurant->time_format,
			'resource_first_day_of_week' => $restaurant->first_day_of_week,
			'resource_fullphone' => [
				'phone' => $restaurant->phone,
				'dial_code' => $restaurant->dial_code,
				'dial_code_country' => $restaurant->dial_code_country
			],
			'resource_address' => $restaurant->address,
			'resource_city' => $restaurant->city,
			'resource_country' => $restaurant->country,
			'resource_postal_code' => $restaurant->postal_code,
			'resource_latitude' => $restaurant->latitude,
			'resource_longitude' => $restaurant->longitude,
			'resource_link_web' => $restaurant->link_web,
			'resource_link_facebook' => $restaurant->link_facebook,
			'resource_link_instagram' => $restaurant->link_instagram,
			'resource_note_from_us' => $restaurant->note_from_us,
			'resource_reservation_policy' => $restaurant->reservation_policy,
			'logo_img_url' => '',
			'resource_timeline_start' => $restaurant->timeline_start ? $restaurant->timeline_start : 10*3600,
			'resource_timeline_end' => $restaurant->timeline_end ? $restaurant->timeline_end : 86400 + 7200,
		];
	}

	public function save( array $options = [] )
	{
		$this->filterValuesToSaveInSettings();
		return parent::save($options);
	}

	/**
	 * Add to the response after updating to force reloading the url
	 * because the restaurant name, language, etc.. could have changed
	 * @param $response
	 *
	 * @return mixed
	 */
	public function addToUpdateResponse($response)
	{
		$response['forceReload'] = true;
		return $response;
	}

	/**
	 * Remove meta_value array items with key resource_
	 * Those values should be saved in the restaurant model
	 * @return void
	 */
	protected function filterValuesToSaveInSettings()
	{
		$meta_value = $this->attributes['meta_value'];
		$meta_value_filtered = [];
		$resource_properties = [];
		foreach($meta_value as $key => $value)
		{
			// These values will be saved in the restaurants table
			if (str_contains($key, 'resource_') || in_array($key, ['phone', 'dial_code', 'dial_code_country'])) {
				$resource_properties[$key] = $value;
			}
			// And these ones in the restaurant_setting table
			else {
				$meta_value_filtered[$key] = $value;
			}
		}
		$this->attributes['meta_value'] = $meta_value_filtered;

		//ray($resource_properties);
		$this->saveIntoTheModel($resource_properties);
	}

	protected function saveIntoTheModel($list)
	{
		$restaurant = $this->restaurant;

		foreach($list as $key => $value) {
			if (in_array($key, ['phone', 'dial_code', 'dial_code_country'])) {
				$restaurant->{$key} = $value;
			} else {
				$real_key = str_replace('resource_', '', $key);
				$restaurant->{$real_key} = $value;
			}
		}

		$restaurant->save();
	}

	public function fields()
	{
		$restaurant = $this->restaurant;

		$fields = [
			/*[
				'attribute' => 'logo_img_url',
				'stacked' => false,
				'name' => __eva('Logo image'),
				'component' => 'image-upload-field',
				'options' => [
					'accept'    => 'image/png, image/jpeg',
					'maxWidth'  => 768,
					'maxHeight' => 250,
					'checkDimensions' => true,
					//'resize' => true
				],
				'value' => $this->logo_img_url,
				'placeholder' => '',
				//'helpText' => __eva('')
			],*/
			[
				'attribute' => 'resource_name',
				'stacked' => true,
				'style' => 'display: inline-block; width: 66%;',
				'name' => __eva('Restaurant Name'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->name,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_email',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%;',
				'name' => __eva('Restaurant Email'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->email,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_timezone',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Timezone'),
				'component' => 'select-timezone',
				'type' => 'text',
				'value' => $restaurant->timezone,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_language',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Language'),
				'component' => 'select-language',
				'type' => 'text',
				'value' => $restaurant->language,
				'helpText' => '<a href="https://alexreservations.com/docs/translations" target="_blank" class="text-red-500">'.__eva('How to enable more languages >>').'</a>',
			],
			[
				'attribute' => 'resource_currency',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Currency'),
				'component' => 'select-field',
				'options' => CurrencyType::options(),
				'value' => $restaurant->currency,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_date_format',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Date Format'),
				'component' => 'select-field',
				'options' => [
					['label' => __eva('Month Day Year'), 'value' => 'mdy'],
					['label' => __eva('Day Month Year'), 'value' => 'dmy'],
					['label' => __eva('Locale'), 'value' => 'locale']
				],
				'value' => $restaurant->date_format,
			],
			[
				'attribute' => 'resource_time_format',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Time Format'),
				'component' => 'select-field',
				'options' => [
					['label' => __eva('12 hours'), 'value' => '12h'],
					['label' => __eva('24 hours'), 'value' => '24h']
				],
				'value' => $restaurant->time_format,
			],
			[
				'attribute' => 'resource_first_day_of_week',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('First day of week'),
				'component' => 'select-field',
				'options' => [
					['label' => __eva('Monday'), 'value' => 1],
					['label' => __eva('Sunday'), 'value' => 7]
				],
				'value' => $restaurant->first_day_of_week,
				//'helpText' => __eva('For showing in the calendars')
			],

			[
				'attribute' => 'resource_address',
				'stacked' => true,
				'style' => 'display: inline-block; width: 66%; vertical-align: top;',
				'name' => __eva('Address'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->address,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_fullphone',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%; vertical-align: top;',
				'name' => __eva('Phone'),
				'component' => 'phone-field',
				'type' => 'text',
				'value' => [
					'phone' => $restaurant->phone,
					'dial_code' => $restaurant->dial_code,
					'dial_code_country' => $restaurant->dial_code_country,
				],
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_city',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%;',
				'name' => __eva('City'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->city,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_country',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%;',
				'name' => __eva('Country'),
				'component' => 'country-field',
				'type' => 'text',
				'value' => $restaurant->country,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_postal_code',
				'stacked' => true,
				'style' => 'display: inline-block; width: 33%;',
				'name' => __eva('Postal Code'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->postal_code,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_latitude',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Latitude'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->latitude,
				'helpText' => __eva('You can use this'). '<a target="_blank" href="https://www.maps.ie/coordinates.html" style="color:red"> '.__eva('LINK').' </a>'.__eva('to get the coordinates')
			],
			[
				'attribute' => 'resource_longitude',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Longitude'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->longitude,
				'helpText' => __eva('You can use this'). '<a target="_blank" href="https://www.maps.ie/coordinates.html" style="color:red"> '.__eva('LINK').' </a>'.__eva('to get the coordinates')
			],
			[
				'attribute' => 'resource_link_web',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 33%;',
				'name' => __eva('Website'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->link_web,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_link_facebook',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Facebook'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->link_facebook,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_link_instagram',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Instagram'),
				'component' => 'text-field',
				'type' => 'text',
				'value' => $restaurant->link_instagram,
				//'helpText' => __eva('')
			],
			[
				'attribute' => 'resource_note_from_us',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('A note from us'),
				'component' => 'textarea-field',
				'type' => 'text',
				'value' => alexr_transform_textarea_to_new_lines($restaurant->note_from_us),
				'helpText' => __eva('To be used inside emails')
			],
			[
				'attribute' => 'resource_reservation_policy',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Reservation policy'),
				'component' => 'textarea-field',
				'type' => 'text',
				'value' => alexr_transform_textarea_to_new_lines($restaurant->reservation_policy),
				'helpText' => __eva('To be used inside emails')
			],

			/*[
				'attribute' => 'resource_timeline',
				'stacked' => true,
				//'style' => 'display: inline-block; width: 69%;',
				'name' => __eva('Timeline views: Start/End time'),
				//'helpText' => __eva('To be used inside the reservation widget and emails'),
				'component' => 'first-last-seating-field',
				'value' => [
					'resource_timeline_start' => $restaurant->timeline_start ? $restaurant->timeline_start : 10*3600,
					'resource_timeline_end' =>  $restaurant->timeline_end ? $restaurant->timeline_end : 86400 + 7200,
				],
				'options' => [
					'start_time' => 3600 * 4,
					'end_time' => 30 * 3600,
					'step' => 3600,
					'maxLowerVal' => 22 * 3600,
					'minUpperVal' => 24 * 3600
				],
			],*/

			/*[
				'attribute' => 'resource_timeline_start',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Timeline Start Time'),
				'component' => 'select-field',
				//'options' => $this->listOfHours(0, 3600),
				'options' => $this->listOfHoursStartEnd( 4*3600, 21*3600,3600),
				'value' => $restaurant->timeline_start,
				'helpText' => __eva('For the timeline views')
			],
			[
				'attribute' => 'resource_timeline_end',
				'stacked' => true,
				'style' => 'display: inline-block; width: 50%;',
				'name' => __eva('Timeline End Time (next day)'),
				'component' => 'select-field',
				//'options' => $this->listOfHours(0, 3600),
				'options' => $this->listOfHoursStartEnd( 0, 4*3600,3600),
				'value' => $restaurant->timeline_end,
				'helpText' => __eva('For the timeline views')
			],*/
		];

		return $this->filter_pro($fields);
	}

	public function filter_pro($fields)
	{
		if (defined('ALEXR_PRO_VERSION')) {
			return $fields;
		}

		$pro = ['resource_note_from_us', 'resource_reservation_policy', 'resource_latitude', 'resource_longitude', 'resource_link_facebook', 'resource_link_instagram'];

		$fields_return = [];
		foreach ($fields as $field){
			if (!in_array($field['attribute'], $pro)){
				$fields_return[] = $field;
			}
		}
		return $fields_return;
	}

	public function validate()
	{
		return [];
	}
}
