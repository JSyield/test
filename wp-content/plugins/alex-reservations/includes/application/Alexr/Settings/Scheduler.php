<?php

namespace Alexr\Settings;

use Alexr\Settings\Traits\FieldsShiftEvent;
use Alexr\Settings\Traits\HasTimeOptions;
use Alexr\Settings\Traits\ManagePayments;
use Alexr\Settings\Traits\ShiftCalculations;
use Alexr\Settings\Traits\TablesCalculations;
use Evavel\Http\Request\AppSettingsRequest;
use Evavel\Models\SettingListing;

class Scheduler extends SettingListing
{
	use HasTimeOptions;
	use FieldsShiftEvent;
	use ShiftCalculations;
	use TablesCalculations;
	use ManagePayments;

	public static $table_name = 'restaurant_setting';
	public static $pivot_tenant_field = 'restaurant_id';
	public static $component_list_item = 'item-shift';

	public const AVAILABILITY_ALL_TABLES = 'tables';
	public const AVAILABILITY_SPECIFIC_TABLES = 'specific_tables';
	public const AVAILABILITY_VOLUME_TOTAL = 'volume_total';
	public const AVAILABILITY_VOLUME_SLOTS = 'volume_slots';

	public static function configuration(AppSettingsRequest $request)
	{
		return [
			'mode' => 'SettingListingWithTabs'
		];
	}
}
