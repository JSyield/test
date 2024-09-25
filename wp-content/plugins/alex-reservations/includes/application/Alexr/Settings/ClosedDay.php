<?php

namespace Alexr\Settings;

use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Http\Request\Request;
use Evavel\Models\SettingCustomized;

class ClosedDay extends SettingCustomized
{
	public static $table_name = 'restaurant_setting';
	public static $meta_key = 'closed_days';
	public static $pivot_tenant_field = 'restaurant_id';

	public static $custom_component = 'EditClosedDays';

	public static function label()
	{
		return __eva('Closed Days');
	}

	public static function getId($tenantId)
	{
		$closedDays = ClosedDay::where(self::$pivot_tenant_field, $tenantId)->first();

		if ($closedDays) {
			return $closedDays->id;
		}

		// Create if does not exists yet
		$closedDays = ClosedDay::create([
			'restaurant_id' => $tenantId,
			'meta_value' => ['dates' => []],
		]);

		return $closedDays->id;
	}

	//public static function getItems(AppSettingsRequest $request, $tenantId)
	public static function getItems(Request $request, $tenantId)
	{
		// Only one per tenant
		$closedDaysId = ClosedDay::getId($tenantId);
		$closedDays = ClosedDay::find($closedDaysId);

		return $closedDays->dates;
	}

	/**
	 * Remove closed days from the array
	 * @param $availableDays
	 *
	 * @return mixed
	 */
	public function removeDatesFromSimpleArray($availableDays)
	{
		$dates = $this->dates;

		if (is_array($dates)){
			foreach($dates as $date) {
				$pos = array_search($date, $availableDays);
				if ($pos !== false && $pos >= 0){
					unset($availableDays[$pos]);
				}
			}
		}

		return array_values($availableDays);
	}

	/**
	 * Remove closed days from array
	 * @param $availableDays
	 *
	 * @return mixed
	 */
	public function removeDatesAssociatedArray($availableDays)
	{
		$dates = $this->dates;
		if (is_array($dates)){
			foreach($dates as $date) {
				unset($availableDays[$date]);
			}
		}
		return $availableDays;
	}
}
